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




/**
 * This is the model class for table "x2_sessions_token" which derives
 * from the model class 'Session'.
 *
 * @package application.models
 * @property integer $id
 * @property string $user
 * @property integer $lastUpdated
 * @property string $IP
 * @property integer $status
 */

class SessionToken extends CActiveRecord {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Session the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_sessions_token';
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('app','Session ID'),
			'user' => Yii::t('app','User'),
			'lastUpdated' => Yii::t('app','Last Updated'),
			'IP' => Yii::t('app','IP Address'),
			'status' => Yii::t('app','Login Status'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('user',$this->user,true);
		$criteria->compare('IP',$this->IP,true);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    /**
     * @param string $username
     * @return bool true if user has a recently updated session record, false otherwise
     */
	public static function isOnline ($username) {
		$record = Yii::app()->db->createCommand()
            ->select('*')
            ->from('x2_sessions_token')
            ->where('status=1 and user=:username and lastUpdated > "'.(time () - 900).
                '"', array ('username' => $username))
            ->queryAll ();

		return (!empty ($record));
    }

    /**
     * Clear session records which have timed out. Log the timeout.
     */
    public static function cleanUpSessions () {
        // Only select users with active sessions to clear out, in case there are
        // dozens of inactive users, to make things more efficient:
        // code for putting an expiration date on tokens
        /*
        $users = Yii::app()->db->createCommand()
                ->select('x2_users.id,x2_users.username')
                ->from('x2_users')
                ->rightJoin('x2_sessions_token', 'x2_sessions_token.user = x2_users.username')
                ->where('x2_users.username IS NOT NULL AND x2_users.username != ""')
                ->queryAll();
        foreach($users as $user){
            $timeout = 518400; //60*60*24*6 6 days
            $sessions = X2Model::model('SessionToken')->findAllByAttributes(
                    array('user' => $user['username']), 
                    'lastUpdated < :cutoff', 
                    array(':cutoff' => time() - $timeout));
            foreach($sessions as $session){
                SessionLog::logSession($session->user, $session->id, 'passiveTimeoutToken');
                $session->delete();
            }
        }
        */
        
        // code for putting an expiration date on tokens
        // check timeout on sessions not corresponding to any existing user
        /* 
         * $defaultTimeout = 518400; //60*60*24*6 6 days
        self::model ()->deleteAll (
            array (
                'condition' => 'lastUpdated < :cutoff and 
                    user not in (select distinct (username) from x2_users)',
                'params' => array (':cutoff' => time () - $defaultTimeout)
            )
        );
         */
        self::model ()->deleteAll (
            array (
                'condition' => 'user not in (select distinct (username) from x2_users)',
            )
        );
        
    }
    
    /*
     * Clean up 'sessionToken' cookie
     */
    public static function cleanSessionTokenCookie () {
        if(!empty(Yii::app()->request->cookies['sessionToken'])){
            $sessionTokenCookie = Yii::app()->request->cookies['sessionToken']->value;
            $activeUser = Yii::app()->db->createCommand()
                    ->select('user')
                    ->from('x2_sessions_token')
                    ->where('id = :id',array(':id'=>$sessionTokenCookie))
                    ->queryScalar();
            return empty($activeUser);
        }
        return true;
    }
    
    /**
     * 
     * cap a limit on the amount of tokens a user can possess in the database
     * and refresh all the user's tokens.
     */
    public static function cleanUpUserTokens ($user) {
        $userTokenCount = Yii::app()->db->createCommand(
            'SELECT COUNT(*) FROM x2_sessions_token WHERE user=:user')
                ->bindValues(
                    array(':user' => $user))
                ->execute(); 
        if($userTokenCount > 50) {
            $sessions = X2Model::model('SessionToken')->findAllByAttributes(
                    array('user' => '$user'));
            foreach($sessions as $session){
                $session->delete();
            }
        }
    }
}
