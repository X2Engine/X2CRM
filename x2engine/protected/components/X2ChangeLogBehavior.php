<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

	public function events() {
		return array_merge(parent::events(),array(
			'onAfterCreate'=>'afterCreate',
			'onAfterUpdate'=>'afterUpdate',
		));
	}

	public function afterCreate($event) {
		
		$model = $this->getOwner();
		
		$api = 0;	// FIX THIS
		
		X2Flow::trigger('record_created',array('model'=>$model));
		
		
		$event = new Events;
		$event->visibility = $model->hasAttribute('visibility')? $model->visibility : 1;
		$event->associationType = get_class($model);
		$event->associationId = $model->id;
		$event->user = Yii::app()->user->getName();
		$event->type = 'record_create';
		// if(!$model instanceof Contacts || $api==0) // Event creation already handled by web lead.
		$event->save(); 
		
		if($model->hasAttribute('assignedTo')) {
			if(!empty($model->assignedTo) && $model->assignedTo != Yii::app()->user->getName() && $model->assignedTo != 'Anyone') {
				$notif = new Notification;
				$notif->user = $model->assignedTo;
				$notif->createdBy = ($api == 1) ? 'API' : Yii::app()->user->getName();
				$notif->createDate = time();
				$notif->type = 'create';
				$notif->modelType = get_class($model);
				$notif->modelId = $model->id;
				$notif->save();
			}
		}
	}
	
	/**
	 * Triggers record_updated, runs changelog calculations and checks notification criteria (soon to be removed)
	 */
	public function afterUpdate($event) {
		X2Flow::trigger('record_updated',array(
			'model'=>$this->getOwner()
		));
		
		$changes = $this->getChanges();
		$this->updateChangelog($changes);
	}
	
	
	/**
	 * Logs the deletion of the model
	 */
	public function afterDelete($event) {
		$modelClass = get_class($this->getOwner());
		if($modelClass === 'Actions' && $this->getOwner()->workflowId !== null)		// no deletion events for workflow actions, that's somebody else's problem
			return;
		
		$event = new Events();
		$event->type='record_deleted';
		$event->associationType = $modelClass;
		$event->associationId = $this->getOwner()->id;
        if($this->getOwner()->hasAttribute('visibility')){
            $event->visibility=$this->getOwner()->visibility;
        }
		$event->text = $this->getOwner()->name;
		$event->user = Yii::app()->user->getName();
		$event->save();
		
		$log = new Changelog;
		$log->type = $modelClass;
		$log->itemId = $this->getOwner()->id;
		$log->recordName = $this->getOwner()->name;
		$log->changed = 'delete';
		
		$log->changedBy = Yii::app()->user->getName();
		$log->timestamp = time();

		$log->save();
		
		X2Flow::trigger('record_deleted',array(
			'model'=>$this->getOwner()
		));
	}

	/**
	 * Finds attributes that were changed and generates an array of changes.
	 * 
	 * @return array a 2-dimensional array of changes, with the format $fieldName => array($old,$new)
	 */
	public function getChanges() {
		$changes = array();
	
		// $this->_oldAttributes
		$oldAttributes = $this->getOwner()->getOldAttributes();
		$newAttributes = $this->getOwner()->getAttributes();

		// compare old and new
		foreach($newAttributes as $fieldName => $new) {
			if(isset($oldAttributes[$fieldName])) {
				$old = $oldAttributes[$fieldName];
				if(is_array($old))
					$old = implode(', ',$old);	// convert arrays to a string with commas in it (for example multiple assignedTo)
					
				if($new != $old)
					$changes[$fieldName] = array($old,$new);
			}
		}
		
		return $changes;
	}


/* 	public function writeChangelog($changes) {
		for($i=0;$i<count($changes); $i++) {
			$old = &$changes[$i][0];
			$new = &$changes[$i][1];
			
			if($new != $old) {
				$log = new Changelog;
				$log->type = get_class($this->getOwner());
				
				$log->itemId = $this->getOwner()->id;
				$log->changedBy = Yii::app()->user->getName();
				$log->fieldName = $field;
				// $log->oldValue = $old;
				$log->timestamp = time();
				
				if(empty($old)) {
					$log->diff = false;
					$log->newValue = $new;
				} else {
					$diff = FineDiff::getDiffOpcodes($old,$new,FineDiff::$wordGranularity);
					
					$log->diff = strlen($diff) > strlen($old);
					$log->newValue = $log->diff? $diff : $new;
				}
				
				$log->save();
			}
		}
	} */

	/**
	 * Writes field changes to the changelog. Calls {@link checkNotificationCriteria()} for each change
	 * @param array $changes the changes array, calls {@link getChanges()} if not provided
	 */
	public function updateChangelog($changes = null) {
		$model = $this->getOwner();
		
		if($changes === null)
			$changes = $this->getChanges();
		
		// $model->lastUpdated = time();
		// $model->updatedBy = Yii::app()->user->getName();
		// $model->save();
		$type = get_class($model);
		
		if(is_array($changes)) {
		
			foreach($changes as $fieldName => $change){
				$changelog = new Changelog;
				$changelog->type = $type;
				if (!isset($model->id)) {
					if ($model->save()) {

					}
				}
				$changelog->itemId = $model->id;
				if($model->hasAttribute('name')){
					$changelog->recordName=$model->name;
				}else{
					$changelog->recordName=$type;
				}
				$changelog->changedBy = Yii::app()->user->getName();
				$changelog->fieldName = $fieldName;
				$changelog->oldValue = $change[0];
				$changelog->newValue = $change[1];
				$changelog->timestamp = time();

				$changelog->save();
				
				
				
				$this->checkNotificationCriteria($fieldName,$change[0],$change[1]);
			}
		}
		// } elseif($changes == 'Create' || $changes == 'Edited') {
			// if($model instanceof Contacts)
				// $change = $model->backgroundInfo;
			// else if($model instanceof Actions)
				// $change = $model->actionDescription;
			// else if($model instanceof Docs)
				// $change = $model->text;
			// else
				// $change = $model->name;
		// } elseif($changes != '' && $changes != 'Completed') {
			// $pieces = explode("<br />", $change);
			// foreach($pieces as $piece) {
				// $newPieces = explode("TO:", $piece);
				// $forDeletion = $newPieces[0];
				// if(isset($newPieces[1]) && preg_match('/<b>' . Yii::t('actions', 'color') . '<\/b>/', $piece) == false) {
					// $changes[] = $newPieces[1];
				// }
			// }
		// }
	}
	
	/**
	 * Looks up notification criteria in x2_criteria relevant to this model 
	 * and field and performs the specified operation.
	 * Soon to be eliminated in wake of x2flow automation system.
	 * 
	 * @param string $fieldName the name of the current field
	 * @param string $old the old value
	 * @param string $new the new value
	 */
	public function checkNotificationCriteria($fieldName,$old,$new) {
		
		$model = $this->getOwner();
		$modelClass = get_class($model);
		
		$allCriteria = Criteria::model()->findAllByAttributes(array('modelType' => $modelClass, 'modelField' => $fieldName));
		foreach ($allCriteria as $criteria) {
			if (($criteria->comparisonOperator == "=" && $new == $criteria->modelValue)
					|| ($criteria->comparisonOperator == ">" && $new >= $criteria->modelValue) 
					|| ($criteria->comparisonOperator == "<" && $new <= $criteria->modelValue)
					|| ($criteria->comparisonOperator == "change" && $new != $old)) {
				
				$users = preg_split('/[\s,]+/',$criteria->users,null,PREG_SPLIT_NO_EMPTY);
				
				if($criteria->type == 'notification') {
					foreach($users as $user) {
						$event=new Events;
						$event->user=$user;
						$event->associationType='Notifications';
						$event->type='notif';
						
						$notif = new Notification;
						$notif->type = 'change';
						$notif->fieldName = $fieldName;
						$notif->modelType = get_class($model);
						$notif->modelId = $model->id;

						if($criteria->comparisonOperator == 'change') {
							$notif->comparison = 'change';	// if the criteria is just 'changed'
							$notif->value = $new;			// record the new value
						} else {
							$notif->comparison = $criteria->comparisonOperator;  // otherwise record the operator type
							$notif->value = substr($criteria->modelValue, 0, 250); // and the comparison value
						}
						$notif->user = $user;
						$notif->createdBy = Yii::app()->user->name;
						$notif->createDate = time();

						if($notif->save()) {
							$event->associationId = $notif->id;
							$event->save();
						}
					}
				} elseif($criteria->type == 'action') {
					foreach($users as $user) {
						$action = new Actions;
						$action->assignedTo = $user;
						if ($criteria->comparisonOperator == "=") {
							$action->actionDescription = "A record of type " . $modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
						} else if ($criteria->comparisonOperator == ">") {
							$action->actionDescription = "A record of type " . $modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
						} else if ($criteria->comparisonOperator == "<") {
							$action->actionDescription = "A record of type " . $modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
						} else if ($criteria->comparisonOperator == "change") {
							$action->actionDescription = "A record of type " . $modelClass . " has had its $criteria->modelField field changed from ".$old.' to '.$new.' by '.Yii::app()->user->getName();
						}
						$action->dueDate = mktime('23', '59', '59');
						$action->createDate = time();
						$action->lastUpdated = time();
						$action->updatedBy = 'admin';
						$action->visibility = 1;
						$action->associationType = strtolower($modelClass);
						$action->associationId = $model->id;
						$action->associationName = $model->name;
						$action->save();
					}
				} elseif ($criteria->type == 'assignment') {
					$model->assignedTo = $criteria->users;

					if ($model->save()) {
						$event=new Events;
						$event->type='notif';
						$event->user=$model->assignedTo;
						$event->associationType='Notifications';
						
						$notif = new Notification;
						$notif->user = $model->assignedTo;
						$notif->createDate = time();
						$notif->type = 'assignment';
						$notif->modelType = $modelClass;
						$notif->modelId = $model->id;
						if($notif->save()){
							$event->associationId = $notif->id;
							$event->save();
						}
					}
				}
			}
		}
	}

}
