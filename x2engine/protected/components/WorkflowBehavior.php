<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * WorkflowBehavior class file.
 * Manages workflow operations for the owner record.
 * 
 * @package X2CRM.components 
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