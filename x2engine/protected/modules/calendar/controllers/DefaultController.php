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

class DefaultController extends x2base {

	public $modelClass = 'Calendar';
	public $calendarUsers = null; // list of users for choosing whose calendar to view
	public $sharedCalendars = null; // list of shared calendars to view/hide
	public $googleCalendars = null;
	public $calendarFilter = null;

	public function accessRules() {
		return array(
			array(
			    'allow',
			    'actions'=>array('getItems'),
			    'users'=>array('*'), 
			),
			array(
				'allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'index',
					'jsonFeed',
					'jsonFeedShared',
					'jsonFeedGoogle',
					'myCalendarPermissions',
					'create',
					'update',
					'list',
					'delete',
					'createEvent',
					'view',
					'viewAction',
					'editAction',
					'viewGoogleEvent',
					'editGoogleEvent',
					'moveAction',
					'moveGoogleEvent',
					'resizeAction',
					'resizeGoogleEvent',
					'saveAction',
					'saveGoogleEvent',
					'deleteGoogleEvent',
					'completeAction',
					'uncompleteAction',
					'deleteAction',
					'saveCheckedCalendar',
					'saveCheckedCalendarFilter',
				),
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
		$this->initCheckedCalendars(); // ensure user has a list to save checked calendars
		$this->render('calendar');
	}
	
	/**
	 * Show Calendar
	 */
	public function actionAdmin() {
		$this->initCheckedCalendars(); // ensure user has a list to save checked calendars
		$this->render('calendar');
	}
	
	public function actionView($id) {
		if($id == 0)
			$this->redirect(array('index'));
		else {
			$model = X2Calendar::model()->findByPk($id);
			parent::view($model, 'calendar');
		}
	}
	
	/**
	 * Set who can view/edit current user's calendar
	 */ 
	public function actionMyCalendarPermissions() {
		$model = User::model()->findByPk(Yii::app()->user->id);
		$users = User::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
		unset($users[Yii::app()->user->name]);

		if(isset($_POST['save-button'])) {
			if(isset($_POST['User']['calendarViewPermission'])) {
				$model->calendarViewPermission = $_POST['User']['calendarViewPermission'];
				$model->calendarViewPermission = Accounts::parseUsers($model->calendarViewPermission);
			} else {
				$model->calendarViewPermission = '';
			}
			
			if(isset($_POST['User']['calendarEditPermission'])) {
				$model->calendarEditPermission = $_POST['User']['calendarEditPermission'];
				$model->calendarEditPermission = Accounts::parseUsers($model->calendarEditPermission);
			} else {
				$model->calendarEditPermission = '';
			}
			
			$model->setCalendarPermissions = true; // user has now set up calendar permissions
			
			$model->update();
			$this->redirect(array('index'));
		}
		
		$this->render('myCalendarPermissions', array('model'=>$model, 'users'=>$users));
	}
	
	/**
	 * Create shared calendar
	 */
	public function actionCreate() {
	
		$model = new X2Calendar; 
		
		if(isset($_POST['X2Calendar'])) {
			// copy $_POST data into Calendar model
//			$this->render('test', array('model'=>$_POST));
			foreach(array_keys($model->attributes) as $field){
				if(isset($_POST['X2Calendar'][$field])){
					$model->$field=$_POST['X2Calendar'][$field];
					$fieldData=Fields::model()->findByAttributes(array('modelName'=>'Calendar','fieldName'=>$field));
					if(isset($fieldData) && $fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
						$model->$field=Accounts::parseUsers($model->$field);
					}elseif(isset($fieldData) && $fieldData->type=='date'){
						$model->$field=strtotime($model->$field);
					}
				}
			}
			
			if($model->googleCalendar && isset($_SESSION['token'])) {
				$token = json_decode($_SESSION['token'], true);
				$model->googleRefreshToken = $token['refresh_token']; // used for accessing this google calendar at a later time
				$model->googleAccessToken = $_SESSION['token'];
			}
			
			$model->createdBy = Yii::app()->user->name;
			$model->updatedBy = Yii::app()->user->name;
			$model->createDate = time();
			$model->lastUpdated = time();

			$model->save();
			$this->redirect(array('index'));
		}
		
		$admin = Yii::app()->params->admin;
		$googleIntegration = $admin->googleIntegration;
		
		// if google integration is activated let user choose if they want to link this calendar to a google calendar
		if($googleIntegration) {
			require_once "protected/extensions/google-api-php-client/src/apiClient.php";
			require_once "protected/extensions/google-api-php-client/src/contrib/apiCalendarService.php";
			
			$client = new apiClient();
			$client->setApplicationName("Google Calendar Integration");
			
			// Visit https://code.google.com/apis/console?api=calendar to generate your
			// client id, client secret, and to register your redirect uri.
			$client->setClientId($admin->googleClientId);
			$client->setClientSecret($admin->googleClientSecret);
			$client->setRedirectUri( (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl(''));
			$client->setDeveloperKey($admin->googleAPIKey);
			$client->setAccessType('offline');
			$googleCalendar = new apiCalendarService($client);
						
			if (isset($_GET['unlinkGoogleCalendar'])) { // user changed thier mind about linking their google calendar
			  unset($_SESSION['token']);
			}
			
			
			if (isset($_GET['code'])) { // returning from google with access token
			  $client->authenticate();
			  $_SESSION['token'] = $client->getAccessToken();
			  header('Location: ' . (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
			}

			if (isset($_SESSION['token'])) {
				$client->setAccessToken($_SESSION['token']);
				$calList = $googleCalendar->calendarList->listCalendarList();
				$events = $googleCalendar->events->listEvents('aubsmpmiimp6s0gb3tee7kermc@group.calendar.google.com');
				$googleCalendarList = array();
				foreach($calList['items'] as $cal)
					$googleCalendarList[$cal['id']] = $cal['summary'];
			} else {
				$googleCalendarList = null;
			}
		} else {
			$client = null;
			$googleCalendarList = null;
		}

		$this->render('create',
			array('model'=>$model,
			'googleIntegration'=>$googleIntegration,
			'client'=>$client,
			'googleCalendarList'=>$googleCalendarList,
			)
		);
	}
	
	/**
	 * update calendar with id $id
	 */
	public function actionUpdate($id) {
		$model = $this->loadModel($id);
	
		if(isset($_POST['X2Calendar'])) {

			// check for empty permissions
			if(!isset($_POST['X2Calendar']['viewPermission']))
				$model->viewPermission = '';
			if(!isset($_POST['X2Calendar']['editPermission']))
				$model->editPermission = '';

			// copy $_POST data into Calendar model
			foreach(array_keys($model->attributes) as $field){
				if(isset($_POST['X2Calendar'][$field])){
					$model->$field=$_POST['X2Calendar'][$field];
					$fieldData=Fields::model()->findByAttributes(array('modelName'=>'Calendar','fieldName'=>$field));
					if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
						$model->$field=Accounts::parseUsers($model->$field);
					}elseif($fieldData->type=='date'){
						$model->$field=strtotime($model->$field);
					}
				}
			}
			
			$model->updatedBy = Yii::app()->user->name;
			$model->lastUpdated = time();
			
			$model->save();
			$this->redirect(array('view','id'=>$model->id));
		}
				
		$admin = Yii::app()->params->admin;
		$googleIntegration = $admin->googleIntegration;
		
		$this->render('update', array('model'=>$model, 'googleIntegration'=>$googleIntegration));
	}
	
	public function actionList() {
		$model=new X2Calendar('search');
		$name='Calendar';
		parent::index($model,$name);
	}
	
	/**
	 * Delete shared Calendar
	 */
	public function actionDelete($id) {
		$model = $this->loadModel($id);
		$model->delete();
		$this->redirect(array('list'));
	}
	
	/**
	 * return a json string of actions associated with the specified user
	 */
	public function actionJsonFeed($user) {
		$actions = Actions::model()->findAllByAttributes(array('assignedTo'=>$user));
		$events = array();
		$user = User::model()->findByPk(Yii::app()->user->id); // get user profile
		$filter = explode(',', $user->calendarFilter); // action types user doesn't want filtered
		$possibleFilters = X2Calendar::getCalendarFilterNames(); // action types that can be filtered
		foreach($actions as $action) {
			if($action->visibility >= 1 || // // don't show private actions, 
				$action->assignedTo == Yii::app()->user->name ||           // unless they belong to current user
				Yii::app()->user->name == 'admin') { // admin sees all
				if(in_array($action->type, $possibleFilters)) // type of action user might filter?
					if(!in_array($action->type, $filter)) // filter actions user doesn't want to see
						continue;
				if(!in_array('completed', $filter)) // filter completed actions if user doesn't want to see them
				    if($action->complete == 'Yes')
				    	continue;
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
				    	$events[$last]['associationUrl'] = $this->createUrl('/contacts/default/view/id/'. $action->associationId);
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
				    	'associationUrl'=>$this->createUrl('/contacts/default/view/id/'. $action->associationId),
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
	
	
	/**
	 * return a json string of actions associated with the specified shared calendar
	 */
	public function actionJsonFeedShared($calendarId) {
		$actions = Actions::model()->findAllByAttributes(array('calendarId'=>$calendarId));
		$events = array();
		$user = User::model()->findByPk(Yii::app()->user->id); // get user profile
		$filter = explode(',', $user->calendarFilter); // action types user doesn't want filtered
		$possibleFilters = X2Calendar::getCalendarFilterNames(); // action types that can be filtered
		foreach($actions as $action) {
			if($action->visibility >= 1 || $action->assignedTo == Yii::app()->user->name || Yii::app()->user->name == 'admin') { // don't show private actions, unless they belong to current user
				if(in_array($action->type, $possibleFilters)) // type of action user might filter?
					if(!in_array($action->type, $filter)) // filter actions user doesn't want to see
						continue;
				if(!in_array('completed', $filter)) // filter completed actions if user doesn't want to see them
				    if($action->complete == 'Yes')
				    	continue;
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
				    	$events[$last]['associationUrl'] = $this->createUrl('/contacts/default/view/id/'. $action->associationId);
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
				    	'associationUrl'=>$this->createUrl('/contacts/default/view/id/'. $action->associationId),
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
	
	public function actionJsonFeedGoogle($calendarId) {
		$calendar = X2Calendar::model()->findByPk($calendarId);
		$events = array();
		if($calendar->googleCalendarId) {
			$googleCalendar = $calendar->getGoogleCalendar();
			$googleEvents = $googleCalendar->events->listEvents($calendar->googleCalendarId);
			foreach($googleEvents['items'] as $googleEvent) {
				$description = $googleEvent['summary'];
				if(isset($googleEvent['start']['dateTime'])) {
					$allDay = false;
					$start = $googleEvent['start']['dateTime'];
					if(isset($googleEvent['end']['dateTime']))
						$end = $googleEvent['end']['dateTime'];
				} else {
					$allDay = true;
					$start = $googleEvent['start']['date'];
					if(isset($googleEvent['end']['date'])) {
						$end = date("Y-m-d", strtotime($googleEvent['end']['date']) - 86400); // subtract a day because google saves all day events with one extra day
					}
				}
				$title = substr($description, 0, 30);
				if(isset($googleEvent['colorId'])) {
					$colorTable = array(
						10=>'Green',
						11=>'Red',
						6=>'Orange',
						8=>'Black',
					);
					if(isset($colorTable[$googleEvent['colorId']]))
						$color = $colorTable[$googleEvent['colorId']];
				}
				$events[] = array(
					'title'=>$title,
					'id'=>$googleEvent['id'],
					'description'=>$description,
					'start'=> $start,
					'allDay'=>$allDay,
				);
				end($events);
				$last = key($events);
				if(isset($end))
				    $events[$last]['end'] = $end;
				if(isset($color)) {
					$events[$last]['color'] = $color;
					unset($color);
				}
			}
		}
		
		echo json_encode($events);
	}
	
	/**
	 *	Ajax requests call this function, which returns a form filled with the event data.
	 *  The form is then appended to a dialog in the users browser.
	 */
	public function actionEditAction() {
		if(isset($_POST['ActionId'])) { // ensure we are getting sane post data
			$id = $_POST['ActionId'];
			$model = Actions::model()->findByPk($id);
			$isEvent = json_decode($_POST['IsEvent']);

			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			$this->renderPartial('editAction', array('model'=>$model, 'isEvent'=>$isEvent), false, true);
		}
	}

	/**
	 *	Ajax requests call this function, which returns read only action data.
	 *  The data is then appended to a dialog in the users browser.
	 */
	public function actionViewAction() {
		if(isset($_POST['ActionId'])) { // ensure we are getting sane post data
			$id = $_POST['ActionId'];
			$model = Actions::model()->findByPk($id);
			$isEvent = json_decode($_POST['IsEvent']);

			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			$this->renderPartial('viewAction', array('model'=>$model, 'isEvent'=>$isEvent), false, true);
		}
	}
	
	public function actionViewGoogleEvent() {
		if(isset($_POST['EventId']) && isset($_POST['CalendarId'])) {
			$eventId = $_POST['EventId'];
			$calendarId = $_POST['CalendarId'];
			$calendar = X2Calendar::model()->findByPk($calendarId);
			$googleCalendar = $calendar->getGoogleCalendar();
			$googleEvent = $googleCalendar->events->get($calendar->googleCalendarId, $eventId);
			$model = new Actions;
			$model->actionDescription = $googleEvent['summary'];
			if(isset($googleEvent['start']['dateTime'])) {
			    $model->allDay = false;
			    $model->dueDate = strtotime($googleEvent['start']['dateTime']);
			} else {
			    $model->allDay = true;
			    $model->dueDate = strtotime($googleEvent['start']['date']);
			}
			if(isset($googleEvent['end']['dateTime']))
			    $model->completeDate = strtotime($googleEvent['end']['dateTime']);
			else
			    $model->completeDate = strtotime($googleEvent['end']['date']);
			
			if(isset($googleEvent['colorId'])) {
				$colorTable = array(
					10=>'Green',
					11=>'Red',
					6=>'Orange',
					8=>'Black',
				);
				if(isset($colorTable[$googleEvent['colorId']]))
					$model->color = $colorTable[$googleEvent['colorId']];
			}
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			$this->renderPartial('viewGoogleEvent', array('model'=>$model, 'eventId'=>$eventId), false, true);
		}
	}
	
	public function actionEditGoogleEvent() {
		if(isset($_POST['EventId']) && isset($_POST['CalendarId'])) {
			$eventId = $_POST['EventId'];
			$calendarId = $_POST['CalendarId'];
			$calendar = X2Calendar::model()->findByPk($calendarId);

			$googleCalendar = $calendar->getGoogleCalendar();
			$googleEvent = $googleCalendar->events->get($calendar->googleCalendarId, $eventId);
			$model = new Actions;
			$model->actionDescription = $googleEvent['summary'];
			if(isset($googleEvent['start']['dateTime'])) {
			    $model->allDay = false;
			    $model->dueDate = strtotime($googleEvent['start']['dateTime']);
			} else {
			    $model->allDay = true;
			    $model->dueDate = strtotime($googleEvent['start']['date']);
			}
			if(isset($googleEvent['end']['dateTime']))
			    $model->completeDate = strtotime($googleEvent['end']['dateTime']);
			else
			    $model->completeDate = strtotime($googleEvent['end']['date']) - 86400;
			
			if(isset($googleEvent['colorId'])) {
				$colorTable = array(
					10=>'Green',
					11=>'Red',
					6=>'Orange',
					8=>'Black',
				);
				if(isset($colorTable[$googleEvent['colorId']]))
					$model->color = $colorTable[$googleEvent['colorId']];
			}
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			$this->renderPartial('editGoogleEvent', array('model'=>$model, 'eventId'=>$eventId), false, true);
		}
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
			$action->allDay = (($allDay == 'true' || $allDay == 1)? 1:0);
			$action->dueDate += ($dayDelta * 86400) + ($minuteDelta * 60);
			if($action->completeDate)
				$action->completeDate += ($dayDelta * 86400) + ($minuteDelta * 60);
			$action->save();
		}
	}
	
	// move the time for a Google Calendar event
	public function actionMoveGoogleEvent($calendarId) {
		if(isset($_POST['EventId'])) {
			$eventId = $_POST['EventId'];
			$dayDelta = $_POST['dayChange']; // +/-
			$minuteDelta = $_POST['minuteChange']; // +/-
			$allDay = json_decode($_POST['isAllDay']);
			$calendar = X2Calendar::model()->findByPk($calendarId);
			$googleCalendar = $calendar->getGoogleCalendar();
			$googleEvent = $googleCalendar->events->get($calendar->googleCalendarId, $eventId);

			if(isset($googleEvent['start']['dateTime'])) { // event was not all day
			    $start = strtotime($googleEvent['start']['dateTime']);
			    if($allDay) { // move event to all day
					unset($googleEvent['start']['dateTime']);
					$googleEvent['start']['date'] = date('Y-m-d', $start);
			    } else { // keep event as not all day
			    	$start += ($dayDelta * 86400) + ($minuteDelta * 60);
			    	$googleEvent['start']['dateTime'] = date('c', $start);
			    }
			} else { // event was all day
			    $start = strtotime($googleEvent['start']['date']);
			    if($allDay) { // keep event as all day
			    	$start += ($dayDelta * 86400) + ($minuteDelta * 60);
			    	$googleEvent['start']['date'] = date('Y-m-d', $start);
			    } else { // move event to not all day
			    	unset($googleEvent['start']['date']);
			    	$start += ($dayDelta * 86400) + ($minuteDelta * 60);
			    	$googleEvent['start']['dateTime'] = date('c', $start);
			    }
			}
			if(isset($googleEvent['end']['dateTime'])) { // event was not all day
			    $end = strtotime($googleEvent['end']['dateTime']);
			    if($allDay) { // move event to all day
			    	unset($googleEvent['end']['dateTime']);
			    	$end = strtotime($googleEvent['start']['date']) + 86400;
			    	$googleEvent['end']['date'] = date('Y-m-d', $end);
			    } else { // keep event as not all day
			    	$end += ($dayDelta * 86400) + ($minuteDelta * 60);
			    	$googleEvent['end']['dateTime'] = date('c', $end);
			    }
			} else if(isset($googleEvent['end']['date'])) { // event was all day
			    $end = strtotime($googleEvent['end']['date']);
			    if($allDay) { // keep event as all day
			    	$end += ($dayDelta * 86400) + ($minuteDelta * 60); // end = start + 1 day
			    	$googleEvent['end']['date'] = date('Y-m-d', $end);
			    } else { // move event to not all day
			    	unset($googleEvent['end']['date']);
			    	$end = strtotime($googleEvent['start']['dateTime']) + 7200; // end = start + 2 hours
			    	$googleEvent['end']['dateTime'] = date('c', $end);
			    }
			}
			
			$googleEvent = new Event($googleEvent);
			$googleCalendar->events->update($calendar->googleCalendarId, $eventId, $googleEvent); 
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
	
	// move the end time of a Google Calendar event
	public function actionResizeGoogleEvent($calendarId) {
		if(isset($_POST['EventId'])) {
			$eventId = $_POST['EventId'];
			$dayDelta = $_POST['dayChange']; // +/-
			$minuteDelta = $_POST['minuteChange']; // +/-
			$calendar = X2Calendar::model()->findByPk($calendarId);
			$googleCalendar = $calendar->getGoogleCalendar();
			$googleEvent = $googleCalendar->events->get($calendar->googleCalendarId, $eventId);

			if(isset($googleEvent['end']['dateTime'])) {
			    $end = strtotime($googleEvent['end']['dateTime']);
			    $end += ($dayDelta * 86400) + ($minuteDelta * 60);
			    $googleEvent['end']['dateTime'] = date('c', $end);
			} else if(isset($googleEvent['end']['date'])) { // all day
			    $end = strtotime($googleEvent['end']['date']);
			    $end += ($dayDelta * 86400) + ($minuteDelta * 60);
			    $googleEvent['end']['date'] = date('Y-m-d', $end);
			}
			
			$googleEvent = new Event($googleEvent);
			$googleCalendar->events->update($calendar->googleCalendarId, $eventId, $googleEvent);
		}
	}
	
	// save a actionDescription
	public function actionSaveGoogleEvent($calendarId) {
		if(isset($_POST['EventId'])) {
			$eventId = $_POST['EventId'];
			$calendar = X2Calendar::model()->findByPk($calendarId);
			$googleCalendar = $calendar->getGoogleCalendar();
			$googleEvent = $googleCalendar->events->get($calendar->googleCalendarId, $eventId);
			
			$model = new Actions;
			foreach($model->attributes as $field=>$value){
			    if(isset($_POST['Actions'][$field])){
			        $model->$field=$_POST['Actions'][$field];
			    }
			}
			
			if($model->allDay) {
				$googleEvent['start']['date'] = date('Y-m-d', $this->parseDateTime($model->dueDate));
				if($model->completeDate)
					$googleEvent['end']['date'] = date('Y-m-d', $this->parseDateTime($model->completeDate) + 86400);
				if(isset($googleEvent['start']['dateTime']))
					unset($googleEvent['start']['dateTime']);
				if(isset($googleEvent['end']['dateTime']))
					unset($googleEvent['end']['dateTime']);
			} else {
				$googleEvent['start']['dateTime'] = date('c', $this->parseDateTime($model->dueDate));
				if($model->completeDate)
					$googleEvent['end']['dateTime'] = date('c', $this->parseDateTime($model->completeDate));
				if(isset($googleEvent['start']['date']))
					unset($googleEvent['start']['date']);
				if(isset($googleEvent['end']['date']))
					unset($googleEvent['end']['date']);
			}
			
			if($model->color && $model->color != '#3366CC') {
				$colorTable = array(
					10=>'Green',
					11=>'Red',
					6=>'Orange',
					8=>'Black',
				);
				if(($key = array_search($model->color, $colorTable)) != false)
					$googleEvent['colorId'] = $key;
			}
			
			$googleEvent = new Event($googleEvent); // we send back a proper Event object to google
			$googleEvent->setSummary($_POST['Actions']['actionDescription']);
			$googleCalendar->events->update($calendar->googleCalendarId, $eventId, $googleEvent);
//			$googleCalendar->events->delete($calendar->googleCalendarId, $eventId);
		}
		
//		$this->render('test', array('model'=>$_POST));
	}
	
	// save a actionDescription
	public function actionDeleteGoogleEvent($calendarId) {
		if(isset($_POST['EventId'])) {
			$eventId = $_POST['EventId'];
			$calendar = X2Calendar::model()->findByPk($calendarId);
			$googleCalendar = $calendar->getGoogleCalendar();
			$googleCalendar->events->delete($calendar->googleCalendarId, $eventId);
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
	
	// check if user profile has a list to remember which calendars the user has checked
	// if not, create the list
	public function initCheckedCalendars() {
		$user = User::model()->findByPk(Yii::app()->user->getId());
		if($user->showCalendars == null) { // calendar list not initialized?
			$showCalendars = array(
				'userCalendars'=>array('Anyone', $user->username), 
				'sharedCalendars'=>array(), 
				'googleCalendars'=>array()
			);
			$user->showCalendars = json_encode($showCalendars);
			
			$user->update();
		}
	}

	// if a user checked/unchecked a calendar, remember for the next to the user visits the page
	public function actionSaveCheckedCalendar() {
		if(isset($_POST['Calendar'])) {
			$calendar = $_POST['Calendar'];
			$checked = $_POST['Checked'];
			$type = $_POST['Type'];
			$calendarType = $type . 'Calendars';
			
			// get user list of checked calendars
			$user = User::model()->findByPk(Yii::app()->user->getId());
			$showCalendars = json_decode($user->showCalendars, true);
			
			if($checked)  // remember to show calendar
				if(!in_array($calendar, $showCalendars[$calendarType]))
					$showCalendars[$calendarType][] = $calendar;
			else // stop remembering to show calendar
				if( ($key = array_search($calendar, $showCalendars[$calendarType])) !== false) // find calendar in list of shown calendars
					unset($showCalendars[$calendarType][$key]);
			
			print_r($showCalendars);
			$user->showCalendars = json_encode($showCalendars);
			$user->update();
		}
	}
	
	// when user checks/unchecks a filter, remember it in user profile
	public function actionSaveCheckedCalendarFilter() {
		if(isset($_POST['Filter'])) {
			$filterName = $_POST['Filter'];
			$checked = $_POST['Checked'];
			$user = User::model()->findByPk(Yii::app()->user->id);
			$calendarFilter = explode(',', $user->calendarFilter);
			
			if($checked)
				if(!in_array($filterName, $calendarFilter))
					$calendarFilter[] = $filterName;
			else
				if( ($key = array_search($filterName, $calendarFilter)) !== false)
					unset($calendarFilter[$key]);
			
			$user->calendarFilter = implode(',', $calendarFilter);
			$user->update();
		}
	}
		
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=X2Calendar::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}
}