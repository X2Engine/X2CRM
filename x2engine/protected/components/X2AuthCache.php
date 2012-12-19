<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

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
		if(mt_rand(0,$this->gcProbability) === 0)
			$this->gc();
	}

	/**
	 * Looks up all the auth results for the specified user ID.
	 * @param integer $userId the user ID, defaults to current user
	 * @return array associative array of authItem names and boolean permission values
	 */
	public function loadAuthCache($userId=null) {

		if($userId === null)
			$userId = Yii::app()->user->getId();
		if(empty($userId))
			return array();
			

		$time = time();
		$sql = 'SELECT authItem, value FROM '.$this->tableName.' WHERE userId='.$userId.' AND (expire=0 OR expire>'.time().')';

		$db=$this->getDbConnection();
		if($db->queryCachingDuration>0) {
			$duration=$db->queryCachingDuration;
			$db->queryCachingDuration=0;
			$rows = $db->createCommand($sql)->queryAll();
			$db->queryCachingDuration=$duration;
		}
		else
			$rows = $db->createCommand($sql)->queryAll();

		$results = array();

		foreach($rows as &$row)
			$results[$row['authItem']] = (bool)$row['value'];
		return $results;
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * @param string $userId the user ID, defaults to current user
	 * @param string $authItem the authItem
	 * @return bool the cached permission value, or null if the value is not in the cache or expired.
	 */
	public function checkResult($userId,$authItem) {
		if(empty($userId))
			return null;
	
		$time=time();
		$sql="SELECT value FROM {$this->tableName} WHERE userId=$userId AND authItem='$authItem' AND (expire=0 OR expire>$time)";
		$db=$this->getDbConnection();
		if($db->queryCachingDuration>0) {
			$duration=$db->queryCachingDuration;
			$db->queryCachingDuration=0;
			$result=$db->createCommand($sql)->queryScalar();
			$db->queryCachingDuration=$duration;
		} else
			$result = $db->createCommand($sql)->queryScalar();
		if($result === false)
			return null;
		else
			return (bool)$result;
	}


	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $userId the user ID
	 * @param string $authItem the authItem
	 * @param string $value the value to be cached
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function addResult($userId,$authItem,$value) {

		$expire = time() + 259200;	// expires in 3 days
			
		$value = $value? '1' : '0';	// convert value to 1 or 0
		
		$sql="REPLACE INTO {$this->tableName} (userId,authItem,expire,value) VALUES ($userId,'$authItem',$expire,$value)";
		try {
			$command = $this->getDbConnection()->createCommand($sql)->execute();
			return true;
		} catch(Exception $e) {
			return false;
		}
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CException if {@link connectionID} does not point to a valid application component.
	 */
	public function getDbConnection() {
		if($this->_db!==null)
			return $this->_db;
		else if(($id=$this->connectionID)!==null)
		{
			if(($this->_db=Yii::app()->getComponent($id)) instanceof CDbConnection)
				return $this->_db;
			else
				throw new CException(Yii::t('yii','CDbCache.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$id)));
		}
		else
		{
			$dbFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'cache-'.Yii::getVersion().'.db';
			return $this->_db=new CDbConnection('sqlite:'.$dbFile);
		}
	}

	/**
	 * Removes the expired data values.
	 */
	protected function gc() {
		$this->getDbConnection()->createCommand('DELETE FROM '.$this->tableName.' WHERE expire>0 AND expire<'.time())->execute();
	}

	/**
	 * Deletes all values from cache.
	 * @return boolean whether the flush operation was successful.
	 */
	public function clear() {
		$this->getDbConnection()->createCommand('DELETE FROM '.$this->tableName.'')->execute();
		return true;
	}
}
