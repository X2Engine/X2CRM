<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

class UserChild extends Users {

	public static function getNames() {
		$order = 'desc';
		$userArray = CActiveRecord::model('UserChild')->findAll();
		$names = array('' => 'Anyone');
		foreach ($userArray as $user) {
			$first = $user->firstName;
			$last = $user->lastName;
			$userName = $user->username;
			$name = $first . ' ' . $last;
			$names[$userName] = $name;
		}
		return $names;
	}
	
	public function getName() {
		return $this->firstName.' '.$this->lastName;
	}

	public static function getProfiles(){
		$arr=CActiveRecord::model('UserChild')->findAll();
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
		$userRecord = CActiveRecord::model('Users')->findByPk(Yii::app()->user->getId());

		//get array of IDs
		$topContactIds = empty($userRecord->topContacts)? array() : explode(',',$userRecord->topContacts);
		$topContacts = array();
		//get record for each ID
		foreach($topContactIds as $contactId) {
			$record = CActiveRecord::model('ContactChild')->findByPk($contactId);
			if (!is_null($record))	//only include contact if the contact ID exists
				$topContacts[] = $record;
		}
		return $topContacts;
	}
	
	public static function getRecentItems() {
		$userRecord = CActiveRecord::model('Users')->findByPk(Yii::app()->user->getId());

		//get array of type-ID pairs
		$recentItemsTemp = empty($userRecord->recentItems)? array() : explode(',',$userRecord->recentItems);
		$recentItems = array();

		//get record for each ID/type pair
		foreach($recentItemsTemp as $item) {
			$itemType = strtok($item,'-');
			$itemId = strtok('-');

			if($itemType=='c') {
				$record = CActiveRecord::model('ContactChild')->findByPk($itemId);
				if (!is_null($record))	//only include contact if the contact ID exists
					array_push($recentItems,array('type'=>$itemType,'model'=>$record));
			} else if($itemType=='t') {
				$record = CActiveRecord::model('ActionChild')->findByPk($itemId);
				if (!is_null($record))	//only include action if the action ID exists
					array_push($recentItems,array('type'=>$itemType,'model'=>$record));
			}
		}
		return $recentItems;
	}

	public static function addRecentItem($type,$itemId,$userId) {
		if ($type=='c' || $type=='t') {	//only proceed if a valid type is given
			$newItem = $type.'-'.$itemId;

			$userRecord = CActiveRecord::model('Users')->findByPk($userId);
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

	public static function getUserLinks($str) {
		if($str=='')
			return Yii::t('app','None');
		$pieces = explode(', ',$str);
		$links='';
			foreach($pieces as $user) {
				if($user=='Anyone' || $user=='Email')
					$link='';
				else {
					$model = CActiveRecord::model('UserChild')->findByAttributes(array('username'=>$user));
                                        if(isset($model))
                                            $link = CHtml::link($model->firstName.' '.$model->lastName,array('profile/view','id'=>$model->id));
                                        else
                                            $link='';
					$links.=$link.', ';
				}
			}
			$links=substr($links,0,strlen($links)-2);
		return $links;
	}

	public static function getEmails(){
		$userArray=UserChild::model()->findAll();
		$emails=array('Anyone'=>Yii::app()->params['adminEmail']);
		foreach($userArray as $user){
			$emails[$user->username]=$user->emailAddress;
		}
		return $emails;
	}

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

}
