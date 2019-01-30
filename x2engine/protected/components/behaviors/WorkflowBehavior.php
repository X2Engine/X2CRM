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
 * WorkflowBehavior class file.
 * Manages workflow operations for the owner record.
 * 
 * @package application.components 
 */
class WorkflowBehavior extends CActiveRecordBehavior {

	protected $_workflows;


	/**
	 * Responds to {@link CActiveRecord::onAfterDelete} event.
	 * 
	 * 
	 * @param CModelEvent $event event parameter
	 */
	public function afterDelete($event) {
		// $this->clearTags();
	}

	/**
	 * 
	 * @param integer $workflowId
	 * @param integer $stageNumber
	 * @return
	 */
	public function startStage($workflowId,$stageNumber) {
		
	}
	
	/**
	 * 
	 * @param integer $workflowId
	 * @param integer $stageNumber
	 * @return
	 */
	public function completeStage($workflowId,$stageNumber) {
		
	}
	
	/**
	 * 
	 * @param integer $workflowId
	 * @param integer $stageNumber
	 * @return
	 */
	public function revertStage($workflowId,$stageNumber) {
		
	}
	
	
	public function getWorkflowStatus($workflowId) {
	
		$workflowStatus = array(
			'id'=>$workflowId,
			'stages'=>array(),
			'started'=>false,
			'completed'=>false
		);
		
		$workflowStages = CActiveRecord::model('WorkflowStage')->findAllByAttributes(array('workflowId'=>$workflowId),new CDbCriteria(array('order'=>'id ASC')));
		
		// $workflowStatus[] = $workflowId;
		foreach($workflowStages as &$stage) {	// load all WorkflowStage names into workflowStatus
			$workflowStatus['stages'][$stage->stageNumber] = array(
				'name'=>$stage->name,
				'requirePrevious'=>$stage->requirePrevious,
				'roles'=>$stage->roles,
				'requireComment'=>$stage->requireComment
			);
		}
		unset($stage);

		$workflowActions = array();
		
		if(!empty($modelId)) {
			$workflowActions = CActiveRecord::model('Actions')->findAllByAttributes(
				array('associationId'=>$modelId,'associationType'=>$modelType,'type'=>'workflow','workflowId'=>$workflowId),
				new CDbCriteria(array('order'=>'createDate ASC'))
			);
		}
		
		foreach($workflowActions as &$action) {
			
			if($action->stageNumber < 1 || $action->stageNumber > count($workflowStages)) {
				$action->delete();
				continue;
			}
			
			$workflowStatus['started'] = true;	// clearly there's at least one stage up in here
		
			$stage = $action->stageNumber;
			// if(!is_array($workflowStatus[$action->stageNumber]))
				// $workflowStatus[$action->stageNumber] = array($workflowStatus[$action->stageNumber]);
			
			// decode workflowActions into a funnel list
			$workflowStatus['stages'][$stage]['createDate'] = $action->createDate;		// Note: multiple actions with the same stage will overwrite each other
			$workflowStatus['stages'][$stage]['completeDate'] = $action->completeDate;
			$workflowStatus['stages'][$stage]['complete'] = ($action->complete == 'Yes') || (!empty($action->completeDate) && $action->completeDate < time());	// determine whether stage is complete			
			$workflowStatus['stages'][$stage]['description'] = $action->actionDescription;																		// or the stage is beyond the possible range somehow
			
			/* $actionData = explode(':',$action->actionDescription);
			// decode workflowActions into a funnel list
			if(count($actionData) >= 2 && $actionData[0] == $workflowId && $actionData[1] <= count($workflowStages)) {		// ignore action if it's for a different workflow
				$workflowStatus[$actionData[1]]['createDate'] = $action->createDate;				// or the stage is beyond the possible range somehow
				$workflowStatus[$actionData[1]]['completeDate'] = $action->completeDate;		// Note: multiple actions with the same stage will overwrite each other
				$workflowStatus[$actionData[1]]['complete'] = ($action->complete == 'Yes') || (!empty($action->completeDate) && $action->completeDate < time());	// determine whether stage is complete
			} */
		}
		
		$workflowStatus['completed'] = true;
		foreach($workflowStatus['stages'] as &$stage) {		// now scan through and see if there are any incomplete stages
			if(!isset($stage['completeDate'])) {
				$workflowStatus['completed'] = false;
				break;
			}
		}
		
		return $workflowStatus;
	}
}