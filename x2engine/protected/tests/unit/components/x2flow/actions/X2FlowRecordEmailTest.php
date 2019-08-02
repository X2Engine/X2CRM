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
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordEmailTest extends X2FlowTestBase {

    const LIVE_DELIVERY = 0;

    public $fixtures = array (
        'contacts' => 'Contacts',
        'x2flow' => array ('X2Flow', '.X2FlowRecordEmailTest'),
        'credentials' => 'Credentials',
        'profile' => 'Profile',
        'user' => 'User',
    );

    /**
     * Replace credId token with cred id from x2_credentials-local.
     */
    public static function setUpBeforeClass () {
        $fixtureDir = implode(DIRECTORY_SEPARATOR, array(__DIR__,'..','..','..','..','fixtures'));
        $file = $fixtureDir.DIRECTORY_SEPARATOR.'x2_flows.X2FlowRecordEmailTestTemplate.php';
        $content = file_get_contents ($file);
        if (self::LIVE_DELIVERY) {
            $localCredentialFixture = require ($fixtureDir.DIRECTORY_SEPARATOR.'x2_credentials-local.php');
            $credId = $localCredentialFixture['liveDeliveryTest']['id'];
        } else {
            $credId = -1;
        }

        $content = preg_replace (
            '/EMAIL_CREDENTIAL_ID/', $credId, $content);
        file_put_contents ($fixtureDir.DIRECTORY_SEPARATOR.'x2_flows.X2FlowRecordEmailTest.php', $content);

        if (!YII_UNIT_TESTING || !X2_DEBUG_EMAIL) {
            X2_TEST_DEBUG_LEVEL > 1 && println (
                'X2FlowRecordEmailTest will not run properly unless '.
                'YII_UNIT_TESTING is true and X2_DEBUG_EMAIL is true');
            self::$skipAllTests = true;
        }

        parent::setUpBeforeClass ();
    }


    public function testDoNotEmailLinkInsertion () {
        Yii::app()->settings->x2FlowRespectsDoNotEmail = 1;
        Yii::app()->settings->externalBaseUrl = TEST_WEBROOT_URL;
        Yii::app()->settings->externalBaseUri = TestingAuxLib::getTestBaseUri ();
        $flow = $this->getFlow ($this,'flow1');
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $contact->doNotEmail = 0;
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];

        // assert flow executed without errors
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($trace));
        $emailMessage = ArrayUtil::pop (ArrayUtil::pop ($this->flattenTrace ($trace)));
        $this->assertTrue (
            (bool) preg_match ('/'.Admin::getDoNotEmailLinkDefaultText ().'/', $emailMessage));
        X2_TEST_DEBUG_LEVEL > 1 && println ($emailMessage);

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // ensure that "Do Not Email" link text can be changed
        Yii::app()->settings->doNotEmailLinkText = 'test';
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($trace));

        // ensure that the "Do Not Email" link, when followed, causes the contact's do not
        // email field to be set to true
        $emailMessage = ArrayUtil::pop (ArrayUtil::pop ($this->flattenTrace ($trace)));
        preg_match ("/href=\"([^\"]+)\"/", $emailMessage, $matches);
        $link = $matches[1];
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlOutput = curl_exec ($ch);
        $contact->refresh ();
        $this->assertEquals (1, $contact->doNotEmail);
        $this->assertTrue (
            (bool) preg_match ('/test/', $emailMessage));

        // now trigger the flow again and ensure that the email doesn't get sent since the
        // contact followed the "Do Not Email" link
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertFalse ($this->checkTrace ($trace));
    }

    /**
     * Sends an email from x2flow to the address TEST_EMAIL_TO, inserting the "Do Not Email"
     * link into the body of the email
     */
    public function testCheckEmailSent () {
        if (self::LIVE_DELIVERY) {
            /*Yii::app()->settings->doNotEmailLinkText = null;
            Yii::app()->settings->externalBaseUrl = 'http://localhost';
            $flow = $this->getFlow ($this,'flow1');
            $contact = $this->contacts ('testAnyone');
            $contact->email = TEST_EMAIL_TO;
            $this->assertSaves ($contact);
            $params = array (
                'model' => $contact,
                'modelClass' => 'Contacts',
            );
            $contact->doNotEmail = 0;
            $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
            $trace = $retVal['trace'];
            X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
            $this->assertTrue ($this->checkTrace ($trace));
            X2_TEST_DEBUG_LEVEL > 1 && println ('testCheckEmailSent: Email sent, check your inbox');


            // ensure that link text can be changed 
            Yii::app()->settings->doNotEmailLinkText = 'test';
            $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
            $trace = $retVal['trace'];
            X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
            $this->assertTrue ($this->checkTrace ($trace));
            X2_TEST_DEBUG_LEVEL > 1 && println ('testCheckEmailSent: Email sent, check your inbox');*/
        }
    }

    /**
     * Ensure that email doesn't get set if x2FlowRespectsDoNotEmail admin setting is set to true
     * and email recipients list contains a contact that has their doNotEmail field set to true.
     */
    public function testDoNotEmailCheck () {
        Yii::app()->settings->externalBaseUrl = 'http://localhost';
        Yii::app()->settings->x2FlowRespectsDoNotEmail = 1;
        $flow = $this->getFlow ($this,'flow1');
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $contact->doNotEmail = 0;
        $this->assertSaves ($contact);
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // email should be sent since contact does not have  doNotEmail field set to 1
        $this->assertTrue ($this->checkTrace ($trace));

        $contact->doNotEmail = 1;
        $this->assertSaves ($contact);
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // email should not be sent since contact has doNotEmail field set to 1
        $this->assertFalse ($this->checkTrace ($trace));

        $contact->doNotEmail = 0;
        $this->assertSaves ($contact);
        $contact2 = $this->contacts ('testUser');
        $contact2->doNotEmail = 1;
        $this->assertSaves ($contact2);

        // email should not be sent because a contact in the CC list has doNotEmail set to
        // 1
        $retVal = $this->executeFlow ($this->x2flow ('flow2'), $params);
        $trace = $retVal['trace'];
        $this->assertFalse ($this->checkTrace ($trace));


        // email should be sent since contact2, which was the only contact with doNotEmail set to 1
        // is now hidden
        $contact2->hide ();
        $this->assertSaves ($contact2);
        $retVal = $this->executeFlow ($this->x2flow ('flow2'), $params);
        $trace = $retVal['trace'];
        $this->assertTrue ($this->checkTrace ($trace));


        $contact2->visibility = 1;
        $this->assertSaves ($contact2);
        $contact2->doNotEmail = 0;
        $this->assertSaves ($contact2);

        $retVal = $this->executeFlow ($this->x2flow ('flow2'), $params);
        $trace = $retVal['trace'];

        // email should be sent because all contacts, including those in the CC list have
        // doNotEmail set to 0
        $this->assertTrue ($this->checkTrace ($trace));
    }

}

?>
