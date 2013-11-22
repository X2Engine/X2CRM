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
 * Action form widget for creating actions on record pages
 * 
 * Provides a form for creating actions. The form is typically used within the 
 * view of the record with which the resulting action will be associated.
 * 
 * @package X2CRM.components
 */
class InlineActionForm extends X2Widget {
	public $associationType;
	public $associationId;
	public $assignedTo;
	public $users;
	public $startHidden = false;
	public $inCalendar = false;
	public $showLogACall = true;
	public $showNewAction = true;
	public $showNewComment = true;
	public $showNewEvent = true;

	public function init() {
		Yii::app()->clientScript->registerScript('hideActionForm',
			"$(document).ready(hideActionForm);
			function hideActionForm() {
				$('#action-form').hide();
			}
			",CClientScript::POS_HEAD);
		
		if (!$this->startHidden) {
			Yii::app()->clientScript->registerScript('gotoActionForm',
				"$('#action-form').ready(gotoActionForm);
				function gotoActionForm() {
					$('#action-form').show();
					//toggleForm('#action-form',400);
					// $('#action-form #Actions_actionDescription').focus();
				}
				",CClientScript::POS_HEAD);
		}
		parent::init();
	}

	public function run() {
		$actionModel = new Actions;
		$actionModel->associationType = $this->associationType;
		$actionModel->associationId = $this->associationId;
		$actionModel->assignedTo = $this->assignedTo;
		echo $this->render('actions.views.actions._form', 
			array(
				'actionModel'=>$actionModel,
				'users'=>$this->users,
				'inlineForm'=>true,
				'inCalendar'=>$this->inCalendar,
				'showLogACall'=>$this->showLogACall,
				'showNewAction'=>$this->showNewAction,
				'showNewComment'=>$this->showNewComment,
				'showNewEvent'=>$this->showNewEvent,
			)
		);
	}
}