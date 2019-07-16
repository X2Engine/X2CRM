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



/**
 * Bounced Email Tracking Class
 * Will travers through EMail accounts and update campaigns list and contacts
 *
 * @package application.components
 * @property Credentials $_credential
 * @author Zain Hameed
 */
class BouncedEmailBehavior extends CBehavior
{

    /**
     * IMAP protocol object
     */
    private $_protocol;

    /**
     * Credential object for email inbox
     */
    private $_credential;

    /**
     * Executes the whole process for Bounce Handling email inbox
     *
     * @param  string $inboxId of the bounce handling email account
     * @param  Object $credential user credentials for the email inbox
     * @throws Exception when inboxId not found or Account is not enabled for bounce handling
     */
    public function executeMailbox($inboxId, $credential = null)
    {
        $this->_credential = $credential;
        if (empty($inboxId)) {
            throw new Exception('Account Id is not provided to proceed!');
        }

        if (empty($this->_credential)) {
            $this->_credential = Credentials::model()->findByPk($inboxId);
        }

        if (!($this->_credential->isBounceAccount)) {
            throw new Exception('Please select Bounce Account to proceed!');
        }

        $credentials = $this->getCredentials();
        if (!isset($this->_protocol)) {
            require_once(realpath(Yii::app()->basePath . '/components/phpMailer/PHPMailerAutoload.php'));
            $this->_protocol = new IMAP($credentials);
        }

        $lastRunDate = empty($this->_credential->lastRunDate) ? $this->_credential->createDate : $this->_credential->lastRunDate;
        $recentMessages = $this->_protocol->searchMessages(null, array(
            0 => array('search_key' => 'SINCE', 'search_value' =>  date("d-M-Y", $lastRunDate)),
        ));
        $this->processEmails($recentMessages);

        $this->_credential->lastRunDate = time();
        $this->_credential->update('lastRunDate');
    }

    /**
     * Process EMail messages one by one
     * @param  array $emailMessages uids for the email messages
     */
    private function processEmails($emailMessages)
    {
        foreach ($emailMessages as $uid) {

            $header = $this->_protocol->getHeader($uid);
            $header = imap_rfc822_parse_headers($header['RFC822.HEADER']);

            $fromEmail = $header->from[0]->mailbox . "@" . $header->from[0]->host;

            //check if email is from mailer daemon
            if (preg_match('/MAILER-DAEMON|POSTMASTER/i', $fromEmail)) {

                $message = $this->_protocol->getMessage($uid);
                $rawMessage = quoted_printable_decode ($message['RFC822.TEXT']);

                $identifiers = $this->parseEmailForIdentifies($rawMessage);
                if ($this->checkAllIdentifiersExist($identifiers)) {
                    $this->processBouncedEmailForCampaigns($identifiers);
                }
            }
        }
    }

    /**
     * Get and returns the user credentials for the email inbox
     * @throws Exception when current Inbox is diabled by admin
     * @return  Array array for the credentials to use for imap connection
     */
    private function getCredentials()
    {
        if ($this->_credential->auth->disableInbox) {
            throw new Exception('This Inbox is disabled by Admin currently!');
        }
        return array(
            'host' => $this->_credential->auth->imapServer,
            'user' => $this->_credential->auth->email,
            'port' => $this->_credential->auth->imapPort,
            'password' => $this->_credential->auth->password,
            'folder' => 'INBOX',
            'ssl' => $this->_credential->auth->imapSecurity,
        );
    }

    /**
     * Parse email message and returns the array of found identifiers in email
     * @param  string $rawMessage raw message of the email body
     * @return  Array array for the identifiers found in message
     */
    private function parseEmailForIdentifies($rawMessage)
    {
        $messageArray = explode("\r\n", $rawMessage);
        $identifiers = array();
        foreach ($messageArray as $key=>$value) {
            if (substr($value, 0, 3) == "To:") {
                $identifiers['recipient']  = substr($value, 4);
                if (strpos($value, '<') !== false ) {
                    $identifiers['recipient'] = explode('>', substr(strrchr($value, "<"), 1))[0];
                }
            }
            if (substr($value, 0, 54) == 'To stop receiving these messages, click here: <a href=')  {
                $identifiers['unsubUrl']  = explode('">',substr($value, 62))[0];
                $urlParts = explode('/', $identifiers['unsubUrl']);
                if ($uidKey = array_search('uid', $urlParts)) {
                    $identifiers['uniqueId'] = $urlParts[$uidKey+1];
                }
                if ($modelKey = array_search('listModel', $urlParts)) {
                    $identifiers['listModel'] = $urlParts[$modelKey+1];
                }
                if ($contactKey = array_search('contactId', $urlParts)) {
                    $identifiers['contactId'] = $urlParts[$contactKey+1];
                }
                if ($campaignKey = array_search('campaignId', $urlParts)) {
                    $identifiers['campaignId'] = $urlParts[$campaignKey+1];
                }
            }
        }
        return $identifiers;
    }

    /**
     * Check whether desired identifiers exist in the current email message
     * @param  Array $identifiers array for the identifiers found in message
     * @return  Bool true if all required identifiers found
     */
    private function checkAllIdentifiersExist($identifiers)
    {
        $idealIdentifiers = array('recipient','unsubUrl','uniqueId','listModel','contactId','campaignId');
        return (count(array_diff(array_keys($identifiers), $idealIdentifiers)) == 0);
    }

    /**
     * Enable doNotEMail for Bounced EMail address Contact and mark bounced to positive in list item record
     * @param  Array $params array of the required params to use in sql ro update records
     */
    private function processBouncedEmailForCampaigns($params)
    {
        Yii::app()->db
            ->createCommand("UPDATE x2_contacts SET doNotEmail = :doNotEmail WHERE id= :ContactID")
            ->bindValues(array(':doNotEmail' => 1, ':ContactID' => $params['contactId']))
            ->execute();
        Yii::app()->db
            ->createCommand("UPDATE x2_list_items SET bounced = :BOUNCED WHERE uniqueId= :UniqueID")
            ->bindValues(array(':BOUNCED' => 1, ':UniqueID' => $params['uniqueId']))
            ->execute();
    }
}
