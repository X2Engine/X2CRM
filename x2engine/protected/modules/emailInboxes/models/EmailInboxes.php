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




Yii::import('application.models.X2Model');
Yii::import('application.modules.emailInboxes.components.permissions.*');

/**
 * This is the model class for table "x2_email_inboxes".
 *
 * @package application.modules.emailInboxes.models
 */
class EmailInboxes extends X2Model {
    
    public $supportsWorkflow = false;

    public $password;

    // Cache messages for 10m
    const IMAP_CACHE_MSG_TIMEOUT = 10;

    const OVERVIEW_PAGE_SIZE = 50;

    // The currently selected folder
    private $_currentFolder = null;

    /**
     * @var EmailInboxes $_myEmailInbox
     */
    private $_myEmailInbox; 

    public static function getLogEmailDescription () {
         return Yii::t(
            'app', 'Log email to the action history of all related contacts (sender and all '.
            'recipients in To, Cc, and Bcc lists).');
    }

    public static function getAutoLogEmailsDescription ($direction = "out") {
         $direction = ($direction === 'out' ? Yii::t('app', 'outbound') : Yii::t('app', 'inbound'));
         return Yii::t(
            'app', "Automatically log {direction} emails to the action history of all related ".
            'contacts (sender and all recipients in To, Cc, and Bcc lists).', array(
                '{direction}' => $direction,
            ));
    }

    // Possible MIME types
    public static $mimeTypes = array(
        "TEXT",
        "MULTIPART",
        "MESSAGE",
        "APPLICATION",
        "AUDIO",
        "IMAGE",
        "VIDEO",
        "OTHER",
    );

    // Inverse lookup of MIME type index
    public static $mimeTypeToIndex = array(
        "TEXT" => 0,
        "MULTIPART" => 1,
        "MESSAGE" => 2,
        "APPLICATION" => 3,
        "AUDIO" => 4,
        "IMAGE" => 5,
        "VIDEO" => 6,
        "OTHER" => 7,
    );

    public static $encodingTypes = array(
        '7BIT',
        '8BIT',
        'BINARY',
        'BASE64',
        'QUOTED-PRINTABLE',
        'OTHER',
    );

    /**
     * Mapping of operators to operand type (or null of operator takes no operand)
     */
    public static $searchOperators = array(
        'all' => null, // return all messages matching the rest of the criteria
        'answered' => null, // match messages with the \\answered flag set
        'bcc'       => "string", // match messages with "string" in the bcc: field
        'before'    => "date", // match messages with date: before "date"
        'body'      => "string", // match messages with "string" in the body of the message
        'cc'        => "string", // match messages with "string" in the cc: field
        'deleted' => null, // match deleted messages
        'flagged' => null,      
        'from'      => "string", // from "string" match messages with "string" in the from: field
        'keyword'   => "string", // keyword "string" match messages with "string" as a keyword
        // match messages with the \\flagged (sometimes referred to as important or urgent) flag 
        // set
        'new' => null, // match new messages
        'old' => null, // match old messages
        'on'        => "date", // match messages with date: matching "date"
        'recent' => null, // match messages with the \\recent flag set
        'seen' => null, // match messages that have been read (the \\seen flag is set)
        'since'     => "date", // match messages with date: after "date"
        'subject'   => "string", // match messages with "string" in the subject:
        //'fullText'  => "string", // non-imap operator used to search across all overview text
        'text'      => "string", // match messages with text "string"
        'to'        => "string", // match messages with "string" in the to:
        'unanswered' => null, // match messages that have not been answered
        'undeleted' => null, // match messages that are not deleted
        'unflagged' => null, // match messages that are not flagged
        'unkeyword' => null, 
        'unkeyword' => "string", // match messages that do not have the keyword "string"
        'unseen' => null, // match messages which have not been read yet
    );

    public $module = 'EmailInboxes';

    // Maximum number of attempts to reconnect
    public $maxRetries = 3;
     
    // IMAP Stream resource
    private $_imapStream;

    // Cached string in the form "{host:port/flags}"
    private $_mailboxString;

    // Associated email inbox credentials
    private $_credentials;

    // Whether the IMAP stream is open
    private $_open = false;

    // Number of messages in the current mailbox
    private $_numMessages;

    // Number of recent messages in the current mailbox
    private $_numUnread;

    // UID Validity for the current mailbox
    private $_uidValidity;

    // Cached list of folders for this mailbox
    private $_folders;

    public static function model($className=__CLASS__) { return parent::model($className); }

    /**
     * @var $_cacheSuffixes
     */
    private $_cacheSuffixesToTimeout = array (
        'search' => 30, // unfiltered email messages
        //'uids' => 30, // uids of emails in inbox
        //'filteredUids' => 30, // filtered uids of emails in inbox
        'folders' => 30,
        'quota' => 5,
        'messageCount' => 5, //  number of messages in inbox
    ); 


    public function rules () {
        return array_merge (parent::rules (), array (
            array ('credentialId', 'validateCredentialId'),
            array ('password', 'required'),
            array ('password', 'validatePassword'),
        ));
    }

    public function validateCredentialId ($attr) {
        $value = $this->$attr;

        $retDict = Credentials::getCredentialOptions (
            $this, 'credentialId', 'email', Yii::app()->user->id, array(), true, true);
        $credentials = $retDict['credentials'];
        if (!in_array ($value, array_keys ($credentials))) {
            $this->addError (
                $attr, 
                Yii::t('app', 'You do not have permission to use the selected email credentials'));
        }
    }

    public function validatePassword ($attr) {
        $value = $this->$attr;
        $credentials = $this->getCredentials ();
        if ($credentials) {
            if (!PasswordUtil::slowEquals ($value, $credentials->auth->password)) {
                $this->addError ($attr, Yii::t('app', 'Incorrect password'));
            }
        }
    }

    public function renderMessage ($uid, $ajax = false) {
        $message = $this->fetchMessage ($uid);
        $message->purifyAttributes ();
        $replyAll = $message->getReplyAllAddresses ();
        if (!empty ($replyAll)) {
            $addresses = array_map (
                function ($address) {
                    if (!empty($address[0]))
                        return "{$address[0]} <{$address[1]}>";
                    else
                        return $address[1];
                },
                $replyAll
            );
            $replyAll = implode (', ', $addresses);
        }
        return Yii::app()->controller->renderPartial (
            '_emailMessage',
            array (
                'message' => $message,
                'replyAll' => $replyAll,
            ),
            true,
            $ajax
        );
    }

    public function getCacheTimeout ($suffix) {
        return 60 * $this->_cacheSuffixesToTimeout[$suffix] * (YII_DEBUG ? 10 : 1);
    }

    /**
     * Helper method to create consistent cache keys
     * @param string $suffix (optional) suffix to append to key
     * @return string Cache key for this mailbox and folder
     */
    public function getCacheKey($suffix) {
        if (!in_array ($suffix, array_keys ($this->_cacheSuffixesToTimeout))) {
            throw new CException ('invalid cache suffix: '.$suffix);
        }

        // Cache dataproviders as either shared, or distinct per user
        $user = ($this->shared ? "shared" : Yii::app()->user->name);
        $folderId = $this->id."_".$this->getCurrentFolder();
        $cacheKey = 'x2_mailbox_'.$user.'_'.$folderId.(isset ($suffix) ? '_'.$suffix : '');
        return $cacheKey;
    }

    public function getCache () {
        return Yii::app()->cache2;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() { return 'x2_email_inboxes'; }

    public function getAttributeLabels () {
        return array_merge (parent::getAttributeLabels (), array (
            'settings[logOutboundByDefault]' => Yii::t('emailInboxes','Auto-log outbound emails'),
            'settings[logInboundByDefault]' => Yii::t('emailInboxes','Auto-log inbound emails'),
            'settings[copyToSent]' => Yii::t('emailInboxes','Copy to Sent Folder'),
            'settings[disableQuota]' => Yii::t('emailInboxes','Disable Quota Operations')
        ));
    }

    public function behaviors() {
        $behaviors = array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'emailInboxes',
                'autoCompleteSource' => null,
                'viewRoute' => '/emailInboxes/emailInboxes/index'
            ),
            'JSONFieldsDefaultValuesBehavior' => array(
                'class' => 'application.components.behaviors.JSONFieldsDefaultValuesBehavior',
                'transformAttributes' => array(
                    'settings' => array(
                        'logOutboundByDefault'=>1,
                        'logInboundByDefault'=>0,
                        'copyToSent'=>'',
                        'disableQuota'=>0,
                    ),
                ),
                'maintainCurrentFieldsOrder' => true
            ),
            'permissions' => array('class' => 'EmailInboxesPermissionsBehavior'),
        ));
        unset($behaviors['relationships']);
        return $behaviors;
    }

    /**
     * Clean up IMAP stream when finished
     */
    public function __destruct() {
        if ($this->isOpen())
            $this->close();
    }

    /**
     * @return credentials
     */
    public function getCredentials() {
        if (!isset($this->_credentials)) {
            $this->_credentials = Credentials::model()->findByPk($this->credentialId);
        }
        return $this->_credentials;
    }

    /**
     * @return string Name of email credentials 
     */
    public function getCredentialsName () {
        $credential = $this->getCredentials ();
        if ($credential) {
            return $credential->name;
        }
    }

    /**
     * Override parent method so that credentialId field can be handled specially 
     */
    public function renderInput ($fieldName, $htmlOptions = array ()) {
        if ($fieldName === 'credentialId') {
            return Credentials::selectorField (
                $this, 'credentialId', 'email', Yii::app()->user->id, array(), true, true);
        } else {
            return parent::renderInput ($fieldName, $htmlOptions);
        }
    }

    /**
     * Helper function to generate a dropdown list of search operators
     * and text field for search operator argument
     * @return string dropdown list HTML
     */
    public function renderSearchForm () {
        $searchCriteria = Yii::app ()->controller->getSearchCriteria ();

        $formModel = new EmailInboxesSearchFormModel;

        $formModel->setAttributes ($searchCriteria, false);
        $request = Yii::app()->getRequest ();
        $action = $request->pathInfo;
        $params = $request->restParams;
        unset ($params['lastUid']);
        unset ($params['EmailInboxesSearchFormModel']);
        $form = Yii::app()->controller->beginWidget ('EmailInboxesSearchForm', array (
            'formModel' => $formModel,
            'action' => array_merge (array ($action), $params),
            'htmlOptions' => array (
                'id' => 'email-search-form',
            ),
        ));

        echo CHtml::activeTextField ($formModel, 'text', array(
            'id' => 'email-search-box',
        ));
        echo 
            '<button title="'.CHtml::encode (Yii::t('emailInboxes', 'Search')).'"
              id="email-search-submit" class="x2-button email-search-button">
                '.X2Html::fa('fa-search fa-lg').'
             </button>';
        echo "<span id='open-advanced-search-form-button'
               title='".CHtml::encode (Yii::t('emailInboxes', 'Advanced search'))."'>
                <img src='".
                    Yii::app()->theme->getBaseUrl ().'/images/icons/Collapse_Widget.png'."'>
                </img>
            </span>";
        echo "<div id='advanced-search-form' class='form' style='display: none;'>";
        $form->renderInputs (array_keys ($formModel->attributeLabels ()));
        echo 
            '<button id="email-advanced-search-submit" class="x2-button email-search-button">'.
              Yii::t('emailInboxes', 'Search').'
             </button>';
        echo "</div>";
        Yii::app()->controller->endWidget ();
    }

    /**
     * Append the specified email message text to the inbox's sent folder
     * @param string $message MIME encoded message
     * @return bool success
     */
    public function copyToSent($message) {
        if (!empty ($message) && in_array($this->settings['copyToSent'], $this->folders)) {
            $sentFolder = $this->settings['copyToSent'];
            $success = imap_append (
                $this->stream,
                $this->encodeMailboxString($this->mailbox.$sentFolder),
                $message,
                '\\Seen'
            );
            return $success;
        } else {
            return false;
        }
    }

    /**
     * Retrieve dropdown options for Copy to Sent configuration
     * @return array of folder options
     */
    public function getCopyToSentOptions() {
        try {
            $folders = $this->folders;
            $folders = array_combine ($folders, $folders);
        } catch (EmailConfigException $e) {
            $folders = array();
        }
        return array_merge (array('' => Yii::t('emailInboxes', 'Disabled')), $folders);
    }

    /**
     * Return the mailbox specification string in the form "{server:port/flags}".
     * @return string Mailbox string
     */
    public function getMailbox() {
        if (!isset($this->_mailboxString)) {
            $cred = $this->credentials;
            $mailboxString = "{".$cred->auth->imapServer.":".$cred->auth->imapPort."/imap";

            // Append flags to the host:port
            if (in_array($cred->auth->imapSecurity, array('ssl', 'tls')))
                $mailboxString .= "/".$cred->auth->imapSecurity;
            if ($cred->auth->imapNoValidate)
                $mailboxString .= "/novalidate-cert";
            $mailboxString .= "}";

            $this->_mailboxString = $mailboxString;
        }
        return $this->_mailboxString;
    }

    /**
     * Fetch the associated IMAP stream
     * @return resource|null The IMAP stream, or null if it does not exist
     */
    private $_configError = false;
    public function getStream() {
        if (!$this->_configError) {
            if ($this->isOpen())
                return $this->_imapStream;
            foreach (range(1, $this->maxRetries) as $i) {
                $this->open ($this->currentFolder);
                if ($this->isOpen())
                    return $this->_imapStream;
            }
        } 
        $this->_configError = true;
        // This call clears the error stack, preventing the yii error handler from triggering
        $errors = imap_errors ();
        if ($errors) {
            $errors = array_unique ($errors);
            $errorMsg = '<ul>';
            foreach ($errors as $error)
                $errorMsg .= '<li>'.$error.'</li>';
            $errorMsg .= '</ul>';
        } else {
            $errorMsg = '';
        }
        throw new EmailConfigException ($errorMsg);
    }

    /**
     * Initialize a mailbox and connect via IMAP
     * @return boolean Whether the IMAP connection was successfully initialized
     */
    public function open($folder = "INBOX") {
        $this->setCurrentFolder ($folder);
        $cred = $this->credentials;
        $this->_imapStream = @imap_open(
            $this->encodeMailboxString($this->mailbox.$folder),
            $cred->auth->email,
            $cred->auth->password);
        if (is_resource($this->_imapStream)) {
            $this->_open = true;
            return true;
        }
        return false;
    }

    /**
     * Close an IMAP connection and expunge moved/deleted messages
     */
    public function close() {
        if ($this->isOpen ()) {
            imap_close($this->_imapStream, CL_EXPUNGE);
            $this->_open = false;
        }
    }

    /**
     * Check if the IMAP stream is currently connected
     * @return boolean Whether the IMAP stream is open
     */
    public function isOpen() {
        return ($this->_open === true && is_resource($this->_imapStream) &&
            imap_ping($this->_imapStream));
    }

    /**
     * Check if this mailbox is associated with a GMail account
     */
    public function isGmail() { return ($this->credentials->modelClass === "GMailAccount"); }

    /**
     * Retrieve the mailbox status. This returns an object with the
     * properties: messages, recent, unseen, uidnext, and uidvalidity,
     * or false if the mailbox does not exist
     * @return object|false
     */
    public function status($folder = null) {
        if (is_null($folder))
            $folder = $this->currentFolder;
        $mailbox = $this->mailbox.$folder;
        return imap_status($this->stream, $this->encodeMailboxString($mailbox), SA_ALL);
    }

    /**
     * Get the number of messages in the current mailbox
     * @return int Total number of messages
     */
    public function numMessages() {
        if (!isset($this->_numMessages))
            $this->_numMessages = imap_num_msg($this->stream);
        return $this->_numMessages;
    }

    /**
     * Get the number of unread messages in the current mailbox
     * @return int Number of unread messages
     */
    public function numUnread() {
        if (!isset($this->_numUnread)) {
            $status = $this->status();
            if ($status && isset($status->unseen))
                $this->_numUnread = $status->unseen;
        }
        return $this->_numUnread;
    }

    /**
     * Get the UIDVALIDITY for the selected folder: when this value changes,
     * previous UIDs may no longer hold
     * @return int Number of unread messages
     */
    public function getUidValidity() {
        if (!isset($this->_uidValidity)) {
            $status = $this->status();
            if ($status && isset($status->uidvalidity))
                $this->_uidValidity = $status->uidvalidity;
        }
        return $this->_uidValidity;
    }

    /**
     * Extract header overview information into an array
     * @param stdClass IMAP Header object
     * @return EmailMessage
     */
    public function parseHeader(stdClass $header) {
        $details = array();
        $headerAttributes = array('subject', 'from', 'to', 'date', 'uid', 'size', 'msgno');
        $headerFlags = array('seen', 'flagged', 'answered');

        foreach ($headerAttributes as $attr) {
            if (property_exists($header, $attr)) {
                $decodedHeader = $this->decodeHeader ($header->$attr);
                if ($attr === 'date') $decodedHeader = strtotime ($decodedHeader);
                $details[$attr] = $decodedHeader;
            }
        }
        foreach ($headerFlags as $flag) {
            if (property_exists($header, $flag))
                $details[$flag] = (isset($header->$flag) && $header->$flag)? true : false;
        }
        $email = new EmailMessage ($this, $details);
        return $email;
    }

    /**
     * Extract additional header info from message. Includes full to, cc, and reply_to fields
     * @param int $uid Unique ID of the message
     * @return array Additional header information
     */
    public function parseFullHeader($uid) {
        $headerInfo = array();
        $rawHeader = @imap_fetchheader($this->stream, $uid, FT_UID);
        if (!$rawHeader)
            throw new CHttpException(400, Yii::t("emailInboxes", "Invalid IMAP message id"));
        $header = imap_rfc822_parse_headers($rawHeader);
        foreach (array('to', 'cc', 'reply_to') as $type) {
            $collection = array();
            if (!property_exists($header, $type))
                continue;

            // If it is a single entry, just save it
            if (!is_array($header->$type)) {
                $headerInfo[$type] = $header->$type;
                continue;
            }

            // Otherwise, concatenate the entries
            foreach ($header->$type as $entry) {
                if (isset($entry->mailbox, $entry->host)) {
                    $email = $entry->mailbox."@".$entry->host;
                    if (isset($entry->personal))
                        $emailString = $this->decodeHeader($entry->personal)." <".$email.">";
                    else
                        $emailString = $email;
                    $collection[] = $emailString;
                }
            }
            $headerInfo[$type] = implode(', ', $collection);
        }
        return $headerInfo;
    }

    /**
     * Helper function to decode MIME header parts to the intended character set
     * @param string IMAP Header object
     * @return string Decoded header part
     */
    public function decodeHeader($header) {
        $result = "";
        $headerObj = imap_mime_header_decode ($header);
        foreach ($headerObj as $part) {
            $encoding = ($part->charset === 'default' ? "ISO-8859-1" : $part->charset);
            $text = @mb_convert_encoding($part->text, 'UTF-8', $encoding);
            $result .= $text;
        }
        return $result;
    }

    /**
     * @return int the number of messages in the mailbox
     */
    private $_messageCount;
    public function getMessageCount () {
        if (isset ($this->_messageCount)) return $this->_messageCount;

        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('messageCount');
        $messageCount = $cache->get ($cacheKey);
        if ($messageCount !== false) return $messageCount;
        $messageCount = imap_num_msg ($this->stream);
        $cache->set ($cacheKey, $messageCount, $this->getCacheTimeout ('messageCount')); 
        $this->_messageCount = $messageCount;
        return $messageCount;
    }

    /**
     * Updates the message count cache entry
     * @param int $count 
     */
    public function setMessageCount ($count) {
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('messageCount');
        $cache->set ($cacheKey, $count, $this->getCacheTimeout ('messageCount')); 
    }

    /**
     * Wrapper around imap_search which maintains separate caches for filtered and unfiltered 
     * results 
     */
//    public function imapSearch ($criteria=null) {
//        $cache = $this->getCache ();
//        $keySuffix = $criteria === null ? 'uids' : 'filteredUids';
//        $cacheKey = $this->getCacheKey ($keySuffix);
//        $uids = $cache->get ($cacheKey);
//        if ($uids instanceof UidsCacheEntry && $uids->searchString === $criteria) {
//            return $uids->uids;
//        } 
//        $uids = imap_search($this->stream, $criteria === null ? 'ALL' : $criteria, SE_UID);
//        $cache->set (
//            $cacheKey, $this->getUidsCacheEntry ($uids, $criteria),
//            $this->getCacheTimeout ($keySuffix));
//        return $uids;
//    }

    /**
     * Search the current IMAP inbox for messages matching the given query.
     * @param string|null $searchString Search criteria
     * @param bool $searchCacheOnly if true, inbox will only be searched if results are cached 
     * @return false|CArrayDataProvider false if $searchCacheOnly is true and messages aren't 
     *  cached
     */
    public function searchInbox (
        $searchString=null, $searchCacheOnly=false, $lastUid=null, $pageSize=null) {

        if ($pageSize === null) $pageSize = self::OVERVIEW_PAGE_SIZE;
        $dataProvider = false;
        $cache = $this->getCache ();
        $cacheSuffix = 'search';
        $lastCachedUid = null;
        $emails = null;
        $cacheKey = $this->getCacheKey ($cacheSuffix);
        $cacheEntry = $cache->get ($cacheKey);
       //AuxLib::debugLogR ('$searchString = ');
        //AuxLib::debugLogR ($searchString);

        if ($cacheEntry instanceof EmailsCacheEntry) {
       //AuxLib::debugLogR ('$cacheEntry->searchString = ');
        //AuxLib::debugLogR ($cacheEntry->searchString);
        }

        // check if cache is valid and extract emails array if it is
        if ($cacheEntry instanceof EmailsCacheEntry && 
            $cacheEntry->searchString === $searchString) {

            //AuxLib::debugLogR ($cacheEntry->expirationTime);
            //AuxLib::debugLogR (time ());
            //AuxLib::debugLogR (gettype ($cacheEntry->expirationTime));
            //AuxLib::debugLogR (gettype (time ()));

            if ($cacheEntry->expirationTime > time ()) {
                //AuxLib::debugLogR ('cache hit');
                $emails = $cacheEntry->emails;
                $uids = $cacheEntry->uids;
            } else {
                //AuxLib::debugLogR ('expired');
                $cache->delete ($cacheKey);
                $cacheEntry = new EmailsCacheEntry;
            }
        } else {
            $cacheEntry = new EmailsCacheEntry;
        }

        if (is_array ($emails)) {
            $expandCache = false;
            if ($lastUid !== null) {
                // check if requested overviews are in the cache and expand the cache if they 
                // aren't
                $uidCount = count ($uids);
                $indexOfLastUid = ArrayUtil::numericIndexOf ($lastUid, $uids); 
                if ($indexOfLastUid === false || 
                    $indexOfLastUid + $pageSize > count ($emails)) {

                    //AuxLib::debugLogR ('expanding cache');
                    $lastCachedUid = $uids[$uidCount - 1];
                    $expandCache = true;
                }
            }

            if (!$expandCache) {
                $dataProvider = $this->getOverviewDataProvider ($emails, $pageSize);
            }
        } else {
            $emails = array ();
        }

        if ($dataProvider) 
            return $dataProvider;
        if ($searchCacheOnly) 
            return false;

        if (!$this->isOpen()) {
            $this->open ($this->currentFolder);
        }

        if (!isset ($uids)) {
            
            // Fetch a list of headers
            $uids = imap_search (
                $this->stream, $searchString === null ? 'ALL' : $searchString, SE_UID);
            if (!$uids) {
                $uids = array ();
            } else {
                $uids = array_reverse ($uids);
            }
        }

        $this->setMessageCount (count ($uids));
       //AuxLib::debugLogR ('$uids = ');
        //AuxLib::debugLogR ($uids);

        $result = $this->overview ($uids, $lastCachedUid, $lastUid);
       //AuxLib::debugLogR ('$result = ');
        //AuxLib::debugLogR ($result);

        $_SESSION[$this->lastUidSessionKey] = $this->nextUid;

        // Iterate over headers to get an array of emails
        // TODO: Make this more efficient. To preserve message order, all cached messages are 
        // iterated over. As a result, this loop gets slower as the cache grows.
        if (is_array($result)) {
            $newEmails = array ();
            foreach ($result as $header) {
                $header = $this->parseHeader ($header);
                $newEmails[$header->uid] = $header;
            }
            foreach ($emails as $uid => $message) {
                $newEmails[$uid] = $message;
            }
            $emails = $newEmails;
        }

        $cacheEntry->uids = $uids;
        $cacheEntry->emails = $emails;
        $cacheEntry->searchString = $searchString;
        $cacheEntry->expirationTime = isset ($cacheEntry->expirationTime) ? 
            $cacheEntry->expirationTime : 
            time () + $this->getCacheTimeout ($cacheSuffix);

       //AuxLib::debugLogR ('$cacheEntry->expirationTime = ');
        //AuxLib::debugLogR ($cacheEntry->expirationTime);

        
        $cache->set ($cacheKey, $cacheEntry, $cacheEntry->expirationTime - time ());

        return $this->getOverviewDataProvider ($emails, $pageSize);
    }

//    private function getEmailsCacheEntry (
//        array $emails, array $uids, $searchString=null, $expirationTime=null) {
//        $cacheEntry = new EmailsCacheEntry;
//        $cacheEntry->emails = $emails;
//        $cacheEntry->uids = $uids;
//        $cacheEntry->searchString = $searchString;
//        $cacheEntry->expirationTime = 
//            $expirationTime === null ? 
//                time () + $this->getCacheTimeout ('search') : $expirationTime;
//        return $cacheEntry;
//    }
//
    /**
     * For each message specified, create an email action if none exists and associate it with
     * all its related contacts and services
     */
    public function logMessages ($uids, $cacheOnly = true, $inbound = false) {
        $dataProvider = $this->searchInbox ();
        $rawData = $dataProvider->rawData;
        $errors = array ();
        $warnings = array ();
        foreach ($uids as $uid) {
            if (!$cacheOnly && !array_key_exists ($uid, $rawData)) {
                // Message not yet cached
                $message = $this->cacheMessage ($uid);
                if ($message instanceof EmailMessage && $uid === (int) $message->uid)
                    $rawData[$uid] = $message;
            }
            if (isset ($rawData[$uid])) {
                $message = $rawData[$uid];
                $message = $this->fetchMessage ($uid);
                list($logErrors, $logWarnings) = $this->logToAssociatedRecords ($message, $inbound);
                $errors = array_merge ($errors, $logErrors);
                $warnings = array_merge ($warnings, $logWarnings);
            } else {
                $errors[] = Yii::t('emailInboxes', 'Message not found') ;
            }
        }
        return array ($errors, $warnings);
    }

    /**
     * Log the message to each of the associated contacts and services, create an event if
     * one doesn't exist, then fire the InboundEmailTrigger
     * @param EmailMessage $message
     * @param bool $inbound
     * @return array of errors and warnings
     */
    public function logToAssociatedRecords($message, $inbound) {
        $errors = array();
        $warnings = array();

        $contacts = $message->getAssociatedContacts ($inbound);
        if (!count ($contacts)) {
            $warnings[] = Yii::t(
                'emailInboxes',
                'Message "{subject}" has no associated contacts', array (
                    '{subject}' => $message->subject,
                ));
            return array($errors, $warnings);
        }

        $services = $message->getAssociatedServices ();
        $associatedModels = array_merge ($contacts, $services);
        $action = $message->getAction ($inbound);

        $existingEvents = Events::model()->countByAttributes (array(
            'associationId' => $action->id,
            'type' => 'email_from',
            'subtype' => 'email',
        ));
        $actionHasEvent = ($existingEvents >= 1);

        foreach ($associatedModels as $model) {
            // Currently only Contacts and Services are supported
            $recordType = ($model instanceof Contacts ? 'Contacts' : 'Services');
            $retVal = $action->multiAssociateWith ($model);
            if (!$retVal) {
                $errors[] = Yii::t(
                    'emailInboxes',
                    'Failed to associate message "{subject}" with {type} {model}', array (
                        '{subject}' => $message->subject,
                        '{type}' => Modules::displayName (false, $recordType),
                        '{model}' => $model->name,
                    )) ;
            } else if ($retVal === -1) {
                $warnings[] = Yii::t(
                    'emailInboxes',
                    'Message "{subject}" already associated with {type} {model}', array (
                        '{subject}' => $message->subject,
                        '{type}' => Modules::displayName (false, $recordType),
                        '{model}' => $model->name,
                    )) ;
            } else {
                if ($inbound && !$actionHasEvent) {
                    // Post a new event to the activity feed for this inbound message
                    $event = new Events;
                    $event->visibility = 1;
                    $event->associationType = 'Actions';
                    $event->associationId = $action->id;
                    $event->timestamp = time();
                    $event->user = $this->assignedTo;
                    $event->type = 'email_from';
                    $event->subtype = 'email';
                    $event->save();
                    $actionHasEvent = true;
                }

                // Activate the Inbound or Outbound email trigger as appropriate
                $trigger = $inbound ? 'InboundEmailTrigger' : 'OutboundEmailTrigger';
                X2Flow::trigger ($trigger, array(
                    'model' => $model,
                    'subject' => $message->subject,
                    'body' => $message->body,
                    'to' => $message->to,
                    'from' => $message->from,
                ));
            }
        }
        return array($errors, $warnings);
    }

    /**
     * Retrieve and log unseen messages in this inbox
     */
    public function logRecentMessages() {
        $inboxAddress = $this->credentials->auth->email;
        $skipFolders = array(
            'Bulk Mail',
            'Deleted',
            'Draft',
            'Drafts',
            'Junk',
            'Trash',
            '[Gmail]/All Mail',
            '[Gmail]/Drafts',
            '[Gmail]/Important',
            '[Gmail]/Spam',
            '[Gmail]/Trash',
        );
        $sentFolders = array(
            'Sent',
            '[Gmail]/Sent Mail',
        );
        try {
            $folders = $this->getFolders ();
        } catch (EmailConfigException $e) {
            $folders = array();
        }
        $folders = array_diff ($folders, $skipFolders);
        foreach ($folders as $folder) {
            // Skip folders if logging is not requested
            if (in_array($folder, $sentFolders)) {
                if (!$this->settings['logOutboundByDefault']) continue;
            } else {
                if (!$this->settings['logInboundByDefault']) continue;
            }
            Yii::log("Beginning inbound logging for folder $folder in inbox {$this->id}", 'trace', 'application.automation.cron');
            $this->selectFolder ($folder);
            // Retrieve the UID of the most recently logged email for this folder and
            // UIDVALIDITY. Note: The UIDVALIDITY field as returned by the IMAP server
            // indicates that previous UIDs can no longer be considered valid
            $maxUid = Yii::app()->db->createCommand()
                ->select ('emailImapUid')
                ->from ('x2_action_meta_data')
                ->where ('emailFolderName = :folder AND emailUidValidity = :uidValidity', array(
                    ':folder' => $folder,
                    ':uidValidity' => $this->getUidValidity(),
                ))
                ->order ('emailImapUid DESC')
                ->limit (1)
                ->queryScalar ();
            $nextUid = $this->nextUid;
            if (!$nextUid || $nextUid === 1)
                continue;

            // Select message UID criteria
            if ($maxUid && $maxUid + 1 != $nextUid) {
                // Retrieve UIDs for messages after the most recently logged and the latest received
                $criteria = ($maxUid + 1).':'.($nextUid - 1);
            } else {
                // Inbound logging for this folder has not started, try to log the most recent message
                $criteria = $nextUid - 1;
            }

            // Retrieve message headers, filter on email to this inbox, and log each message
            if (isset ($criteria)) {
                $result = $this->overview ($criteria);
                $inbound = !in_array ($folder, $sentFolders);
                if (is_array($result)) {
                    if ($inbound) {
                        $result = array_filter ($result,
                            function ($e) use ($inboxAddress) {
                                return ($e->to === $inboxAddress ||
                                    preg_match ('/<'.preg_quote($inboxAddress) .'>$/', $e->to));
                            }
                        );
                    }
                    $uids = array_map (function ($e) { return $e->uid; }, $result);
                    if (!empty($uids)) {
                        list ($errors, $warnings) = $this->logMessages ($uids, false, $inbound);
                        if (!empty($errors))
                            foreach ($errors as $error)
                                Yii::log($error, 'error', 'application.automation.cron');
                        if (!empty($warnings))
                            foreach ($warnings as $warning)
                                Yii::log($warning, 'warning', 'application.automation.cron');

                        foreach ($result as $message) // Preserve seen flag on messages
                            if ($message->seen === 0)
                                $this->markUnread ($message->uid);
                    }
                }
            }
        }
    }

    /**
     * Retrieve latest messages since last time messages were fetched
     */
    public function fetchLatest() {
        $newEmails = array();
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('search');
        $emails = $cache->get ($cacheKey);
        if ($emails instanceof EmailsCacheEntry) {
            $emails = $emails->emails;
        }
        $dataProvider = $this->getOverviewDataProvider ($emails);
            
        if (!$dataProvider || 
            !isset($_SESSION[$this->lastUidSessionKey]) ||
            $_SESSION[$this->lastUidSessionKey] === null) {

            return $this->searchInbox ();
        }

        // Return if the next uid hasn't changed, otherwise retrieve the newest headers
        if ($_SESSION[$this->lastUidSessionKey] === $this->nextUid)
            return $dataProvider;
        $criteria = $_SESSION[$this->lastUidSessionKey].':'.$this->nextUid;

        $result = $this->overview ($criteria);

        $_SESSION[$this->lastUidSessionKey] = $this->nextUid;

        // Iterate over headers to append new emails to the data provider
        if (is_array($result))
            foreach ($result as $header) {
                $header = $this->parseHeader ($header);
                $dataProvider->rawData[$header->uid] = $header;
            }
        $cache->set ($cacheKey, $dataProvider->rawData, $this->getCacheTimeout ('search'));
        //return $dataProvider;
    }

    /**
     * Return the current folders quota settings in bytes
     * @return array used and total space for quota
     */
    public function getQuota() {
        if (!($quota = $this->getCache ()->get ($this->getCacheKey ('quota')))) {
            $incompatibleImapServers = array(
                'outlook.office365.com',
                'imap-mail.outlook.com',
                'imap.mail.yahoo.com',
                'imap.secureserver.net',
                'imap.mail.ru',
            );
            if (!($this->settings['disableQuota'] ||
               in_array($this->credentials->auth->imapServer, $incompatibleImapServers))) {

                $folder = $this->encodeMailboxString ($this->currentFolder);
                $quota = @imap_get_quotaroot($this->stream, $folder);
            } else {
                $quota = null; 
            }
            if (is_array($quota) && array_key_exists('STORAGE', $quota)) {
                // Quota settings are returned in KB
                $used = $quota['STORAGE']['usage'] * 1024;
                $total = $quota['STORAGE']['limit'] * 1024;
                $quota = array($used, $total);
            } else {
                // If the STORAGE key does not exist, we Received an invalid response
                // from the server and were instead given an array of error information
                $quota = null;
            }
            $this->getCache ()->set (
                $this->getCacheKey ('quota'), $quota, $this->getCacheTimeout ('quota'));
        }
        return $quota;
    }

    /**
     * Return a human readable summary of the current folder's quota usage
     * in the format "used / total (percent%)"
     */
    public function getQuotaString() {
        $quota = $this->quota;
        if (is_array($quota) && count($quota) === 2) {
            $used = $quota[0];
            $total = $quota[1];
            $percent = sprintf('%.1f%%', 100 * ($used / $total));
            $used = FileUtil::formatSize($used);
            $total = FileUtil::formatSize($total);
            return "$used / $total ($percent)";
        }
    }

    /**
     * Create an AJAX link to select a folder
     * @param string $folder Folder name
     */
    public function renderFolderLink($folder) {
        $options = array(
            'class' => 'folder-link'.($folder === $this->getCurrentFolder () ? 
                ' current-folder' : ''),
            'data-folder' => CHtml::encode ($folder) 
        );
        if ($folder === "INBOX")
            $folder = "Inbox";
        return CHtml::link($folder, '#', $options);
    }

    /**
     * Encodes a mailbox/folder string to UTF7-IMAP before initiating IMAP commands
     */
    public function encodeMailboxString($mailbox) {
        return @mb_convert_encoding($mailbox, 'UTF7-IMAP', 'UTF8');
    }

    /**
     * List available folders in a given mailbox
     * @return array Folder names
     */
    public function getFolders() {
        if (isset($this->_folders) && is_array($this->_folders))
            return $this->_folders;

        if (!($this->_folders = $this->getCache ()->get ($this->getCacheKey ('folders')))) {
            $this->_folders = array();
            $folderList = imap_list($this->stream, $this->mailbox, "*");
            if ($folderList) {
                // process $folderList to make it more user friendly
                foreach ($folderList as $folder) {
                    $folderName = mb_convert_encoding($folder, 'UTF-8', 'UTF7-IMAP');
                    $folderName = str_replace($this->mailbox, "", $folderName);
                    $this->_folders[] = $folderName;
                }
            }
            $this->getCache ()->set (
                $this->getCacheKey ('folders'), $this->_folders,
                $this->getCacheTimeout ('folders'));
        }

        return $this->_folders;
    }

    /**
     * Magic getter for the current folder saved in session
     */
    public function getCurrentFolder() {
        if (is_null($this->_currentFolder))
            $this->_currentFolder = "INBOX";
        return $this->_currentFolder;
    }

    /**
     * Magic setter to handle the current folder saved in session
     */
    public function setCurrentFolder($folder) {
        $this->_currentFolder = $folder;
    }

    /**
     * Reopen the IMAP connection with a different mailbox
     * @param string $mailbox
     */
    public function selectFolder($folder) {
        $this->setCurrentFolder ($folder);
        $mailbox = $this->encodeMailboxString ($this->mailbox.$folder);
        if ($this->isOpen())
            imap_reopen($this->_imapStream, $mailbox);
        else
            $this->open ($folder);
    }

    /**
     * Generate a sequence string from a list of ids
     * @param mixed $ids The list of IDs to form a sequence
     * @return string Comma separated list of IDs
     */
    public function sequence($ids) {
        if (!is_array($ids))
            $ids = array($ids);
        return implode(',', $ids);
    }

    /**
     * Mark the specified messages as read
     * @param int|array $uids The message UIDs to mark as read
     * @return bool true for success, false for failure
     */
    public function markRead($uids) { return $this->setFlag ($uids, '\\Seen'); }

    /**
     * Mark the specified messages as unread
     * @param int|array $uids The message UIDs to mark as unread
     * @return bool true for success, false for failure
     */
    public function markUnread($uids) {
        $this->setUnreadOnCachedMessages ($uids);
        return $this->setFlag ($uids, '\\Seen', false);
    }

    /**
     * Mark the specified messages as unread
     * @param int|array $uids The message UIDs to mark as unread
     * @return bool true for success, false for failure
     */
    public function markImportant($uids) { return $this->setFlag ($uids, '\\Flagged'); }

    /**
     * Mark the specified messages as unread
     * @param int|array $uids The message UIDs to mark as unread
     * @return bool true for success, false for failure
     */
    public function markNotImportant($uids) { return $this->setFlag ($uids, '\\Flagged', false); }

    /**
     * Return the overview of specified messages
     * @param int|array $uids The message UIDs to retrieve overviews for
     * @param null|int $firstUid if set, only overviews of uids after firstUid will be fetched 
     * @param null|int $lastUId if set, overviews overview page size past lastUid will not
     *  be fetched
     * @return array message overviews
     */
    public function overview ($uids, $firstUid=null, $lastUid=null) {
        if (is_array ($uids)) {
            if ($lastUid !== null) {
                $indexOfLastUid = ArrayUtil::numericIndexOf ($lastUid, $uids);
                if ($indexOfLastUid) {
                    $endIndex = min (
                        count ($uids), $indexOfLastUid + self::OVERVIEW_PAGE_SIZE + 1);
                    $uids = array_slice ($uids, 0, $endIndex);
                }
            } else {
                $uids = array_slice ($uids, 0, self::OVERVIEW_PAGE_SIZE);
            }
            if ($firstUid !== null) {
                $indexOfFirstUid = ArrayUtil::numericIndexOf ($lastUid, $uids);
                if ($indexOfLastUid) {
                    $uids = array_slice ($uids, $indexOfFirstUid + 1);
                }
            }
        }

        $overview = imap_fetch_overview (
            $this->stream,
            $this->sequence ($uids),
            FT_UID);
        return $overview;
    }

    /**
     * Move the selected messages to the given folder
     * @param int|array $uids The message UIDs to move
     * @param string $folder Name of the target folder
     * @return bool true if imap move succeeded, false otherwise
     */
    public function moveMessages($uids, $folder) {
        $success = imap_mail_move (
            $this->stream,
            $this->sequence($uids),
            $this->encodeMailboxString ($folder),
            CP_UID
        );
        imap_expunge($this->stream);
        $this->updateCachedMailbox($uids, true);

        // Fetch lastest in target folder
        $lastFolder = $this->currentFolder;
        $this->selectFolder ($folder);
        $this->fetchLatest();
        $this->selectFolder ($lastFolder);
        return $success;
    }

    /**
     * Retrieve a specific message
     * @param int Unique ID of the message
     * @return array containing message information
     */
    public function fetchMessage($uid) {
        $message = $this->cacheMessage($uid);
        if (!$message->seen) {
            $message->seen = true;
            $this->updateCachedMailbox ($uid);
        }
        return $message;
    }

    /**
     * Retrieve a message from the cache if it exists, otherwise load
     * the message and cache it
     * @param int $uid Unique ID of the message
     */
    public function cacheMessage($uid) {
        $cacheKey = $this->getMsgCacheKey ($uid);
        $cache = $this->getCache ();
        $message = $cache->get($cacheKey);
        if ($message && is_array($message->attachments))
            return $message;

        // Fetch full header of the message
        $overview = $this->overview($uid);
        if (empty($overview))
            throw new CHttpException(404, Yii::t('emailInboxes',
                'Unable to retrieve the specified message'));
        $message = $this->parseHeader($overview[0]);
        $additionalHeaders = $this->parseFullHeader($uid);
        foreach ($additionalHeaders as $type => $value)
            $message->$type = $value;

        // Fetch message body and attachments
        $structure = imap_fetchstructure($this->stream, $uid, FT_UID);
        $this->parseMessageBody ($message, $structure);

        $this->logToAssociatedRecords ($message, true);
        $cache->set($cacheKey, $message, 60 * self::IMAP_CACHE_MSG_TIMEOUT);
        return $message;
    }

    /**
     * Clear a message from the cache
     * @param int $uid Unique ID of the message to remove
     */
    public function invalidateCachedMessage($uid) {
        $cache = $this->getCache ();
        $cacheKey = $this->getMsgCacheKey ($uid);
        $cache->delete ($cacheKey);
    }

    /**
     * Invalidate the current mailbox from the cache
     */
    public function invalidateCachedMailbox($folder = null) {
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('search');
        // Replace folder in cache key if specified
        if (!is_null($folder))
            $cacheKey = preg_replace('/_[^_]*$/', '_'.$folder, $cacheKey);
        $cache->delete ($cacheKey);
    }

    /**
     * Update the cached data provider for the currently selected mailbox
     * @param int|array $ids Unique IDs of the messages to update
     * @param boolean $delete Whether to delete the message from the cached data provider
     * @return boolean whether the messages were successfully updated
     */
    public function updateCachedMailbox($uids, $delete = false) {
        if (!is_array($uids))
            $uids = array($uids);
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('search');
        $emails = $cache->get ($cacheKey);
        if ($emails instanceof EmailsCacheEntry) {
            $emails = $emails->emails;
        }
        $dataProvider = $this->getOverviewDataProvider ($emails);
        if (!$dataProvider)
            return false;

        $rawData = $dataProvider->rawData;

        foreach ($uids as $uid) {
            if (isset ($rawData[$uid])) {
                if ($delete) {
                    $this->invalidateCachedMessage ($uid);
                    unset($dataProvider->rawData[$uid]);
                } else {
                    $this->invalidateCachedMessage ($uid);
                    $message = $this->cacheMessage ($uid);
                    $dataProvider->rawData[$uid] = $message;
                }
            }
        }
        $cache->set ($cacheKey, $dataProvider->rawData, $this->getCacheTimeout ('search'));
        return true;
    }

    /**
     * Update the cached data provider and reset the Seen flag on the specified messages
     * @param int|array $ids Unique IDs of the messages to update
     * @return boolean whether the messages were successfully updated
     */
    private function setUnreadOnCachedMessages($uids) {
        if (!is_array($uids))
            $uids = array($uids);
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('search');
        $emails = $cache->get ($cacheKey);
        if ($emails instanceof EmailsCacheEntry) {
            $emails = $emails->emails;
        }
        $dataProvider = $this->getOverviewDataProvider ($emails);
        if (!$dataProvider)
            return false;

        $rawData = $dataProvider->rawData;

        foreach ($uids as $uid) {
            if (isset ($rawData[$uid])) {
                $message = $rawData[$uid];
                $message->seen = false;
                $dataProvider->rawData[$uid] = $message;
            }
        }
        $cache->set ($cacheKey, $dataProvider->rawData, $this->getCacheTimeout ('search'));
        return true;
    }

    /**
     * Helper method to create consistent cache keys for individual email messages
     * @return string Cache key for this mailbox and folder
     */
    public function getMsgCacheKey($uid) {
        $user = ($this->shared ? "shared" : Yii::app()->user->name);
        $folderId = $this->id."_".$this->currentFolder;
        $cacheKey = "x2_mailbox_".$user."_msg_".$folderId."_".$uid;
        return $cacheKey;
    }

    public function deleteCache () {
        $cache = $this->getCache ();
        foreach ($this->_cacheSuffixesToTimeout as $suffix => $timeout) {
            $cache->delete ($this->getCacheKey ($suffix));
        }
    }

    /**
     * Helper method to generate a session key unique to this mailbox and folder
     * for storing the last fetched message UID
     * @return string Session key for storing lastUid
     */
    public function getLastUidSessionKey() {
        return 'lastuid_'.$this->id.'_'.$this->currentFolder;
    }

    /**
     * Handle parsing a message structure to extract the message body and attachments
     */
    public function parseMessageBody(&$message, $structure) {
        list($body, $attachments) = $this->parseBodyPart ($message->uid, $structure);
        $message->attachments = $attachments;
        if (!empty($body['html'])) {
            $message->body = $body['html'];
            if (count($message->attachments) > 0)
                $message->parseInlineAttachments();
        } else {
            $message->body = nl2br($body['plain']);
        }
    }

    public function getBodyPart ($uid, $structure, $part) {
        // Simple, non-multipart messages can be fetched with imap_body
        if (is_null($part))
            $message = imap_body($this->stream, $uid, FT_UID);
        else
            $message = imap_fetchbody($this->stream, $uid, $part, FT_UID);
        return $message;
    }

    /**
     * Fetch and decode a part of the message body
     * @param int Unique ID of the email message
     * @param int Message part number
     * @return string Decoded body part
     */
    public function decodeBodyPart($uid, $structure, $part) {
        $message = $this->getBodyPart ($uid, $structure, $part);
        $encoding = self::$encodingTypes[ $structure->encoding ];
        switch ($encoding) {
            case '7BIT':
                // https://stackoverflow.com/questions/12682208/parsing-email-body-with-7bit-content-transfer-encoding-php
                //$lines = explode('\r\n', $message);
                //$words = explode(' ', $lines[0]);
                //if ($lines[0] === $words[0])
                //    $message = base64_decode($message);
            case '8BIT':
                $message = quoted_printable_decode(imap_8bit($message));   break;
            case 'BINARY':
                $message = imap_binary($message); break;
            case 'BASE64':
                $message = imap_base64($message); break;
            case 'QUOTED-PRINTABLE':
                $message = imap_qprint($message); break;
        }
        return $message;
    }

    /**
     * Return the MIME type of an IMAP message structure
     * @param object IMAP message structure
     * @return string Message structure MIME type
     */
    public function getStructureMimetype($structure, $attachment = false) {
        if (isset($structure->subtype))
            $structureEncoding = self::$mimeTypes[$structure->type] . "/" . $structure->subtype;
        else if ($attachment)
            $structureEncoding = 'APPLICATION/OCTET-STREAM';
        else
            $structureEncoding = 'TEXT/PLAIN';
        return strtoupper($structureEncoding);
    }

    /**
     * Delete a specific email by UID
     * @param int|array $uids The message UIDs to delete
     */
    public function deleteMessages ($uids) {
        imap_delete($this->stream, $this->sequence($uids), FT_UID);
        imap_expunge($this->stream);
        $this->updateCachedMailbox($uids, true);
    }

    /**
     * @return EmailInboxes The current user's personal email inbox 
     */
    public function getMyEmailInbox ($refresh=false) {
        if ($refresh || !isset ($this->_myEmailInbox)) {
            $this->_myEmailInbox = $this->findByAttributes (array (
                'shared' => 0,
                'assignedTo' => Yii::app()->user->getName (),
            ));
        }
        return $this->_myEmailInbox;
    }

    /**
     * @return bool true if current user's personal inbox is setup, false otherwise
     */
    public function myEmailInboxIsSetUp () {
        return intval (Yii::app()->db->createCommand ("
            select count(*) from 
            x2_email_inboxes
            where shared=0 and assignedTo=:username
        ")->queryScalar (array (
            ':username' => Yii::app()->user->getName (),
        ))) === 1;
    }

    /**
     * Helper function to set/clear a message flag
     * @return bool true for success, false for failure
     */
    private function setFlag($uids, $flag, $value = true) {
        if (!is_array($uids))
            $uids = array($uids);
        $operation = ($value ? 'imap_setflag_full' : 'imap_clearflag_full');
        if ($operation ($this->stream, $this->sequence($uids), $flag, ST_UID)) {
            imap_expunge ($this->stream);
            if (!($flag === '\Seen' && !$value)) {
                // Skip updating cached mailbox, as this would re-mark the message as Seen
                $this->updateCachedMailbox ($uids);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Instantiates data provider of email headers to display in mailbox grid view
     * @param null|array $emails
     * @return CArrayDataProvider
     */
    private function getOverviewDataProvider ($emails, $pageSize=null) {
        if ($pageSize === null) $pageSize = self::OVERVIEW_PAGE_SIZE;
        if ($emails === null) return null;
        $dataProvider = new CArrayDataProvider($emails, array(
            'keyField' => 'uid',
            'sort' => array(
                'defaultOrder' => 'msgno DESC',
                'attributes' => array(
                    'msgno',
                    'subject',
                    'from',
                ),
            ),
            'pagination' => array(
                'class' => 'EmailInboxesPagination', 
                'pageSize' => $pageSize,
                'messageCount' => $this->getMessageCount (),
            ),
        ));
        $dataProvider->getPagination ()->dataProvider = $dataProvider;
        return $dataProvider;
    }

    /**
     * Recursively handle message body parts
     * @return array (message body, attachments)
     */
    private function parseBodyPart($uid, $structure, $part = null) {
        $attachments = array();
        $body = array('plain' => '', 'html' => '');
        if (isset($structure->parts) && count($structure->parts) > 0 &&
                $structure->type === self::$mimeTypeToIndex['MULTIPART']) {
            // Recursively parse the multipart message
            foreach ($structure->parts as $i => $subPart) {
                $nextPart = is_null($part) ? ($i + 1) : $part.".".($i + 1);
                list($newBody, $newAttachments) = $this->parseBodyPart ($uid, $subPart, $nextPart);
                if (!empty($newBody['html']))
                    $body['html'] .= $newBody['html'];
                if (!empty($newBody['plain']))
                    $body['plain'] .= $newBody['plain'];
                $attachments = array_merge($attachments, $newAttachments);
            }
        } else {
            // Handle parsing body part
            $structureEncoding = $this->getStructureMimetype ($structure);
            $disposition = isset($structure->ifdisposition) && $structure->ifdisposition ?
                $structure->disposition : false;
            $disposition = strtoupper($disposition);
            if (!in_array ($disposition, array ('ATTACHMENT', 'INLINE')) && 
                $structureEncoding === 'TEXT/HTML') {
                $body['html'] = $this->decodeBodyPart ($uid, $structure, $part);
            } else if (
                !in_array ($disposition, array ('ATTACHMENT', 'INLINE')) && 
                $structureEncoding === 'TEXT/PLAIN') {

                $body['plain'] = $this->decodeBodyPart ($uid, $structure, $part);
            } else if (in_array($disposition, array('ATTACHMENT', 'INLINE')) &&
                isset ($structure->dparameters)) {

                $filename = $structure->dparameters[0]->value;
                $size = $structure->bytes;
                $type = ($disposition === 'ATTACHMENT' ? 'attachment' : 'inline');
                $mimeType = $this->getStructureMimetype($structure, true);
                $partNumber = is_null($part) ? 1 : $part;
                if (isset($structure->id))  {
                    // Retrieve inline attachment content ids
                    if (preg_match('/<(.*?)>/', $structure->id, $matches))
                        $cid = $matches[1];
                }

                // Save attachment info
                $attachments[] = array(
                    'filename' => $filename,
                    'cid' => isset($cid) ? $cid : null,
                    'part' => $partNumber,
                    'mimetype' => $mimeType,
                    'type' => $type,
                    'link' => CHtml::link($filename, array(
                        'downloadAttachment',
                        'uid' => $uid,
                        'part' => $partNumber,
                    )),
                );
            }
        }
        return array($body, $attachments);
    }

    /**
     * @param array $criteria email attributes values indexed by attribute name
     * @param array $emails 
     */
//    private function filterEmails (array $criteria, $emails) {
//        $filteredEmails = array ();
//        $searchOperators = self::$searchOperators;
//        foreach ($emails as $email) {
//            $meetsCriteria = true;
//            if (isset ($criteria['fullText'])) {
//                $fullText = implode (' ', array_values ($email->getAttributes (array (
//                    'uid', 'msgno', 'subject', 'from', 'to', 'cc', 'reply_to', 'body', 'date',
//                    'size', 'seen', 'flagged', 'answered',
//                ))));
//            }
//            foreach ($criteria as $operator => $val) {
//                $operandType = $searchOperators[$operator];
//                switch ($operator) {
//                    case 'fullText':
//                        if (stripos ($fullText, $val) === false) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                    case 'subject':
//                    case 'from':
//                    case 'to':
//                    case 'cc':
//                    case 'body':
//                        if (stripos ($email->$operator, $val) === false) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                    case 'seen':
//                    case 'flagged':
//                    case 'answered':
//                        if (!$email->$operator) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                    case 'unseen':
//                    case 'unflagged':
//                    case 'unanswered':
//                        $operator = preg_replace ('/^un/', '', $operator);
//                        if ($email->$operator) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                }
//                if (!$meetsCriteria) break;
//            }
//            if ($meetsCriteria) {
//                $filteredEmails[] = $email;
//            }
//        }
//        return $filteredEmails;
//    }

    /**
     * @return array of EmailInboxes tab options for current user 
     */
    public function getVisibleInboxes () {
        $criteria = $this->getAccessCriteria ();
        return $this->findAll ($criteria);
    }

    /**
     * @return array visible inbox names indexed by id
     */
    public function getTabOptions () {
        $visibleInboxes = $this->getVisibleInboxes ();
        if (!Yii::app()->params->isAdmin) {
            $visibleInboxes = array_filter($visibleInboxes, function($x) {
                // Filter out inboxes that are shared, but not owned by System.
                // These credentials will NOT be present in the hidden credentials dropdown,
                // causing mail to be sent from an incorrect address
                if ($x->shared)
                    return $x->credentials->userId == Credentials::SYS_ID;
                return true;
            });
        }
        if (!empty($visibleInboxes)) {
            return array_combine (array_map (function ($inbox) {
                return $inbox->id;
            }, $visibleInboxes), 
            array_map (function ($inbox) {
                return $inbox->name;
            }, $visibleInboxes));
        } else {
            return array ();
        }
    }

    /**
     * Return the next UID to use for this mailbox
     * @return int|null next available UID
     */
    public function getNextUid() {
        $status = $this->status();
        if (!isset ($status->uidnext)) return null;
        $nextUid = $status->uidnext;
        return $nextUid;
    }

    public function getDisplayName ($plural=true, $ofModule=true) {
        if (!$ofModule) return Yii::t('app', 'Email Inbox' . ($plural ? 'es' : ''));
        return parent::getDisplayName ($plural, $ofModule);
    }
}

/**
 * Thrown when imap stream cannot be opened 
 */
class EmailConfigException extends CException {}

class EmailsCacheEntry {

    /**
     * @param array $emails 
     */
    public $emails;

    /**
     * @var array $uids
     */
    public $uids; 

    /**
     * @param string $searchString 
     */
    public $searchString = null;

    /**
     * @var $expirationTime
     */
    public $expirationTime; 
}

//class UidsCacheEntry {
//     
//    /**
//     * @param array $uids 
//     */
//    public $uids;
//
//    /**
//     * @param string $searchString 
//     */
//    public $searchString = null;
//}
