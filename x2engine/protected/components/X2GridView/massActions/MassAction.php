<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

abstract class MassAction extends CComponent {

    const SESSION_KEY_PREFIX = 'superMassAction';
    const SESSION_KEY_PREFIX_PASS_CONFIRM = 'superMassActionPassConfirm';

    /**
     * If true, user must enter their password before super mass action can proceed 
     */
    protected $requiresPasswordConfirmation = false;

    // used to hold success, warning, and error messages
    protected static $successFlashes = array ();
    protected static $noticeFlashes = array ();
    protected static $errorFlashes = array ();

    protected static function getFlashes () {
        return array (
            'notice' => self::$noticeFlashes,
            'success' => self::$successFlashes,
            'error' => self::$errorFlashes
        );
    }

    /**
     * Echoes flashes in the flashes arrays
     */
    public static function echoFlashes () {
        echo CJSON::encode (self::getFlashes ());
    }

    abstract public function execute ($gvSelection);

    /**
     * Helper method for superExecute. Returns array of ids of records in search results.
     * @param string $modelClass
     * @return array
     */
    protected function getIdsFromSearchResults ($modelClass) {
        // copy sort & filter parameters from POST data to GET superglobal so
        // that ERememberFiltersBehavior and SmartDataProviderBehavior will filter/sort records
        // properly
        if (isset ($_POST[$modelClass])) {
            $_GET[$modelClass] = $_POST[$modelClass];
        }
        if (isset ($_POST[$modelClass.'_sort'])) {
            $_GET[$modelClass.'_sort'] = $_POST[$modelClass.'_sort'];
        }

        // here data provider is retrieved for the sole purpose of using it's criteria object
        $model = new $modelClass ('search');
        $dataProvider = $model->search (null, 0); // page size set to 0 to improve performance
        $dataCriteria = $dataProvider->getCriteria ();
        $table = $model->tableName ();
        $orderByClause = isset ($dataCriteria->order) ? 
            ('order by ' . $dataCriteria->order) : '';
        $whereClause = isset ($dataCriteria->condition) ? 
            ('where ' . $dataCriteria->condition) : '';
        $command = Yii::app()->db->createCommand ("
            select id
            from $table as $dataCriteria->alias
            $whereClause
            $orderByClause
        ");
        //AuxLib::debugLogR ($command->getText ());
        $ids = $command->queryAll (true, $dataCriteria->params);
        foreach ($ids as $i => $row) {
            $ids[$i] = $row['id'];
        }

        return $ids;
    }

    /**
     * Execute mass action on next batch of records
     * @param string $massAction
     * @param string $uid
     */
    public function superExecute ($massAction, $uid) {
        // ensure that for super mass deletion, user confirmed deletion with password
        if ($this->requiresPasswordConfirmation && (empty ($uid) || 
            !isset ($_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid]) ||
            !$_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid])) {

            throw new CHttpException (
                401, Yii::t('app', 'You are not authorized to perform this action'));
        }
        if (!$this->requiresPasswordConfirmation && !empty ($uid) && 
            !isset ($_SESSION[self::SESSION_KEY_PREFIX.$uid])) { 

            /**/AuxLib::debugLogR ('Error: $uid is not empty and SESSION key is not set');
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }

        // clear saved ids if user clicked the stop button
        if (isset ($_POST['clearSavedIds']) && $_POST['clearSavedIds']) {
            if (!empty ($uid)) {
                unset ($_SESSION[self::SESSION_KEY_PREFIX.$uid]);
            }
            echo 'success';
            return;
        }

        $modelClass = Yii::app()->controller->modelClass;

        // if super mass operation hasn't started, initialize id list from which batches will
        // be retrieved
        if ($this->requiresPasswordConfirmation || empty ($uid)) {
            if (!$this->requiresPasswordConfirmation)
                $uid = uniqid (false, true);
            $ids = $this->getIdsFromSearchResults ($modelClass);
            $_SESSION[self::SESSION_KEY_PREFIX.$uid] = $ids;
        }

        // grab next batch of ids from session
        $selectedRecords = $_SESSION[self::SESSION_KEY_PREFIX.$uid];
        $selectedRecordsCount = count ($selectedRecords);
        $batchSize = Yii::app()->settings->massActionsBatchSize;
        $batchSize = $selectedRecordsCount < $batchSize ? $selectedRecordsCount : $batchSize;
        $batch = array ();
        for ($i = 0; $i < $batchSize; $i++) {
            $batch[] = array_shift ($selectedRecords);
        }
        $_SESSION[self::SESSION_KEY_PREFIX.$uid] = $selectedRecords;

        // execute mass action on batch
        $successes = $this->execute ($batch);

        $response = $this->generateSuperMassActionResponse ($successes, $selectedRecords, $uid);
        echo CJSON::encode ($response);
    }

    /**
     * @param int $successes number of records for which mass action was successful
     * @param array records yet to be acted upon
     * @return array response data for super mass action request 
     */
    protected function generateSuperMassActionResponse ($successes, $selectedRecords, $uid) {
        $flashes = self::getFlashes ();
        $response = $flashes;
        $response['successes'] = $successes;
        $response['uid'] = $uid;
        if (count ($selectedRecords) === 0) {
            $response['complete'] = true;
        } else {
            $response['batchComplete'] = true;
        }
        return $response;
    }

    public static function superMassActionPasswordConfirmation () {
        if (!isset ($_POST['password'])) 
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        $loginForm = new LoginForm;
        $loginForm->username = Yii::app()->params->profile->username;
        $loginForm->password = $_POST['password'];
        if ($loginForm->validate ()) {
            $uid = EncryptUtil::secureUniqueIdHash64 ();
            $_SESSION[self::SESSION_KEY_PREFIX_PASS_CONFIRM.$uid] = true;
            echo CJSON::encode (array (true, $uid));
        } else {
            echo CJSON::encode (array (false, Yii::t('app', 'incorrect password')));
        }
    }

}
