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
 * This is the model class for table "x2_opportunities".
 * 
 * @package X2CRM.modules.opportunities.models
 */
class Opportunity extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Opportunity the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_opportunities'; }

	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/opportunities'
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		);
	}

	public static function getNames() {
		$arr=Opportunity::model()->findAll();
		$names=array(0=>"None");
		foreach($arr as $opportunity){
			$names[$opportunity->id]=$opportunity->name;
		}
		return $names;
	}

	public static function parseUsers($userArray){
		return implode(', ',$userArray);
	}

	public static function parseUsersTwo($arr){
		$str="";
		foreach($arr as $user=>$name){
			$str.=$user.", ";
		}
		$str=substr($str,0,strlen($str)-2);
						
		return $str;
	}

	public static function parseContacts($contactArray){
		return implode(' ',$contactArray);
	}

	public static function parseContactsTwo($arr){
		$str="";
		foreach($arr as $id=>$contact){
			$str.=$id." ";
		}
		return $str;
	}

	public static function getOpportunityLinks($accountId) {

		$allOpportunities = CActiveRecord::model('Opportunity')->findAllByAttributes(array('accountName'=>$accountId));

		$links = array();
		foreach($allOpportunities as $model) {
			$links[] = CHtml::link($model->name,array('/opportunities/view','id'=>$model->id));
		}
		return implode(', ',$links);
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
			if($username!='' && !is_numeric($username))
				$data[]=User::model()->findByAttributes(array('username'=>$username));
			elseif(is_numeric($username))
				$data[]=Groups::model()->findByPK($username);
		}
		
		$temp=array();
		if(isset($data)){
			foreach($data as $item){
				if(isset($item)){
					if($item instanceof Users)
						$temp[$item->username]=$item->firstName.' '.$item->lastName;
					else
						$temp[$item->id]=$item->name;
				}
			}
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

	public function search() {
		$criteria=new CDbCriteria;
		// $parameters=array("condition"=>"salesStage='Working'",'limit'=>ceil(ProfileChild::getResultsPerPage()));
		$parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}
	
	public function searchBase($criteria) {

		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Opportunity'));
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
		
		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}
        
 
}