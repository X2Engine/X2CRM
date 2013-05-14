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

Yii::import('application.modules.users.models.*');

/**
 * Remote data insertion & lookup API
 * @package X2CRM.controllers
 * @author Jake Houser <jake@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 */
class ApiController extends x2base {

	/**
	 * @var string The model that the API is currently being used with.
	 */
	public $modelClass;
	public $user;

	/**
	 * Default response format either 'json' or 'xml'
	 */
	private $format = 'json';

	/**
	 * Auth items to be checked against in {@link filterCheckPermissions} where
	 * their action isn't the same as the prefix.
	 */
	public $actionAuthItemMap = array(
		'lookUp' => 'View',
	);

	public function behaviors() {
		return array(
			'responds' => array(
				'class' => 'application.components.ResponseBehavior',
				'isConsole' => false,
				'exitNonFatal' => false,
				'longErrorTrace' => false,
			),
		);
	}

	/**
	 * @return array action filters
	 */
	public function filters() {
		return array(
			'noSession',
			'authenticate - voip,webListener',
			'validModel + create,view,lookup,update,delete',
			'checkCRUDPermissions + create,view,lookup,update,delete',
		);
	}

	public function actions() {
		if (class_exists('WebListenerAction'))
			return array(
				'webListener' => array(
					'class' => 'WebListenerAction',
				),
			);
		return array();
	}

	/**
	 * Multi-purpose method for checking permissions. If called as an action,
	 * it will return "true" or "false" in plain text (to stay backwards-
	 * compatibile with old API scripts). Otherwise, will return true or false.	 * 
	 * @param type $action
	 * @param type $username
	 * @param type $api
	 * @return type
	 */
	public function actionCheckPermissions($action, $username = null, $api = 0) {
		$access = true; // Default: permissive if no auth item exists
		Yii::log("Checking permissions...",'api');
		$auth = Yii::app()->authManager;
		$item = $auth->getAuthItem($action);
		$authenticated = $auth->getAuthItem('DefaultRole');
		if (isset($item)) {
			$access = false; // Auth item exists; set true only through verification
			$userId = null;
			$access = $authenticated->checkAccess($action);

			if (!$access) { // Skip this if we already have access
				if ($username != null) { // Override current API user if any
					$userId = User::model()->findByAttributes(array('username' => $username))->id;
				} elseif (isset($this->user)) { // Called from within another API action that required credentials
					$userId = $this->user->id;
				}
			}

			if ($userId != null && !$access) { // Skip this if we already have access
				Yii::log("Verifying that user with id=$userId can perform action $action...",'api');
				$access = $access || $userId == 1;
				if (!$access) {
					// Check role-based permissions:
					Yii::log('Checking for role-based privileges...','api');
					$roles = RoleToUser::model()->findAllByAttributes(array('userId' => $userId));
					foreach ($roles as $role) {
						$access = $access || $auth->checkAccess($action, $role->roleId);
					}
				}
			}
		} elseif($this->action->id != 'checkPermissions') 
			Yii::log(sprintf("Auth item %s not found. Permitting action %s.",$action,$this->action->id),'api');

		if ($api == 1) { // API model:
			// The method is being called as an action, most likely from APIModel
			$access = $access ? "true" : "false";
			header('Content-type: text/plain');
			echo $access;
			Yii::app()->end();
		} else {
			// This method is not being called as an action; rather, from a
			// filter or some other method.
			return $access;
		}
	}

	/**
	 * Creates a new record.
	 *
	 * This method allows for the creation of new records via API request.
	 * Requests should be made of the following format:
	 * www.[server].com/index.php/path/to/x2/index.php/api/create/model/[modelType]
	 * With the model's attributes as $_POST data.  Furthermore, in the post array
	 * a valid username and encrypted password must be submitted under the indeces
	 * 'authUser' and 'authPassword' for the request to be authenticated.
	 */
	public function actionCreate() {
		// Get an instance of the respective model
		$model = new $this->modelClass;
		$model->setX2Fields($_POST);
        if($this->modelClass=='Actions' && isset($_POST['actionDescription'])){
            $model->actionDescription=$_POST['actionDescription'];
        }
		$this->modelSetUsernameFields($model);

		if(!empty($model->createDate)) // If create date is being manually set, i.e. an import, don't overwrite
			$model->disableBehavior('changelog');

		// Attempt to save the model, and perform special post-save (or error)
		// operations based on the model type:
		$valid = $model->validate();
		if($valid)
			$valid = $model->save();
		if ($valid) { // New record successfully created
			$message =  "A {$this->modelClass} type record was created"; //sprintf(' <b>%s</b> was created',$this->modelClass);
			switch ($this->modelClass) {
				// Special extra actions to take for each model type:
				case 'Actions':
					$message .= " with description {$model->actionDescription}";
					$model->syncGoogleCalendar('create');
					break;
				case 'Contacts':
					$message .= " with name {$model->name}";
			}
			$this->addResponseProperty('model',$model->attributes);
			$this->_sendResponse(200,$message);
		} else { // API model creation failure
			$this->addResponseProperty('modelErrors',$model->errors);
			switch ($this->modelClass) {
				case 'Contacts':
					Yii::log(sprintf('Failed to save record of type %s due to errors: %s', $this->modelClass, CJSON::encode($model->errors)), 'api');
					$msg = "<h1>Error</h1>";
					$msg .= sprintf("Couldn't create model <b>%s</b>", $this->modelClass);
					$msg .= "<ul>";
					foreach ($model->errors as $attribute => $attr_errors) {
						$msg .= "<li>Attribute: $attribute</li>";
						$msg .= "<ul>";
						foreach ($attr_errors as $attr_error) {
							$msg .= "<li>$attr_error</li>";
						}
						$msg .= "</ul>";
					}
					$msg .= "</ul>";
					// Special lead failure notification in the app and through email:
					
					$notif = new Notification;
					$notif->user = 'admin';
					$notif->type = 'lead_failure';
					$notif->createdBy = $this->user->username;
					$notif->createDate = time();
					$notif->save();

					$to = Yii::app()->params->admin->webLeadEmail;
					$subject = "Web Lead Failure";
					if(!Yii::app()->params->automatedTesting){
						$phpMail = $this->getPhpMailer();
						$fromEmail = Yii::app()->params->admin->emailFromAddr;
						$fromName = Yii::app()->params->admin->emailFromName;
						$phpMail->AddReplyTo($fromEmail, $fromName);
						$phpMail->SetFrom($fromEmail, $fromName);
						$phpMail->Subject = $subject;
						$phpMail->AddAddress($to, 'X2CRM Administrator');
						$phpMail->MsgHTML($msg."<br />JSON Encoded Attributes:<br /><br />".json_encode($model->attributes));
						$phpMail->Send();
					}

					$attributes = $model->attributes;
					ksort($attributes);
					if (file_exists('failed_leads.csv')) {
						$fp = fopen('failed_leads.csv', "a+");
						fputcsv($fp, $attributes);
					} else {
						$fp = fopen('failed_leads.csv', "a+");
						fputcsv($fp, array_keys($attributes));
						fputcsv($fp, $attributes);
					}
					$this->_sendResponse(500, $msg);
					break;
				default:
					Yii::log(sprintf('Failed to save record of type %s due to errors: %s', $this->modelClass, CJSON::encode($model->errors)), 'api');
					// Errors occurred
					$msg = "<h1>Error</h1>";
					$msg .= sprintf("Couldn't create model <b>%s</b> due to errors:", $this->modelClass);
					$msg .= "<ul>";
					foreach ($model->errors as $attribute => $attr_errors) {
						$msg .= "<li>Attribute: $attribute</li>";
						$msg .= "<ul>";
						foreach ($attr_errors as $attr_error)
							$msg .= "<li>$attr_error</li>";
						$msg .= "</ul>";
					}
					$msg .= "</ul>";
					$this->_sendResponse(500, $msg);
			}
		}
	}

	/**
	 * Updates a preexisting record.
	 *
	 * Usage of this function is very similar to {@link actionCreate}, although
	 * it requires the "id" parameter that corresponds to the (auto-increment)
	 * id field of the record in the database. Thus, URLs for post requests to
	 * this API function should be formatted as follows:
	 *
	 * index.php/api/update/model/[model name]/id/[record id]
	 *
	 * The attributes of the model should be submitted in the $_POST array along
	 * with 'authUser' and 'authPassword' just as in create.
	 */
	public function actionUpdate() {
		$modelSingle = X2Model::model($this->modelClass);
		$model = $modelSingle->findByPkInArray($_GET);

		// Did we find the requested model? If not, raise an error
		if (is_null($model))
			$this->_respondBadPk($modelSingle, $_GET);

		$this->modelSetUsernameFields($model);
		$model->setX2Fields($_POST);

		// Try to save the model and perform special post-save operations based on
		// each class:
		if ($model->save()) {
			switch ($this->modelClass) {
				case 'Actions':
					$model->syncGoogleCalendar('update');
					break;
				default:
					$this->_sendResponse(200, $model->attributes,true);
			}
			$this->addResponseProperty('model',$model->attributes);
			$this->_sendResponse(200, 'Model created successfully');
		} else {
			// Errors occurred
			$this->addResponseProperty('modelErrors',$model->errors);
			$msg = "<h1>Error</h1>";
			$msg .= sprintf("Couldn't update model <b>%s</b>", $_GET['model']);
			$msg .= "<ul>";
			foreach ($model->errors as $attribute => $attr_errors) {
				$msg .= "<li>Attribute: $attribute</li>";
				$msg .= "<ul>";
				foreach ($attr_errors as $attr_error)
					$msg .= "<li>$attr_error</li>";
				$msg .= "</ul>";
			}
			$msg .= "</ul>";
			$this->_sendResponse(500, $msg);
		}
	}

	/**
	 * Records a phone call as a notification.
	 *
	 * Given a phone number, if a contact matching that phone number exists, a
	 * notification assigned to that contact's assignee will be created.
	 * Software-based telephony systems such as Asterisk can thus immediately
	 * notify sales reps of a phone call by making a cURL request to a url
	 * formatted as follows:
	 *
	 * api/voip/data/[phone number]
	 *
	 * (Note: the phone number itself must not contain anything but digits, i.e.
	 * no periods or dashes.)
	 *
	 * For Asterisk, one possible integration method is to insert into the
	 * dialplan, at the appropriate position, a call to a script that uses
	 * {@link http://phpagi.sourceforge.net/ PHPAGI} to extract the phone
	 * number. The script can then make the necessary request to this action.
	 */
	public function actionVoip() {

		if (isset($_GET['data'])) {

			$matches = array();
			if (preg_match('/\d{10,}/', $_GET['data'], $matches)) {

				$contact = X2Model::model('Contacts')->findByAttributes(array('phone' => $matches[0]));
				if (isset($contact)) {

					$contact->updateLastActivity();

					$notif = new Notification;
					$notif->type = 'voip_call';
					$notif->user = $contact->assignedTo;
					$notif->modelType = 'Contacts';
					$notif->modelId = $contact->id;
					$notif->value = $matches[0];
					$notif->createDate = time();
					$notif->save();

					X2Flow::trigger('RecordVoipInboundTrigger', array(
						'model' => $contact,
						'number' => $matches[0]
					));

					echo 'Notification created.';
				} else {
					echo 'No contact found.';
					// $notif = new Notification;
					// $notif->type = 'voip_call';
					// $notif->user = ?;
					// $notif->modelType = 'Contacts';
					// $notif->value = $matches[0];
					// $notif->createDate = time();
					// $notif->save();
				}
			} else
				echo 'Invalid phone number format.';
		}
	}

	/**
	 * Obtain a model by its record ID.
	 *
	 * Looks up a model by its record ID and responds with its attributes as a
	 * JSON-encoded string.
	 *
	 * URLs to use this function:
	 * index.php/view/id/[record id]
	 *
	 * Include 'authUser' and 'authPassword' just like in create and update.
	 */
	public function actionView() {
		$modelSingle = X2Model::model($this->modelClass);
		$model = $modelSingle->findByPkInArray($_GET);
		// Did we find the requested model? If not, raise an error
		if (is_null($model)) {
			// Tell that the primary key is missing or incorrect.
			$this->_respondBadPk($modelSingle,$_GET);
		} else
			$this->_sendResponse(200, $model->attributes,true);
	}

	public function actionList() {
		$accessLevel = $this->getAccessLevel('Contacts', $user);
		$listId = $_POST['id'];
		$list = X2List::model()->findByPk($listId);
		if (isset($list)) {
			//$list=X2List::load($listId);
		} else {
			$list = X2List::model()->findByAttributes(array('name' => $listId));
			if (isset($list)) {
				$listId = $list->id;
				//$list=X2List::load($listId);
			} else {
				$this->_sendResponse(404, 'No list found with id: ' . $_POST['id']);
			}
		}
		$model = new Contacts('search');
		$dataProvider = $model->searchList($listId, 10);
		$data = $dataProvider->getData();
		printR($dataProvider, true);
		$this->_sendResponse(200, json_encode($data),true);
	}

	/**
	 * Get a list of all users in the app.
	 */
	public function actionListUsers() {
		$access = $this->actionCheckPermissions('UsersAccess');
		$fullAccess = false;
		if($access)
			$fullAccess = $this->actionCheckPermissions('UsersFullAccess');
		if(!$access)
			$this->sendResponse(403,"User {$this->user} does not have permission to run UsersIndex");
		$users = User::model()->findAll();
		$userAttr = User::model()->attributes;
		if(!$fullAccess) {
			unset($userAttr['password']);
			unset($userAttr['userKey']);
		}
		$userAttr = array_keys($userAttr);
		$userList = array();
		foreach($users as $user) {
			$userList[] = $user->getAttributes($userAttr);
		}
		$this->_sendResponse(200,$userList,true);
	}

	/**
	 * Obtain a model using search parameters.
	 *
	 * Finds a record based on its first name, last name, and/or email and responds with its full
	 * attributes as a JSON-encoded string.
	 *
	 * URLs to use this function:
	 * index.php/api/lookup/[model name]/[attribute]/[value]/...
	 *
	 * 'authUser' and 'authPassword' are required.
	 */
	public function actionLookup() {
		$attrs = $_POST;
		unset($attrs['user']);
		unset($attrs['userKey']);

		$model = X2Model::model($this->modelClass)->findByAttributes($attrs);

		// Did we find the requested model? If not, raise an error
		if (is_null($model)) {
			$this->_sendResponse(404, 'No Item found with specified attributes.');
		} else {
			$this->_sendResponse(200, $model->attributes,true);
		}
	}

	/**
	 * Delete a model record by primary key value.
	 */
	public function actionDelete() {
		$model = X2Model::model($this->modelClass)->findByPkInArray($_POST);
		// Was a model found? If not, raise an error
		if (is_null($model))
			$this->_sendResponse(400, sprintf("Error: Didn't find any model <b>%s</b> with primary key value <b>%s</b>.", $this->modelClass,is_array($pk)?implode('-',$pk):$pk));

		if ($this->modelClass === 'Actions')
				$model->syncGoogleCalendar('delete');

		// Delete the model
		$num = $model->delete();
		if ($num > 0) {
			$this->_sendResponse(200, 1);
		} else
			$this->_sendResponse(500, sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_POST['id']));
	}

	/**
	 * Respond to a request with a specified status code and body.
	 *
	 * @param integer $status The HTTP status code.
	 * @param string $body The body of the response message, or the object to be
	 *  JSON-encoded in the response (if "direct" is used)
	 * @param bool $direct Whether the body should be JSON-encoded and returned
	 *	directly instead of putting it into the standard response object's
	 *	"model" property or the like.
	 */
	protected function _sendResponse($status = 200, $body = '',$direct = false) {
		// set the status
		header("HTTP/1.1 $status " . $this->_getStatusCodeMessage($status));
		if($direct) {
			header('Content-type: application/json');
			echo CJSON::encode($body);
			Yii::app()->end();
		}
		
		// we need to create the body if none is passed
		if ($body == '') {
			// create some body messages
			$message = '';

			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch ($status) {
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}

			// servers don't always have a signature turned on
			// (this is an apache directive "ServerSignature On")
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

			// this should be templated in a real-world solution
			$body = '<h1>' . $this->_getStatusCodeMessage($status) . '</h1>
		<p>' . $message . '</p>
		<hr />
		<address>' . $signature . '</address>';
		}
		// data.message is $body, data.error is true if the return status isn't 200 for success
		self::respond($body, $status != 200);
	}

	/**
	 * Obtain an appropriate message for a given HTTP response code.
	 *
	 * @param integer $status
	 * @return string
	 */
	protected function _getStatusCodeMessage($status) {
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
			200 => 'OK',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	/**
	 * Tells the client that the primary key was bad or missing.
	 * @param X2Model $modelSingle
	 * @param array $params
	 */
	protected function _respondBadPk(X2Model $modelSingle, array $params) {
		$pkc = $modelSingle->tableSchema->primaryKey;
		$pk = array();
		if (is_array($pkc)) { // Composite primary key
			foreach ($pkc as $colName) {
				if (array_key_exists($colName, $_GET)) {
					$pk[$colName] = $params[$colName];
				}
			}
			$pkc = array_keys($pkc);
		} else {
			if (array_key_exists($pkc, $params))
				$pk[$pkc] = $params[$pkc];
			$pkc = array($pkc);
		}
		if (!empty($pk)) {
			$this->_sendResponse(404, "No record of model {$this->modelClass} found with specified primary key value (" . implode('-', array_keys($pk)) . '): ' . (implode('-', array_values($pk))));
		} else {
			$this->_sendResponse(400, sprintf("No GET parameters matching primary key column(s) <b>%s</b> for model <b>%s</b>.",implode('-',$pkc),$this->modelClass));
		}
	}

	/**
	 * Checks credentials for API access
	 *
	 * @param CFilterChain $filterChain
	 */
	public function filterAuthenticate($filterChain) {
		$haveCred = false;
		Yii::log("Checking user record.", 'api');
		if (Yii::app()->request->requestType == 'POST') {
			$haveCred = isset($_POST['userKey']) && isset($_POST['user']);
			$params = $_POST;
		} else {
			$haveCred = isset($_GET['userKey']) && isset($_GET['user']);
			$params = $_GET;
		}

		if ($haveCred) {
			$this->user = User::model()->findByAttributes(array('username' => $params['user'], 'userKey' => $params['userKey']));
			if ((bool) $this->user) {
				if (!empty($this->user->userKey))
					$filterChain->run();
				else
					$this->_sendResponse(403, "User \"{$this->user->username}\" cannot use API; userKey not set.");
			} else {
				Yii::log("Authentication failed; invalid user credentials; IP = {$_SERVER['REMOTE_ADDR']}; get or post params =  " . CJSON::encode($params).'', 'api');
				$this->_sendResponse(401, "Invalid user credentials.");
			}
		} else {
			Yii::log('No user credentials provided; IP = '.$_SERVER['REMOTE_ADDR'],'api');
			$this->_sendResponse(401, "No user credentials provided.");
		}
	}

	/**
	 * Basic permissions check filter.
	 *
	 * It is meant to simplify the simpler actions where named after existing
	 * actions (or actions listed among the keys of {@link actionAuthItemMap})
	 *
	 * @param type $filterChain
	 */
	public function filterCheckCRUDPermissions($filterChain) {
		$model = new $this->modelClass;
		$module = ucfirst($model->module);
		$action = $this->action->id;
		if(array_key_exists($action,$this->actionAuthItemMap))
			$action = $this->actionAuthItemMap[$action];
		else
			$action = ucfirst($action);
		$level = $this->actionCheckPermissions($module . $action);
		if($level)
			$filterChain->run();
		else {
			Yii::log("User \"{$this->user->username}\" denied API action; does not have permission for $module$action",'api');
			$this->_sendResponse(403, 'This user does not have permission to perform operation "'.$action."\" on model <b>{$this->modelClass}</b>");
		}
	}

	public function filterNoSession($filterChain) {
		Yii::app()->params->noSession = true;
		$filterChain->run();
	}

	/**
	 * Ensures that the "model" parameter is present and valid.
	 *
	 * @param CFilterChain $filterChain
	 */
	public function filterValidModel($filterChain) {
		if (!isset($this->modelClass)) {
			Yii::log("Checking for valid model class...", 'api');
			$noModel = empty($_GET['model']);
			if(!$noModel)
				$noModel = preg_match('/^\s*$/',$_GET['model']);
			if ($noModel) {
				Yii::log('Parameter "model" missing.', 'api');
				$this->_sendResponse(400, "Model class name required.");
			}
			if (!class_exists($_GET['model'])) {
				Yii::log("Class {$_GET['model']} not found.", 'api');
				$this->_sendResponse(501, "Model class \"{$_GET['model']}\" not found or does not exist.");
			}
			$modelRef = new $_GET['model'];
			if (get_parent_class($modelRef) != 'X2Model') {
				Yii::log("Class {$_GET['model']} is not a child of X2Model.", 'api');
				$this->_sendResponse(403, "Model class \"{$_GET['model']}\" is not a child of X2Model and cannot be used in API calls.");
			}
			// We're all clear to proceed
			$this->modelClass = $_GET['model'];

			// Set user for the model:
			X2Model::model($this->modelClass)->setSuModel($this->user);
		}
		$filterChain->run();
	}

	/**
	 * A quick and dirty hack for filling in the gaps if the model requested
	 * does not make use of the changelog behavior (which takes care of that
	 * automatically)
	 */
	public function modelSetUsernameFields(&$model) {
		$restrictedAttr = array('updatedBy');
		if($this->action->id == 'create')
			$restrictedAttr[] = 'createdBy';
		foreach($restrictedAttr as $attr){
			if($model->hasAttribute($attr)){
				$model->$attr = $this->user->username;
			}
		}
		if($model->hasAttribute('assignedTo')){
			if(array_key_exists('assignedTo', $_POST)){
				$model->assignedTo = $_POST['assignedTo'];
			}else{
				$model->assignedTo = $this->user->username;
			}
		}
	}
}
