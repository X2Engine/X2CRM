<?php
/***********************************************************************************
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
 * ****************************************************************************** */


class WebFormAction extends CAction {

    public static function sanitizeGetParams () {
        //sanitize get params
        $whitelist = array(
            'fg', 'bgc', 'font', 'bs', 'bc', 'tags', 'iframeHeight'
        );
        $_GET = array_intersect_key($_GET, array_flip($whitelist));
        //restrict param values, alphanumeric, # for color vals, comma for tag list, . for decimals
        $_GET = preg_replace('/[^a-zA-Z0-9#,.]/', '', $_GET);
    }

    private static function addTags ($model) {
        // add tags
        if(!empty($_POST['tags'])){
            $taglist = explode(',', $_POST['tags']);
            if($taglist !== false){
                foreach($taglist as &$tag){
                    if($tag === '')
                        continue;
                    if(substr($tag, 0, 1) != '#')
                        $tag = '#'.$tag;
                    $tagModel = new Tags;
                    $tagModel->taggedBy = 'API';
                    $tagModel->timestamp = time();
                    $tagModel->type = get_class ($model);
                    $tagModel->itemId = $model->id;
                    $tagModel->tag = $tag;
                    $tagModel->itemName = $model->name;
                    $tagModel->save();

                    X2Flow::trigger('RecordTagAddTrigger', array(
                        'model' => $model,
                        'tag' => $tag,
                    ));
                }
            }
        }
    }

    /* x2prostart */
    /*
    Helper funtion for run ().
    */
    private static function formatEmailBodyAttrs ($emailBody, $model) {
        // set the template variables
        $matches = array();

        // find all the things
        preg_match_all('/{\w+}/', $emailBody, $matches);

        if(isset($matches[0])){     // loop through the things
            foreach($matches[0] as $match){
                $match = substr($match, 1, -1); // remove { and }

                if($model->hasAttribute($match)){

                    // get the correctly formatted attribute
                    $value = $model->renderAttribute($match, false, true);
                    $emailBody = preg_replace(
                        '/{'.$match.'}/', $value, $emailBody);
                }
            }
        }
        return $emailBody;
    }
    /* x2proend */

    /* x2prostart */
    /**
     * Sets tracking key of contact model. First looks for the key generated on client (this key
     * allows the visitor to be tracked on a domain other than the one on which the crm is
     * running) then, if no key exists, generates a new key.
     *
     * $model object the contact model
     */
    private function setNewWebleadTrackingKey ($model) {
        if (isset ($_POST['x2_key'])) { 
            // tracking key set in iframe parent's domain always takes precedence
            $model->trackingKey = $_POST['x2_key'];
        } else if (empty($model->trackingKey)) { // legacy tracker
            if(isset($_COOKIE['x2_key']) && ctype_alnum($_COOKIE['x2_key'])) { 
                $model->trackingKey = $_COOKIE['x2_key'];
            } else {
                $model->trackingKey = Contacts::getNewTrackingKey();
            }

        }
    }
    /* x2proend */

    private function handleWebleadFormSubmission ($model, $extractedParams) {
        if(isset($_POST['Contacts'])) {
            $model->createEvent = false;
            $model->setX2Fields($_POST['Contacts'], true);
            $now = time();

            //require email field, check format
            /*if(preg_match(
                "/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",
                $_POST['Contacts']['email']) == 0) {
                $this->renderPartial('application.components.views.webFormSubmit',
                    array (
                        'type' => 'weblead',
                        'error' => Yii::t('contacts', 'Invalid Email Address')
                    )
                );
                return;
            }*/

            /* x2prostart */
            if (PRO_VERSION) {
                foreach($extractedParams['fieldList'] as $field){
                    if($field['required'] &&
                       (!isset($model->$field['fieldName']) || $model->$field['fieldName'] == '')){
                        $model->addError($field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                    }
                }
            }
            /* x2proend */

            if(!$model->hasErrors()){

                $duplicates = array ();
                if(!empty($model->email)){

                    //find any existing contacts with the same contact info
                    $criteria = new CDbCriteria();
                    $criteria->compare('email', $model->email, false, "OR");
                    $duplicates = $model->findAll($criteria);
                }

                if(count($duplicates) > 0){ //use existing record, update background info
                    $newBgInfo = $model->backgroundInfo;
                    $model = $duplicates[0];
                    $oldBgInfo = $model->backgroundInfo;
                    $model->backgroundInfo .= (($oldBgInfo && $newBgInfo) ? "\n" : '') . $newBgInfo;

                    /* x2prostart */
                    if(PRO_VERSION) {
                        foreach($_POST['Contacts'] as $index => $value){
                            if(empty($value)){
                                unset($_POST['Contacts'][$index]);
                            }
                        }
                        $this->setNewWebleadTrackingKey ($model);
                    }
                    /* x2proend */

                    $success = $model->save();
                }else{ //create new record
                    $model->assignedTo = $this->controller->getNextAssignee();
                    $model->visibility = 1;
                    $model->createDate = $now;
                    $model->lastUpdated = $now;
                    $model->updatedBy = 'admin';

                    /* x2prostart */
                    if(PRO_VERSION) {
                        $this->setNewWebleadTrackingKey ($model);
                    }
                    /* x2proend */

                    $success = $model->save();
                    //TODO: upload profile picture url from webleadfb
                }

                if($success){
                    self::addTags ($model);
                    $tags = ((!isset($_POST['tags']) || empty($_POST['tags'])) ? array() : explode(',',$_POST['tags']));
                    X2Flow::trigger('WebleadTrigger', array('model' => $model, 'tags' => $tags));

                    //use the submitted info to create an action
                    $action = new Actions;
                    $action->actionDescription = Yii::t('contacts', 'Web Lead')
                            ."\n\n".Yii::t('contacts', 'Name').': '.
                            CHtml::decode($model->firstName)." ".
                            CHtml::decode($model->lastName)."\n".Yii::t('contacts', 'Email').": ".
                            CHtml::decode($model->email)."\n".Yii::t('contacts', 'Phone').": ".
                            CHtml::decode($model->phone)."\n".
                            Yii::t('contacts', 'Background Info').": ".
                            CHtml::decode($model->backgroundInfo);

                    // create action
                    $action->type = 'note';
                    $action->assignedTo = $model->assignedTo;
                    $action->visibility = '1';
                    $action->associationType = 'contacts';
                    $action->associationId = $model->id;
                    $action->associationName = $model->name;
                    $action->createDate = $now;
                    $action->lastUpdated = $now;
                    $action->completeDate = $now;
                    $action->complete = 'Yes';
                    $action->updatedBy = 'admin';
                    $action->save();

                    // create a notification if the record is assigned to someone
                    $event = new Events;
                    $event->associationType = 'Contacts';
                    $event->associationId = $model->id;
                    $event->user = $model->assignedTo;
                    $event->type = 'weblead_create';
                    $event->save();

                    /* x2prostart */
                    if (PRO_VERSION) {
                        // email to send from
                        $emailFrom = Credentials::model()->getDefaultUserAccount(
                            Credentials::$sysUseId['systemResponseEmail'], 'email');
                        if($emailFrom == Credentials::LEGACY_ID)
                            $emailFrom = array(
                                'name' => Yii::app()->params->admin->emailFromName,
                                'address' => Yii::app()->params->admin->emailFromAddr
                            );
                    }
                    /* x2proend */

                    if($model->assignedTo != 'Anyone' && $model->assignedTo != '') {

                        $notif = new Notification;
                        $notif->user = $model->assignedTo;
                        $notif->createdBy = 'API';
                        $notif->createDate = time();
                        $notif->type = 'weblead';
                        $notif->modelType = 'Contacts';
                        $notif->modelId = $model->id;
                        $notif->save();

                        $profile = Profile::model()->findByAttributes(
                            array('username' => $model->assignedTo));

                        /* send user that's assigned to this weblead an email if the user's email
                        address is set and this weblead has a user email template */
                        if($profile !== null && !empty($profile->emailAddress)){

                            /* x2prostart */
                            if (PRO_VERSION) {
                                /* We'll be using the user's own email account to send the
                                web lead response (since the contact has been assigned) and
                                additionally, if no system notification account is available,
                                as the account for sending the notification to the user of
                                the new web lead (since $emailFrom is going to be modified,
                                and it will be that way when this code block is exited and the
                                time comes to send the "welcome aboard" email to the web lead)*/
                                $emailFrom = Credentials::model()->getDefaultUserAccount(
                                    $profile->user->id, 'email');
                                if($emailFrom == Credentials::LEGACY_ID)
                                    $emailFrom = array(
                                        'name' => $profile->fullName,
                                        'address' => $profile->emailAddress
                                    );

                                /* Security Check: ensure that at least one webform is using this
                                email template */
                                /* if(!empty($userEmailTemplate) &&
                                CActiveRecord::model('WebForm')->exists(
                                    'userEmailTemplate=:template',array(
                                        ':template'=>$userEmailTemplate))) { */

                                $template = X2Model::model('Docs')->findByPk(
                                    $extractedParams['userEmailTemplate']);
                                if($template){
                                    $emailBody = $template->text;

                                    $subject = '';
                                    if($template->subject){
                                        $subject = $template->subject;
                                    }

                                    $emailBody = self::formatEmailBodyAttrs ($emailBody, $model);

                                    $address = array(
                                        'to' => array(array('', $profile->emailAddress)));

                                    $notifEmailFrom = Credentials::model()->getDefaultUserAccount(
                                        Credentials::$sysUseId['systemNotificationEmail'], 'email');

                                    /* Use the same sender as web lead response if notification
                                    emailer not available */
                                    if($notifEmailFrom == Credentials::LEGACY_ID)
                                        $notifEmailFrom = $emailFrom;

                                    // send user template email
                                    $status = $this->controller->sendUserEmail(
                                        $address, $subject, $emailBody, null, $notifEmailFrom);
                                }
                                // }
                            } else { /* x2proend */
                                $subject = Yii::t('marketing', 'New Web Lead');
                                $message =
                                    Yii::t('marketing',
                                        'A new web lead has been assigned to you: ').
                                    CHtml::link(
                                        $model->firstName.' '.$model->lastName,
                                        array('/contacts/contacts/view', 'id' => $model->id)).'.';
                                $address = array('to' => array(array('', $profile->emailAddress)));
                                $emailFrom = Credentials::model()->getDefaultUserAccount(
                                    Credentials::$sysUseId['systemNotificationEmail'], 'email');
                                if($emailFrom == Credentials::LEGACY_ID)
                                    $emailFrom = array(
                                        'name' => $profile->fullName,
                                        'address' => $profile->emailAddress
                                    );

                                $status = $this->controller->sendUserEmail(
                                    $address, $subject, $message, null, $emailFrom);
                            /* x2prostart */
                            }
                            /* x2proend */
                        }

                    }

                    /* x2prostart */
                    /* send new weblead an email if we have their email address and this web
                    form has a weblead email template */
                    if(PRO_VERSION && !empty($webleadEmailTemplate) && !empty($model->email)) {
                        /* Security Check: ensure that at least one webform is using this
                        email template */
                        /* if(CActiveRecord::model('WebForm')->exists(
                            'webleadEmailTemplate=:template',array(
                                ':template'=>$webleadEmailTemplate))){ */
                        $template = X2Model::model('Docs')->findByPk(
                            $extractedParams['webleadEmailTemplate']);
                        if($template !== null){
                            $emailBody = $template->text;

                            $subject = '';
                            if($template->subject){
                                $subject = $template->subject;
                            }

                            $emailBody = self::formatEmailBodyAttrs ($emailBody, $model);

                            $address = array('to' => array(array((isset($model->firstName) ?
                                $model->firstName : '').' '.
                                    (isset($model->lastName) ? $model->lastName :
                                ''), $model->email)));

                            // send user template email
                            $status = $this->controller->sendUserEmail(
                                $address, $subject, $emailBody, null, $emailFrom);
                        }
                        // }
                    }

                    if (PRO_VERSION) {
                        if(class_exists('WebListenerAction') && $model->trackingKey !== null)
                            WebListenerAction::setKey($model->trackingKey);

                        if(!empty($tags)){
                            X2Flow::trigger('RecordTagAddTrigger', array(
                                'model' => $model,
                                'tags' => $tags,
                            ));
                        }
                    }
                    /* x2proend */
                } else {
                    $errMsg = 'Error: WebListenerAction.php: model failed to save';
                    AuxLib::debugLog ($errMsg);
                    Yii::log ($errMsg, '', 'application.debug');
                }

                $this->controller->renderPartial('application.components.views.webFormSubmit',
                    array ('type' => 'weblead'));

                Yii::app()->end(); // success!
            }
        } /* x2prostart */elseif (PRO_VERSION && class_exists('WebListenerAction')){
            /* 
            legacy web tracker
            Web lead form doubles as a tracker (unless user is submitting data)
            */
            //AuxLib::debugLog ('legacy web form tracking');
            if (isset ($_COOKIE['x2_key'])) WebListenerAction::track();
        } /* x2proend */

        self::sanitizeGetParams ();

        /* x2prostart */
        if (PRO_VERSION) {
            $viewParams = array (
                'model' => $model,
                'type' => 'weblead',
                'fieldList' => $extractedParams['fieldList'],
                'css' => $extractedParams['css'], 
                'header' => $extractedParams['header']
            );
            if (isset ($extractedParams['x2_key'])) {
                $viewParams['x2_key'] = $extractedParams['x2_key'];
            }
            $this->controller->renderPartial('application.components.views.webForm', $viewParams);
        } else {
        /* x2proend */
            $this->controller->renderPartial(
                'application.components.views.webForm', array('type' => 'weblead'));
        /* x2prostart */
        }
        /* x2proend */

    }


    private function handleServiceFormSubmission ($model, $extractedParams) {
        if(isset($_POST['Services'])){ // web form submitted
            if(isset($_POST['Services']['firstName'])){
                $firstName = $_POST['Services']['firstName'];
                $fullName = $firstName;
            }

            if(isset($_POST['Services']['lastName'])){
                $lastName = $_POST['Services']['lastName'];
                if(isset($fullName)){
                    $fullName .= ' '.$lastName;
                }else{
                    $fullName = $lastName;
                }
            }

            if(isset($_POST['Services']['email'])){
                $email = $_POST['Services']['email'];
            }
            if(isset($_POST['Services']['phone'])){
                $phone = $_POST['Services']['phone'];
            }
            if(isset($_POST['Services']['desription'])){
                $description = $_POST['Services']['description'];
            }

            /* x2prostart */
            if (PRO_VERSION) {
                $model->setX2Fields($_POST['Services'],true);
            }
            /* x2proend */

            $contact = Contacts::model()->findByAttributes(array('email' => $email));

            if(isset($email) && $email) {
                $contact = Contacts::model()->findByAttributes(array('email' => $email));
            } else {
                $contact = false;
            }

            if($contact){
                $model->contactId = $contact->id;
            }else{
                $model->contactId = "Unregistered";
            }

            if(isset($fullName) || isset($email)){
                $model->subject = Yii::t('services', 'Web Form Case entered by {name}', array(
                            '{name}' => isset($fullName) ? $fullName : $email,
                ));
            }else{
                $model->subject = Yii::t('services', 'Web Form Case');
            }

            $model->origin = 'Web';
            if(!isset($model->impact) || $model->impact == '')
                $model->impact = Yii::t('services', '3 - Moderate');
            if(!isset($model->status) || $model->status == '')
                $model->status = Yii::t('services', 'New');
            if(!isset($model->mainIssue) || $model->mainIssue == '')
                $model->mainIssue = Yii::t('services', 'General Request');
            if(!isset($model->subIssue) || $model->subIssue == '')
                $model->subIssue = Yii::t('services', 'Other');
            $model->assignedTo = $this->controller->getNextAssignee();
            $model->email = CHtml::encode($email);
            $now = time();
            $model->createDate = $now;
            $model->lastUpdated = $now;
            $model->updatedBy = 'admin';
            if (isset ($description))
                $model->description = CHtml::encode($description);

            /* x2prostart */
            if (PRO_VERSION) {
                $contactFields = array('firstName', 'lastName', 'email', 'phone');
                foreach($extractedParams['fieldList'] as $field){
                    if(in_array($field['fieldName'], $contactFields)){
                        if($field['required'] &&
                           (!isset($_POST['Services'][$field['fieldName']]) ||
                            $_POST['Services'][$field['fieldName']] == '')){

                            $model->addError($field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                        }
                    }else{
                        if($field['required'] &&
                           (!isset($model->$field['fieldName']) || $model->$field['fieldName'] == '')) {

                            $model->addError($field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                        }
                    }
                }
            }
            /* x2proend */

            if(!$model->hasErrors()){

                if($model->save()){
                    $model->name = $model->id;
                    $model->update(array('name'));

                    self::addTags ($model);

                    //use the submitted info to create an action
                    $action = new Actions;
                    $action->actionDescription = Yii::t('contacts', 'Web Form')."\n\n".
                            (isset($fullName) ? (Yii::t('contacts', 'Name').': '.$fullName."\n") : '').
                            (isset($email) ? (Yii::t('contacts', 'Email').": ".$email."\n") : '').
                            (isset($phone) ? (Yii::t('contacts', 'Phone').": ".$phone."\n") : '').
                            (isset($description) ?
                                (Yii::t('services', 'Description').": ".$description) : '');

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

                    if(isset($email)){

                        //send email
                        $emailBody = Yii::t('services', 'Hello').' '.$fullName.",<br><br>";
                        $emailBody .= Yii::t('services',
                            'Thank you for contacting our Technical Support '.
                            'team. This is to verify we have received your request for Case# '.
                            '{casenumber}. One of our Technical Analysts will contact you shortly.',
                            array('{casenumber}' => $model->id));

                        $emailBody = Yii::app()->params->admin->serviceCaseEmailMessage;
                        if(isset($firstName))
                            $emailBody = preg_replace('/{first}/u', $firstName, $emailBody);
                        if(isset($lastName))
                            $emailBody = preg_replace('/{last}/u', $lastName, $emailBody);
                        if(isset($phone))
                            $emailBody = preg_replace('/{phone}/u', $phone, $emailBody);
                        if(isset($email))
                            $emailBody = preg_replace('/{email}/u', $email, $emailBody);
                        if(isset($description))
                            $emailBody = preg_replace('/{description}/u', $description, $emailBody);
                        $emailBody = preg_replace('/{case}/u', $model->id, $emailBody);
                        $emailBody = preg_replace('/\n|\r\n/', "<br>", $emailBody);

                        $uniqueId = md5(uniqid(rand(), true));
                        $emailBody .= '<img src="'.$this->controller->createAbsoluteUrl(
                            '/actions/actions/emailOpened', array('uid' => $uniqueId, 'type' => 'open')).'"/>';

                        $emailSubject = Yii::app()->params->admin->serviceCaseEmailSubject;
                        if(isset($firstName))
                            $emailSubject = preg_replace('/{first}/u', $firstName, $emailSubject);
                        if(isset($lastName))
                            $emailSubject = preg_replace('/{last}/u', $lastName, $emailSubject);
                        if(isset($phone))
                            $emailSubject = preg_replace('/{phone}/u', $phone, $emailSubject);
                        if(isset($email))
                            $emailSubject = preg_replace('/{email}/u', $email, $emailSubject);
                        if(isset($description))
                            $emailSubject = preg_replace('/{description}/u', $description,
                                $emailSubject);
                        $emailSubject = preg_replace('/{case}/u', $model->id, $emailSubject);
                        if(Yii::app()->params->admin->serviceCaseEmailAccount != 
                           Credentials::LEGACY_ID) {
                            $from = (int) Yii::app()->params->admin->serviceCaseEmailAccount;
                        } else {
                            $from = array(
                                'name' => Yii::app()->params->admin->serviceCaseFromEmailName,
                                'address' => Yii::app()->params->admin->serviceCaseFromEmailAddress
                            );
                        }
                        $useremail = array('to' => array(array(isset($fullName) ?
                            $fullName : '', $email)));

                        $status = $this->controller->sendUserEmail(
                            $useremail, $emailSubject, $emailBody, null, $from);

                        if($status['code'] == 200){
                            if($model->assignedTo != 'Anyone'){
                                $profile = X2Model::model('Profile')->findByAttributes(
                                    array('username' => $model->assignedTo));
                                if(isset($profile)){
                                    $useremail['to'] = array(
                                        array(
                                            $profile->fullName,
                                            $profile->emailAddress,
                                        ),
                                    );
                                    $emailSubject = 'Service Case Created';
                                    $emailBody = "A new service case, #".$model->id.
                                        ", has been created in X2CRM. To view the case, click ".
                                        "this link: ".$model->getLink();
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
                            $action->actionDescription = '<b>'.$model->subject."</b>\n\n".
                                $emailBody;
                            if($action->save()){
                                $track = new TrackEmail;
                                $track->actionId = $action->id;
                                $track->uniqueId = $uniqueId;
                                $track->save();
                            }
                        } else {
                            $errMsg = 'Error: actionWebForm.php: sendUserEmail failed';
                            AuxLib::debugLog ($errMsg);
                            Yii::log ($errMsg, '', 'application.debug');
                        }
                    }
                    $this->controller->renderPartial('application.components.views.webFormSubmit',
                        array('type' => 'service', 'caseNumber' => $model->id));

                    Yii::app()->end(); // success!
                }
            }
        }

        self::sanitizeGetParams ();

        /* x2prostart */
        if (PRO_VERSION) {
            $viewParams = array (
                'model' => $model,
                'type' => 'service',
                'fieldList' => $extractedParams['fieldList'],
                'css' => $extractedParams['css']
            );
            $this->controller->renderPartial('application.components.views.webForm', $viewParams);
        } else {
        /* x2proend */
            $this->controller->renderPartial (
                'application.components.views.webForm',
                array('model' => $model, 'type' => 'service'));
        /* x2prostart */
        }
        /* x2proend */
    }



    /**
     * Create a web lead form with a custom style
     *
     * Currently web forms have all options passed as GET parameters. Saved web forms
     * are saved to the table x2_web_forms. Saving, retrieving, and updating a web form
     * all happens in this function. Someday this should be updated to be it's own module.
     *
     *
     * This get request is for weblead/service type only, marketing/weblist/view supplies
     * the form that posts for weblist type
     *
     */
    public function run(){
        $modelClass = $this->controller->modelClass;
        if ($modelClass === 'Campaign') $modelClass = 'Contacts';

        if ($modelClass === 'Contacts')
            $model = new Contacts ('webForm');
        elseif ($modelClass === 'Services')
            $model = new Services ('webForm');

        $extractedParams = array ();

        /* x2prostart */
        if (PRO_VERSION) {

            $purifier = new CHtmlPurifier ();

            // retrieve list of fields from get params (if any)
            $fieldList = array ();
            if (isset ($_GET['webFormId'])) { // new method
                $webForm = WebForm::model()->findByPk($_GET['webFormId']);
                if ($webForm !== null) $fieldList = CJSON::decode ($webForm->fields);

            } else if (isset($_GET['fieldlist'])){ // legacy method
                $fieldList = CJSON::decode($_GET['fieldlist'], true);
            }
            if (is_array ($fieldList) && sizeof ($fieldList) > 0) {
                foreach($fieldList as &$field){
                    $tempField = array();
                    foreach($field as $key => $val){
                        $key=$purifier->purify($key);
                        $tempField[$key] = $purifier->purify($val);
                    }
                    $field = $tempField;
                }
            }
            if (!is_array($fieldList)) $fieldList = array ();
            $extractedParams['fieldList'] = $fieldList;

            $css = '';
            if(isset($_GET['css'])){
                $css = $purifier->purify($_GET['css']);
            }
            $extractedParams['css'] = $css;

            if ($modelClass === 'Contacts') {

                // get custom header
                $header = null;
                if(isset($_GET['header'])){
                    $webForm = WebForm::model()->findByPk($_GET['header']);
                    if($webForm){
                        $header = $webForm->header;
                    }
                }
                $extractedParams['header'] = $header;

                $userEmailTemplate = false;
                if(isset($_GET['user_email_template'])){
                    $userEmailTemplate = CJSON::decode($_GET['user_email_template'], true);
                }
                $extractedParams['userEmailTemplate'] = $userEmailTemplate;
                $webleadEmailTemplate = false;
                if(isset($_GET['weblead_email_template'])){
                    $webleadEmailTemplate = CJSON::decode($_GET['weblead_email_template'], true);
                }
                $extractedParams['webleadEmailTemplate'] = $webleadEmailTemplate;

                // get tracking key 
                if (isset ($_GET['x2_key'])) {
                    $extractedParams['x2_key'] = $_GET['x2_key'];
                    unset ($_GET['x2_key']); // from now on key will be stored in hidden input
                }
            }
        }
        /* x2proend */

        if ($modelClass === 'Contacts') {
            $this->handleWebleadFormSubmission ($model, $extractedParams);
        } else if ($modelClass === 'Services') {
            $this->handleServiceFormSubmission ($model, $extractedParams);
        }

    }

}

?>
