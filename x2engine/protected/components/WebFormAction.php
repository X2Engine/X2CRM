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




class WebFormAction extends CAction {

    public static function sanitizeGetParams() {
        //sanitize get params
        $whitelist = array(
            'fg', 'bgc', 'font', 'bs', 'bc', 'iframeHeight'
        );
        $_GET = array_intersect_key($_GET, array_flip($whitelist));
        //restrict param values, alphanumeric, # for color vals, comma for tag list, . for decimals
        $_GET = preg_replace('/[^a-zA-Z0-9#,.]/', '', $_GET);
        return $_GET;
    }

    private static function addTags($model) {
        // add tags
        if (!empty($_POST['tags'])) {
            $taglist = explode(',', $_POST['tags']);
            if ($taglist !== false) {
                foreach ($taglist as &$tag) {
                    $tag = trim($tag);
                    if ($tag === '') {
                        continue;
                    }
                    if (substr($tag, 0, 1) !== '#') {
                        $tag = '#' . $tag;
                    }
                    $tagModel = new Tags;
                    $tagModel->taggedBy = 'API';
                    $tagModel->timestamp = time();
                    $tagModel->type = get_class($model);
                    $tagModel->itemId = $model->id;
                    $tagModel->tag = $tag;
                    $tagModel->itemName = $model->name;
                    $tagModel->save();

                    X2Flow::trigger('RecordTagAddTrigger', array(
                        'model' => $model,
                        'tags' => $tag,
                    ));
                }
            }
        }
    }

    private function handleWebleadFormSubmission(X2Model $model, $extractedParams) {
        $newRecord = $model->isNewRecord;
        if (isset($_POST['Contacts'])) {

            $model->createEvent = false;
            $model->setX2Fields($_POST['Contacts'], true);
            // Extra sanitizing
            $p = Fields::getPurifier();
            foreach ($model->attributes as $name => $value) {
                if ($name != $model->primaryKey() && !empty($value)) {
                    $model->$name = $p->purify($value);
                }
            }
            $now = time();


            if (Yii::app()->contEd('pro')) {
                foreach ($extractedParams['fieldList'] as $field) {
                    if ($field['required'] &&
                            (!isset($model->{$field['fieldName']}) || $model->{$field['fieldName']} == '')) {

                        $model->addError($field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                    }
                }
            }

            if ($extractedParams['requireCaptcha'] && CCaptcha::checkRequirements() &&
                    array_key_exists('verifyCode', $_POST['Contacts'])) {
                $model->verifyCode = $_POST['Contacts']['verifyCode'];
            }

            $model->visibility = 1;

            $model->validate(null, false);
            if (!$model->hasErrors()) {
                $model->lastUpdated = $now;
                $model->updatedBy = 'admin';


                if (Yii::app()->contEd('pro')) {
                    $this->controller->setNewWebleadTrackingKey($model);
                }


                if ($model->asa('DuplicateBehavior') && $model->checkForDuplicates()) {
                    $duplicates = $model->getDuplicates();
                    $oldest = $duplicates[0];
                }

                if (Yii::app()->settings->enableFingerprinting && isset($_POST['fingerprint']) &&
                        isset($extractedParams['fingerprintDetection']) && $extractedParams['fingerprintDetection']) {
                    $attributes = (isset($_POST['fingerprintAttributes'])) ?
                            json_decode($_POST['fingerprintAttributes'], true) : array();

                    $anonContact = AnonContact::model()
                            ->findByFingerprint($_POST['fingerprint'], $attributes);

                    // if there's not an anonyomous contact, then the fingerprint match
                    // was for an actual contact.
                    if ($anonContact !== null) {
                        if ($model->isNewRecord) {
                            // save new contact for subsequent update() when merging AnonContact
                            $model->save();
                        }
                        $model->mergeWithAnonContact($anonContact);
                    } else {
                        $fingerprint = Fingerprint::model()->findByAttributes(array(
                            'fingerprint' => $_POST['fingerprint'],
                        ));
                        if (is_null($fingerprint)) {
                            $model->setFingerprint($_POST['fingerprint'], $attributes);
                        } else if ($fingerprint->anonymous === '0') {
                            $oldest = X2Model::model('Contacts')->findByAttributes(array(
                                'fingerprintId' => $fingerprint->id,
                            ));
                        }
                    }
                }

                // Merge in previous fields if an existing contact is located by duplicate
                // detection or fingerprinting
                if (isset($oldest) && $oldest) {
                    $fields = $model->getFields(true);
                    foreach ($fields as $field) {
                        if (!in_array($field->fieldName, $model->MergeableBehavior->restrictedFields) && !is_null($model->{$field->fieldName})) {
                            if ($field->type === 'text' && !empty($oldest->{$field->fieldName})) {
                                $oldest->{$field->fieldName} .= "\n--\n" . $model->{$field->fieldName};
                            } else {
                                $oldest->{$field->fieldName} = $model->{$field->fieldName};
                            }
                        }
                    }
                    $model = $oldest;
                    $model->scenario = $extractedParams['requireCaptcha'] ? 'webFormWithCaptcha' : 'webForm';
                    if ($extractedParams['requireCaptcha'] && CCaptcha::checkRequirements() &&
                            array_key_exists('verifyCode', $_POST['Contacts'])) {
                        $model->verifyCode = $_POST['Contacts']['verifyCode'];
                    }
                    $newRecord = $model->isNewRecord;
                }

                if ($newRecord) {
                    $model->createDate = $now;
                    $model->assignedTo = $this->controller->getNextAssignee();
                }

                $success = $model->save();
                $model->scenario = $extractedParams['requireCaptcha'] ? 'webFormWithCaptcha' : 'webForm';

                $mediaLookups = $model->getMediaLookupFields();
                if (!empty($mediaLookups)) {
                    $uploaded = $this->controller->uploadAssociatedMedia($model);
                    if (!is_null($uploaded)) {
                        $success = $success && $uploaded;
                    }
                }

                //TODO: upload profile picture url from webleadfb

                if ($success) {
                    $location = $model->logLocation('weblead', 'POST');

                    if ($extractedParams['generateLead']) {
                        $newLead = call_user_func(array($this->controller, 'generateLead'), $model, $extractedParams['leadSource']);
                        if ($newLead) {
                            self::addTags($newLead);
                        }
                    }
                    if ($extractedParams['generateAccount']) {
                        $newAccount = call_user_func(array($this->controller, 'generateAccount'), $model);
                        if ($newAccount) {
                            self::addTags($newAccount);
                        }
                    }

                    self::addTags($model);
                    $tags = ((!isset($_POST['tags']) || empty($_POST['tags'])) ?
                            array() : explode(',', $_POST['tags']));
                    if ($newRecord) {
                        X2Flow::trigger(
                                'WebleadTrigger', array('model' => $model, 'tags' => $tags));
                    }

                    //use the submitted info to create an action
                    $actionParams = isset($location) ? array('locationId' => $location->id) : array();
                    $this->controller->createWebleadAction($model, $actionParams);
                    $this->controller->createWebleadEvent($model);

                    if (Yii::app()->contEd('pro')) {
                        // email to send from
                        $emailFrom = Credentials::model()->getDefaultUserAccount(
                                Credentials::$sysUseId['systemResponseEmail'], 'email');
                        if ($emailFrom == Credentials::LEGACY_ID) {
                            $emailFrom = array(
                                'name' => Yii::app()->settings->emailFromName,
                                'address' => Yii::app()->settings->emailFromAddr
                            );
                        }
                    }

                    if ($model->assignedTo != 'Anyone' && $model->assignedTo != '') {

                        $this->controller->createWebleadNotification($model);

                        $profile = Profile::model()->findByAttributes(
                                array('username' => $model->assignedTo));

                        /* send user that's assigned to this weblead an email if the user's email
                          address is set and this weblead has a user email template */
                        if ($profile !== null && !empty($profile->emailAddress)) {


                            if (Yii::app()->contEd('pro') &&
                                    $extractedParams['userEmailTemplate']) {

                                /* We'll be using the user's own email account to send the
                                  web lead response (since the contact has been assigned) and
                                  additionally, if no system notification account is available,
                                  as the account for sending the notification to the user of
                                  the new web lead (since $emailFrom is going to be modified,
                                  and it will be that way when this code block is exited and the
                                  time comes to send the "welcome aboard" email to the web lead) */
                                $emailFrom = Credentials::model()->getDefaultUserAccount(
                                        $profile->user->id, 'email');
                                if ($emailFrom == Credentials::LEGACY_ID) {
                                    $emailFrom = array(
                                        'name' => $profile->fullName,
                                        'address' => $profile->emailAddress
                                    );
                                }

                                /* Security Check: ensure that at least one webform is using this
                                  email template */
                                /* if(!empty($userEmailTemplate) &&
                                  CActiveRecord::model('WebForm')->exists(
                                  'userEmailTemplate=:template',array(
                                  ':template'=>$userEmailTemplate))) { */

                                //$address = array(
                                //    'to' => array(array('', $profile->emailAddress)));
                                $this->controller->sendUserNotificationEmail($model, $profile->emailAddress, $emailFrom, $extractedParams['userEmailTemplate']);
                                // }
                            } else {
                                $emailFrom = Credentials::model()->getDefaultUserAccount(
                                        Credentials::$sysUseId['systemNotificationEmail'], 'email');
                                if ($emailFrom == Credentials::LEGACY_ID) {
                                    $emailFrom = array(
                                        'name' => $profile->fullName,
                                        'address' => $profile->emailAddress
                                    );
                                }
                                $this->controller->sendLegacyUserNotificationEmail($model, $profile->emailAddress, $emailFrom);
                            }
                        }
                    }


                    /* send new weblead an email if we have their email address and this web
                      form has a weblead email template */
                    if (Yii::app()->contEd('pro') && $extractedParams['webleadEmailTemplate'] &&
                            !empty($model->email)) {

                        /* Security Check: ensure that at least one webform is using this
                          email template */
                        /* if(CActiveRecord::model('WebForm')->exists(
                          'webleadEmailTemplate=:template',array(
                          ':template'=>$webleadEmailTemplate))){ */
                        $this->controller->sendWebleadNotificationEmail($model, $emailFrom, $extractedParams['webleadEmailTemplate']);
                        // }
                    }

                    if (Yii::app()->contEd('pro')) {
                        if (class_exists('WebListenerAction') && $model->trackingKey !== null) {
                            WebListenerAction::setKey($model->trackingKey);
                        }

                        if (!empty($tags)) {
                            X2Flow::trigger('RecordTagAddTrigger', array(
                                'model' => $model,
                                'tags' => $tags,
                            ));
                        }
                    }

                    $this->controller->renderPartial('application.components.views.webFormSubmit', array(
                        'type' => 'weblead',
                        'redirectUrl' => $extractedParams['redirectUrl'],
                        'thankYouText' => $extractedParams['thankYouText'],
                            )
                    );

                    return; // to commit transaction
                } else {
                    AuxLib::debugLog('Error: WebListenerAction.php: model failed to save');
                }
            }
        } elseif (Yii::app()->contEd('pro') && class_exists('WebListenerAction')) {
            if (isset($_COOKIE['x2_key'])) {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    // since the web tracking script passes the website url in the web request,
                    // the web listener expects the website url to be in the $_GET superglobal
                    $_GET['url'] = $_SERVER['HTTP_REFERER'];
                }
                WebListenerAction::track();
            }
        }

        $sanitizedGetParams = self::sanitizeGetParams();

        $viewParams = array_merge(array(
            'model' => $model,
            'type' => 'weblead',
            'fieldList' => $extractedParams['fieldList'],
            'css' => $extractedParams['css'],
            'header' => $extractedParams['header'],
            'requireCaptcha' => $extractedParams['requireCaptcha'],
                ), $sanitizedGetParams);
        $this->controller->renderPartial('application.components.views.webForm', $viewParams);

        if (isset($success) && $success === false) {
            throw new WebFormException;
        }
    }

    private function handleServiceFormSubmission($model, $extractedParams) {
        if (isset($_POST['Services'])) { // web form submitted
            if (isset($_POST['Services']['firstName'])) {
                $firstName = $_POST['Services']['firstName'];
                $fullName = $firstName;
            }

            if (isset($_POST['Services']['lastName'])) {
                $lastName = $_POST['Services']['lastName'];
                if (isset($fullName)) {
                    $fullName .= ' ' . $lastName;
                } else {
                    $fullName = $lastName;
                }
            }

            if (isset($_POST['Services']['email'])) {
                $email = $_POST['Services']['email'];
            }
            if (isset($_POST['Services']['phone'])) {
                $phone = $_POST['Services']['phone'];
            }
            if (isset($_POST['Services']['desription'])) {
                $description = $_POST['Services']['description'];
            }


            if (Yii::app()->contEd('pro')) {
                $model->setX2Fields($_POST['Services'], true);
            }


            // Extra sanitizing
            $p = Fields::getPurifier();
            foreach ($model->attributes as $name => $value) {
                if ($name != $model->primaryKey() && !empty($value)) {
                    $model->$name = $p->purify($value);
                }
            }

            if (isset($email) && $email) {
                $contact = Contacts::model()->findByAttributes(array('email' => $email));
            } else {
                $contact = false;
            }

            if ($contact) {
                $model->contactId = $contact->nameId;
            } else {
                $model->contactId = "Unregistered";
            }

            if (isset($fullName) || isset($email)) {
                $model->subject = Yii::t('services', 'Web Form Case entered by {name}', array(
                            '{name}' => isset($fullName) ? $fullName : $email,
                ));
            } else {
                $model->subject = Yii::t('services', 'Web Form Case');
            }

            $model->origin = 'Web';
            if (!isset($model->impact) || $model->impact == '') {
                $model->impact = Yii::t('services', '3 - Moderate');
            }
            if (!isset($model->status) || $model->status == '') {
                $model->status = Yii::t('services', 'New');
            }
            if (!isset($model->mainIssue) || $model->mainIssue == '') {
                $model->mainIssue = Yii::t('services', 'General Request');
            }
            if (!isset($model->subIssue) || $model->subIssue == '') {
                $model->subIssue = Yii::t('services', 'Other');
            }
            $model->assignedTo = $this->controller->getNextAssignee();
            if (isset($email))
                $model->email = CHtml::encode($email);
            $now = time();
            $model->createDate = $now;
            $model->lastUpdated = $now;
            $model->updatedBy = 'admin';
            if (isset($description)) {
                $model->description = CHtml::encode($description);
            }

            if (Yii::app()->contEd('pro')) {
                $contactFields = array('firstName', 'lastName', 'email', 'phone');
                foreach ($extractedParams['fieldList'] as $field) {
                    if (in_array($field['fieldName'], $contactFields)) {
                        if ($field['required'] &&
                                (!isset($_POST['Services'][$field['fieldName']]) ||
                                $_POST['Services'][$field['fieldName']] == '')) {

                            $model->addError(
                                    $field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                        }
                    } else {
                        if ($field['required'] &&
                                (!isset($model->{$field['fieldName']}) ||
                                $model->{$field['fieldName']} == '')) {

                            $model->addError(
                                    $field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                        }
                    }
                }
            }

            if ($extractedParams['requireCaptcha'] && CCaptcha::checkRequirements() &&
                    array_key_exists('verifyCode', $_POST['Services'])) {
                $model->verifyCode = $_POST['Services']['verifyCode'];
            }

            $model->validate(null, false);

            if (!$model->hasErrors()) {
                $success = $model->save();

                if ($success) {
                    $model->name = $model->id;
                    // reset scenario for webForms after saving
                    $model->scenario = $extractedParams['requireCaptcha'] ? 'webFormWithCaptcha' : 'webForm';
                    $model->update(array('name'));
                    $mediaLookups = $model->getMediaLookupFields();
                    if (!empty($mediaLookups)) {
                        $uploaded = $this->controller->uploadAssociatedMedia($model);
                        if (!is_null($uploaded))
                            $success = $success && $uploaded;
                    }

                    self::addTags($model);

                    //use the submitted info to create an action
                    $action = new Actions;
                    $action->actionDescription = Yii::t('contacts', 'Web Form') . "\n\n" .
                            (isset($fullName) ? (Yii::t('contacts', 'Name') . ': ' . $fullName . "\n") : '') .
                            (isset($email) ? (Yii::t('contacts', 'Email') . ": " . $email . "\n") : '') .
                            (isset($phone) ? (Yii::t('contacts', 'Phone') . ": " . $phone . "\n") : '') .
                            (isset($description) ?
                            (Yii::t('services', 'Description') . ": " . $description) : '');

                    // create action
                    $action->type = 'note';
                    $action->assignedTo = $model->assignedTo;
                    $action->visibility = '1';
                    $action->associationType = 'services';
                    $action->associationId = $model->id;
                    $action->associationName = $model->name;
                    $action->createDate = $now;
                    $action->lastUpdated = $now;
                    $action->completeDate = $now;
                    $action->complete = 'Yes';
                    $action->updatedBy = 'admin';
                    $action->save();

                    if ($success && isset($email)) {

                        //send email
                        $emailBody = Yii::t('services', 'Hello') . ' ' . $fullName . ",<br><br>";
                        $emailBody .= Yii::t('services', 'Thank you for contacting our Technical Support ' .
                                        'team. This is to verify we have received your request for Case# ' .
                                        '{casenumber}. One of our Technical Analysts will contact you shortly.', array('{casenumber}' => $model->id));

                        $emailBody = Yii::app()->settings->serviceCaseEmailMessage;
                        if (isset($firstName)) {
                            $emailBody = preg_replace('/{first}/u', $firstName, $emailBody);
                        }
                        if (isset($lastName)) {
                            $emailBody = preg_replace('/{last}/u', $lastName, $emailBody);
                        }
                        if (isset($phone)) {
                            $emailBody = preg_replace('/{phone}/u', $phone, $emailBody);
                        }
                        if (isset($email)) {
                            $emailBody = preg_replace('/{email}/u', $email, $emailBody);
                        }
                        if (isset($description)) {
                            $emailBody = preg_replace('/{description}/u', $description, $emailBody);
                        }
                        $emailBody = preg_replace('/{case}/u', $model->id, $emailBody);
                        $emailBody = preg_replace('/\n|\r\n/', "<br>", $emailBody);

                        $uniqueId = md5(uniqid(rand(), true));
                        $emailBody .= '<img src="' . Yii::app()->createExternalUrl(
                                        '/actions/actions/emailOpened', array('uid' => $uniqueId, 'type' => 'open')) . '"/>';

                        $emailSubject = Yii::app()->settings->serviceCaseEmailSubject;
                        if (isset($firstName)) {
                            $emailSubject = preg_replace('/{first}/u', $firstName, $emailSubject);
                        }
                        if (isset($lastName)) {
                            $emailSubject = preg_replace('/{last}/u', $lastName, $emailSubject);
                        }
                        if (isset($phone)) {
                            $emailSubject = preg_replace('/{phone}/u', $phone, $emailSubject);
                        }
                        if (isset($email)) {
                            $emailSubject = preg_replace('/{email}/u', $email, $emailSubject);
                        }
                        if (isset($description)) {
                            $emailSubject = preg_replace('/{description}/u', $description, $emailSubject);
                        }
                        $emailSubject = preg_replace('/{case}/u', $model->id, $emailSubject);
                        if (Yii::app()->settings->serviceCaseEmailAccount != Credentials::LEGACY_ID) {
                            $from = (int) Yii::app()->settings->serviceCaseEmailAccount;
                        } else {
                            $from = array(
                                'name' => Yii::app()->settings->serviceCaseFromEmailName,
                                'address' => Yii::app()->settings->serviceCaseFromEmailAddress
                            );
                        }
                        $useremail = array('to' => array(array(isset($fullName) ?
                                    $fullName : '', $email)));

                        $status = $this->controller->sendUserEmail(
                                $useremail, $emailSubject, $emailBody, null, $from);

                        if ($status['code'] == 200) {
                            if ($model->assignedTo != 'Anyone') {
                                $profile = X2Model::model('Profile')->findByAttributes(
                                        array('username' => $model->assignedTo));
                                if (isset($profile)) {
                                    $useremail['to'] = array(
                                        array(
                                            $profile->fullName,
                                            $profile->emailAddress,
                                        ),
                                    );
                                    $emailSubject = 'Service Case Created';
                                    $emailBody = "A new service case, #" . $model->id .
                                            ", has been created in X2Engine. To view the case, click " .
                                            "this link: " . $model->getLink();
                                    $status = $this->controller->sendUserEmail(
                                            $useremail, $emailSubject, $emailBody, null, $from);
                                }
                            }
                            //email action
                            $action = new Actions;
                            $action->associationType = 'services';
                            $action->associationId = $model->id;
                            $action->associationName = $model->name;
                            $action->visibility = 1;
                            $action->complete = 'Yes';
                            $action->type = 'email';
                            $action->completedBy = 'admin';
                            $action->assignedTo = $model->assignedTo;
                            $action->createDate = time();
                            $action->dueDate = time();
                            $action->completeDate = time();
                            $action->actionDescription = '<b>' . $model->subject . "</b>\n\n" .
                                    $emailBody;
                            if ($action->save()) {
                                $track = new TrackEmail;
                                $track->actionId = $action->id;
                                $track->uniqueId = $uniqueId;
                                $track->save();
                            }
                        } else {
                            $errMsg = 'Error: actionWebForm.php: sendUserEmail failed';
                            /**/AuxLib::debugLog($errMsg);
                            Yii::log($errMsg, '', 'application.debug');
                        }
                    }
                    if ($success) {
                        $this->controller->renderPartial('application.components.views.webFormSubmit', array(
                            'type' => 'service',
                            'caseNumber' => $model->id,
                            'thankYouText' => $extractedParams['thankYouText'],
                        ));

                        return; // to commit transaction
                    }
                }
            }
        }

        $sanitizedGetParams = self::sanitizeGetParams();


        $viewParams = array_merge(array(
            'model' => $model,
            'type' => 'service',
            'fieldList' => $extractedParams['fieldList'],
            'css' => $extractedParams['css'],
            'requireCaptcha' => $extractedParams['requireCaptcha'],
                ), $sanitizedGetParams);
        $this->controller->renderPartial('application.components.views.webForm', $viewParams);

        if (isset($success) && $success === false) {
            throw new WebFormException;
        }
    }

    /**
     * Create a web lead form with a custom style
     *
     * There are currently two methods of specifying web form options. 
     *  Method 1 (legacy):
     *      Web form options are sent in the GET parameters (limited options: css, web form
     *      id for retrieving custom header)
     *  Method 2 (new):
     *      CSS options are passed in the GET parameters and all other options (custom fields, 
     *      custom html, and email templates) are stored in the database and accessed via a
     *      web form id sent in the GET parameters.
     *
     * This get request is for weblead/service type only, marketing/weblist/view supplies
     * the form that posts for weblist type
     *
     */
    public function run() {
        $modelClass = $this->controller->modelClass;
        if ($modelClass === 'Campaign') {
            $modelClass = 'Contacts';
        }

        if ($modelClass === 'Contacts') {
            $model = new Contacts('webForm');
        } elseif ($modelClass === 'Services') {
            $model = new Services('webForm');
        }

        $extractedParams = array();

        if (isset($_GET['webFormId'])) {
            $webForm = WebForm::model()->findByPk($_GET['webFormId']);
        }
        $extractedParams['leadSource'] = null;
        $extractedParams['generateLead'] = false;
        $extractedParams['generateAccount'] = false;
        $extractedParams['redirectUrl'] = null;
        $extractedParams['requireCaptcha'] = false;
        $extractedParams['thankYouText'] = false;
        if (isset($webForm)) { // new method
            if (!empty($webForm->leadSource)) {
                $extractedParams['leadSource'] = $webForm->leadSource;
            }
            if (!empty($webForm->generateLead)) {
                $extractedParams['generateLead'] = $webForm->generateLead;
            }
            if (!empty($webForm->generateAccount)) {
                $extractedParams['generateAccount'] = $webForm->generateAccount;
            }
            if (!empty($webForm->requireCaptcha)) {
                $extractedParams['requireCaptcha'] = $webForm->requireCaptcha;
                if ($webForm->requireCaptcha) {
                    $model->scenario = 'webFormWithCaptcha';
                }
            }
            if (!empty($webForm->redirectUrl)) {
                $extractedParams['redirectUrl'] = $webForm->redirectUrl;
            }
            if (!empty($webForm->thankYouText)) {
                $extractedParams['thankYouText'] = $webForm->thankYouText;
            }
        }


        if (Yii::app()->contEd('pro')) {

            // retrieve list of fields (if any)
            $fieldList = array();
            if (isset($webForm)) {
                $fieldList = CJSON::decode($webForm->fields);
            }

            // purify fields
            $purifier = new CHtmlPurifier ();
            if (is_array($fieldList) && sizeof($fieldList) > 0) {
                foreach ($fieldList as &$field) {
                    $tempField = array();
                    foreach ($field as $key => $val) {
                        $key = $purifier->purify($key);
                        $tempField[$key] = $purifier->purify($val);
                    }
                    $field = $tempField;
                }
            }

            if (!is_array($fieldList)) {
                $fieldList = array();
            }
            $extractedParams['fieldList'] = $fieldList;

            $css = '';
            if (isset($_GET['css'])) {
                $css = $purifier->purify($_GET['css']);
            }
            $extractedParams['css'] = $css;

            if ($modelClass === 'Contacts') {
                $extractedParams['header'] = '';
                $extractedParams['userEmailTemplate'] = null;
                $extractedParams['webleadEmailTemplate'] = null;
                $extractedParams['fingerprintDetection'] = true;

                if (isset($webForm)) { // new method
                    if (!empty($webForm->header))
                        $extractedParams['header'] = $webForm->header;
                    if (!empty($webForm->userEmailTemplate))
                        $extractedParams['userEmailTemplate'] = $webForm->userEmailTemplate;
                    if (!empty($webForm->webleadEmailTemplate))
                        $extractedParams['webleadEmailTemplate'] = $webForm->webleadEmailTemplate;
                    if (!empty($webForm->fingerprintDetection))
                        $extractedParams['fingerprintDetection'] = $webForm->fingerprintDetection;
                } else { // legacy method
                    if (isset($_GET['header'])) {
                        $webFormLegacy = WebForm::model()->findByPk($_GET['header']);
                        if ($webFormLegacy) {
                            $extractedParams['header'] = $webFormLegacy->header;
                        }
                    }
                }
            }
        }


        $transaction = Yii::app()->db->beginTransaction();
        try {
            if ($modelClass === 'Contacts') {
                $this->handleWebleadFormSubmission($model, $extractedParams);
            } else if ($modelClass === 'Services') {
                $this->handleServiceFormSubmission($model, $extractedParams);
            }
            $transaction->commit();
        } catch (WebFormException $e) {
            AuxLib::debugLog('Failed to save webform, rolling back transaction');
            $transaction->rollback();
        }
        Yii::app()->end();
    }

}

// Exception for triggering transaction rollback
class WebFormException extends CException {
    
}

?>
