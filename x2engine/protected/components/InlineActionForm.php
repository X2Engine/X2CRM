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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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