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




class EmailTestingUtil {

    /**
     * @var Credentials $credentials
     */
    public $credentials; 
    public $maxRetries = 3;

    private $_currentFolder; 
    private $_imapStream;
    private $_mailboxString;
    private $_open = false;

    public function setCurrentFolder ($currentFolder) {
        $this->_currentFolder = $currentFolder;
    }
    
    public function getCurrentFolder () {
        return $this->_currentFolder;
    }

    public function open($mailbox = "INBOX") {
        $this->setCurrentFolder ($mailbox);
        $cred = $this->credentials;
        $this->_imapStream = @imap_open(
            $this->getMailbox () . $mailbox,
            $cred->auth->email,
            $cred->auth->password);
        if (is_resource($this->_imapStream)) {
            $this->_open = true;
            return true;
        }
        return false;
    }

    public function close() {
        if ($this->isOpen ()) {
            imap_close($this->_imapStream, CL_EXPUNGE);
            $this->_open = false;
        }
    }

    public function getStream() {
        if ($this->isOpen())
            return $this->_imapStream;
        foreach (range(1, $this->maxRetries) as $i) {
            $this->open ($this->getCurrentFolder ());
            if ($this->isOpen())
                return $this->_imapStream;
        }
    }

    public function isOpen() {
        return ($this->_open === true && is_resource($this->_imapStream) &&
            imap_ping($this->_imapStream));
    }

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

}

?>
