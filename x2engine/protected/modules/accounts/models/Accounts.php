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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_accounts".
 *
 * @package X2CRM.modules.accounts.models
 */
class Accounts extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Accounts the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_accounts';
	}

	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/accounts',
				'icon'=>'accounts_icon.png',
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array();
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public static function getNames(){
	
		$acctNames = array();
		foreach(Yii::app()->db->createCommand()->select('id,name')->from('x2_accounts')->order('name ASC')->queryAll(false) as $row)
			$acctNames[$row[0]] = $row[1];
		
		return $acctNames;
	
		// $arr=Accounts::model()->findAll();
		// $names=array('0'=>'None');
		// foreach($arr as $account){
			// $names[$account->id]=$account->name;
		// }
		// return $names;
	}

	public static function parseUsers($arr){
		$str="";
		foreach($arr as $user){
			 $str.=$user.", ";
		}
		$str=substr($str,0,strlen($str)-2);
		return $str;
	}

	public static function parseUsersTwo($arr){
		$str="";
		foreach($arr as $user=>$name){
			$str.=$user.", ";
		}
		$str=substr($str,0,strlen($str)-2);
						
		return $str;
	}

	public static function parseContacts($arr){
		$str="";
		foreach($arr as $contact){
			$str.=$contact." ";
		}
		return $str;
	}

	public static function parseContactsTwo($arr){
		$str="";
		foreach($arr as $id=>$contact){
			$str.=$id." ";
		}
		return $str;
	}

	public static function editContactArray($arr, $model) {

		$pieces=explode(" ",$model->associatedContacts);
		unset($arr[0]);

		foreach($pieces as $contact){
			if(array_key_exists($contact,$arr)){
				unset($arr[$contact]);
			}
		}
		
		return $arr;
	}

	public static function editUserArray($arr, $model) {

		$pieces=explode(', ',$model->assignedTo);
		unset($arr['Anyone']);
		unset($arr['admin']);
		foreach($pieces as $user){
			if(array_key_exists($user,$arr)){
				unset($arr[$user]);
			}
		}
		return $arr;
	}

	public static function editUsersInverse($arr) {
		
		$data=array();
		
		foreach($arr as $username){
			$data[]=User::model()->findByAttributes(array('username'=>$username));
		}
		
		$temp=array();
			foreach($data as $item){
				if(isset($item))
					$temp[$item->username]=$item->firstName.' '.$item->lastName;
			}
		return $temp;
	}

	public static function editContactsInverse($arr) {
		$data=array();
		
		foreach($arr as $id){
			if($id!='')
				$data[]=CActiveRecord::model('Contacts')->findByPk($id);
		}
		$temp=array();
		
		foreach($data as $item){
			$temp[$item->id]=$item->firstName.' '.$item->lastName;
		}
		return $temp;
	}

	public static function getAvailableContacts($accountId = 0) {
	
		$availableContacts = array();
		
		$criteria = new CDbCriteria;
		$criteria->addCondition("accountId='$accountId'");
		$criteria->addCondition(array("accountId=''"),'OR');
		
		
		$contactRecords = CActiveRecord::model('Contacts')->findAll($criteria);
		foreach($contactRecords as $record)
			$availableContacts[$record->id] = $record->name;

		return $availableContacts;
	}
		
	
	public static function getContacts($accountId) {
		$contacts = array();
		$contactRecords = CActiveRecord::model('Contacts')->findAllByAttributes(array('accountId'=>$accountId));
		if(!isset($contactRecords))
			return array();
		
		foreach($contactRecords as $record)
			$contacts[$record->id] = $record->name;
		
		return $contacts;
	}
	
	public static function setContacts($contactIds,$accountId) {
	
		$account = CActiveRecord::model('Accounts')->findByPk($accountId);
		
		if(!isset($account))
			return false;
		
		// get all contacts currently associated
		$oldContacts = CActiveRecord::model('Contacts')->findAllByAttributes(array('accountId'=>$accountId));
		foreach($oldContacts as $contact) {
			if(!in_array($contact->id,$contactIds)) {
				$contact->accountId = 0;
				$contact->company = '';		// dissociate if they are no longer in the list
				$contact->save();
			}
		}
		
		// now set association for all contacts in the list
		foreach($contactIds as $id) {
			$contactRecord = CActiveRecord::model('Contacts')->findByPk($id);
			$contactRecord->accountId = $account->id;
			$contactRecord->company = $account->name;
			$contactRecord->save();
		}
		return true;
	}

	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Accounts'));
                foreach($fields as $field){
                    $fieldName=$field->fieldName;
                    switch($field->type){
                        case 'boolean':
                            $criteria->compare($field->fieldName,$this->compareBoolean($this->$fieldName), true);
                            break;
                        case 'link':
                            $criteria->compare($field->fieldName,$this->compareLookup($field, $this->$fieldName), true);
                            break;
                        case 'assignment':
                            $criteria->compare($field->fieldName,$this->compareAssignment($this->$fieldName), true);
                            break;
                        default:
                            $criteria->compare($field->fieldName,$this->$fieldName,true);
                    }
                    
                }

		
		$dataProvider=new SmartDataProvider(get_class($this), array(
			'sort'=>array('defaultOrder'=>'name ASC'),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
		$arr=$dataProvider->getData();
		foreach($arr as $account){
			$account->assignedTo=User::getUserLinks($account->assignedTo);
			$account->associatedContacts=Contacts::getContactLinks($account->associatedContacts);
		}
		$dataProvider->setData($arr);

		return $dataProvider;
	}
 
}
