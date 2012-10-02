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

/**
 * Changelog recording behavior class.
 * 
 * X2ChangeLogBehavior is a CActiveRecordBehavior which automatically saves changelog 
 * data when a record is saved. It also looks up any applicable notification criteria 
 * and takes the appropriate action (create a notification, create a new action, 
 * reassign the record, etc.)
 *
 * @package X2CRM.components
 * @property string $baseRoute The default module/controller this model "belongs" to
 * @property string $viewRoute The default action to view this model
 * @property string $autoCompleteSource The action to user for autocomplete data
 */
class X2ChangeLogBehavior extends CActiveRecordBehavior  {


	private $_oldAttributes = array();

	public function attach($owner) {
		parent::attach($owner);
		
		if(!$this->owner->isNewRecord && empty($this->_oldAttributes))	// if the behavior was attached manually,
			$this->_oldAttributes = $this->owner->getAttributes();		// afterFind() won't be fired so let's get the attributes manually
	}

	public function afterSave($event) {
		if ($this->owner->isNewRecord) {

			// $log=new ActiveRecordLog;
			// $log->description=  'User ' . Yii::app()->user->Name 
									// . ' created ' . get_class($this->owner) 
									// . '[' . $this->owner->getPrimaryKey() .'].';
			// $log->action=       'CREATE';
			// $log->model=        get_class($this->owner);
			// $log->idModel=      $this->owner->getPrimaryKey();
			// $log->field=        '';
			// $log->creationdate= new CDbExpression('NOW()');
			// $log->userid=       Yii::app()->user->id;
			// $log->save();
		} else {

		
		
		
			// new attributes
			$newattributes = $this->owner->getAttributes();
			$oldattributes = $this->getOldAttributes();

			// compare old and new
			foreach ($newattributes as $name => $value) {
				if (!empty($oldattributes)) {
					$old = $oldattributes[$name];
				} else {
					$old = '';
				}

				if ($value != $old) {
					//$changes = $name . ' ('.$old.') => ('.$value.'), ';

					$log=new ActiveRecordLog;
					$log->description=  'User ' . Yii::app()->user->Name 
											. ' changed ' . $name . ' for ' 
											. get_class($this->owner) 
											. '[' . $this->owner->getPrimaryKey() .'].';
					$log->action = 'CHANGE';
					$log->model = get_class($this->owner);
					$log->idModel = $this->owner->getPrimaryKey();
					$log->field = $name;
					$log->creationdate = new CDbExpression('NOW()');
					$log->userid = Yii::app()->user->id;
					$log->save();
				}
			}
		}
	}

	/**
	 * Logs the deletion of the model
	 */
	public function afterDelete($event) {
		
	}

	/**
	 * Saves attributes on initial model lookup
	 */
	public function afterFind($event) {
		$this->_oldAttributes = $this->owner->getAttributes();
	}

	public function getOldAttributes() {
		return $this->_oldAttributes;
	}

	
	
	protected function calculateChanges(){
		
		
		
	
	
		$arr=array();
		$keys=array_keys($new);
		for($i=0;$i<count($keys);$i++){
			if($old[$keys[$i]]!=$new[$keys[$i]]){
				$arr[$keys[$i]]=$new[$keys[$i]];
				$allCriteria=Criteria::model()->findAllByAttributes(array('modelType'=>$this->modelClass,'modelField'=>$keys[$i]));
				foreach($allCriteria as $criteria){
					if(($criteria->comparisonOperator=="=" && $new[$keys[$i]]==$criteria->modelValue)
								|| ($criteria->comparisonOperator==">" && $new[$keys[$i]]>=$criteria->modelValue)
								|| ($criteria->comparisonOperator=="<" && $new[$keys[$i]]<=$criteria->modelValue)
								|| ($criteria->comparisonOperator=="change" && $new[$keys[$i]]!=$old[$keys[$i]])){
								
						$users = explode(", ",$criteria->users);
								
						if($criteria->type=='notification') {
							foreach($users as $user) {

								$notif = new Notification;
								$notif->type = 'change';
								$notif->fieldName = $keys[$i];
								
								if($criteria->comparisonOperator == 'change') {
									$notif->comparison = 'change';				// if the criteria is just 'changed'
									$notif->value = $new[$keys[$i]];			// record the new value
								} else {
									$notif->comparison = $criteria->comparisonOperator;		// otherwise record the operator type
									$notif->value = substr($criteria->modelValue,0,250);	// and the comparison value
								}
								$notif->user = $user;
								$notif->createdBy = Yii::app()->user->getName();
								$notif->createDate = time();
								$notif->viewed=0;
								$notif->record=$this->modelClass.":".$new['id'];
								$notif->save();

						/* 		$notif=new Notifications;
								$profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
								if($criteria->comparisonOperator=="="){
									$notif->text="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator==">"){
									$notif->text="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator=="<"){
									$notif->text="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator=="change"){
									$notif->text="A record of type ".$this->modelClass." has had its $criteria->modelField field changed from ".$old[$keys[$i]]." to ".$new[$keys[$i]]." by ".Yii::app()->user->getName();
								}
								$notif->user=$user;
								$notif->createDate=time();
								$notif->viewed=0;
								$notif->record=$this->modelClass.":".$new['id'];
								$notif->save(); */
							}
						} else if($criteria->type=='action') {
							$users=explode(", ",$criteria->users);
							foreach($users as $user){
								$action=new Actions;
								$action->assignedTo=$user;
								if($criteria->comparisonOperator=="="){
									$action->actionDescription="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator==">"){
									$action->actionDescription="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator=="<"){
									$action->actionDescription="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator=="change"){
									$action->actionDescription="A record of type ".$this->modelClass." has had its $criteria->modelField field changed from ".$old[$keys[$i]]." to ".$new[$keys[$i]]." by ".Yii::app()->user->getName();
								}
								$action->dueDate=mktime('23','59','59');
								$action->createDate=time();
								$action->lastUpdated=time();
								$action->updatedBy='admin';
								$action->visibility=1;
								$action->associationType=strtolower($this->modelClass);
								$action->associationId=$new['id'];
								$model=CActiveRecord::model($this->modelClass)->findByPk($new['id']);
								$action->associationName=$model->name;
								$action->save();
							}
						} else if($criteria->type=='assignment') {
							$model->assignedTo = $criteria->users;
							$model->save();
							
							$notif = new Notification;
							$notif->user = $model->assignedTo;
							$notif->createdBy = Yii::app()->user->getName();
							$notif->createDate = time();
							$notif->type = 'assignment';
							$notif->modelType = $this->modelClass;
							$notif->modelId = $new['id'];
							$notif->save();

							// $notif=new Notifications;  
							// $notif->text="A record of type ".$this->modelClass." has been re-assigned to you.";
							// $notif->user=$model->assignedTo;
							// $notif->createDate=time();
							// $notif->viewed=0;
							// $notif->record=$this->modelClass.":".$new['id'];
							// $notif->save();
						} 
					}
				}
			}
		}
		$str='';
		foreach($arr as $key=>$item){
				$str.="<b>$key</b> <u>FROM:</u> $old[$key] <u>TO:</u> $item <br />";
		}
		return $str;
	}
	
	
	
	
	
	
	
	

}