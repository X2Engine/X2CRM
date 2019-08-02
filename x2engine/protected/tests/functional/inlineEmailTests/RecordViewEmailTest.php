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
 * Tests inline email form 
 */

class RecordViewEmailTest extends X2WebTestCase {

    public $fixtures = array (
        'contacts' => 'Contacts',
        'credentials' => 'Credentials',
        'defaultCredentials' => ':x2_credentials_default',
    );

    public function tearDown () {
        TestingAuxLib::setConstant ('X2_DEBUG_EMAIL', 'false');
        return parent::tearDown ();
    }

    public function assertEmailSuccess () {
        $this->waitForElementPresent ('css=#top-flashes-message');
        $this->assertNotVisible ('css=#inline-email-errors');
    }

    /**
     * Send a test email with email debug mode on and try to ensure that no errors occurred 
     */
    public function testEmailSend () {
        TestingAuxLib::setConstant ('X2_DEBUG_EMAIL', 'true');
        $contact = $this->contacts ('testAnyone');
        $this->openX2 ('contacts/'.$contact->id);
        sleep (1);
        $this->click ("dom=document.querySelector ('.page-title .email')");
        $this->type("name=InlineEmail[subject]", 'test');
        $this->storeEval (
            "window.$('#email-message').val ('test')",
            'placeholder');
        sleep (2);
        $this->click ("dom=document.querySelector('#send-email-button')");
        $this->assertEmailSuccess ();
    }

    /**
     * Send an actual email using local credentials and then assert that the email was received
     * by using imap_search
     */
    public function testLiveEmail () {
        TestingAuxLib::setConstant ('X2_DEBUG_EMAIL', 'false');
        $contact = $this->contacts ('testAnyone');
        $liveDeliveryTestCreds = $this->credentials ('liveDeliveryTest');
        $this->openX2 ('contacts/'.$contact->id);
        sleep (1);
        $this->click ("dom=document.querySelector ('.page-title .email')");
        $subject = 'functional test email '.time ();
        $this->select ("css=#InlineEmail_credId", 'value='.$liveDeliveryTestCreds->id);
        $this->type("name=InlineEmail[subject]", $subject);
        $this->type("name=InlineEmail[to]", $liveDeliveryTestCreds->auth->email);
        $this->storeEval (
            "window.$('#email-message').val ('test')",
            'placeholder');
        sleep (2);
        $this->click ("dom=document.querySelector('#send-email-button')");
        sleep (2);
        $this->assertEmailSuccess ();
        TestingAuxLib::assertEmailReceived ($this, $liveDeliveryTestCreds, $subject, 3);
    }

}

?>
