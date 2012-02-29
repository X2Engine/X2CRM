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

class CalendarController extends x2base {

	public $modelClass = 'Calendar';
	public $calendarUsers = array(); // list of users for choosing whose calendar to view

	public function accessRules() {
		return array(
			array(
			    'allow',
			    'actions'=>array('getItems'),
			    'users'=>array('*'), 
			),
			array(
				'allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index', 'jsonFeed', 'createEvent', 'view', 'moveAction', 'resizeAction', 'saveAction', 'completeAction', 'uncompleteAction', 'deleteAction'),
				'users'=>array('@'),
			),
			array(
				'allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array(
				'deny',  // deny all users
			    'users'=>array('*'),
			),
		);
	}

	/**
	 * Show Calendar
	 */
	public function actionIndex() {
		$this->render('index');
	}
	
	/**
	 * Show Calendar
	 */
	public function actionAdmin() {
		$this->render('index');
	}
	
	public function actionView($id) {
		$this->redirect(array('index'));
	}
	
	/**
	 * return a json string of actions associated with the specified user
	 */
	public function actionJsonFeed($user) {
		$actions = Actions::model()->findAllByAttributes(array('assignedTo'=>$user));
		$events = array();
		foreach($actions as $action) {
			if($action->visibility >= 1 || $action->assignedTo == Yii::app()->user->name || Yii::app()->user->name == 'admin') { // don't show private actions, unless they belong to current user
				$description = $action->actionDescription;
				$title = substr($description, 0, 30);
  				if($action->type == 'event') {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action->dueDate),
				    	'id'=>$action->id,
				    	'complete'=>$action->complete,
				    	'associationType'=>$action->associationType,
				    	'type'=>'event',
				    	'allDay'=>false,
					);
					end($events);
					$last = key($events);
				    if($action->completeDate)
				    	$events[$last]['end'] = date('Y-m-d H:i', $action->completeDate);
				    if($action->allDay)
				    	$events[$last]['allDay'] = $action->allDay;
				    if($action->color)
				    	$events[$last]['color'] = $action->color;
				    if($action->associationType == 'contacts') {
				    	$events[$last]['associationUrl'] = $this->createUrl('contacts/'. $action->associationId);
				    	$events[$last]['associationName'] = $action->associationName;
				    }
				    	
  				} else if($action->associationType == 'contacts') {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action->dueDate),
				    	'id'=>$action->id,
				    	'complete'=>$action->complete,
				    	'associationType'=>'contacts',
				    	'associationUrl'=>$this->createUrl('contacts/'. $action->associationId),
				    	'associationName'=>$action->associationName,
				    	'allDay'=>false,
				    );
				    end($events);
				    $last = key($events);
				    if($action->allDay)
				    	$events[$last]['allDay'] = $action->allDay;
				    if($action->color)
				    	$events[$last]['color'] = $action->color;
				} else {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action->dueDate),
				    	'id'=>$action->id,
				    	'complete'=>$action->complete,
				    	'allDay'=>false,
				    );
				    end($events);
				    $last = key($events);
				    if($action->allDay)
				    	$events[$last]['allDay'] = $action->allDay;
				    if($action->color)
				    	$events[$last]['color'] = $action->color;
				}
			}
		}
		echo json_encode($events);
	}
	
	// move the start time of an action
	// if the action has a complete date (or end date) it is also moved
	public function actionMoveAction() {
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			$dayDelta = $_POST['dayChange']; // +/-
			$minuteDelta = $_POST['minuteChange']; // +/-
			$allDay = $_POST['isAllDay'];
			
			$action = Actions::model()->findByPk($id);
			$action->allDay = ($allDay == 'true'? 1:0);
			$action->dueDate += ($dayDelta * 86400) + ($minuteDelta * 60);
			if($action->completeDate)
				$action->completeDate += ($dayDelta * 86400) + ($minuteDelta * 60);
			$action->save();
		}
	}
	
	// move the end (or complete) time of an action
	// if the action doesn't have a 
	public function actionResizeAction() {
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			$dayDelta = $_POST['dayChange']; // +/-
			$minuteDelta = $_POST['minuteChange']; // +/-
			
			$action = Actions::model()->findByPk($id);
			if($action->completeDate) // actions without complete date aren't updated
				$action->completeDate += ($dayDelta * 86400) + ($minuteDelta * 60);
			else if($action->type == 'event') // event without end date? give it one
				$action->completeDate = $action->dueDate + ($dayDelta * 86400) + ($minuteDelta * 60);
			$action->save();
		}
	}
	
	// save a actionDescription
	public function actionSaveAction() {
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			$actionDescription = $_POST['actionDescription'];
		
			$action = Actions::model()->findByPk($id);
			$action->actionDescription = $actionDescription;
			$action->update();
		}
	}
	
	// make an action complete
	public function actionCompleteAction() {
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			
			$action = Actions::model()->findByPk($id);
			$action->complete = "Yes";
			$action->completedBy=Yii::app()->user->getName();
			$action->completeDate = time();
			$action->update();
		}
	}
	
	// make an action uncomplete
	public function actionUncompleteAction() {
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			
			$action = Actions::model()->findByPk($id);
			$action->complete = "No";
			$action->completedBy = null;
			$action->completeDate = null;
			$action->update();
		}
	}
	
	// delete an action from the database
	public function actionDeleteAction() {
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			
			$action = Actions::model()->findByPk($id);
			$action->delete();
		}
	}
}