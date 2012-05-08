<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/**
 * This is the model class for table "x2_users".
 *
 * The followings are the available columns in table 'x2_users':
 * @property integer $id
 * @property string $firstName
 * @property string $lastName
 * @property string $username
 * @property string $password
 * @property string $title
 * @property string $department
 * @property string $officePhone
 * @property string $cellPhone
 * @property string $homePhone
 * @property string $address
 * @property string $backgroundInfo
 * @property string $emailAddress
 * @property integer $status
 * @property string $lastUpdated
 * @property string $updatedBy
 * @property string $recentItems
 * @property string $topContacts
 * @property integer $lastLogin
 * @property integer $login
 */
class User extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_users'; }

	/**
	 * @return string the route to view this model
	 */
	public function getDefaultRoute() { return '/profile/'; }
	
	/**
	 * @return string the route to this model's AutoComplete data source
	 */
	public function getAutoCompleteSource() { return '/users/getItems'; }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('firstName, lastName, username, password, status', 'required'),
			array('status, lastLogin, login', 'numerical', 'integerOnly'=>true),
			array('firstName, username, title, updatedBy', 'length', 'max'=>20),
			array('lastName, department, officePhone, cellPhone, homePhone', 'length', 'max'=>40),
			array('password, address, emailAddress, recentItems, topContacts', 'length', 'max'=>100),
			array('lastUpdated', 'length', 'max'=>30),
			array('backgroundInfo', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, firstName, lastName, username, password, title, department, officePhone, cellPhone, homePhone, address, backgroundInfo, emailAddress, status, lastUpdated, updatedBy, recentItems, topContacts, lastLogin, login', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}


	public static function getNames() {
		
		$userNames = array();
		$query = Yii::app()->db->createCommand()
			->select('username, CONCAT(firstName," ",lastName) AS name')
			->from('x2_users')
			->where('status=1')
			->order('name ASC')
			->query();

		while(($row = $query->read()) !== false)
			$userNames[$row['username']] = $row['name'];
		natcasesort($userNames);

		return array('Anyone'=>Yii::t('app','Anyone')) + $userNames;
	}
	
	public function getName() {
		return $this->firstName.' '.$this->lastName;
	}

	public static function getProfiles(){
		$arr=CActiveRecord::model('User')->findAll();
		$names=array('0'=>Yii::t('app','All'));
		foreach($arr as $user){
			$names[$user->id]=$user->firstName." ".$user->lastName;
		}
		return $names;
	}

	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),			/* optional line */
				'defaultStickOnClear'=>false	/* optional line */
			),
		);
	}

	public static function getTopContacts() {
		$userRecord = CActiveRecord::model('User')->findByPk(Yii::app()->user->getId());

		//get array of IDs
		$topContactIds = empty($userRecord->topContacts)? array() : explode(',',$userRecord->topContacts);
		$topContacts = array();
		//get record for each ID
		foreach($topContactIds as $contactId) {
			$record = CActiveRecord::model('Contacts')->findByPk($contactId);
			if (!is_null($record))	//only include contact if the contact ID exists
				$topContacts[] = $record;
		}
		return $topContacts;
	}
	
	public static function getRecentItems() {
		$userRecord = CActiveRecord::model('User')->findByPk(Yii::app()->user->getId());

		//get array of type-ID pairs
		$recentItemsTemp = empty($userRecord->recentItems)? array() : explode(',',$userRecord->recentItems);
		$recentItems = array();

		//get record for each ID/type pair
		foreach($recentItemsTemp as $item) {
			$itemType = strtok($item,'-');
			$itemId = strtok('-');

			if($itemType=='c') {
				$record = CActiveRecord::model('Contacts')->findByPk($itemId);
				if (!is_null($record))	//only include contact if the contact ID exists
					array_push($recentItems,array('type'=>$itemType,'model'=>$record));
			} else if($itemType=='t') {
				$record = CActiveRecord::model('Actions')->findByPk($itemId);
				if (!is_null($record))	//only include action if the action ID exists
					array_push($recentItems,array('type'=>$itemType,'model'=>$record));
			}
		}
		return $recentItems;
	}

	public static function addRecentItem($type,$itemId,$userId) {
		if ($type=='c' || $type=='t') {	//only proceed if a valid type is given
			$newItem = $type.'-'.$itemId;

			$userRecord = CActiveRecord::model('User')->findByPk($userId);
			//create an empty array if recentItems is empty
			$recentItems = ($userRecord->recentItems=='')? array() : explode(',',$userRecord->recentItems);
			$existingEntry = array_search($newItem,$recentItems);	//check for a pre-existing entry
			if ($existingEntry!==false)								//if there is one,
				unset($recentItems[$existingEntry]);				//remove it
			array_unshift($recentItems,$newItem);				//add new entry to beginning

			while (count($recentItems)>10) {	//now if there are more than 10 entries,
				array_pop($recentItems);		//remove the oldest ones
			}
			$userRecord->setAttribute('recentItems',implode(',',$recentItems));
			$userRecord->save();
		}
	}

	public static function getUserLinks($users) {
		if(!is_array($users)) {
			 /* x2temp */
			if(is_numeric($users)){
				$group=Groups::model()->findByPk($users);
				if(isset($group))
					$link=CHtml::link($group->name,array('/groups/default/view','id'=>$group->id));
				else
					$link="";
				return $link;
			}
			/* end x2temp */
				if($users=='' || $users=="Anyone")
						return Yii::t('app','Anyone');
				$users = explode(', ',$users);
		}
		$links='';
			foreach($users as $user) {
				if($user=='Anyone' || $user=='Email')
					$link='';
				else if(is_numeric($user)){
					$group=Groups::model()->findByPk($users);
					if(isset($group))
						$link=CHtml::link($group->name,array('/groups/default/view','id'=>$group->id));
					else
						$link='';
					$links.=$link.", ";
				}else {
					$model = CActiveRecord::model('User')->findByAttributes(array('username'=>$user));
					if(isset($model))
						$link = CHtml::link($model->name,array('/profile/view','id'=>$model->id));
					else
						$link='';
					$links.=$link.', ';
				}
			}
			$links=substr($links,0,strlen($links)-2);
		return $links;
	}

	public static function getEmails(){
		$userArray=User::model()->findAll();
		$emails=array('Anyone'=>Yii::app()->params['adminEmail']);
		foreach($userArray as $user){
			$emails[$user->username]=$user->emailAddress;
		}
		return $emails;
	}
	
	/**
	 * Returns the attribute labels.
	 * @return array attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id'=>Yii::t('users','ID'),
			'firstName'=>Yii::t('users','First Name'),
			'lastName'=>Yii::t('users','Last Name'),
			'username'=>Yii::t('users','Username'),
			'password'=>Yii::t('users','Password'),
			'title'=>Yii::t('users','Title'),
			'department'=>Yii::t('users','Department'),
			'officePhone'=>Yii::t('users','Office Phone'),
			'cellPhone'=>Yii::t('users','Cell Phone'),
			'homePhone'=>Yii::t('users','Home Phone'),
			'address'=>Yii::t('users','Address'),
			'backgroundInfo'=>Yii::t('users','Background Info'),
			'emailAddress'=>Yii::t('users','Email'),
			'status'=>Yii::t('users','Status'),
			'updatePassword'=>Yii::t('users','Update Password'),
			'lastUpdated'=>Yii::t('users','Last Updated'),
			'updatedBy'=>Yii::t('users','Updated By'),
			'recentItems'=>Yii::t('users','Recent Items'),
			'topContacts'=>Yii::t('users','Top Contacts'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('firstName',$this->firstName,true);
		$criteria->compare('lastName',$this->lastName,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('department',$this->department,true);
		$criteria->compare('officePhone',$this->officePhone,true);
		$criteria->compare('cellPhone',$this->cellPhone,true);
		$criteria->compare('homePhone',$this->homePhone,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('backgroundInfo',$this->backgroundInfo,true);
		$criteria->compare('emailAddress',$this->emailAddress,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);
		$criteria->compare('updatedBy',$this->updatedBy,true);
		$criteria->compare('recentItems',$this->recentItems,true);
		$criteria->compare('topContacts',$this->topContacts,true);
		$criteria->compare('lastLogin',$this->lastLogin);
		$criteria->compare('login',$this->login);

		return new SmartDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
