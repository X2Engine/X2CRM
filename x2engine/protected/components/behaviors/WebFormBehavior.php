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




class WebFormBehavior extends CBehavior {

    protected function createWebleadAction($model, $params = array()) {
        Actions::associateAction ($model, array_merge(array (
            'actionDescription' => 
            Yii::t('contacts', 'Web Lead')
            ."\n\n".Yii::t('contacts', 'Name').': '.
            CHtml::decode($model->firstName)." ".
            CHtml::decode($model->lastName)."\n".Yii::t('contacts', 'Email').
            ": ".CHtml::decode($model->email)."\n".Yii::t('contacts', 'Phone').
            ": ".CHtml::decode($model->phone)."\n".
            Yii::t('contacts', 'Background Info').": ".
            CHtml::decode($model->backgroundInfo),
                'type' => 'note',
            ), $params));
    }

    /**
     * create a notification if the record is assigned to someone
     */
    protected function createWebleadEvent($model) {
        $event = new Events;
        $event->associationType = 'Contacts';
        $event->associationId = $model->id;
        $event->user = $model->assignedTo;
        $event->type = 'weblead_create';
        $event->save();
    }

    protected function createWebleadNotification($model) {
        $notif = new Notification;
        $notif->user = $model->assignedTo;
        $notif->createdBy = 'API';
        $notif->createDate = time();
        $notif->type = 'weblead';
        $notif->modelType = 'Contacts';
        $notif->modelId = $model->id;
        $notif->save();
    }

    /**
     * Sets tracking key of contact model. First looks for the key generated on client (this key
     * allows the visitor to be tracked on a domain other than the one on which the crm is
     * running) then, if no key exists, generates a new key.
     *
     * $model object the contact model
     */
    protected function setNewWebleadTrackingKey ($model) {
        if (empty($model->trackingKey)) { 
            if(isset($_COOKIE['x2_key']) && ctype_alnum($_COOKIE['x2_key'])) { 
                $model->trackingKey = $_COOKIE['x2_key'];
            } else {
                $model->trackingKey = Contacts::getNewTrackingKey();
            }

        }
    }

    /**
     * Creates a new lead and associates it with the contact
     * @param Contacts $contact
     * @param null|string $leadSource
     */
    protected static function generateLead (Contacts $contact, $leadSource=null) {
        $lead = new X2Leads ('webForm');
 
        $ConFields = X2Model::model("Contacts")->getFields();
        $LeadFields = X2Model::model("X2Leads")->getFields();
        $FieldNames = array();
        //added code so it will check for all shareded fields
        foreach($ConFields as $field){
            array_push($FieldNames , $field->fieldName);
        }
        foreach($LeadFields as $field) {
            if(in_array($field->fieldName, $FieldNames)  && (($field->fieldName) != 'id') && (($field->fieldName) != 'nameId')){
                $nameHere = $field->fieldName;
                $lead->$nameHere = $contact->$nameHere;
            }
        
        }
        $lead->firstName = $contact->firstName;
        $lead->lastName = $contact->lastName;
        $lead->leadSource = $leadSource;
        // disable validation to prevent saving from failing if leadSource isn't set
        if ($lead->save (false)) {
            $lead->createRelationship($contact);
            return $lead;
        }

    }

    /**
     * Generates an account from the contact's company field, if that field has a value 
     */
    protected static function generateAccount (Contacts $contact) {
        if (isset ($contact->company)) {
            $account = new Accounts ();
            $account->name = $contact->company;
            if ($account->save ()) {
                $account->refresh ();
                $contact->company = $account->nameId;
                $ConFields = X2Model::model("Contacts")->getFields();
                $AccFields = X2Model::model("Accounts")->getFields();
                $FieldNames = array();
                //added code so it will check for all shareded fields
                foreach ($ConFields as $field) {
                    array_push($FieldNames, $field->fieldName);
                }
                foreach($AccFields as $field) {
                    if(in_array($field->fieldName, $FieldNames)  && (($field->fieldName) != 'id') && (($field->fieldName) != 'name')  && (($field->fieldName) != 'nameId')){
                        $nameHere = $field->fieldName;
                        $account->$nameHere = $contact->$nameHere;
                     }
        
                }
                $contact->update ();
                return $account;
            }
        }
    }

    protected function sendUserNotificationEmail($model, $emailTo, $emailFrom, $templateId) {
        $template = X2Model::model('Docs')->findByPk($templateId);
        if($template){
            $emailBody = $template->text;

            $subject = '';
            if($template->subject){
                $subject = $template->subject;
            }

            $emailBody = self::formatEmailBodyAttrs ($emailBody, $model);

            $address = array(
                'to' => array(array('', $emailTo)));

            $notifEmailFrom = Credentials::model()->getDefaultUserAccount(
                Credentials::$sysUseId['systemNotificationEmail'], 'email');

                                    /* Use the same sender as web lead response if notification
                                    emailer not available */
            if($notifEmailFrom == Credentials::LEGACY_ID)
                $notifEmailFrom = $emailFrom;

            // send user template email
            $status = Yii::app()->controller->sendUserEmail(
                $address, $subject, $emailBody, null, $notifEmailFrom);

            if ($status['code'] !== '200') {
                                        /**/AuxLib::debugLog (
                                            'Error: sendUserEmail: '.$status['message']);
            }

        }
    }

    protected function sendLegacyUserNotificationEmail($model, $emailTo, $emailFrom) {
        $subject = Yii::t('marketing', 'New Web Lead');
        $message =
            Yii::t('marketing',
                'A new web lead has been assigned to you: ').
                CHtml::link(
                    $model->firstName.' '.$model->lastName,
                    array('/contacts/contacts/view', 'id' => $model->id)).'.';
        $address = array('to' => array(array('', $emailTo)));

        $status = Yii::app()->controller->sendUserEmail(
            $address, $subject, $message, null, $emailFrom);
    }

    protected function sendWebleadNotificationEmail($model, $emailFrom, $templateId) {
        $template = X2Model::model('Docs')->findByPk($templateId);
        if($template !== null){
            $emailBody = $template->text;

            $subject = '';
            if($template->subject){
                $subject = Docs::replaceVariables($template->subject, $model);
            }

            $emailBody = self::formatEmailBodyAttrs ($emailBody, $model);

            $address = array('to' => array(array((isset($model->firstName) ?
                $model->firstName : '').' '.
                (isset($model->lastName) ? $model->lastName :
                ''), $model->email)));

            // send user template email
            $status = Yii::app()->controller->sendUserEmail(
                $address, $subject, $emailBody, null, $emailFrom);

            if ($status['code'] !== '200') {
                                /**/AuxLib::debugLog (
                                    'Error: sendUserEmail: '.$status['message']);
            } else {
                    //record the email 
                    $action = new Actions;
                     $now = time();
                    // These attributes will be the same regardless of the type of
                    // email being sent:
                    $action->createDate = $now;
                    $action->dueDate = $now;
                    $action->subject = $subject;
                    $action->completeDate = $now;
                    $action->complete = 'Yes';
                    $action->actionDescription = $emailBody;
                    $action->associationId = $model->id;
                    $action->associationType = ucfirst($model->module);
                    $action->type = 'email';
                    $action->visibility =  1;
                    $action->save();
            }
        }
    }

    /*
    Helper funtion for run ().
    */
    protected static function formatEmailBodyAttrs ($emailBody, $model) {
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

    /**
     * Handle associated Media upload
     */
    protected function uploadAssociatedMedia($model) {
        $modelName = get_class($model);

        foreach ($model->getMediaLookupFields() as $field) {
            $fieldName = $field->fieldName;
            $associatedMedia = Yii::app()->file->set($modelName.'['.$fieldName.']');

            if ($associatedMedia->exists) {
                $username = Yii::app()->user->name;
                $userFolderPath = 'uploads/protected/media/' . $username;
                // if user folder doesn't exit, try to create it
                if (!(file_exists($userFolderPath) && is_dir($userFolderPath))) {
                    if (!@mkdir($userFolderPath, 0777, true)) { // make dir with edit permission
                        throw new CHttpException(500, "Couldn't create user folder $userFolderPath");
                    }
                }
                $dstPath = $userFolderPath . '/' . $associatedMedia->basename;

                if ($associatedMedia->rename($dstPath)) {
                    $media = new Media;
                    $media->fileName = $associatedMedia->basename;
                    $media->createDate = time();
                    $media->lastUpdated = time();
                    $media->uploadedBy = $username;
                    $media->associationType = X2Model::getAssociationType($modelName);
                    $media->associationId = $model->id;
                    if ($media->save()) {
                        $createdRelationship = $model->createRelationship($media);
                        $model->$fieldName = $media->nameId;
                        $savedModel = $model->save();
                        //AuxLib::debugLogR(array($createdRelationship, $savedModel));
                        return $savedModel && $createdRelationship;
                        //return true;
                    } else {
                        foreach ($media->errors as $error) {
                            $model->addError($fieldName, implode(',', $error));
                        }
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
    }
}
