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






Yii::import('application.components.*');
Yii::import('application.components.util.*');
Yii::import('application.models.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.services.models.*');
Yii::import('application.components.permissions.*');


/**
 * Utilities and methods for the email dropbox and parsing utilities
 *
 * @property string $alias The email address of the alias
 * @property integer|bool $caseId The case ID to which the email is associated, if so
 * @property bool $isCase Whether to treat this email as belonging to a service case.
 * @property EmailDropboxSettings $settings (read-only) settings stored in the {@link Admin} model
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailImportBehavior extends CBehavior {

    public static $authorEmail = 'customersupport@x2engine.com';

    /**
     * An array of action types to event types
     * @var array
     */
    public static $typeMap = array(
        'email' => 'email_sent',
        'emailFrom' => 'email_from'
    );

    /**
     * Email parsing object for the incoming email
     * @var EmlParse
     */
    public $parser;


    /**
     * User model
     * @var User
     */
    public $user;

    /**
     * The mail name used locally
     * @var string
     */
    private $_alias;

    public $case = null;

    private $_caseId;

    /**
     * Whether to treat this email as a service case.
     * @var type
     */
    private $_isCase;

    /**
     * Will be set to true if a case is being created in the scope of this capture
     * @var type
     */
    private $_isNewCase = false;

    private $_settings;

    /**
     * Checks the body of the email for the special case attachment flag.
     *
     * Sets properties that pertain to case creation/attachment based on how the
     * properties have been set already (i.e. for an API script that handles
     * dedicated case handling aliases) and whether there's a special code in
     * the body of the email.
     */
    public function checkForCaseAttachment($contact = null) {
        $fullBody = $this->parser->getBody();
        $caseFlagPattern = preg_quote($this->settings->caseFlag, '/').'(?<caseId>\d*)';
        // Get the first instance (there may be many, in replies, but the last
        // one (at the top, in standard email quoting convention) is
        // authoritative, if the service case changes or or a preexisting case
        // must be selected instead of making a new one
        if(preg_match("/$caseFlagPattern/",$fullBody,$matches)) {
            $this->_isCase = true;
            if(!empty($matches['caseId'])){
                $this->_caseId = $matches['caseId'];
            } else {
                $this->_caseId = false;
                $this->case = new Services;
            }
        } else {
            $this->_caseId = false;
            $this->_isCase = false;
        }
        // Get the case record.
        if(!empty($this->_caseId)) {
            $this->case = Services::model()->findByPk($this->_caseId);
        }
        // Nonexistent case selected. Reset the case ID so that it starts with a
        // new case.
        if(empty($this->case)) {
            $this->_caseId = false;
            $this->case = new Services;
        }
    }

    /**
     * Creates an action based on the email associated with a given contact.
     */
    public function createAction(X2Model $model, $type = 'email', $subject = '',$save = true){
        if(empty($subject) && !empty($this->parser))
            $subject = $this->parser->getSubject();
        $assignedTo = $this->user->username;
        $action = new Actions('noNotif');
        $action->subject = $subject;
        Yii::app()->setSuModel($this->user);
        $action->associationType = $model->module;
        $action->associationId = $model->id;
        $action->associationName = $model->name;
        $action->assignedTo = $assignedTo;
        $action->completedBy = $assignedTo;
        $action->type = $type;
        $action->visibility = 1;

        if(!empty($this->parser)){
            $action->actionDescription = str_replace("\n", "<br />\n", '<b>'.$this->parser->getSubject().'</b><br />'.($plainBody = $this->parser->bodyCleanup()));
        }else{
            // The error case
            $plainBody = '';
            $action->actionDescription = '<b>Error</b><br />Email parser object was not initialized, so this email was not properly imported.';
        }
        if($model instanceof Services && $this->_isNewCase) {
            $model->description = $plainBody;
            $model->update('description');
        }
        $action->createDate = time();
        $action->lastUpdated = $action->createDate;
        $action->completeDate = $action->createDate;
        if($save) {
            if($action->save()) {
                $this->log(Yii::t('app','Created action record for the email.'));
                $this->createEvent($action);
            }
        }
        return $action;
    }

    /**
     * Create a case and/or action for the current email.
     * @param Contacts $contact The contact record.
     * @param string $type The type of email (to or from); passed to
     *  {@link createAction} when creating the action history record.
     */
    public function createCase(Contacts $contact, $type){
        if(!($createAction = !$this->case->isNewRecord)){
            // Forgo creating the case if it has already been created
            $this->case->contactId = $contact->nameId;
            $this->case->email = $contact->email;

            // Set field defaults.
            //
            // This is for when there are required fields but with no defaults
            // set for them. In such cases, we will need to "guess" the
            // appropriate values based on base code dropdown menus and typical
            // values for cases.
            foreach($this->case->getFields(true) as $name => $field) {
                if($field->required && empty($this->case->$name)) {
                    if($field->type='dropdown') {
                        // Pick the first one that can be found:
                        $dropdownItems = array_keys(Dropdowns::getItems($field->linkType));
                        $this->case->$name = reset($dropdownItems);
                    } 
                    // Otherwise we're SOL; the user hasn't specified any
                    // default value and so it's better to give up than put some
                    // arbitrary hard-coded non-future-proof value in there.
                }
            }

            // This is a nice little extra to have: set the origin to "Email"
            if(empty($this->case->origin))
                $this->case->origin = 'Email';

            $createAction = $this->case->save();
            $this->_isNewCase = $createAction;
        }
        if($createAction){
            $this->case->afterFind();
            $this->createAction($this->case, $type);
        }
    }

    /**
     * Add a new activity feed item for the email
     * @param type $emailAction
     */
    public function createEvent(Actions $emailAction){
        $event = new Events();
        $event->type = self::$typeMap[$emailAction->type];
        $event->subtype = 'email';
        $event->associationId = $emailAction->associationId;
        $event->associationType = X2Model::getModelName($emailAction->associationType);
        $event->timestamp = time();
        $event->lastUpdated = $event->timestamp;
        $event->user = $emailAction->assignedTo;
        if($event->save())
            $this->log(Yii::t('app','Created activity feed event for the email.'));
        return $event;
    }

    public function createSocialPost($text){
        $event = new Events();
        $event->type = 'feed';
        $event->subtype = 'Social Post';
        $event->user = $this->user->username;
        $event->text = $text;
        $event->visibility = 1;
        $event->timestamp = time();
        $event->lastUpdated = $event->timestamp;
        $event->save();
        return $event;
    }


    /**
     * Run the dropbox import on a raw email.
     *
     * Convert an email's raw contents, open for reading at file resource object
     * $fh, into contact/action records.
     *
     * @param type $fh
     */
    public function eml2records($fh){
        $data = is_resource($fh) ? stream_get_contents($fh) : $fh;
        // Initialize the parser
        $this->parser = new EmlParse($data);
        $this->checkForCaseAttachment();
        $this->parser->zapLineBreaks = $this->settings->zapLineBreaks;
        $addressees = $this->parser->getTo();
        $sender = $this->parser->getFrom();
        $subject = $this->parser->getSubject();
        $senderSubject = array('{sender}' => $sender->address, '{subject}' => $subject);
        $this->log(Yii::t('app','Loaded contents of email from {sender}, subject "{subject}".', $senderSubject));

        // Test that the sender is a valid user:
        $user = Profile::model()->findByAttributes(array('emailAddress' => $sender->address));
        if(!(bool) $user){// User doesn't exist in database; no matching address.
            // Silently log the event and exit, but don't notify the sender.
            $this->log(Yii::t('app','Rejecting email with subject "{subject}" from {sender}; it is from an email address that does not match any user profile and may possibly be spam.', $senderSubject));
            return;
        }
        // Set user for model creation
        $this->user = User::model()->findByAttributes(array('username' => $user->username));
        Yii::app()->setSuModel($this->user);


        if(count($addressees)){ // Typically should never be equivalent to false, unless the parser fails.
            $dropboxFwd = false;
            foreach($addressees as $dest){
                // Check to see if the email is addressed directly to the alias.
                // If not, use case 2 will be the case.
                $dropboxFwd = $dropboxFwd || strpos($dest->address, $this->alias) !== false;
                if($dropboxFwd){
                    break;
                }
            }
            if($dropboxFwd){
                // Use case 1: user is forwarding directly to the dropbox capture, so
                // the contact details will need to be parsed from the email body
                // Only operate if the user with that email address exists
                // in the database (to distinguish from spam). Examine the
                // forwarded message.
                $this->log(Yii::t('app','Interpreting email as a forwarded message from a contact or social feed post; email has been sent directly to the alias.'));
                try{
                    $from = $this->parser->getForwardedFrom($this->settings->ignoreEmptyName);
                }catch(Exception $e){
                    if(!preg_match('/^fwd:/i', $this->parser->getSubject())){
                        // Use case 3: Assume the user is *sending* and not
                        // forwarding an email, to put it in as a social post
                        // rather than importing a user's email.
                        $this->log(Yii::t('app','Interpreting email as a social post; it contained no recognized forwarded message patterns and its subject does not indicate that it is a forwarded message.'));
                        return $this->createSocialPost($this->parser->bodyCleanup());
                    }else{
                        $this->log(Yii::t('app','Tried to extract contact info from the attached forwarded message, but no matching patterns are available for extracting it.'));
                        $this->sendErrorEmail('', 'forward');
                        return;
                    }
                }

                $this->log(Yii::t('app','Successfully parsed contact details from the forwarded message.'));
                $contact = $this->resolveContact($from);


                if((bool) $contact){
                    if(!$this->isCase){
                        // Make a new action tied to the contact record
                        $body = Yii::t('app', "Email sent from {contact}:", array(
                            '{contact}' => strtolower(Modules::displayName(false, 'Contacts')),
                        ))."\n\n".$this->parser->bodyCleanup();
                        $action = $this->createAction($contact, 'emailFrom');
                    }else{
                        // Making a service case
                        $this->createCase($contact,'emailFrom');
                    }
                }
            }else{
                // Use case 2: user is CC-ing to the dropbox capture. Get the contact
                // details from the header fields. Much simpler case.
                // Obtain all destinations:
                $this->log(Yii::t('app','Obtaining contact details from the "To:" field; email has been CC-ed to the alias.'));
                $addressees = $this->parser->getTo();
                foreach($addressees as $recipient){
                    $contact = $this->resolveContact($recipient);
                    if((bool) $contact){
                        $body = $this->parser->bodyCleanup();
                        if(!$this->isCase){
                            $action = $this->createAction($contact, 'email');
                        } else {
                            $this->createCase($contact,'email');
                        }
                    }
                }
            }
        }
    }

    /**
     * Getter for {@link settings}
     * @return type
     */
    public function getSettings() {
        if(!isset($this->_settings)) {
            $this->_settings = Yii::app()->settings->emailDropbox;
        }
        return $this->_settings;
    }

    /**
     * Getter for {@link alias}
     * @return string
     */
    public function getAlias(){
        if(!isset($this->_alias)){
            $alias = $this->settings->alias;
            if(!empty($alias))
                $this->_alias = $alias;
            else{
                // No alias specified by the user.
                //
                // Try to guess the full mail alias including domain name based
                // on the default alias and all the addresses in the To: and CC:
                // fields.
                if(isset($this->parser)){
                    foreach($this->parser->getTo() as $addressee){
                        if(preg_match('/^dropbox@/', $addressee->address)){
                            $this->_alias = $addressee->address;
                            break;
                        }
                    }
                    if(!isset($this->_alias)){
                        foreach($this->parser->getCC() as $addressee){
                            if(preg_match('/^dropbox@/', $addressee)){
                                $this->_alias = $addressee;
                                break;
                            }
                        }
                    }
                } else // There's nothing more that can be done at this point
                    $this->_alias = 'dropbox@localhost';
            }
        }
        return $this->_alias;
    }

    public function getCaseId() {
        return $this->_caseId;
    }

    /**
     * Getter for {@link isCase}
     */
    public function getIsCase() {
        if(!isset($this->_isCase)) {
            $this->checkForCaseAttachment();
        }
        return $this->_isCase;
    }

    /**
     * Create a contact
     * @param type $fullName
     * @param Contacts $contact
     * @return \Contacts
     */
    public function instantiateContact($emlContact){
        $contact = new Contacts();
        $fullName = EmlRegex::fullName($emlContact->name);
        $contact->email = $emlContact->address;
        $contact->firstName = $fullName[0];
        $contact->lastName = $fullName[1];
        $contact->name = "{$fullName[0]} {$fullName[1]}";
        $contact->visibility = 1;
        $contact->createDate = time();
        $contact->lastUpdated = $contact->createDate;
        $contact->leadtype = 'E-Mail';
        $contact->leadstatus = 'Assigned';
        $contact->assignedTo = $this->user->username;
        $contact->leadSource = 'None';
        $contact->updatedBy = $this->user->username;
        return $contact;
    }

    /**
     * Record a message in the log.
     *
     * @param string $message Message to record.
     * @param array $params Translation parameters passed to {@link Yii::t()}
     */
    public function log($message){
        if($this->settings->logging)
            Yii::log($message,'trace','application.emailcapture');
    }

    /**
     * Look up a preexisting contact and instantiate/save a new one if one does
     * not exist, based on system settings.
     *
     * @param object $entity An object containing a first/last name and email address
     * @param bool $sendEmail Whether to send an error email if the contact fails validation.
     * @return bool|Contacts
     */
    public function resolveContact($entity, $sendEmail = true){
        $contact = Contacts::model()->findByEmail($entity->address);
        if(!(bool) $contact){
            // Contact not found. Create new?
            $this->log(Yii::t('app','No preexisting contact matching {email} was found.', array('{email}' => $entity->address)));
            if($this->settings->createContact){
                // Make a new contact
                $contact = $this->instantiateContact($entity);
                $params = array(
                    '{firstName}' => $contact->firstName,
                    '{lastName}' => $contact->lastName,
                    '{email}' => $contact->email,
                    '{errors}' => '',
                    '{Contact}' => Modules::displayName(false, 'Contacts'),
                );
                if(!$this->settings->emptyContact){ // Check for non-empty first or last name
                    if($contact->firstName == 'UnknownFirstName' || $contact->lastName == 'UnknownLastName'){
                        $this->log(Yii::t('app','Skipping creation of new contact; first or last name was not found in email metadata, and option Admin.emailDropbox.emptyContact is disabled.'));
                        return false;
                    }
                }
                $this->log(Yii::t('app','Creating new contact: {firstName} {lastName} ({email})', $params));
                if(!$contact->save()){
                    $params['{errors}'] = CJSON::encode($contact->errors);
                    $this->log(Yii::t('app','Contact failed validation and/or could not be saved: {firstName} {lastName}, email {email}. Validation errors were as follows: {errors}', $params));
                    if($sendEmail){
                        $this->sendErrorEmail(Yii::t('app', '{Contact} failed validation and/or could not be saved: {firstName} {lastName}, email {email}. Validation errors were as follows: {errors}', $params));
                    }
                    return false;
                }
                $this->log(Yii::t('app','Contact saved successfully.'));
            }else{
                // Nope.
                $this->log(Yii::t('app','Skipping creation of new contact; option "Create contacts from emails" is disabled.'));
                return false;
            }
        }
        Yii::app()->setSuModel($this->user);
        return $contact;
    }

    /**
     * In the case of failure, sends a message to the original sender.
     *
     * @param string $message The message.
     * @param string $type The type of error
     * @param bool $send Whether to send the email, or return the PHPMailer object (default: true)
     * @return \PHPMailer
     */
    public function sendErrorEmail($message, $type = null, $send = True){
        $eml = new InlineEmail;
        $emailFrom = Credentials::model()->getDefaultUserAccount(Credentials::$sysUseId['systemNotificationEmail'], 'email');
        if($emailFrom == Credentials::LEGACY_ID){
            $eml->from = array(
                'name' => 'X2Engine Email Capture',
                'address' => Yii::app()->settings->emailFromAddr,
            );
        }else{
            $eml->credId = $emailFrom;
        }

        $mail = $eml->mailer;


        $mail->FromName = 'X2Engine Email Capture';
        $fancyDiv = '<div style="font:normal 14px/21px Arial,\'Lucida Sans Unicode\',\'Lucida Grande\',\'Trebuchet MS\',Helvetica,sans-serif;color:#666;">';
        switch($type){
            case 'forward':
                $mail->Subject = 'Unrecognized forwarded email format';
                $msg = $fancyDiv.'<p>The email capture script was not able to recognize the format of the forwarded message, and thus was not able to obtain contact data. Note also that if email is sent directly to the email capture address, it requires that the body contain a forwarded message.</p>';
                $msg .= '<p>Please forward this email, using the email client program that caused the error, to X2Engine Customer Support at '.self::$authorEmail.' so that support for the format can be added. You may redact any information you wish from the original message.</p>';
                $msg .= "<p>The original email's contents were as follows:</p></div>";
                $msg .= '<pre>'.$this->parser->getBody().'</pre>';
                $this->log(Yii::t('app','Failed parsing forwarded email. The contents were as follows:')."\n".$this->parser->getBody());
                // We also should stash the failed email in the database so it
                // can later be used for self-service, i.e. generating new
                // patterns through the app.
                break;
            default:
                $mail->Subject = 'Error while attempting to import data from an email.';
                $msg = $fancyDiv.'An unexpected error occurred while attempting to import an email. The email was:</div>';
                $msg .= "<pre>$message</pre>";
                $msg .= $fancyDiv."The original message was:</div><pre>".$this->parser->getBody()."</pre>";
                $this->log(Yii::t('app','Failed creating a contact. The email was:').$this->parser->getBody());
        }

        $mail->MsgHTML($msg);
        $origSender = $this->parser->getFrom();
        if(is_array($origSender)){
            foreach($origSender as $sender)
                $mail->AddAddress($sender->address);
        } else
            $mail->AddAddress($origSender->address);

        if($send){
            $mail->Send();
            return null;
        }else{
            return $mail;
        }
    }

    /**
     * Setter for {@link alias}
     * @param string $value
     * @return string
     */
    public function setAlias($value){
        return $this->_alias = $value;
    }

    /**
     * Setter for {@link caseId}
     * @param type $value
     */
    public function setCaseId($value) {
        $this->_caseId = $value;
    }

}

?>
