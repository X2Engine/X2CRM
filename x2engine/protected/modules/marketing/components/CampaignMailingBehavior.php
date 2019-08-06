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




Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.components.util.StringUtil', true);

/**
 * Behavior class for email delivery in email marketing campaigns.
 *
 * Static methods are used for batch emailing; all non-static methods assume that
 * an individual email is being sent.
 *
 * @property Campaign $campaign Campaign model for the current email
 * @property boolean $isNewsletter True if sending to a newsletter list (not a contacts list)
 * @property X2List $list The list corresponding to the current campaign being operated on
 * @property X2ListItem $listItem List item of the
 * @property Contacts $recipient The contact of the current recipient that the
 *  email is being sent to. If it's not a campaign, but a newsletter, this will
 *  be an ad-hoc contact model with its email address set to that of the list
 *  item.
 * @package application.modules.marketing.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CampaignMailingBehavior extends EmailDeliveryBehavior {

    /**
     * Filename of lock file in protected/runtime, to signal that emailing is
     * already in progress and other processes should not attempt to send email
     * (as this may result in race conditions and duplicate emails)
     */
    const EMLLOCK = 'campaign_emailing.lock';

    /**
     * Error code for the bulk limit being reached
     */
    const STATE_BULKLIMIT = 1;

    /**
     * Error code for an email already sending.
     */
    const STATE_RACECOND = 2;

    /**
     * Error code for an item whose address has suddenly gone blank
     */
    const STATE_NULLADDRESS = 3;

    /**
     * Error code for an unsubscribed / do-not-email contact
     */
    const STATE_DONOTEMAIL = 4;

    /**
     * Error code for another email process beating us to the punch
     */
    const STATE_SENT = 5;

    /**
     * Stores the time that the batch operation started (when calling this
     * class' methods statically)
     * @var type
     */
    public static $batchTime;

    /**
     * @var Campaign The current campaign model being operated on
     */
    public $_campaign;

    /**
     * True if the campaign is getting sent to a web list (not corresponding to
     * contacts).
     * @var boolean
     */
    private $_isNewsletter;

    /**
     * List model corresponding to the campaign.
     * @var type X2List
     */
    private $_list;

    /**
     * List item model
     */
    private $_listItem;

    /**
     * Contact record corresponding to the recipient of the current mail being
     * delivered.
     * 
     * @var Contacts
     */
    private $_recipient;

    /**
     * Whether the campaign mailing process should halt as soon as possible
     * @var type 
     */
    public $fullStop = false;

    /**
     * The ID of the campaign list item corresponding to the current recipient.
     * @var integer
     */
    public $itemId;

    /**
     * Indicates whether the mail cannot be sent due to a recent change in the
     * list item or contact record.
     * @var boolean
     */
    public $stateChange = false;

    /**
     * Indicates the type of state change that should block email delivery.
     * This purpose is not relegated to {@link status} ("code" element) because
     * that array is intended for PHPMailer codes.
     * @var integer
     */
    public $stateChangeType = 0;


    /**
     * Whether the current email could not be delivered due to bad RCPT or something
     * that's not a critical PHPMailer error
     */
    public $undeliverable = false;

    /**
     * The IDs of suppressed contacts from suppression List
     * @var array
     */
    public $suppressedContactsIds = array();

    /////////////////////////
    // INDEPENDENT METHODS //
    /////////////////////////
    //
    // Used whether bulk-sending or individually sending

    /**
     * Prepares the subject and body of a campaign email.
     *
     * Any and all features of the campaign that are dynamically added at the
     * last minute to the email body are added in this method right before
     * the sending of the email.
     *
     * Returns an array; 
     * 
     * First element: email subject
     * Second element: email body
     * Third element: unique ID assigned to the current email
     *
     * @param Campaign $campaign Campaign of the current email being sent
     * @param Contacts $contact Contact to whom the email is being sent
     * @param type $email
     * @param bool $replaceBreaks used for unit testing
     * @param bool $replaceUnsubToken used for unit testing
     * @return type
     * @throws Exception
     */
    public static function prepareEmail (
        Campaign $campaign, Contacts $contact, $replaceBreaks=true, $replaceUnsubToken=true) {

        $email = $contact->email;
        $now = time();
        $uniqueId = md5 (uniqid (mt_rand (), true));

        // Add some newlines to prevent hitting 998 line length limit in
        // phpmailer and rfc2821
        if ($replaceBreaks) {
            $emailBody = StringUtil::pregReplace('/<br>/', "<br>\n", $campaign->content);
        } else {
            $emailBody = $campaign->content;
        }

        // Add links to attachments
        try{
            $attachments = $campaign->attachments;
            if(sizeof($attachments) > 0){
                $emailBody .= "<br>\n<br>\n";
                $emailBody .= '<b>'.Yii::t('media', 'Attachments:')."</b><br>\n";
            }
            foreach($attachments as $attachment){
                $media = $attachment->mediaFile;
                if($media){
                    if($file = $media->getPath()){
                        if(file_exists($file)){ // check file exists
                            if ($media->getPublicUrl()) {
                                $emailBody .= CHtml::link($media->fileName, $media->getPublicUrl()).
                                    "<br>\n";
                            }
                        }
                    }
                }
            }
        }catch(Exception $e){
            throw $e;
        }

        // Replacement in body
        $emailBody = Docs::replaceVariables($emailBody, $contact, array (
            '{trackingKey}' => $uniqueId, // Use the campaign key, not the general contact key
        ));

        // transform links after attribute replacement but before signature and unsubscribe link 
        // insertion
        if ($campaign->enableRedirectLinks) {

            // Replace links with tracking links
            $url = Yii::app()->controller->createAbsoluteUrl (
                '/marketing/marketing/click', array ('uid' => $uniqueId, 'type' => 'click'));
            $emailBody = StringUtil::pregReplaceCallback (
                '/(<a[^>]*href=")([^"]*)("[^>]*>)/', 
                function (array $matches) use ($url) {
                    return $matches[1].$url.'&url='.urlencode ($matches[2]).''.
                        $matches[3];
                }, $emailBody);
        }

        // Insert unsubscribe link placeholder in the email body if there is
        // none already:
        if(!preg_match('/\{_unsub\}/', $campaign->content)){
            $unsubText = "<br/>\n-----------------------<br/>\n".
                Yii::t('marketing', 'To stop receiving these messages, click here').": {_unsub}";
            // Insert
            if(strpos($emailBody,'</body>')!==false) {
                $emailBody = str_replace('</body>',$unsubText.'</body>',$emailBody);
            } else {
                $emailBody .= $unsubText;
            }
        }

        // Insert unsubscribe link(s):
        $unsubUrl = Yii::app()->createExternalUrl('/marketing/marketing/click', array(
            'uid' => $uniqueId,
            'type' => 'unsub',
            'email' => $email,
            'listModel' => 'Contacts',
            'contactId' => $contact->id,
            'campaignId' => $campaign->id,

        ));
        $unsubLinkText = Yii::app()->settings->getDoNotEmailLinkText();
        if ($replaceUnsubToken) {
            $emailBody = StringUtil::pregReplace (
                '/\{_unsub\}/', 
                '<a href="'.$unsubUrl.'">'.Yii::t('marketing', $unsubLinkText).'</a>',
                $emailBody);
        }

        // Get the assignee of the campaign, for signature replacement.
        $user = User::model()->findByAttributes(array('username' => $campaign->assignedTo));
        $emailBody = Docs::replaceVariables($emailBody, null, array (
            '{signature}' => ($user instanceof User) ? 
                Docs::replaceVariables ($user->profile->signature, $contact) : '',
        ));

        // Replacement in subject
        $subject = Docs::replaceVariables($campaign->subject, $contact);

        // Add the transparent tracking image:
        $trackingImage = '<img src="'.Yii::app()->createExternalUrl(
            '/marketing/marketing/click', array('uid' => $uniqueId, 'type' => 'open')).'"/>';
        if(strpos($emailBody,'</body>')!==false) {
            $emailBody = str_replace('</body>',$trackingImage.'</body>',$emailBody);
        } else {
            $emailBody .= $trackingImage;
        }

        return array($subject, $emailBody, $uniqueId);
    }

    public static function emailLockFile() {
        return implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'runtime',self::EMLLOCK));
    }

    public static function lockEmail($lock = true) {
        $lf = self::emailLockFile();
        if($lock) {
            file_put_contents($lf,time());
        } else {
            unlink($lf);
        }
    }

   /**
    * Mediates lockfile checking.
    */
    public static function emailIsLocked() {
        $lf = self::emailLockFile();
        if(file_exists($lf)) {
            $lock = file_get_contents($lf);
            if(time() - (int) $lock > 3600) { // No operation should take longer than an hour
                unlink($lf);
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * For a given list ID, find all contact/list item entries such that sending
     * is possible, is permissible, or has happened.
     *
     * The criteria are:
     * - x2_list_item.listId matches the list being operated on
     * - x2_list_item.unsubscribed and x2_contacts.doNotEmail are both zero
     *  (contact has not specified that email is unwelcome)
     * - One of x2_list_item.emailAddress or x2_contacts.email is non-empty, so
     *  that there is actually an email address to send to
     *
     * @param integer $listId The ID of the list operating on
     * @param boolean $unsent Constrain (if true) the query to unsent entries.
     * @return array An array containing the "id", "sent" and "uniqueId" columns.
     */
    public static function deliverableItems($listId,$unsent = false) {
        //this first bit is to get the type of stuff the list is made of IE(contacts, leads, ...)
        $MyType = X2List::model()->findByPk($listId)->modelName;
        $tableName= 'x2_contacts';
        if($MyType == 'X2Leads'){$tableName= 'x2_x2leads';}
        if($MyType == 'Opportunity'){$tableName= 'x2_opportunities';}
        if($MyType == 'Accounts'){$tableName= 'x2_accounts';}
        $where = ' WHERE 
            i.listId=:listId
            AND i.unsubscribed=0
            AND (c.doNotEmail!=1 OR c.doNotEmail IS NULL)
            AND NOT ((c.email IS NULL OR c.email="") AND (i.emailAddress IS NULL OR i.emailAddress=""))';
        if($unsent) {
            $where .= ' AND i.sent=0 AND i.suppressed=0';
        }
        return Yii::app()->db->createCommand('SELECT
            i.id,i.sent,i.uniqueId,i.suppressed
            FROM x2_list_items AS i
            LEFT JOIN ' . $tableName . ' AS c ON c.id=i.contactId '.$where)
                    ->queryAll(true,array(':listId'=>$listId));
    }

    /**
     * Similar to deliverableItems but only retrieves count
     */
    public static function deliverableItemsCount($listId,$unsent = false) {
                //this first bit is to get the type of stuff the list is made of IE(contacts, leads, ...)
       $MyType = X2List::model()->findByPk($listId)->modelName;
       $list = X2List::model()->findByPk($listId);
        if($MyType == 'Contacts'){$tableName= 'x2_contacts'; $model = new Contacts('search');}
        if($MyType == 'X2Leads'){$tableName= 'x2_x2leads'; $model = new X2Leads('search');}
        if($MyType == 'Opportunity'){$tableName= 'x2_opportunities'; $model = new Opportunity('search');}
        if($MyType == 'Accounts'){$tableName= 'x2_accounts'; $model = new Accounts('search');}
        if($list->type == 'dynamic'){
            return $model->searchList($listId)->totalItemCount;
        }
        $where = ' WHERE 
            i.listId=:listId
            AND i.unsubscribed=0
            AND (c.doNotEmail!=1 OR c.doNotEmail IS NULL)
            AND NOT ((c.email IS NULL OR c.email="") AND (i.emailAddress IS NULL OR i.emailAddress=""))';
        if($unsent) {
            $where .= ' AND i.sent=0 AND i.suppressed=0';
        }
        return Yii::app()->db->createCommand('SELECT COUNT(*)
            FROM x2_list_items AS i
            LEFT JOIN ' . $tableName . ' AS c ON c.id=i.contactId '.$where)
                ->queryScalar(array(':listId'=>$listId));
    }

    public static function recordEmailSent(Campaign $campaign, Contacts $contact){
        $action = new Actions;
        // Disable the unsightly notifications for loads of emails:
        $action->scenario = 'noNotif';
        $now = time();
        
        //this next code will check to make sure the campaign is using contact or other (opp, leads, accounts ....)
        $action->associationType = 'contacts';
        if($campaign->list->modelName == "Opportunity"){
            $action->associationType = 'opportunities';
        }
        if($campaign->list->modelName == "Accounts"){
            $action->associationType = 'accounts';
        }
        if($campaign->list->modelName == "X2Leads"){
            $action->associationType = 'x2Leads';
        }
        $action->associationId = $contact->id;
        $action->associationName = $contact->firstName.' '.$contact->lastName;
        $action->visibility = $contact->visibility;
        $action->type = 'email';
        $action->assignedTo = $contact->assignedTo;
        $action->createDate = $now;
        $action->completeDate = $now;
        $action->complete = 'Yes';
        $actionDescription = '<b>'.Yii::t('marketing', 'Campaign').': '.$campaign->name."</b>\n\n"."<br><br>".Yii::t('marketing', 'Email'). ': '.$contact->email."<br><br>\n\n"
                .Yii::t('marketing', 'Subject').": ".Docs::replaceVariables($campaign->subject, $contact)."<br><br>\n\n".Docs::replaceVariables($campaign->content, $contact);

        // Prepare action attributes for direct insertion to skip ActiveRecord overhead
        $attr = $action->attributes;
        $attachedAttr = array_merge(array('actionDescription'), $action->getMetaDataFieldNames());
        $attr = array_diff_assoc($attr, array_fill_keys($attachedAttr, ''));
        $count = Yii::app()->db->createCommand()
            ->insert('x2_actions', $attr);
        if($count < 1) {
            throw new CException('Campaign email action history record failed to save with validation errors: '.CJSON::encode($action));
        }
        $actionId = Yii::app()->db->schema->commandBuilder
            ->getLastInsertId('x2_actions');
        $count = Yii::app()->db->createCommand()
            ->insert('x2_action_text', array('actionId' => $actionId, 'text' => $actionDescription));
        if($count < 1) {
            throw new CException('Campaign email action history record failed to save with validation errors: '.CJSON::encode($action));
        }
        
        // Manually trigger since hooks won't be called
        $contact->lastActivity = time();
        //have to update each type 
        if($campaign->list->modelName == "Contacts"){
            $contact->update(array('lastActivity'));
        X2Flow::trigger('RecordUpdateTrigger', array(
            'model' => $contact,
        ));
        }
        if($campaign->list->modelName == "Opportunity"){
            Yii::app()->db
            ->createCommand("UPDATE x2_opportunities SET lastActivity = :TIME WHERE  id=:RListID")
            ->bindValues(array(':TIME' => time(), ':RListID' => $contact->id))
            ->execute();
        X2Flow::trigger('RecordUpdateTrigger', array(
            'model' => Opportunity::model()->findByPk($contact->id),
        ));
        }
        if($campaign->list->modelName == "Accounts"){
            Yii::app()->db
            ->createCommand("UPDATE x2_accounts SET lastActivity = :TIME WHERE  id=:RListID")
            ->bindValues(array(':TIME' => time(), ':RListID' => $contact->id))
            ->execute();
        X2Flow::trigger('RecordUpdateTrigger', array(
            'model' => Accounts::model()->findByPk($contact->id),
        ));
        }
        if($campaign->list->modelName == "X2Leads"){
            Yii::app()->db
            ->createCommand("UPDATE x2_x2leads SET lastActivity = :TIME WHERE  id=:RListID")
            ->bindValues(array(':TIME' => time(), ':RListID' => $contact->id))
            ->execute();
        X2Flow::trigger('RecordUpdateTrigger', array(
            'model' => X2Leads::model()->findByPk($contact->id),
        ));
        }
        

    }

    /////////////////////////////////
    // INDIVIDUAL DELIVERY METHODS //
    /////////////////////////////////
    //
    // When used as a behavior, the class is geared towards sending individual
    // emails.

    /**
     * Campaign model for this e-mail
     * @return type
     */
    public function getCampaign() {
        return $this->_campaign;
    }

    /**
     * Credentials record to be used. Overrides {@link EmailDeliveryBehavior::sendAs}
     * in order to configure SMTP delivery.
     * @return type
     */
    public function getCredId() {
        return $this->campaign->sendAs;
    }

    public function getIsNewsletter() {
        if(!isset($this->_isNewsletter)) {
            $this->_isNewsletter = empty($this->listItem->contactId);
        }
        return $this->_isNewsletter;
    }

    /**
     * Getter for {@link list}
     */
    public function getList() {
        if(!isset($this->_list)) {
            $this->_list = $this->campaign->list;
        }
    }

    /**
     * Getter for {@link listItem}
     */
    public function getListItem() {
        if(!isset($this->_listItem)) {
            $this->_listItem = X2ListItem::model()->findByAttributes(array (
                'id' => $this->itemId,
            ));
        }
        return $this->_listItem;
    }

    /**
     * Getter for {@link recipient}
     * @return type
     */
    public function getRecipient() {
 
        if(!isset($this->_recipient)) {
 
           
                 if($this->campaign->list->modelName == "Contacts"){
                $this->_recipient = $this->listItem->contact;
                 }
                           //this code is to ensure I can work with accounts
                    if($this->campaign->list->modelName == "Accounts"){

                        $Acc = Accounts::model()->findByPk($this->listItem->contactId);
                        $ContHold = new Contacts;
                        $ContHold->id =  $Acc->id;
                        $ContHold->name =  $Acc->name;
                        $ContHold->nameId =  $Acc->nameId;
                        $ContHold->firstName =  $Acc->firstName;
                        $ContHold->lastName =  $Acc->lastName;
                        $ContHold->company =  $Acc->company;
                        $ContHold->email =  $Acc->email;
                        $ContHold->phone =  $Acc->phone;
                        $ContHold->doNotEmail =  $Acc->doNotEmail;
                        $ContHold->doNotCall =  $Acc->doNotCall;
                        $ContHold->visibility = $Acc->visibility;
                        $ContHold->preferredEmail = $Acc->preferredEmail;
                        $ContHold->businessEmail = $Acc->businessEmail;
                        $ContHold->personalEmail = $Acc->personalEmail;
                        $ContHold->alternativeEmail = $Acc->alternativeEmail;
                        $this->_recipient = $ContHold;
                    }
                    if($this->campaign->list->modelName == "X2Leads"){

                        $Acc = X2Leads::model()->findByPk($this->listItem->contactId);
                        $ContHold = new Contacts;
                        $ContHold->id =  $Acc->id;
                        $ContHold->name =  $Acc->name;
                        $ContHold->nameId =  $Acc->nameId;
                        $ContHold->firstName =  $Acc->firstName;
                        $ContHold->lastName =  $Acc->lastName;
                        $ContHold->visibility = $Acc->visibility;
                        $ContHold->email =  $Acc->email;
                        $ContHold->phone =  $Acc->phone;
                        $ContHold->doNotEmail =  $Acc->doNotEmail;
                        $ContHold->doNotCall =  $Acc->doNotCall;
                        $ContHold->preferredEmail = $Acc->preferredEmail;
                        $ContHold->businessEmail = $Acc->businessEmail;
                        $ContHold->personalEmail = $Acc->personalEmail;
                        $ContHold->alternativeEmail = $Acc->alternativeEmail;
                        $this->_recipient = $ContHold;
                    }
                    if($this->campaign->list->modelName == "Opportunity"){

                        $Acc = Opportunity::model()->findByPk($this->listItem->contactId);
                        $ContHold = new Contacts;
                        $ContHold->id =  $Acc->id;
                        $ContHold->name =  $Acc->name;
                        $ContHold->nameId =  $Acc->nameId;
                        $ContHold->firstName =  $Acc->firstName;
                        $ContHold->lastName =  $Acc->lastName;
                        $ContHold->visibility = $Acc->visibility;
                        $ContHold->email =  $Acc->email;
                        $ContHold->phone =  $Acc->phone;
                        $ContHold->doNotEmail =  $Acc->doNotEmail;
                        $ContHold->doNotCall =  $Acc->doNotCall;
                        $ContHold->preferredEmail = $Acc->preferredEmail;
                        $ContHold->businessEmail = $Acc->businessEmail;
                        $ContHold->personalEmail = $Acc->personalEmail;
                        $ContHold->alternativeEmail = $Acc->alternativeEmail;
                        $this->_recipient = $ContHold;
                    }

            if (!isset($this->_recipient)) {
                // Newsletter
                $this->_recipient = new Contacts;
                $this->_recipient->email = $this->listItem->emailAddress;
            }
        }
        //this code is to change the email 
        if(!empty($this->_recipient->preferredEmail) && $this->_recipient->preferredEmail !== "Default" && $this->_recipient->preferredEmail !== "email"){
            $emailType = $this->_recipient->preferredEmail;
            $this->_recipient->email = $this->_recipient->$emailType;
        }
        return $this->_recipient;
    }

    /**
     * One final check for whether the mail should be sent, and enable the
     * 'sending' flag.
     * 
     * This is a safeguard for the use case of batch emailing when a user
     * subscribes or a value in the database changes between loading the list
     * items to deliver and when the actual delivery takes place.
     * @return bool True if we're clear to send; false otherwise.
     */
    public function mailIsStillDeliverable() {
        // Check if the batch limit has been reached:
        $admin = Yii::app()->settings;
        if($admin->emailCountWillExceedLimit() && !empty($admin->emailStartTime)) {
            $this->status['code'] = 0;
            $t_now = time();
            $t_remain = ($admin->emailStartTime + $admin->emailInterval) - $t_now;
            $params = array();
            if($t_remain > 60) {
                $params['{units}'] = $t_remain >= 120 ? Yii::t('app','minutes') : Yii::t('app','minute');
                $params['{t}'] = round($t_remain/60);
            } else {
                $params['{units}'] = $t_remain == 1 ? Yii::t('app','second') : Yii::t('app','seconds');
                $params['{t}'] = $t_remain;
            }

            $this->status['message'] = Yii::t('marketing', 'The email sending limit has been reached.').' '.Yii::t('marketing','Please try again in {t} {units}.',$params);
            $this->fullStop = true;
            $this->stateChange = true;
            $this->stateChangeType = self::STATE_BULKLIMIT;
            return false;
        }

        // Sending flag check:
        //
        // Perform the update operation to flip the flag, and if zero rows were
        // affected, that indicates it's already sending.
        $sendingItems = Yii::app()->db->createCommand()
                ->update($this->listItem->tableName(), array('sending' => 1), 'id=:id AND sending=0', array(':id' => $this->listItem->id));
        // If no rows matched, the message is being sent right now.
        $this->stateChange = $sendingItems == 0;
        if($this->stateChange) {
            $this->status['message'] = Yii::t('marketing','Skipping {email}; another concurrent send operation is handling delivery to this address.',array('{email}'=>$this->recipient->email));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_RACECOND;
            return false;
        }

        // Additional checks
        //
        // Email hasn't been set blank:
        if($this->stateChange = $this->stateChange || $this->recipient->email == null) {
            $this->status['message'] = Yii::t('marketing','Skipping delivery for recipient {id}; email address has been set to blank.',array('{id}'=>$this->itemId));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_NULLADDRESS;
            return false;
        }

        // Contact unsubscribed suddenly
        if($this->stateChange = $this->stateChange || $this->listItem->unsubscribed!=0 || $this->recipient->doNotEmail!=0) {
            $this->status['message'] = Yii::t('marketing','Skipping {email}; the contact has unsubscribed.',array('{email}'=>$this->recipient->email));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_DONOTEMAIL;
            return false;
        }

        // Contact has been suppressed by another list
        if (!empty($this->campaign->suppressionListId) && $this->isContactSuppressed()) {
            $this->status['message'] = Yii::t(
                'marketing',
                'Skipping email sending to {address}. This contact is skipped as it is present in suppressed List.',
                array('{address}'=>$this->recipient->email)
            );
            // Undeliverable mail. Mark as sent but without unique ID, designating it as a bad address but suppressed
            $this->undeliverable = true;
            $this->markEmailSent(null, false, true);
            $this->status['code'] = 412; // precondition failed
            return false;
        }

        // Contact has unsubscribed from this category
        $userCheck = X2ListItem::model()->findByAttributes(array(
                'emailAddress' => $this->recipient->email,
                'listId' => $this->campaign->categoryListId,
            ));
        if (!empty($userCheck)) {
            $this->status['message'] = Yii::t(
                'marketing',
                'Skipping email sending to {address}. This Email is skipped as it unsubscribed from this Category.',
                array('{address}'=>$this->recipient->email)
            );
            // Undeliverable mail. Mark as sent but without unique ID, designating it as a bad address but suppressed
            $this->undeliverable = true;
            $this->markEmailSent(null, false, false, true);
            $this->status['code'] = 412; // precondition failed
            return false;
        }

        // Another mailing process sent it already:
        $this->listItem->refresh();
        if($this->stateChange = $this->stateChange || $this->listItem->sent !=0) {
            $this->status['message'] = Yii::t('marketing','Email has already been sent to {address}',array('{address}'=>$this->recipient->email));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_SENT;
            return false;
        }

        return true;
    }


    /**
     * Records the date of delivery and marks the list record with the unique id.
     *
     * This method will not just update the current list item; it selects all
     * list items if their email address and list ID are the same. This is to
     * avoid sending duplicate messages.
     *
     * If mail is non-deliverable, it should still be marked as sent but with a
     * null unique ID, to designate it as a bad email address.
     * 
     * @param type $uniqueId
     * @param bool $unsent If false, perform the opposite operation (mark as not
     *  currently sending).
     * @param bool $suppressed true if contact is in suppression List
     */
    public function markEmailSent($uniqueId,$sent = true, $suppressed=false, $unsubscribed=false) {
        $params = array(
            ':listId' => $this->listItem->listId,
            ':emailAddress' => $this->recipient->email,
            ':email' => $this->recipient->email,
            ':setEmail' => $this->recipient->email,
            ':id' => $this->itemId,
            ':sent' => $sent?time():0,
            ':uniqueId' => $sent?$uniqueId:null,
            ':suppressed' => $suppressed ? time() : 0,
            ':unsubscribed' => $unsubscribed ? time() : 0,
        );
        $condition = 'i.id=:id OR (i.listId=:listId AND (i.emailAddress=:emailAddress OR c.email=:email))';
        $columns = 'i.sent=:sent,i.uniqueId=:uniqueId,i.unsubscribed=:unsubscribed,i.suppressed=:suppressed,sending=0,emailAddress=:setEmail';
        Yii::app()->db->createCommand('UPDATE x2_list_items AS i LEFT JOIN x2_contacts AS c ON c.id=i.contactId SET '.$columns.' WHERE '.$condition)->execute($params);
    }

    /**
     * Checks whether this contact is present is suppressed
     * @return bool true if contact is in suppression list else false
     */
    public function isContactSuppressed() {
        if (count($this->campaign->suppressionList->listItems) !=count($this->suppressedContactsIds)) {
            $this->setSuppressedContactIds();
        }
        return (in_array($this->recipient->id, $this->suppressedContactsIds));
    }

    /**
     * This function set the array for suppression contact for the active campaign
     */
    public function setSuppressedContactIds() {
        foreach ($this->campaign->suppressionList->listItems as $listItem) {
            $contactId = $listItem->__get('contactId');
            if (!in_array($contactId, $this->suppressedContactsIds)) {
                array_push($this->suppressedContactsIds, $contactId);
            }
        }
    }

    /**
     * Send an email.
     */
    public function sendIndividualMail() {
        
        if(!$this->mailIsStillDeliverable()) {
            return;
        }
        
 
        $addresses = array(array('',$this->recipient->email));
        $deliver = true;
 
        try {
            list($subject,$message,$uniqueId) = self::prepareEmail(
                $this->campaign,$this->recipient);
        } catch (StringUtilException $e) {
            $this->fullStop = true;
            $this->status['code'] = 500;
            $this->status['exception'] = $e;
            if ($e->getCode () === StringUtilException::PREG_REPLACE_CALLBACK_ERROR) {
                $this->status['message'] = Yii::t('app', 'Email redirect link insertion failed');
            } else {
                $this->status['message'] = Yii::t('app', 'Failed to prepare email contents');
            }
            $deliver = false;
        }

        if ($deliver) {
            $unsubUrl = Yii::app()->createExternalUrl('/marketing/marketing/click', array(
                'uid' => $uniqueId,
                'type' => 'unsub',
                'email' => $this->recipient->email
            ));
            $bounceHandlingEmail = null;
            $bounceInfo = array(
                'campaignId' => $this->campaign->id,
                'contactId' => $this->recipient->id,
                'listModel' => $this->campaign->list->modelName,
                'uniqueId' => $uniqueId,
            );
            if ($this->campaign->enableBounceHandling && isset($this->campaign->bouncedAccount)) {
                $credential = Credentials::model ()->findByPk ($this->campaign->bouncedAccount);
                $bounceHandlingEmail = $credential->getAuthModel()->email;
            }
            $this->deliverEmail($addresses, $subject, $message, array(), $unsubUrl, $bounceHandlingEmail, $bounceInfo);
        }
        if($this->status['code'] == 200) {
            // Successfully sent email. Mark as sent.
            $this->markEmailSent($uniqueId);
            if(!$this->isNewsletter) // Create action history records; sent to contact list
                self::recordEmailSent($this->campaign,$this->recipient);
            $this->status['message'] = Yii::t('marketing','Email sent successfully to {address}.',array('{address}' => $this->recipient->email));
        } else if ($this->status['exception'] instanceof phpmailerException) {
            // Undeliverable mail. Mark as sent but without unique ID, designating it as a bad address
            $this->status['message'] = Yii::t('marketing','Email could not be sent to {address}. The message given was: {message}',array(
                '{address}'=>$this->recipient->email,
                '{message}'=>$this->status['exception']->getMessage()
            ));

            if($this->status['exception']->getCode() != PHPMailer::STOP_CRITICAL){
                $this->undeliverable = true;
                $this->markEmailSent(null, false);
            }else{
                $this->fullStop = true;
            }
        } else if($this->status['exception'] instanceof phpmailerException && $this->status['exception']->getCode() == PHPMailer::STOP_CRITICAL) {
        } else {
            // Mark as "not currently working on sending"...One way or another, it's done.
            $this->listItem->sending = 0;
            $this->listItem->update(array('sending'));
        }

        // Keep track of this email as part of bulk emailing
        Yii::app()->settings->countEmail();

        // Update the last activity on the campaign
        $this->campaign->lastActivity = time();
        // Finally, if the campaign is totally done, mark as complete.
        if(self::deliverableItemsCount($this->campaign->list->id, true) == 0) {
            $this->status['message'] = Yii::t('marketing','All emails sent.');
            $this->campaign->active = 0;
            $this->campaign->complete = 1;
            $this->campaign->update(array('lastActivity','active','complete'));
        } else {
            $this->campaign->update(array('lastActivity'));
        }
    }

    public function setCampaign(Campaign $value) {
        $this->_campaign = $value;
    }


    //////////////////////////
    // BULK MAILING METHODS //
    //////////////////////////

   /**
    * Send mail for any active campaigns, in a batch.
    *
    * This method is made public and static to allow it to be called from elsewhere,
    * without instantiation.
    *
    * @param integer $id The ID of the campaign to return status messages for
    */
    public static function sendMail($id = null, $t0 = null){
        self::$batchTime = $t0 === null ? time() : $t0;
        $admin = Yii::app()->settings;
        $messages = array();
        $totalSent = 0;
        try{
            // Get all campaigns that could use mailing
            $campaigns = Campaign::model()->findAllByAttributes(
                    array('complete' => 0, 'active' => 1, 'type' => 'Email'), 'launchdate > 0 AND launchdate < :time', array(':time' => time()));
            foreach($campaigns as $campaign){
                if ($campaign->list->type != 'campaign') {
                    // A campaign with a launch date but whose list is not yet a campaign type,
                    // has not yet been launched.
                    $newList = $campaign->list->staticDuplicate();
                    if(!isset($newList) || empty($campaign->subject))
                        continue;
                    $newList->type = 'campaign';
                    if($newList->save()) {
                        $campaign->list = $newList;
                        $campaign->listId = $newList->nameId;
                        $campaign->update('listId');
                    } else {
                        continue;
                    }
                }
                try{
                    list($sent, $errors) = self::campaignMailing($campaign);
                }catch(CampaignMailingException $e){
                    $totalSent += $e->return[0];
                    $messages = array_merge($messages, $e->return[1]);
                    $messages[] = Yii::t('marketing', 'Successful email sent').': '.$totalSent;
                    $wait = ($admin->emailInterval + $admin->emailStartTime) - time();
                    return array('wait' => $wait, 'messages' => $messages);
                }
                $messages = array_merge($messages, $errors);
                $totalSent += $sent;
                if(time() - self::$batchTime > Yii::app()->settings->batchTimeout)
                    break;

            }
            if(count($campaigns) == 0){
                $messages[] = Yii::t('marketing', 'There is no campaign email to send.');
            }

        }catch(Exception $e){
            $messages[] = $e->getMessage();
        }
        $messages[] = $totalSent == 0 ? Yii::t('marketing', 'No email sent.') : Yii::t('marketing', 'Successful email sent').': '.$totalSent;
        $wait = ($admin->emailInterval + $admin->emailStartTime) - time();
        return array('wait' => $wait, 'messages' => $messages);
    }

    /**
     * Send mail for one campaign
     *
     * @param Campaign $campaign The campaign to send
     * @param integer $limit The maximum number of emails to send
     *
     * @return Array [0]=> The number of emails sent, [1]=> array of applicable error messages
     */
    protected static function campaignMailing(Campaign $campaign, $limit = null){
        $class = __CLASS__;
        $totalSent = 0;
        $errors = array();
        $items = self::deliverableItems($campaign->list->id,true);
        foreach($items as $item) {
            $mailer = new $class;
            $mailer->campaign = $campaign;
            $mailer->itemId = $item['id'];
            $mailer->sendIndividualMail();
            if($mailer->fullStop) {
                $errors[] = $mailer->status['message'];
                throw new CampaignMailingException(array($totalSent,$errors));
            } elseif($mailer->status['code'] != 200) {
                $errors[] = $mailer->status['message'];
            } else {
                $totalSent++;
            }
            if(time() - self::$batchTime > Yii::app()->settings->batchTimeout) {
                $errors[] = Yii::t('marketing','Batch timeout limit reached.');
                break;
            }
        }
        return array($totalSent, $errors);
    }
}

/**
 * Campaign mailing instant halt exception class that retains data regarding the
 * current operation.
 */
class CampaignMailingException extends CException {
    public $return;

    public function __construct($return,$message=null, $code=0, $previous=null){
        parent::__construct($message, $code, $previous);
        $this->return = $return;
    }
}

?>
