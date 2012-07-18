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
 * This is the model class for table "x2_actions".
 */
class Actions extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Actions the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_actions';
	}
	
	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/actions'
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('actionDescription','required','on'=>'insert'),	// code-generated actions may not have a description
			array('allDay','boolean'),
			array('createDate, completeDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('id,assignedTo,actionDescription,visibility,associationId,associationType,associationName,dueDate,
				priority,type,createDate,complete,reminder,completedBy,completeDate,lastUpdated,updatedBy,color','safe')
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

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	
	public function attributeLabels() {
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Actions'));
		$arr=array();
		foreach($fields as &$field)
			$arr[$field->fieldName] = Yii::t('actions',$field->attributeLabel);
		
		return $arr;
	}
	
	/**
	 * return an array of possible colors for an action
	 */
	public static function getColors() {
		return array(
		    '#3366CC'=>Yii::t('actions', 'Blue'),
		    'Green'=>Yii::t('actions', 'Green'),
		    'Red'=>Yii::t('actions', 'Red'),
		    'Orange'=>Yii::t('actions', 'Orange'),
		    'Black'=>Yii::t('actions', 'Black'),
		);
	}

	public static function completeAction($id) {
		$action=Actions::model()->findByPk($id);
		$action->complete="Yes";
		$action->completedBy=Yii::app()->user->getName();
		$action->completeDate = time();
		$action->update();
	}

	public function getName() {
		return $this->actionDescription;
	}
	
	public function getLink($length = 0) {
	
		$text = $this->owner->name;
		if($length && strlen($text) > $length)
			$text = substr($text,0,$length).'...';
		return CHtml::link($text,array($this->viewRoute.'/'.$this->owner->id));
	}
	
	
	public static function parseStatus($dueDate) {

		if (empty($dueDate))	// there is no due date
			return false;
		if (!is_numeric($dueDate))
			$dueDate = strtotime($dueDate);	// make sure $date is a proper timestamp

		//$due = getDate($dueDate);
		//$dueDate = mktime(23,59,59,$due['mon'],$due['mday'],$due['year']); // if there is no time, give them until 11:59 PM to finish the action
		
		//$dueDate += 86399;	
	
		$timeLeft = $dueDate - time();	// calculate how long till due date
		if ($timeLeft < 0)
			return Yii::t('actions','Overdue {time}',array('{time}'=>Actions::formatDate($dueDate)));	// overdue by X hours/etc

		else
			return Yii::t('actions','Due {date}',array('{date}'=>Actions::formatDate($dueDate)));
	}
	
	public static function formatDate($date) {

		if (!is_numeric($date))
			$date = strtotime($date);	// make sure $date is a proper timestamp

		$now = getDate();			// generate date arrays
		$due = getDate($date);	// for calculations
		//$date = mktime(23,59,59,$due['mon'],$due['mday'],$due['year']);	// give them until 11:59 PM to finish the action
		//$due = getDate($date);
	
		if ($due['year'] == $now['year']) {		// is the due date this year?
			if ($due['yday'] == $now['yday'])		// is the due date today?
				return Yii::t('app','Today');
			else if ($due['yday'] == $now['yday']+1)	// is it tomorrow?
				return Yii::t('app','Tomorrow');
			else 
				return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'),$date);	// any other day this year
		} else {
			return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'),$date);	// due date is after this year
		}
	}
	
	public static function formatTimeLength($seconds) {
		$seconds = abs($seconds);
		if($seconds < 60)
			return Yii::t('app','{n} second|{n} seconds',$seconds);	// less than 1 min
		if($seconds < 3600)
			return Yii::t('app','{n} minute|{n} minutes',floor($seconds/60));	// minutes (less than an hour)
		if($seconds < 86400)
			return Yii::t('app','{n} hour|{n} hours',floor($seconds/3600));	// hours (less than a day)
		if($seconds < 5184000)
			return Yii::t('app','{n} day|{n} days',floor($seconds/86400));	// days (less than 60 days)
		else
			return Yii::t('app','{n} month|{n} months',floor($seconds/2592000));	// months (more than 90 days)
	}
	
	// finds record for the "owner" of a action, using the owner type and ID
	public static function getOwnerModel($ownerType,$ownerId) {

		if(!(empty($ownerType) || empty($ownerId))) {	// both ID and type must be set
			if($ownerType=='projects')
				return CActiveRecord::model('ProjectChild')->findByPk($ownerId);
			if($ownerType=='contacts')
				return CActiveRecord::model('Contacts')->findByPk($ownerId);
			if($ownerType=='accounts')
				return CActiveRecord::model('Accounts')->findByPk($ownerId);
			if($ownerType=='cases')
				return CActiveRecord::model('CaseChild')->findByPk($ownerId);
			if($ownerType=='sales')
				return CActiveRecord::model('Sales')->findByPk($ownerId);
		}
		return false;	// either the type is unkown, or there simply is no owner
	}
	
	// creates virtual attribute for owner's name, if exists
	public function getOwnerName() {
		$ownerModel = Actions::getOwnerModel($this->ownerType,$this->ownerId);
		if ($ownerModel)
			return $ownerModel->name;	// get name of owner
		else
			return false;
	}
	
	
	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('condition'=>"(assignedTo='Anyone' OR assignedTo='".Yii::app()->user->getName()."' OR assignedTo='' OR assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE userId='".Yii::app()->user->getId()."')) AND dueDate <= '".mktime(23,59,59)."'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));
		
		return $this->searchBase($criteria);
	}

	public function searchComplete() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"completedBy='".Yii::app()->user->getName()."' AND complete='Yes'","limit"=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAll() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"(assignedTo='".Yii::app()->user->getName()."' OR assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE userId='".Yii::app()->user->getId()."'))",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}
	
	public function searchGroup() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"visibility='1' AND complete!='Yes'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}
	
	public function searchAllGroup() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"visibility='1'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAllComplete() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"visibility='1' AND complete='Yes'","limit"=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}
	
	public function searchBase($criteria) {
		
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Actions'));
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
		
		$criteria->addCondition('(type != "workflow" AND type!="email" AND type!="event") OR type IS NULL');
		
		
		$dataProvider=new SmartDataProvider('Actions', array(
			'sort'=>array(
				'defaultOrder'=>'completeDate DESC, dueDate DESC',
			),
			'pagination'=>array(
				'pageSize'=>ceil(ProfileChild::getResultsPerPage())
			),
			'criteria'=>$criteria
		));
	
		return $dataProvider;
	}
	protected function compareLookup($field, $data){
		if(is_null($data) || $data=="") return null; 
		$type=ucfirst($field->linkType);
		if($type=='Contacts'){
			eval("\$lookupModel=$type::model()->findAllBySql('SELECT * FROM x2_$field->linkType WHERE CONCAT(firstName,\' \', lastName) LIKE \'%$data%\'');");
		}else{
			eval("\$lookupModel=$type::model()->findAllBySql('SELECT * FROM x2_$field->linkType WHERE name LIKE \'%$data%\'');");
		}
		if(isset($lookupModel) && count($lookupModel)>0){
			$arr=array();
			foreach($lookupModel as $model){
				$arr[]=$model->id;
			}
			return $arr;
		}else
			return -1;
	}
	
	protected function compareBoolean($data){
		if(is_null($data) || $data=='') return null;
		if(is_numeric($data)) return $data;
		if($data==Yii::t('actions',"Yes"))
			return 1;
		elseif($data==Yii::t('actions',"No"))
			return 0;
		else
			return -1;
	}
	
	protected function compareAssignment($data){
		if(is_null($data)) return null;
		if(is_numeric($data)){
			$models=Groups::model()->findAllBySql("SELECT * FROM x2_groups WHERE name LIKE '%$data%'");
			$arr=array();
			foreach($models as $model){
				$arr[]=$model->id;
			}
			return count($arr)>0?$arr:-1;
		}else{
			$models=User::model()->findAllBySql("SELECT * FROM x2_users WHERE CONCAT(firstName,' ',lastName) LIKE '%$data%'");
			$arr=array();
			foreach($models as $model){
				$arr[]=$model->username;
			}
			return count($arr)>0?$arr:-1;
		}
	}
}