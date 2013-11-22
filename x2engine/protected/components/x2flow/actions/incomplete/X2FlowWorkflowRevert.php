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
 * X2FlowAction that reverts a workflow stage
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowWorkflowRevert extends X2FlowAction {
	public $title = 'Revert Workflow Stage';
	public $info = '';
	
	public function paramRules() {
		$workflows = Workflow::getList(false);	// no "none" options
		$workflowIds = array_keys($workflows);
		$stages = count($workflowIds)? Workflow::getStages($workflowIds[0]) : array('---');
		
		return array(
			'title' => Yii::t('studio',$this->title),
			'modelRequired' => 1,
			'options'=>array(
				array('name'=>'model'),
				array('name'=>'workflowId','label'=>'Workflow','type'=>'dropdown','options'=>$workflows),
				array('name'=>'stageNumber','label'=>'Stage','type'=>'dropdown','options'=>$stages),
			));
	}
	
	public function execute(&$params) {
		
	}
}