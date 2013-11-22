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
 * X2FlowAction that creates a reminder
 * 
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCreateReminder extends X2FlowAction {
	public $title = 'Create Action Reminder';
	public $info = 'At the specified time, this user will receive a reminder on their activity feed.';
	
	public function paramRules() {
		$assignmentOptions = array('{assignedTo}'=>'{'.Yii::t('studio','Owner of Record').'}') + X2Model::getAssignmentOptions(false,false);	// '{assignedTo}', no groups, no 'anyone'
		
		return array('title'=>$this->title,'info'=>$this->info,'options'=>array(
			array('name'=>'user','label'=>'User','type'=>'dropdown','options'=>$assignmentOptions),
			array('name'=>'text','label'=>'Message','type'=>'text'),
			array('name'=>'timestamp','label'=>'Time','type'=>'dateTime'),
		));
	}
	
	public function execute(&$params) {
		
		
		
		
	}
}