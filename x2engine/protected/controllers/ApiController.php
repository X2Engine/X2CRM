<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * Remote data insertion & lookup API
 * 
 * @package X2CRM.controllers
 * @author Jake Houser <jake@x2engine.com>
 */
class ApiController extends x2base {

    /**
     * @var string The model that the API is currently being used with.
     */
    public $modelClass = "";

    /**
     * Default response format either 'json' or 'xml'
     */
    private $format = 'json';

    /**
     * @return array action filters
     */
    public function filters() {
        return array();
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
        if (isset($_POST['authUser']) && isset($_POST['authPassword'])) {
            $username = $_POST['authUser'];
            $password = $_POST['authPassword'];
            $apiUser = User::model()->findByAttributes(array('username' => $username, 'password' => $password));
            if (isset($apiUser)) {
                switch ($_GET['model']) {
                    // Get an instance of the respective model
                    case 'Contacts':
                        $model = new Contacts;
                        $this->modelClass = "Contacts";
                        $temp = $model->attributes;
                        break;
                    case 'Actions':
                        $model = new Actions;
                        $this->modelClass = "Actions";
                        $temp = $model->attributes;
                        break;
                    case 'Accounts':
                        $model = new Accounts;
                        $this->modelClass = "Accounts";
                        $temp = $model->attributes;
                        break;
                    default:
                        $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>', $_GET['model']));
                        exit;
                }
                
                $model->setX2Fields($_POST);
                

                switch ($_GET['model']) {
                    case 'Contacts':
                        
                        Yii::import("application.modules.contacts.controllers.ContactsController");
                        $controller = new ContactsController('ContactsController');
                        if ($controller->create($model, $temp, '1')) {
                            $this->_sendResponse(200, sprintf('Model <b>%s</b> was created with name <b>%s</b>', $_GET['model'], $model->firstName . " " . $model->lastName));
                        } else {
                            // Errors occurred
                            $msg = "<h1>Error</h1>";
                            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
                            $msg .= "<ul>";
                            foreach ($model->errors as $attribute => $attr_errors) {
                                $msg .= "<li>Attribute: $attribute</li>";
                                $msg .= "<ul>";
                                foreach ($attr_errors as $attr_error){
                                    $msg .= "<li>$attr_error</li>";
                                }
                                $msg .= "</ul>";
                            }
                            $msg .= "</ul>";
                            $notif=new Notification;
                            $notif->user='admin';
                            $notif->type='lead_failure';
                            $notif->createdBy='API';
                            $notif->createDate = time();
                            $notif->save();
                            
                            $to=Yii::app()->params->admin->webLeadEmail;
                            $subject="Web Lead Failure";
                            $phpMail = $this->getPhpMailer();
                            $fromEmail = Yii::app()->params->admin->emailFromAddr;
                            $fromName = Yii::app()->params->admin->emailFromName;
                            $phpMail->AddReplyTo($fromEmail, $fromName);
                            $phpMail->SetFrom($fromEmail, $fromName);
                            $phpMail->Subject = $subject;
                            $phpMail->AddAddress($to, 'X2CRM Administrator');
                            $phpMail->MsgHTML($msg."<br />JSON Encoded Attributes:<br /><br />".json_encode($model->attributes));
                            $phpMail->Send();
                            
                            $attributes=$model->attributes;
                            ksort($attributes);
                            if(file_exists('failed_leads.csv')){
                                $fp=fopen('failed_leads.csv',"a+");
                                fputcsv($fp,$attributes);
                            }else{
                                $fp=fopen('failed_leads.csv',"a+");
                                fputcsv($fp,array_keys($attributes));
                                fputcsv($fp,$attributes);
                            }
                            $this->_sendResponse(500, $msg);
                        }
                        break;
                    case 'Accounts':
                        Yii::import("application.modules.accounts.controllers.AccountsController");
                        $controller = new AccountsController('AccountsController');
                        if ($controller->create($model, $temp, '1')) {
                            $this->_sendResponse(200, sprintf('Model <b>%s</b> was created with name <b>%s</b>', $_GET['model'], $model->name));
                        } else {
                            // Errors occurred
                            $msg = "<h1>Error</h1>";
                            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
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
                        break;
                    case 'Actions':
                        Yii::import("application.modules.actions.controllers.ActionsController");
                        $controller = new ActionsController('ActionsController');
                        if ($controller->create($model, $temp, '1')) {
                            $this->_sendResponse(200, sprintf('Model <b>%s</b> was created with description <b>%s</b>', $_GET['model'], $model->actionDescription));
                        } else {
                            // Errors occurred
                            $msg = "<h1>Error</h1>";
                            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
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
                        break;
                    default:
                        $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>', $_GET['model']));
                        exit;
                }
            } else {
                $this->_sendResponse(403, "Invalid user credentials.");
            }
        } else {
            $this->_sendResponse(403, "No user credentials provided.");
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
        if (isset($_POST['authUser']) && isset($_POST['authPassword'])) {
            $username = $_POST['authUser'];
            $password = $_POST['authPassword'];
            $apiUser = User::model()->findByAttributes(array('username' => $username, 'password' => $password));
            if (isset($apiUser)) {
                switch ($_GET['model']) {
                    // Find respective model
                    case 'Contacts':
                        $model = CActiveRecord::model('Contacts')->findByPk($_GET['id']);
                        $this->modelClass = "Contacts";
                        $temp = $model->attributes;
                        break;
                    case 'Actions':
                        $model = CActiveRecord::model('Actions')->findByPk($_GET['id']);
                        $this->modelClass = "Actions";
                        $temp = $model->attributes;
                        break;
                    case 'Accounts':
                        $model = CActiveRecord::model('Accounts')->findByPk($_GET['id']);
                        $this->modelClass = "Accounts";
                        $temp = $model->attributes;
                        break;
                    default:
                        $this->_sendResponse(501, sprintf('Error: Mode <b>update</b> is not implemented for model <b>%s</b>', $_GET['model']));
                        exit;
                }
                // Did we find the requested model? If not, raise an error
                if (is_null($model))
                    $this->_sendResponse(400, sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_GET['id']));

                // Try to assign PUT parameters to attributes
                foreach ($_POST as $var => $value) {
                    // Does the model have this attribute? If not raise an error
                    if ($model->hasAttribute($var))
                        $model->$var = $value;
                    else
                        $this->_sendResponse(500, sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var, $_GET['model']));
                }
                // Try to save the model
                switch ($_GET['model']) {
                    // Get an instance of the respective model
                    case 'Contacts':
                        Yii::import("application.modules.contacts.controllers.ContactsController");
                        $controller = new ContactsController('ContactsController');
                        if ($controller->create($model, $temp, '1')) {
                            $this->_sendResponse(200, sprintf('Model <b>%s</b> was updated with name <b>%s</b>', $_GET['model'], $model->firstName . " " . $model->lastName));
                        } else {
                            // Errors occurred
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
                        break;
                    case 'Accounts':
                        Yii::import("application.modules.accounts.controllers.AccountsController");
                        $controller = new AccountsController('AccountsController');
                        if ($controller->update($model, $temp, '1')) {
                            $this->_sendResponse(200, sprintf('Model <b>%s</b> with ID <b>%s</b> was updated.', $_GET['model'], $model->id));
                        } else {
                            // Errors occurred
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
                        break;
                    case 'Actions':
                        Yii::import("application.modules.actions.controllers.ActionsController");
                        $controller = new ActionsController('ActionsController');
                        if ($controller->update($model, $temp, '1')) {
                            $this->_sendResponse(200, sprintf('Model <b>%s</b> with ID <b>%s</b> was updated.', $_GET['model'], $model->id));
                        } else {
                            // Errors occurred
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
                        break;
                    default:
                        $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>', $_GET['model']));
                        exit;
                }
            } else {
                $this->_sendResponse(403, "Invalid user credentials.");
            }
        } else {
            $this->_sendResponse(403, "No user credentials provided.");
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

                $search = CActiveRecord::model('Contacts')->findByAttributes(array('phone' => $matches[0]));
                if (isset($search)) {

                    $notif = new Notification;
                    $notif->type = 'voip_call';
                    $notif->user = $search->assignedTo;
                    $notif->modelType = 'Contacts';
                    $notif->modelId = $search->id;
                    $notif->value = $matches[0];
                    $notif->createDate = time();
                    $notif->save();
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
        if (isset($_POST['authUser']) && isset($_POST['authPassword'])) {
            $username = $_POST['authUser'];
            $password = $_POST['authPassword'];
            $apiUser = User::model()->findByAttributes(array('username' => $username, 'password' => $password));
            if (isset($apiUser)) {
                if (!isset($_GET['id']))
                    $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing');

                switch ($_GET['model']) {
                    // Find respective model    
                    case 'Contacts':
                        $model = CActiveRecord::model('Contacts')->findByPk($_GET['id']);
                        break;
                    case 'Actions':
                        $model = CActiveRecord::model('Actions')->findByPk($_GET['id']);
                        break;
                    case 'Accounts':
                        $model = CActiveRecord::model('Accounts')->findByPk($_GET['id']);
                        break;
                    default:
                        $this->_sendResponse(501, sprintf(
                                        'Mode <b>view</b> is not implemented for model <b>%s</b>', $_GET['model']));
                        exit;
                }
                // Did we find the requested model? If not, raise an error
                if (is_null($model))
                    $this->_sendResponse(404, 'No Item found with id ' . $_GET['id']);
                else
                    $this->_sendResponse(200, CJSON::encode($model->attributes));
            }else {
                $this->_sendResponse(403, "Invalid user credentials.");
            }
        } else {
            $this->_sendResponse(403, "No credentials provided.");
        }
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
        if (isset($_POST['authUser']) && isset($_POST['authPassword'])) {
            $username = $_POST['authUser'];
            $password = $_POST['authPassword'];
            $apiUser = User::model()->findByAttributes(array('username' => $username, 'password' => $password));
            if (isset($apiUser)) {
                switch ($_GET['model']) {
                    // Find respective model    
                    case 'Contacts':
                        $attributes = array();
                        if (isset($_GET['firstName']))
                            $attributes['firstName'] = $_GET['firstName'];
                        if (isset($_GET['lastName']))
                            $attributes['lastName'] = $_GET['lastName'];
                        if (isset($_GET['email']))
                            $attributes['email'] = $_GET['email'];
                        $model = CActiveRecord::model('Contacts')->findByAttributes($attributes);
                        break;
                    default:
                        $this->_sendResponse(501, sprintf(
                                        'Mode <b>view</b> is not implemented for model <b>%s</b>', $_GET['model']));
                        exit;
                }
                // Did we find the requested model? If not, raise an error
                if (is_null($model))
                    $this->_sendResponse(404, 'No Item found with specified attributes.');
                else
                    $this->_sendResponse(200, CJSON::encode($model->attributes));
            }else {
                $this->_sendResponse(403, "Invalid user credentials.");
            }
        } else {
            $this->_sendResponse(403, "No credentials provided.");
        }
    }

    /**
     * Delete a model record by ID.
     * 
     * URLs to use this action:
     * index.php/api/delete/id/[id]
     * 
     * 'authUser' and 'authPassword' are required
     */
    public function actionDelete() {
        if (isset($_POST['authUser']) && isset($_POST['authPassword'])) {
            $username = $_POST['authUser'];
            $password = $_POST['authPassword'];
            $apiUser = User::model()->findByAttributes(array('username' => $username, 'password' => $password));
            if (isset($apiUser)) {
                switch ($_GET['model']) {
                    // Load the respective model
                    case 'Contacts':
                        $model = CActiveRecord::model('Contacts')->findByPk($_GET['id']);
                        break;
                    case 'Actions':
                        $model = CActiveRecord::model('Actions')->findByPk($_GET['id']);
                        break;
                    case 'Accounts':
                        $model = CActiveRecord::model('Accounts')->findByPk($_GET['id']);
                        break;
                    default:
                        $this->_sendResponse(501, sprintf('Error: Mode <b>delete</b> is not implemented for model <b>%s</b>', $_GET['model']));
                        exit;
                }
                // Was a model found? If not, raise an error
                if (is_null($model))
                    $this->_sendResponse(400, sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_GET['id']));

                // Delete the model
                $num = $model->delete();
                if ($num > 0)
                    $this->_sendResponse(200, sprintf("Model <b>%s</b> with ID <b>%s</b> has been deleted.", $_GET['model'], $_GET['id']));
                else
                    $this->_sendResponse(500, sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.", $_GET['model'], $_GET['id']));
            }else {
                $this->_sendResponse(403, "Invalid user credentials.");
            }
        } else {
            $this->_sendResponse(403, "No credentials provided.");
        }
    }

   /**
    * Respond to a request with a specified status code and body.
    * 
    * @param integer $status The HTTP status code.
    * @param string $body The body of the response message
    * @param string $content_type The response mimetype.
    */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
            exit;
        }
        // we need to create the body if none is passed
        else {
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
            $body = '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
	</head>
	<body>
		<h1>' . $this->_getStatusCodeMessage($status) . '</h1>
		<p>' . $message . '</p>
		<hr />
		<address>' . $signature . '</address>
	</body>
	</html>';

            echo $body;
            exit;
        }
    }
    
    /**
     * Obtain an appropriate message for a given HTTP response code.
     * 
     * @param integer $status
     * @return string 
     */
    private function _getStatusCodeMessage($status) {
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

}

?>
