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




Yii::import('application.modules.users.models.*');

/**
 * Remote data insertion & lookup API
 * @package application.controllers
 * @author Jake Houser <jake@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 */
class ApiController extends x2base {

    /**
     * @var string The model that the API is currently being used with.
     */
    public $modelClass;
    public $user;
    private $_model;

    /**
     * Auth items to be checked against in {@link filterCheckPermissions} where
     * their action isn't the same as the prefix.
     */
    public $actionAuthItemMap = array(
        'lookUp' => 'View',
    );

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'LeadRoutingBehavior' => array(
                'class' => 'LeadRoutingBehavior'
            ),
            'responds' => array(
                'class' => 'application.components.ResponseBehavior',
                'isConsole' => false,
                'exitNonFatal' => false,
                'longErrorTrace' => false,
            ),
            'CommonControllerBehavior' => array(
                'class' => 'application.components.behaviors.CommonControllerBehavior',
                'redirectOnNullModel' => false,
                'throwOnNullModel' => false
            ),
        ));
    }

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'noSession',
            'available',
            'authenticate - voip,webListener,targetedContent,x2cron',
            'validModel + create,view,lookup,update,delete,relationships,tags',
            'checkCRUDPermissions + create,view,lookup,update,delete',
        );
    }

    public function actions() {
        $actions = array();
        if (class_exists('WebListenerAction'))
            $actions['webListener'] = array('class' => 'WebListenerAction');
        if (class_exists('TargetedContentAction'))
            $actions['targetedContent'] = array('class' => 'TargetedContentAction');
        if (class_exists('X2CronAction'))
            $actions['x2cron'] = array('class' => 'X2CronAction');
        if (class_exists('EmailImportAction'))
            $actions['dropbox'] = array('class' => 'EmailImportAction');
        return $actions;
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
        $this->log("Checking user permissions for API transaction.");
        $auth = Yii::app()->authManager;
        $item = $auth->getAuthItem($action);
        $authenticated = $auth->getAuthItem('DefaultRole');
        if (isset($item)) {
            $access = false; // Auth item exists; set true only through verification
            $userId = null;
            $access = Yii::app()->params->isAdmin;
            $access = $authenticated->checkAccess($action);

            if (!$access) { // Skip this if we already have access
                if ($username != null) { // Override current API user if any
                    $userId = User::model()->findByAttributes(array('username' => $username))->id;
                } elseif (isset($this->user)) { // Called from within another API action that required credentials
                    $userId = $this->user->id;
                }
            }

            if ($userId != null && !$access) { // Skip this if we already have access
                $access = $access || $auth->checkAccess($action, $userId);
            }
        } elseif ($this->action->id != 'checkPermissions')
            $this->log(sprintf("Auth item %s not found. Permitting action %s.", $action, $this->action->id));

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
     * a valid username and API key must be submitted under the indices
     * 'user' and 'userKey' for the request to be authenticated.
     */
    public function actionCreate() {
        // Get an instance of the respective model
        $model = $this->getModel(true);
        $model->setX2Fields($_POST);

        if ($this->modelClass === 'Contacts') {
            if (isset($_POST['trackingKey'])) {
                // key is read-only, won't be set by setX2Fields
                $model->trackingKey = $_POST['trackingKey'];
            }
            if (isset($_POST['_leadRouting']) && $_POST['_leadRouting']) {
                $model->assignedTo = $this->getNextAssignee();
            }
        }

        $setUserFields = false;
        // $scenario = 'Changelog behavior in effect.';
        if (!empty($_POST['createDate'])) { // If create date is being manually set, i.e. an import, don't overwrite
            $model->disableBehavior('changelog');
            $setUserFields = true;
            // $scenario = 'Changelog behavior disabled; create date not empty.';
        }
        try {
            $editingUsername = $model->editingUsername;
            // $scenario .= ' Model or one of its behaviors has a property "editingUsername".';
        } catch (Exception $e) {
            $setUserFields = true;
            // $scenario .= ' Model nor its behaviors have a property "editingUsername".';
        }
        // $this->response['scenario'] = $scenario;
        if ($setUserFields)
            $this->modelSetUsernameFields($model);
        // Attempt to save the model, and perform special post-save (or error)
        // operations based on the model type:
        if ($model->save()) { // New record successfully created
            $this->response['model'] = $model->attributes;
            $message = "A {$this->modelClass} type record was created"; //sprintf(' <b>%s</b> was created',$this->modelClass);
            switch ($this->modelClass) {
                // Special extra actions to take for each model type:
                case 'Actions':
                    // Set actionDescription manually since it's stored in a different table
                    // which is updated using the magic getter:
                    if (isset($_POST['actionDescription'])) {
                        $model->actionDescription = $_POST['actionDescription'];
                    }
                    $message .= " with description {$model->actionDescription}";
                    break;
                case 'Contacts':
                    $message .= " with name {$model->name}";
                    break;
            }
            $this->_sendResponse(200, $message);
        } else { // API model creation failure
            $this->response['modelErrors'] = $model->errors;
            switch ($this->modelClass) {
                case 'Contacts':
                    $this->log(sprintf('Failed to save record of type %s due to errors: %s', $this->modelClass, CJSON::encode($model->errors)));
                    $msg = $this->validationMsg('create', $model);
                    // Special lead failure notification in the app and through email:

                    $notif = new Notification;
                    $notif->user = 'admin';
                    $notif->type = 'lead_failure';
                    $notif->createdBy = $this->user->username;
                    $notif->createDate = time();
                    $notif->save();

                    $to = Yii::app()->settings->webLeadEmail;
                    $subject = "Web Lead Failure";
                    if (!Yii::app()->params->automatedTesting) {
                        // Send notification of failure
                        $responderId = Credentials::model()->getDefaultUserAccount(Credentials::$sysUseId['systemNotificationEmail'], 'email');
                        if ($responderId != Credentials::LEGACY_ID) { // Using configured 3rd-party email account
                            $this->sendUserEmail(array('to' => array(array($to, 'X2Engine Administrator'))), $subject, $msg, null, $responderId);
                        } else { // Using plain old PHP mail
                            $phpMail = $this->getPhpMailer();
                            $fromEmail = Yii::app()->settings->emailFromAddr;
                            $fromName = Yii::app()->settings->emailFromName;
                            $phpMail->AddReplyTo($fromEmail, $fromName);
                            $phpMail->SetFrom($fromEmail, $fromName);
                            $phpMail->Subject = $subject;
                            $phpMail->AddAddress($to, 'X2Engine Administrator');
                            $phpMail->MsgHTML($msg . "<br />JSON Encoded Attributes:<br /><br />" . json_encode($model->attributes));
                            $phpMail->Send();
                        }
                    }

                    $attributes = $model->attributes;
                    ksort($attributes);

                    if (file_exists($flCsv = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', 'failed_leads.csv')))) {
                        $fp = fopen($flCsv, 'a+');
                        fputcsv($fp, $attributes);
                    } else {
                        $fp = fopen($flCsv, 'w+');
                        fputcsv($fp, array_keys($attributes));
                        fputcsv($fp, $attributes);
                    }
                    $this->_sendResponse(500, $msg);
                    break;
                default:
                    $this->log(sprintf('Failed to save record of type %s due to errors: %s', $this->modelClass, CJSON::encode($model->errors)));
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
     * Delete a model record by primary key value.
     */
    public function actionDelete() {
        $model = $this->model;
        // Delete the model
        $num = $model->delete();
        if ($num > 0) {
            $this->_sendResponse(200, 1);
        } else
            $this->_sendResponse(500, sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_POST['id']));
    }

    /**
     * Gets a list of contacts.
     */
    public function actionList() {
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
        $this->_sendResponse(200, json_encode($data), true);
    }

    /**
     * Get a list of all users in the app.
     */
    public function actionListUsers() {
        $access = $this->actionCheckPermissions('UsersAccess');
        $fullAccess = false;
        if ($access)
            $fullAccess = $this->actionCheckPermissions('UsersFullAccess');
        if (!$access)
            $this->sendResponse(403, "User {$this->user} does not have permission to run UsersIndex");
        $users = User::model()->findAll();
        $userAttr = User::model()->attributes;
        if (!$fullAccess) {
            unset($userAttr['password']);
            unset($userAttr['userKey']);
        }
        $userAttr = array_keys($userAttr);
        $userList = array();
        foreach ($users as $user) {
            $userList[] = $user->getAttributes($userAttr);
        }
        $this->_sendResponse(200, $userList, true);
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
     * 'user' and 'userKey' are required.
     */
    public function actionLookup() {
        $attrs = $_POST;
        unset($attrs['user']);
        unset($attrs['userKey']);
        $tempModel = new $this->modelClass('search');

        // Use only attributes that the model has
        $attrs = array_intersect_key($attrs, $tempModel->attributes);
        $model = X2Model::model($this->modelClass)->findByAttributes($attrs);

        // Did we find the requested model? If not, raise an error
        if (is_null($model)) {
            $this->_sendResponse(404, 'No Item found with specified attributes.');
        } else {
            $this->_sendResponse(200, $model->attributes, true);
        }
    }

    /**
     * REST-ful API method for adding and removing relationships between records.
     */
    public function actionRelationship() {
        $rType = Yii::app()->request->requestType;
        switch ($rType) {
            case 'GET': // Look up relationships on a model
                $attr = array('firstType' => $_GET['model']);
                $relationships = Relationships::model()->findAllByAttributes(array_merge(array_intersect_key($_GET, array_flip(Relationships::model()->safeAttributeNames)), $attr));
                if (empty($relationships))
                    $this->_sendResponse(404, Yii::t('api', 'No relationships found.'));
                else
                    $this->_sendResponse(200, array_map(function($r) {
                                return $r->attributes;
                            }, $relationships), 1);
            case 'POST': // Add a new relationship to model
                $relationship = new Relationships('api');
                $relationship->attributes = $_POST;
                $relationship->firstType = $_GET['model'];
                if ($relationship->validate()) {
                    $existingRelationship = Relationships::model()->findByAttributes(array_intersect_key($relationship->attributes, array_flip(array('firstType', 'secondType', 'firstId', 'secondId'))));
                    if ($existingRelationship)
                        $this->_sendResponse(200, Yii::t('api', 'Such a relationship already exists.'));
                    if ($relationship->save()) {
                        $this->_sendResponse(200, Yii::t('api', 'Successfully saved a relationship.'));
                    } else {
                        $this->_sendResponse(500, Yii::t('api', 'Failed to save relationship record for unknown reason.'));
                    }
                } else {
                    $this->response['modelErrors'] = $relationship->errors;
                    $this->_sendResponse(400, $this->validationMsg('create', $relationship));
                }
                break;
            case 'DELETE':
                if (!isset($_GET['secondType'], $_GET['firstId'], $_GET['secondId']))
                    $this->_sendResponse(400, Yii::t('api', 'Cannot delete; no parameters specified for finding a relationship record to delete.'));
                $relationships = Relationships::model()->findAllByAttributes(array_merge(array('firstType' => $_GET['model']), array_intersect_key($_GET, array_flip(Relationships::model()->attributeNames()))));
                if (empty($relationships))
                    $this->_sendResponse(404, Yii::t('api', 'No relationships deleted; none were found matching specfied parameters.'));
                $n_d = 0;
                $n_d_t = count($relationships);
                foreach ($relationships as $model) {
                    $n_d += $model->delete() ? 1 : 0;
                }
                if ($n_d == $n_d_t)
                    $this->_sendResponse(200, Yii::t('api', '{n} relationships deleted.', array('{n}' => $n_d)));
                else
                    $this->_sendResponse(500, Yii::t('api', 'One or more relationships could not be deleted.'));
                break;
            default:
                $this->_sendResponse(400, Yii::t('api', 'Request type not supported for this action.'));
                break;
                ;
        }
    }

    /**
     * Operations involving tags associated with a model.
     *
     * There needs to be the tagged model's primary key value in the URL's
     * parameters, in addition to the model's class. If DELETE, or POST, there
     * needs to be an array of tags, JSON-encoded, in postdata, to delete or
     * add to the model.
     */
    public function actionTags() {
        $model = $this->model;
        $rType = Yii::app()->request->requestType;
        switch ($rType) {
            case 'GET':
                // Query all tags associated with a model.
                $this->_sendResponse(200, $model->getTags(), true);
            case 'POST':
                // Add tag(s).
                if (array_key_exists('tags', $_POST))
                    $tags = json_decode($_POST['tags'], 1);
                else if (array_key_exists('tag', $_POST))
                    $tags = array($_POST['tag']);
                else
                    $this->_sendResponse(400, 'Parameter "tags" (json-encoded list of tags) or "tag" (single tag to add) requried.');
                if ($model->addTags($tags))
                    $this->_sendResponse(200, sprintf('Record "%s" (%s) tagged with "%s"', $model->name, get_class($model), implode('","', $tags)));
                else
                    $this->_sendResponse(500, Yii::t('api', 'Tags not added.'));
            case 'DELETE':
                // Delete a tag
                if (array_key_exists('tag', $_GET))
                    $tag = "#" . ltrim($_GET['tag'], '#'); // Works whether or not the hash is attached. It is difficult to add the tag due to how it's a special URL character.
                else
                    $this->_sendResponse(400, 'Please specify a tag to be deleted.');
                $removed = $model->removeTags($tag);
                if ($removed)
                    $this->_sendResponse(200, sprintf('Tag "%s" deleted from "%s" (%s).', $tag, $model->name, get_class($model))); // .'$_GET='.var_export($_GET,1).'; $_POST='.var_export($_POST,1).'; uri='.$_SERVER['REQUEST_URI']);
                else
                    $this->_sendResponse(404, 'Did not delete any existing tags.');
                break;
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
     * with 'user' and 'userKey' just as in create.
     */
    public function actionUpdate() {
        $model = $this->model;
        $model->setX2Fields($_POST);

        // Try to save the model and perform special post-save operations based on
        // each class:
        if ($model->save()) {
            switch ($this->modelClass) {
                default:
                    $this->_sendResponse(200, $model->attributes, true);
            }
            $this->response['model'] = $model->attributes;
            $this->_sendResponse(200, 'Model created successfully');
        } else {
            // Errors occurred
            $this->response['modelErrors'] = $model->errors;
            $msg = $this->validationMsg('update', $model);
            $this->_sendResponse(500, $msg);
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
     * Include 'user' and 'userKey' just like in create and update.
     */
    public function actionView() {
        $this->_sendResponse(200, $this->model->attributes, true);
    }

    /**
     * Records a phone call as a notification.
     *
     * Given a phone number, if a contact matching that phone number exists, a
     * notification assigned to that contact's assignee will be created.
     * 
     * Software-based telephony systems such as Asterisk can thus immediately
     * notify sales reps of a phone call by making a cURL request to a url
     * formatted as follows:
     *
     * ~/index.php/api/voip/data/<phone number>
     *
     * (Note: the phone number itself must not contain anything but digits, i.e.
     * no periods or dashes.)
     *
     * For Asterisk, one possible integration method is to insert into the
     * dial plan, at the appropriate position, a call to a script that uses
     * {@link http://phpagi.sourceforge.net/ PHPAGI} to extract the phone
     * number. The script can then make the necessary request to this action.
     * 
     * @param bool $actionHist If set to 1, create an action history item for
     * the contact.
     */
    public function actionVoip($actionHist = 0) {
        $data = $_GET['id'];

        // Checks if a phone number was given
        if (!isset($data)) {
            $this->_sendResponse(400, 'Phone number required as "data" '
                    . 'URL parameter.');
            return;
        }

        // Create array to store phone number from regex
        $matches = array();

        // Checks if phone number format is correct
        if (!preg_match('/^\s*(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})' .
                        '[-. ]*(\d{4})(?: *x(\d+))?\s*$/', $data, $matches)) {
            $this->_sendResponse(400, 'Invalid phone number format.');
            return;
        }

        $number = $matches[0];

        // Creates model criteria
        $phoneCrit = new CDbCriteria(array(
            'condition' => "modelType='Contacts' AND number LIKE :number",
            'params' => array(':number' => "%$number%")
        ));
        $phoneCrit->join = 'join x2_contacts on modelId=x2_contacts.id AND '
                . Contacts::model()->getHiddenCondition('x2_contacts');

        // Finds phone number through model given model criteria
        $phoneNumber = PhoneNumber::model()->find($phoneCrit);

        // Check if phone number was not found through model
        if (empty($phoneNumber)) {
            $this->_sendResponse(404, 'No matching phone number found.');
            return;
        }

        // Find contact through phone number
        $contact = Contacts::model()->findByPk($phoneNumber->modelId);

        // Checks if contact not found
        if (!isset($contact)) {
            $this->_sendResponse(404, 'Phone number record refers to a '
                    . 'contact that no longer exists.');
            return;
        }

        // Disables changelog behavior and updates last activity
        $contact->disableBehavior('changelog');
        $contact->updateLastActivity();

        // Gets users that contact is assigned to
        $assignees = array($contact->assignedTo);

        // If contact is not assigned to any specific user
        if ($contact->assignedTo == 'Anyone' || $contact->assignedTo == null) {
            $users = User::model()->findAll();
            $assignees = array_map(function($user) {
                return $user->username;
            }, $users);
        }

        // Create arrays to keep track of successes and failures
        $usersSuccess = array();
        $usersFailure = array();

        // Creates notifications to assigned users
        $time = time();
        $formattedNumber = PhoneNumber::model()->formatPhoneNumber($number);
        foreach ($assignees as $user) {
            $notif = new Notification();
            $notif->type = 'voip_call';
            $notif->user = $user;
            $notif->modelType = 'Contacts';
            $notif->modelId = $contact->id;
            $notif->value = $formattedNumber;
            $notif->createDate = $time;
            if ($notif->save()) {
                $usersSuccess[] = $user;
            } else {
                $usersFailure = array();
            }
        }

        // Create action history for voip
        if ($actionHist) {
            $action = new Actions();
            $action->assignedTo = 'Anyone';
            $action->visibility = 1;
            $action->associationId = $contact->id;
            $action->associationType = 'contacts';
            $action->associationName = $contact->name;
            $action->dueDate = $time;
            $action->createDate = $time;
            $action->completeDate = $time;
            $action->lastUpdated = $time;
            $action->type = 'call';
            $action->complete = 'Yes';
            $action->completedBy = 'Anyone';
            $action->actionText = Yii::t('app', 'Phone system reported'
                            . ' inbound call from contact.');
            $action->save();
        }

        // Checks for failures
        $failure = count($usersSuccess) == 0;
        $partialFailure = count($usersFailure) > 0;

        // If failure
        if ($failure) {
            $message = 'Saving notifications failed.';
        } else {
            // Voip inbound trigger
            X2Flow::trigger('VoipInboundTrigger', array(
                'model' => $contact
            ));

            $message = 'Notifications created for user(s): ' .
                    implode(',', $usersSuccess);

            // If partial failure
            if ($partialFailure) {
                $message .= '; saving notifications failed for users(s): ' .
                        implode(',', $usersFailure);
            }
        }

        // Create an event record for the feed:
        $event = new Events();
        $event->type = 'voip_call';
        $event->associationType = get_class($contact);
        $event->associationId = $contact->id;
        $event->save();

        $this->_sendResponse($failure ? 500 : 200, $message);
    }

    /**
     * Checks the GET parameters for a valid model class.
     */
    public function checkValidModel() {
        $this->log("Checking for valid model class.");
        $noModel = empty($_GET['model']);
        if (!$noModel)
            $noModel = preg_match('/^\s*$/', $_GET['model']);
        if ($noModel) {
            $this->log('Parameter "model" missing.');
            $this->_sendResponse(400, "Model class name required."); // .'$_GET='.var_export($_GET,1).'; $_POST='.var_export($_POST,1).'; uri='.$_SERVER['REQUEST_URI']);
        }
        if (!class_exists($_GET['model'])) {
            $this->log("Class {$_GET['model']} not found.");
            $this->_sendResponse(501, "Model class \"{$_GET['model']}\" not found or does not exist.");
        }
        $modelRef = new $_GET['model'];
        if (get_parent_class($modelRef) != 'X2Model') {
            $this->log("Class {$_GET['model']} is not a child of X2Model.");
            $this->_sendResponse(403, "Model class \"{$_GET['model']}\" is not a child of X2Model and cannot be used in API calls.");
        }
        // We're all clear to proceed
        $this->modelClass = $_GET['model'];
    }

    /**
     * Checks credentials for API access
     *
     * @param CFilterChain $filterChain
     */
    public function filterAuthenticate($filterChain) {
        $haveCred = false;
        $this->log("Checking user record.");
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
                Yii::app()->suModel = $this->user;
                if (!empty($this->user->userKey)) {
                    Yii::app()->params->groups = Groups::getUserGroups($this->user->id);
                    Yii::app()->params->roles = Roles::getUserRoles($this->user->id);
                    // Determine if the API user is admin (so that Yii::app()->params->isAdmin gets set properly):
                    $roles = RoleToUser::model()->findAllByAttributes(array('userId' => $this->user->id));
                    $access = false;
                    $auth = Yii::app()->authManager;
                    foreach ($roles as $role) {
                        $access = $access || $auth->checkAccess('AdminIndex', $role->roleId);
                    }
                    if ($access)
                        Yii::app()->params->isAdmin = true;
                    $filterChain->run();
                } else
                    $this->_sendResponse(403, "User \"{$this->user->username}\" cannot use API; userKey not set.");
            } else {
                $this->log("Authentication failed; invalid user credentials; IP = {$_SERVER['REMOTE_ADDR']}; get or post params =  " . CJSON::encode($params) . '');
                $this->_sendResponse(401, "Invalid user credentials.");
            }
        } else {
            $this->log('No user credentials provided; IP = ' . $_SERVER['REMOTE_ADDR']);
            $this->_sendResponse(401, "No user credentials provided.");
        }
    }

    /**
     * Sends the appropriate response if X2Engine is locked.
     * 
     * @param type $filterChain
     */
    public function filterAvailable($filterChain) {
        if (is_int(Yii::app()->locked)) {
            $this->_sendResponse(503, "X2Engine is currently undergoing maintenance. Please try again later.");
        }

        if (Yii::app()->settings->api2->disableLegacy) {
            $this->_sendResponse(503, "The legacy web API has been disabled.");
        }

        $filterChain->run();
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
        if (array_key_exists($action, $this->actionAuthItemMap))
            $action = $this->actionAuthItemMap[$action];
        else
            $action = ucfirst($action);
        $level = $this->actionCheckPermissions($module . $action);
        if ($level)
            $filterChain->run();
        else {
            $this->log("User \"{$this->user->username}\" denied API action; does not have permission for $module$action", 'application.automation.api');
            $this->_sendResponse(403, 'This user does not have permission to perform operation "' . $action . "\" on model <b>{$this->modelClass}</b>");
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
            $this->checkValidModel();
            // Set user for the model:
            Yii::app()->setSuModel($this->user);
        }
        $filterChain->run();
    }

    /**
     * Model getter; assumes $_GET parameters include the model's primary key,
     * but $_POST is included for backwards compatibility.
     */
    public function getModel($new = false) {
        if (!isset($this->_model)) {
            if ($new) {
                $this->_model = new $this->modelClass;
            } else {
                $mSingle = X2Model::model($this->modelClass);
                $params = array_merge($_POST, $_GET);
                $this->_model = $mSingle->findByPkInArray($params);
                // Was a model found? If not, raise an error
                if (empty($this->_model))
                    $this->_respondBadPk($mSingle, $params);
            }
        }
        return $this->_model;
    }

    /**
     * A quick and dirty hack for filling in the gaps if the model requested
     * does not make use of the changelog behavior (which takes care of that
     * automatically)
     */
    public function modelSetUsernameFields(&$model) {
        ChangeLogBehavior::usernameFieldsSet($model, $this->user->username);

        if ($model->hasAttribute('assignedTo')) {
            if (array_key_exists('assignedTo', $_POST)) {
                $model->assignedTo = $_POST['assignedTo'];
            } else {
                $model->assignedTo = $this->user->username;
            }
        }
    }

    /**
     * Compose a UI-friendly validation error summary in HTML
     *
     * @param type $action
     * @param type $model
     * @return string
     */
    public function validationMsg($action, $model) {
        $msg = "<h1>" . Yii::t('api', 'Error') . "</h1>";
        $msg .= Yii::t('api', "Couldn't perform {a} on model {m}", array('{a}' => $action, '{m}' => "<b>" . get_class($model) . "</b>"));
        $msg .= "<ul>";
        foreach ($model->errors as $attribute => $attr_errors) {
            $msg .= "<li>$attribute</li>";
            $msg .= "<ul>";
            foreach ($attr_errors as $attr_error)
                $msg .= "<li>$attr_error</li>";
            $msg .= "</ul>";
        }
        $msg .= "</ul>";
        return $msg;
    }

    public function log($message, $level = 'trace') {
        Yii::log($message, $level, 'application.api');
    }

    /**
     * Respond to a request with a specified status code and body.
     *
     * @param integer $status The HTTP status code.
     * @param string $body The body of the response message, or the object to be
     *  JSON-encoded in the response (if "direct" is used)
     * @param bool $direct Whether the body should be JSON-encoded and returned
     * 	directly instead of putting it into the standard response object's
     * 	"model" property or the like.
     */
    protected function _sendResponse($status = 200, $body = '', $direct = false) {
        // set the status

        if ($direct) {
            // Send the body without an envelope, i.e. the "message" or "error"
            // properties that are standard to ResponseBehavior.
            $this->response->body = json_encode($body);
            $this->response->sendHttp($status);
        }

        // Create the body if none is passed
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
                case 503:
                    $message = "X2Engine is currently unavailable.";
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
        $this->response->sendHttp($status, $body);
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
                if (array_key_exists($colName, $params)) {
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
            $this->_sendResponse(400, sprintf("No parameters matching primary key column(s) <b>%s</b> for model <b>%s</b>.", implode('-', $pkc), $this->modelClass));
        }
    }

}
