<?php

/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




class X2AuthCache extends CApplicationComponent {

    /**
     * @var string the ID of the {@link CDbConnection} application component.
     */
    public $connectionID;

    /**
     * @var string the name of the auth cache table.
     */
    public $tableName = 'x2_auth_cache';

    /**
     * @var integer how often to garbage collect (delete expired values).
     * GC is performed using an N-sided coin flip
     */
    public $gcProbability = 100;
    private $_db;

    /**
     * Initializes this application component.
     * Trimmed down version of {@link CDbCache::init}.
     */
    public function init() {
        parent::init();

        $db = $this->getDbConnection();
        $db->setActive(true);

        // garbage collect every now and then
        if (mt_rand(0, $this->gcProbability) === 0)
            $this->gc();
    }

    /**
     * Looks up all the auth results for the specified user ID.
     * @param integer $userId the user ID, defaults to current user
     * @return array associative array of authItem names and boolean permission values
     */
    public function loadAuthCache($userId = null) {

        if ($userId === null)
            $userId = Yii::app()->user->getId();
        if (empty($userId))
            return array();

        $time = time();
        $sql = 
            'SELECT authItem, value, params 
             FROM ' . $this->tableName . ' 
             WHERE userId=' . $userId . ' AND (expire=0 OR expire>' . time() . ') 
             LIMIT 200';

        $db = $this->getDbConnection();
        if ($db->queryCachingDuration > 0) {
            $duration = $db->queryCachingDuration;
            $db->queryCachingDuration = 0;
            $rows = $db->createCommand($sql)->queryAll();
            $db->queryCachingDuration = $duration;
        } else
            $rows = $db->createCommand($sql)->queryAll();

        $results = array();

        foreach ($rows as &$row) {
            $results[$row['authItem']][$row['params']] = $row['value'];
        }

        return $results;
    }

    /**
     * Retrieves a value from cache with a specified key.
     * @param string $userId the user ID, defaults to current user
     * @param string $authItem the authItem
     * @return bool the cached permission value, or null if the value is not in the cache or expired.
     */
//    public function checkResult($userId, $authItem) {
//        if (empty($userId))
//            return null;
//
//        $time = time();
//        $sql = "SELECT value FROM {$this->tableName} WHERE userId=$userId AND authItem='$authItem' AND (expire=0 OR expire>$time)";
//        $db = $this->getDbConnection();
//        if ($db->queryCachingDuration > 0) {
//            $duration = $db->queryCachingDuration;
//            $db->queryCachingDuration = 0;
//            $result = $db->createCommand($sql)->queryScalar();
//            $db->queryCachingDuration = $duration;
//        } else
//            $result = $db->createCommand($sql)->queryScalar();
//        if ($result === false)
//            return null;
//        else
//            return (bool) $result;
//    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $userId the user ID
     * @param string $authItem the authItem
     * @param string $value the value to be cached
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    private $_authCacheSize = null;
    public function addResult($userId, $authItem, $value, $cacheParams = array()) {
        $expire = time() + 259200; // expires in 3 days

        $value = $value ? '1' : '0'; // convert value to 1 or 0

        $cacheStr = json_encode($cacheParams);

        $sql = "REPLACE INTO {$this->tableName} (userId,authItem,expire,value,params) VALUES ($userId,'$authItem',$expire,$value,:cacheStr)";
        try {
            $command = $this->getDbConnection()->createCommand($sql)->execute(array(':cacheStr' => $cacheStr));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return CDbConnection the DB connection instance
     * @throws CException if {@link connectionID} does not point to a valid application component.
     */
    public function getDbConnection() {
        if ($this->_db !== null)
            return $this->_db;
        else if (($id = $this->connectionID) !== null) {
            if (($this->_db = Yii::app()->getComponent($id)) instanceof CDbConnection)
                return $this->_db;
            else
                throw new CException(Yii::t('yii', 'CDbCache.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.', array('{id}' => $id)));
        }
        else {
            $dbFile = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'cache-' . Yii::getVersion() . '.db';
            return $this->_db = new CDbConnection('sqlite:' . $dbFile);
        }
    }

    /**
     * Removes the expired data values.
     */
    protected function gc() {
        $this->getDbConnection()->createCommand('DELETE FROM ' . $this->tableName . ' WHERE expire>0 AND expire<' . time())->execute();
    }

    /**
     * Deletes all values from cache.
     * @return boolean whether the flush operation was successful.
     */
    public function clear() {
        $this->getDbConnection()->createCommand('DELETE FROM ' . $this->tableName . '')->execute();
        return true;
    }

}
