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
 * Action form widget for creating actions on record pages
 * 
 * Provides a form for creating actions. The form is typically used within the 
 * view of the record with which the resulting action will be associated.
 * 
 * @package application.components
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
