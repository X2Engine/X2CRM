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
 *  Calendar lets you create calendar events, view actions from other modules, and sync to google calendar.
 *
 * @package X2CRM.modules.calendar.controllers
 */
class CalendarController extends x2base {

	public $modelClass = 'X2Calendar';
	public $calendarUsers = null; // list of users for choosing whose calendar to view
	public $groupCalendars = null;
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
					'jsonFeedGroup',
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
					'syncActionsToGoogleCalendar',
					'toggleUserCalendarsVisible',
					'togglePortletVisible',
				),
				'users'=>array('@'),
			),
			array(
				'allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin', 'userCalendarPermissions'),
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

		if(isset($_POST['save-button'])) {

			// clear old permissions
			X2CalendarPermissions::model()->deleteAllByAttributes(array('user_id'=>Yii::app()->user->id));

			$viewPermission = array();
			$editPermission = array();

			// $_POST['view-permission'] won't be set if no user has view permission
			if(isset($_POST['view-permission'])) { // any users have permssion to view this user's calendar?
				$viewPermission = $_POST['view-permission'];
			}
			if(isset($_POST['edit-permission'])) { // any users have permssion to edit this user's calendar?
				$editPermission = $_POST['edit-permission'];
			}

			$users = User::model()->findAll(array('select'=>'id'));
			foreach($users as $user) {
				$view = in_array($user->id, $viewPermission);
				$edit = in_array($user->id, $editPermission);

				$permission = new X2CalendarPermissions;
				$permission->user_id = Yii::app()->user->id;
				$permission->other_user_id = $user->id;
				$permission->view = $view;
				$permission->edit = $edit;
				$permission->save();
			}
		}

		$this->render('myCalendarPermissions');
	/*
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

		$this->render('myCalendarPermissions', array('model'=>$model, 'users'=>$users)); */
	}

	/**
	 *	Admin can set calendar permissions for all users
	 */
	public function actionUserCalendarPermissions() {
		if(isset($_POST['user-id'])) {
			$id = $_POST['user-id'];

			// clear old permissions
			X2CalendarPermissions::model()->deleteAllByAttributes(array('user_id'=>$id));

			$viewPermission = array();
			$editPermission = array();

			// $_POST['view-permission'] won't be set if no user has view permission
			if(isset($_POST['view-permission'])) { // any users have permssion to view this user's calendar?
				$viewPermission = $_POST['view-permission'];
			}
			if(isset($_POST['edit-permission'])) { // any users have permssion to edit this user's calendar?
				$editPermission = $_POST['edit-permission'];
			}

			$users = User::model()->findAll(array('select'=>'id'));
			foreach($users as $user) {
				$view = in_array($user->id, $viewPermission);
				$edit = in_array($user->id, $editPermission);

				$permission = new X2CalendarPermissions;
				$permission->user_id = $id;
				$permission->other_user_id = $user->id;
				$permission->view = $view;
				$permission->edit = $edit;
				$permission->save();
			}
		}
		if(isset($_GET['id'])) {
			$this->render('userCalendarPermissions', array('id'=>$_GET['id']));
		} else {
			$this->render('userCalendarPermissions');
		}
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
			require_once "protected/extensions/google-api-php-client/src/Google_Client.php";
			require_once "protected/extensions/google-api-php-client/src/contrib/Google_CalendarService.php";

			$client = new Google_Client();
			$client->setApplicationName("Google Calendar Integration");

			// Visit https://code.google.com/apis/console?api=calendar to generate your
			// client id, client secret, and to register your redirect uri.
			$client->setClientId($admin->googleClientId);
			$client->setClientSecret($admin->googleClientSecret);
			$client->setRedirectUri( (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl(''));
			//$client->setDeveloperKey($admin->googleAPIKey);
			$client->setAccessType('offline');
			$googleCalendar = new Google_CalendarService($client);

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
		$this->render('index', array('model'=>$model));
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
	 * $user - string - username
	 * $start, $end - unix timestamp - fetch actions between these dates
	 *
	 * return a json string of actions associated with the specified user
	 */
	public function actionJsonFeed($user, $start, $end) {
		$loggedInUser = User::model()->findByPk(Yii::app()->user->id); // get logged in user profile
		$filter = explode(',', $loggedInUser->calendarFilter); // action types user doesn't want filtered
		$possibleFilters = X2Calendar::getCalendarFilterNames(); // action types that can be filtered

		// SQL where clause
		$where = "(assignedTo=\"$user\") "; // must be assigned to $user
		if(!in_array('contacts', $filter))
			$where .= "AND (associationType != \"contacts\") "; // filter contact actions
		if(!in_array('accounts', $filter))
			$where .= "AND (associationType != \"accounts\") "; // filter account actions
		if(!in_array('opportunities', $filter))
			$where .= "AND (associationType != \"opportunities\") "; // filter opportunities actions
		if(!in_array('quotes', $filter))
			$where .= "AND (associationType != \"quotes\") "; // filter quote actions
		if(!in_array('products', $filter))
			$where .= "AND (associationType != \"product\") "; // filter product actions
		if(!in_array('media', $filter))
			$where .= "AND (associationType != \"media\") "; // filter media actions
		if(!in_array('completed', $filter))
			$where .= "AND (complete != \"Yes\") "; // filter completed actions
		if(!in_array('email', $filter))
			$where .= "AND (type IS NULL OR type != \"email\") "; // filter emails
		if(!in_array('attachment', $filter))
			$where .= "AND (type IS NULL OR type != \"attachment\") "; // filter attachments
		$where .= "AND (type IS NULL OR type != \"quotes\") ";
		$where .= "AND (";
		$where .= 	"(dueDate >= $start AND dueDate <= $end) OR (completeDate >= $start AND completeDate <= $end)"; // actions that happen between $start and $end
		$where .= ")";

		// get actions assigned to user
		$actions = Yii::app()->db->createCommand()
			->select('id, visibility, assignedTo, complete, type, (SELECT text FROM x2_action_text a WHERE a.actionId = id) AS actionDescription, dueDate, completeDate, associationType, associationName, associationId, allDay, color')
			->from('x2_actions')
			->where($where)
			->queryAll();

		$events = array();
		$user = User::model()->findByPk(Yii::app()->user->id); // get user profile
		$filter = explode(',', $user->calendarFilter); // action types user doesn't want filtered
		$possibleFilters = X2Calendar::getCalendarFilterNames(); // action types that can be filtered
		foreach($actions as $action) {
			if($action['visibility'] >= 1 || // // don't show private actions,
				$action['assignedTo'] == Yii::app()->user->name ||           // unless they belong to current user
				Yii::app()->user->checkAccess('AdminIndex')) { // admin sees all
				if(in_array($action['type'], $possibleFilters)) // type of action user might filter?
					if(!in_array($action['type'], $filter)) // filter actions user doesn't want to see
						continue;
				if(!in_array('completed', $filter)) // filter completed actions if user doesn't want to see them
				    if($action['complete'] == 'Yes')
				    	continue;
				$description = $action['actionDescription'];
				$title = substr($description, 0, 30);
  				if($action['type'] == 'event') {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action['dueDate']),
				    	'id'=>$action['id'],
				    	'complete'=>$action['complete'],
				    	'associationType'=>$action['associationType'],
				    	'type'=>'event',
				    	'allDay'=>false,
					);
					end($events);
					$last = key($events);
				    if($action['completeDate'])
				    	$events[$last]['end'] = date('Y-m-d H:i', $action['completeDate']);
				    if($action['allDay'])
				    	$events[$last]['allDay'] = $action['allDay'];
				    if($action['color'])
				    	$events[$last]['color'] = $action['color'];
				    if($action['associationType'] == 'contacts') {
				    	$events[$last]['associationUrl'] = $this->createUrl('/contacts/contacts/view/id/'. $action['associationId']);
				    	$events[$last]['associationName'] = $action['associationName'];
				    }

  				} else if($action['associationType'] == 'contacts') {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action['dueDate']),
				    	'id'=>$action['id'],
				    	'complete'=>$action['complete'],
				    	'associationType'=>'contacts',
				    	'associationUrl'=>$this->createUrl('/contacts/contacts/view/id/'. $action['associationId']),
				    	'associationName'=>$action['associationName'],
				    	'allDay'=>false,
				    );
				    end($events);
				    $last = key($events);
				    if($action['allDay'])
				    	$events[$last]['allDay'] = $action['allDay'];
				    if($action['color'])
				    	$events[$last]['color'] = $action['color'];
				} else {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action['dueDate']),
				    	'id'=>$action['id'],
				    	'complete'=>$action['complete'],
				    	'allDay'=>false,
				    );
				    end($events);
				    $last = key($events);
				    if($action['allDay'])
				    	$events[$last]['allDay'] = $action['allDay'];
				    if($action['color'])
				    	$events[$last]['color'] = $action['color'];
				}
			}
		}
		echo json_encode($events);
	}

	public function actionJsonFeedGroup($groupId, $start, $end) {

		$user = User::model()->findByPk(Yii::app()->user->id); // get user profile
		$filter = explode(',', $user->calendarFilter); // action types user doesn't want filtered
		$possibleFilters = X2Calendar::getCalendarFilterNames(); // action types that can be filtered

		// SQL where clause
		$where = "(assignedTo=\"$groupId\") "; // must be assigned to $user
		if(!in_array('contacts', $filter))
			$where .= "AND (associationType != \"contacts\") "; // filter contact actions
		if(!in_array('accounts', $filter))
			$where .= "AND (associationType != \"accounts\") "; // filter account actions
		if(!in_array('opportunities', $filter))
			$where .= "AND (associationType != \"opportunities\") "; // filter opportunities actions
		if(!in_array('quotes', $filter))
			$where .= "AND (associationType != \"quotes\") "; // filter quotes
		if(!in_array('products', $filter))
			$where .= "AND (associationType != \"product\") "; // filter product actions
		if(!in_array('media', $filter))
			$where .= "AND (associationType != \"media\") "; // filter media actions
		if(!in_array('completed', $filter))
			$where .= "AND (complete != \"Yes\") "; // filter completed actions
		if(!in_array('email', $filter))
			$where .= "AND (type IS NULL OR type != \"email\") "; // filter emails
		if(!in_array('attachment', $filter))
			$where .= "AND (type IS NULL OR type != \"attachment\") "; // filter attachments
		$where .= "AND (type IS NULL OR type != \"quotes\") ";
		$where .= "AND (";
		$where .= 	"(dueDate >= $start AND dueDate <= $end) OR (completeDate >= $start AND completeDate <= $end)"; // actions that happen between $start and $end
		$where .= ")";

		// get actions assigned to user
		$actions = Yii::app()->db->createCommand()
			->select('id, visibility, assignedTo, complete, type, actionDescription, dueDate, completeDate, associationType, associationName, associationId, allDay, color')
			->from('x2_actions')
			->where($where)
			->queryAll();

		$events = array();
		$user = User::model()->findByPk(Yii::app()->user->id); // get user profile
		$filter = explode(',', $user->calendarFilter); // action types user doesn't want filtered
		$possibleFilters = X2Calendar::getCalendarFilterNames(); // action types that can be filtered
		foreach($actions as $action) {
			if($action['visibility'] >= 1 || // // don't show private actions,
				$action['assignedTo'] == Yii::app()->user->name ||           // unless they belong to current user
				Yii::app()->user->checkAccess('AdminIndex')) { // admin sees all
				if(in_array($action['type'], $possibleFilters)) // type of action user might filter?
					if(!in_array($action['type'], $filter)) // filter actions user doesn't want to see
						continue;
				if(!in_array('completed', $filter)) // filter completed actions if user doesn't want to see them
				    if($action['complete'] == 'Yes')
				    	continue;
				$description = $action['actionDescription'];
				$title = substr($description, 0, 30);
  				if($action['type'] == 'event') {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action['dueDate']),
				    	'id'=>$action['id'],
				    	'complete'=>$action['complete'],
				    	'associationType'=>$action['associationType'],
				    	'type'=>'event',
				    	'allDay'=>false,
					);
					end($events);
					$last = key($events);
				    if($action['completeDate'])
				    	$events[$last]['end'] = date('Y-m-d H:i', $action['completeDate']);
				    if($action['allDay'])
				    	$events[$last]['allDay'] = $action['allDay'];
				    if($action['color'])
				    	$events[$last]['color'] = $action['color'];
				    if($action['associationType'] == 'contacts') {
				    	$events[$last]['associationUrl'] = $this->createUrl('/contacts/contacts/view/id/'. $action['associationId']);
				    	$events[$last]['associationName'] = $action['associationName'];
				    }

  				} else if($action['associationType'] == 'contacts') {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action['dueDate']),
				    	'id'=>$action['id'],
				    	'complete'=>$action['complete'],
				    	'associationType'=>'contacts',
				    	'associationUrl'=>$this->createUrl('/contacts/contacts/view/id/'. $action['associationId']),
				    	'associationName'=>$action['associationName'],
				    	'allDay'=>false,
				    );
				    end($events);
				    $last = key($events);
				    if($action['allDay'])
				    	$events[$last]['allDay'] = $action['allDay'];
				    if($action['color'])
				    	$events[$last]['color'] = $action['color'];
				} else {
				    $events[] = array(
				    	'title'=>$title,
				    	'description'=>$description,
				    	'start'=>date('Y-m-d H:i', $action['dueDate']),
				    	'id'=>$action['id'],
				    	'complete'=>$action['complete'],
				    	'allDay'=>false,
				    );
				    end($events);
				    $last = key($events);
				    if($action['allDay'])
				    	$events[$last]['allDay'] = $action['allDay'];
				    if($action['color'])
				    	$events[$last]['color'] = $action['color'];
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
			if($action->visibility >= 1 || $action->assignedTo == Yii::app()->user->name || Yii::app()->user->checkAccess('AdminIndex')) { // don't show private actions, unless they belong to current user
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
				    	$events[$last]['associationUrl'] = $this->createUrl('/contacts/contacts/view/id/'. $action->associationId);
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
				    	'associationUrl'=>$this->createUrl('/contacts/contacts/view/id/'. $action->associationId),
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

			$profile = ProfileChild::model()->findByAttributes(array('username'=>$action->assignedTo));
			if(isset($profile))
				$profile->updateGoogleCalendarEvent($action); // update action in Google Calendar if user has a Google Calendar

			if($action->save()){
                $event=X2Model::model('Events')->findByAttributes(array('associationType'=>'Actions','associationId'=>$action->id));
                if(isset($event)){
                    $event->timestamp=$action->dueDate;
                    $event->save();
                }
            }
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

			$profile = ProfileChild::model()->findByAttributes(array('username'=>$action->assignedTo));
			if(isset($profile))
				$profile->updateGoogleCalendarEvent($action); // update action in Google Calendar if user has a Google Calendar

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
				$googleEvent['start']['date'] = date('Y-m-d', Formatter::parseDateTime($model->dueDate));
				if($model->completeDate)
					$googleEvent['end']['date'] = date('Y-m-d', Formatter::parseDateTime($model->completeDate) + 86400);
				if(isset($googleEvent['start']['dateTime']))
					unset($googleEvent['start']['dateTime']);
				if(isset($googleEvent['end']['dateTime']))
					unset($googleEvent['end']['dateTime']);
			} else {
				$googleEvent['start']['dateTime'] = date('c', Formatter::parseDateTime($model->dueDate));
				if($model->completeDate)
					$googleEvent['end']['dateTime'] = date('c', Formatter::parseDateTime($model->completeDate));
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

			$profile = ProfileChild::model()->findByAttributes(array('username'=>$action->assignedTo));
			if(isset($profile))
				$profile->deleteGoogleCalendarEvent($action); // update action in Google Calendar if user has a Google Calendar

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
				'groupCalendars'=>array(),
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

	public function actionSyncActionsToGoogleCalendar() {
		$model = Yii::app()->params->profile;

		if(isset($_POST['Profile'])) {
			foreach(array_keys($model->attributes) as $field){
				if(isset($_POST['Profile'][$field])) {
					$model->$field = $_POST['Profile'][$field];
				}
			}

			if($model->syncGoogleCalendarId && isset($_SESSION['token'])) {
				$token = json_decode($_SESSION['token'], true);
				$model->syncGoogleCalendarRefreshToken = $token['refresh_token']; // used for accessing this google calendar at a later time
				$model->syncGoogleCalendarAccessToken = $_SESSION['token'];
			}

			$model->update();
		}

		$admin = Yii::app()->params->admin;
		$googleIntegration = $admin->googleIntegration;

		// if google integration is activated let user choose if they want to link this calendar to a google calendar
		if($googleIntegration) {
			$timezone = date_default_timezone_get();
			require_once "protected/extensions/google-api-php-client/src/Google_Client.php";
			require_once "protected/extensions/google-api-php-client/src/contrib/Google_CalendarService.php"; // for google calendar sync
			require_once 'protected/extensions/google-api-php-client/src/contrib/Google_Oauth2Service.php'; // for google oauth login
			date_default_timezone_set($timezone);

			$client = new Google_Client();
			$syncGoogleCalendarName = null; // name of the Google Calendar that current user's actions are being synced to if it has been set

			if (isset($_GET['unlinkGoogleCalendar'])) { // user changed thier mind about linking their google calendar
				unset($_SESSION['token']);
				$model->syncGoogleCalendarId = null;
				$model->syncGoogleCalendarRefreshToken = null; // used for accessing this google calendar at a later time
				$model->syncGoogleCalendarAccessToken = null;
				$model->update();
				$googleCalendarList = null;

				$client->setApplicationName("X2 Engine CRM");

				// Visit https://code.google.com/apis/console?api=calendar to generate your
				// client id, client secret, and to register your redirect uri.
				$client->setClientId($admin->googleClientId);
				$client->setClientSecret($admin->googleClientSecret);
				$client->setRedirectUri( (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl(''));
				//$client->setDeveloperKey($admin->googleAPIKey);
				$client->setAccessType('offline');
				$googleCalendar = new Google_CalendarService($client);

				if (isset($_GET['code'])) { // returning from google with access token
				  $client->authenticate();
				  $_SESSION['token'] = $client->getAccessToken();
				  header('Location: ' . (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				}

				if (isset($_SESSION['token'])) {
					$client->setAccessToken($_SESSION['token']);
					$calList = $googleCalendar->calendarList->listCalendarList();
					$googleCalendarList = array();
					foreach($calList['items'] as $cal)
						$googleCalendarList[$cal['id']] = $cal['summary'];
				} else {
					$googleCalendarList = null;
				}
			} else if($model->syncGoogleCalendarRefreshToken) {
				$client->setClientId($admin->googleClientId);
				$client->setClientSecret($admin->googleClientSecret);
				//$client->setDeveloperKey($admin->googleAPIKey);
				$client->setAccessToken($model->syncGoogleCalendarAccessToken);
				$googleCalendar = new Google_CalendarService($client);

				// check if the access token needs to be refreshed
				// note that the google library automatically refreshes the access token if we need a new one,
				// we just need to check if this happend by calling a google api function that requires authorization,
				// and, if the access token has changed, save this new access token
				$testCal = $googleCalendar->calendars->get($model->syncGoogleCalendarId);
				if($model->syncGoogleCalendarAccessToken != $client->getAccessToken()) {
					$model->syncGoogleCalendarAccessToken = $client->getAccessToken();
					$model->update();
				}

				$calendar = $googleCalendar->calendars->get($model->syncGoogleCalendarId);

				$syncGoogleCalendarName = $calendar['summary'];
				$calList = $googleCalendar->calendarList->listCalendarList();
				$googleCalendarList = array();
				foreach($calList['items'] as $cal)
					$googleCalendarList[$cal['id']] = $cal['summary'];
			} else {
				$client->setApplicationName("X2Engine CRM");

				// Visit https://code.google.com/apis/console?api=calendar to generate your
				// client id, client secret, and to register your redirect uri.
				$client->setClientId($admin->googleClientId);
				$client->setClientSecret($admin->googleClientSecret);
				$client->setRedirectUri( (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl(''));
				//$client->setDeveloperKey($admin->googleAPIKey);
				$client->setAccessType('offline');
				$googleCalendar = new Google_CalendarService($client);
				$oauth2 = new Google_Oauth2Service($client);

				if (isset($_GET['code'])) { // returning from google with access token
				  $client->authenticate();
				  $_SESSION['token'] = $client->getAccessToken();
				  header('Location: ' . (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				}

				if (isset($_SESSION['token'])) {
					$client->setAccessToken($_SESSION['token']);
					$calList = $googleCalendar->calendarList->listCalendarList();
					$googleCalendarList = array();
					foreach($calList['items'] as $cal)
						$googleCalendarList[$cal['id']] = $cal['summary'];
				} else {
					$googleCalendarList = null;
				}
			}
		} else {
			$client = null;
			$googleCalendarList = null;
		}

		$this->render('syncActionsToGoogleCalendar',
			array(
				'model'=>$model,
				'googleIntegration'=>$googleIntegration,
				'client'=>$client,
				'googleCalendarList'=>$googleCalendarList,
				'syncGoogleCalendarName'=>$syncGoogleCalendarName,
			)
		);
	}


	public function actionToggleUserCalendarsVisible() {
		echo Yii::app()->params->profile->userCalendarsVisible;
	}


	public function actionTogglePortletVisible($portlet) {
		$parameterName = $portlet . "Visible";
		if(isset(Yii::app()->params->profile->$parameterName)) {
			$visible = Yii::app()->params->profile->$parameterName;
			$visible = !$visible;
			Yii::app()->params->profile->$parameterName = $visible;
			Yii::app()->params->profile->update();
			echo $visible;
		} else {
			echo 1; // if portlet not found, just make it visible
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
