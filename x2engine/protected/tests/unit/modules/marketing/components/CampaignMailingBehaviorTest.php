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




Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.marketing.controllers.*');
Yii::import('application.modules.marketing.*');
Yii::import('application.modules.marketing.components.*');
Yii::import('application.components.util.StringUtil', true);

/**
 * 
 * @package application.tests.unit.modules.marketing.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CampaignMailingBehaviorTest extends X2DbTestCase {


    public static function referenceFixtures() {
        return array(
            'campaign' => array ('Campaign', '.CampaignMailingBehaviorTest'),
            'lists' => 'X2List',
            'credentials' => 'Credentials',
            'users' => 'User',
            'profile' => array('Profile','.marketing'),
            'actions' => 'Actions'
        );
    }

    public $fixtures = array(
        'listItem' => 'X2ListItem',
        'contacts' => 'Contacts'
    );

    private static $_savedIniSettings = array (
        'pcre.backtrack_limit' => null,
        'pcre.recursion_limit' => null,
    );

    public static function setUpBeforeClass () {
        // save relevant ini settings
        foreach (self::$_savedIniSettings as $setting => $val) { 
            self::$_savedIniSettings[$setting] = ini_get ($setting);
        }
        return parent::setUpBeforeClass ();
    }

    public function tearDown () {
        TestingAuxLib::restoreController();
        self::restoreIniSettings ();
        return parent::tearDown ();
    }

    private static function restoreIniSettings () {
        foreach (self::$_savedIniSettings as $setting => $val) { 
            if ($val !== null) { 
                assert (ini_set ($setting, $val) !== false);
            }
        }
    }

    public function instantiate($config = array()) {
        $obj = new CComponent;
        $obj->attachBehavior('CampaignMailing', array_merge(array(
            'class' => 'CampaignMailingBehavior',
            'itemId' => $this->listItem('testUser_unsent')->id,
            'campaign' => $this->campaign('testUser')
        ),$config));
        return $obj;
    }

    public function testGetCredId() {
        $cmb = $this->instantiate();
        $this->assertEquals($this->campaign('testUser')->sendAs, $cmb->credId);
        $this->assertTrue($cmb->credentials instanceof Credentials);
    }

    public function testGetListItem() {
        $cmb = $this->instantiate();
        $this->assertTrue($cmb->listItem instanceof X2ListItem);
        $this->assertEquals($this->listItem('testUser_unsent')->id,$cmb->listItem->id);
    }

    public function testMarkEmailSent() {
        $cmb = $this->instantiate();
        $cmb->listItem->sending = 1;
        $cmb->listItem->update(array('sending'));
        $cmb->markEmailSent('abcde');
        $cmb->listItem->refresh();
        $this->assertFalse((bool) $cmb->listItem->sending);
        $this->assertTrue(abs($cmb->listItem->sent - time())<=1);
        $this->assertEquals('abcde',$cmb->listItem->uniqueId);
        $cmb->markEmailSent(null);
        // Expect: null unique ID corresponds to 
        $cmb->listItem->refresh();
        $this->assertTrue(abs($cmb->listItem->sent - time())<=1);
        $this->assertEquals(null,$cmb->listItem->uniqueId);
    }

    public function testRedirectLinkGeneration () {
        TestingAuxLib::loadControllerMock ('localhost', '/index-test.php');
        $cmb = $this->instantiate();
        $contact = $this->contacts('testUser_unsent');
        $campaign = $this->campaign('redirectLinkGeneration');
        $url = preg_replace ('/^[^"]*"([^"]*)".*$/', '$1', $campaign->content);
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('redirectLinkGeneration'),
            $contact);
        $this->assertRegExp ('/'.preg_quote (urlencode ($url)).'/', $message);

    }

    public function testUnsubTokenReplacement () {
        $admin = Yii::app()->settings;
        TestingAuxLib::loadControllerMock ('examplecrm.com', '/X2Engine/index-test.php');
        $admin->externalBaseUrl = 'http://examplecrm.com';
        $admin->externalBaseUri = '/X2Engine';
        $cmb = $this->instantiate();
        $contact = $this->contacts('testUser_unsent');
        $campaign = $this->campaign('unsubToken');
        list($subject,$message,$uniqueId) = $cmb->prepareEmail($campaign,$contact);

        $unsubUrl = Yii::app()->createExternalUrl('/marketing/marketing/click', array(
            'uid' => $uniqueId,
            'type' => 'unsub',
            'email' => $contact->email
        ));
        $unsubLinkText = Yii::app()->settings->getDoNotEmailLinkText ();
        $expectedLink = 
            '<a href="'.$unsubUrl.'">'.Yii::t('marketing', $unsubLinkText).'</a>';

        $this->assertRegExp(
            '/'.preg_quote($expectedLink, '/').'/', $message, 'Unsubscribe link not inserted');
    }

    /**
     * Cause preg_replace_callback to fail and ensure that exception is thrown
     */
    public function testRedirectLinkGenerationException () {
        $this->setExpectedException (
            'StringUtilException', '',
            StringUtilException::PREG_REPLACE_CALLBACK_ERROR);
        TestingAuxLib::loadControllerMock ('localhost', '/index-test.php');

        // set this high enough to cause a redirect link replacement error, but not a CUrlRule error
        ini_set('pcre.backtrack_limit', '10');
        ini_set('pcre.recursion_limit', '10');
        
        $cmb = $this->instantiate();
        $contact = $this->contacts('testUser_unsent');
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('redirectLinkGeneration'), $contact, false);
    }

     
    public function testPrepareEmail() {

        // get app config behavior to generate generate links correctly
        TestingAuxLib::loadControllerMock ('examplecrm.com', '/X2Engine/index-test.php');

        $cmb = $this->instantiate();
        $contact = $this->contacts('testUser_unsent');
        $recipientAddress = $contact->email;
        $admin = Yii::app()->settings;
        $admin->doNotEmailLinkText = 'unsubscribe';
        // Set URL/URI to verify proper link generation:
        $admin->externalBaseUrl = 'http://examplecrm.com';
        $admin->externalBaseUri = '/X2Engine';
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('testUser'),$contact);
        $email = $cmb->recipient->email;
        
        $this->assertEquals($recipientAddress,$email);
        $this->assertEquals(
            str_replace('{firstName}',$contact->firstName,$this->campaign('testUser')->subject),
            $subject);
        // Find the contact's name and tracking key:
        $replaceVars = array(
            '{firstName}' => $contact->firstName,
            '{signature}' => $this->users('testUser')->profile->signature,
            '{trackingKey}' => $uniqueId
        );
        $this->assertRegExp(
            '/'.preg_quote(strtr($this->campaign('testUser')->content,$replaceVars),'/').'/',
            $message,'Variable replacement didn\'t take place');
        // Find the tracking image:
        $trackImgUrl = Yii::app()->createExternalUrl('/marketing/marketing/click', array(
            'uid' => $uniqueId
        ));
        $this->assertRegExp(
            '/'.preg_quote(
                '<img src="'.$trackImgUrl,'/').'/',
            $message,'Tracking image not inserted');
        // Find the unsubscribe link:
        $unsubUrl = Yii::app()->createExternalUrl('/marketing/marketing/click', array(
            'uid' => $uniqueId,
            'type' => 'unsub',
            'email' => $recipientAddress
        ));
        $this->assertRegExp(
            '/'.preg_quote(
                'To stop receiving these messages, click here: '.
                '<a href="'.$unsubUrl.'">'.
                'unsubscribe</a>','/').'/',
            $message,'Unsubscribe link not inserted');
        // Find the tracking key:
        $this->assertRegExp(
            '/'.preg_quote('visit http://example.com/?x2_key=','/').$uniqueId.'/',
            $message,'Tracking key not inserted!');
    }
     

    public function testRecordEmailSent() {
        $contact = $this->contacts('testUser');
        $campaign = $this->campaign('testUser');
        $now = time();
        CampaignMailingBehavior::recordEmailSent($campaign,$contact);
        
        $action = Actions::model()->findByAttributes(array(
            'associationType' => 'contacts',
            'associationId' => $contact->id,
            'type' => 'email',
        ));
        $this->assertTrue((bool) $action);
        $this->assertTrue(abs($action->completeDate - $now)<=1);
    }

    public function testDeliverableItems() {
        $listItems = CampaignMailingBehavior::deliverableItems(
            $this->lists('launchedEmailCampaign')->id);
        $this->assertEquals(array(
            array(
                'id' => '252',
                'sent' => '0',
                'uniqueId' => NULL,
            ),
            array(
                'id' => '253',
                'sent' => '0',
                'uniqueId' => NULL,
            ),
            array(
                'id' => '254',
                'sent' => '0',
                'uniqueId' => NULL,
            ),
                ), $listItems
        );
    }

    /**
     * Test the last-minute-check function
     */
    public function testMailIsStillDeliverable() {
        $cmb = $this->instantiate();
        // Bulk limit reached:
        $admAttr = Yii::app()->settings->attributes;
        Yii::app()->settings->emailBatchSize = 1;
        Yii::app()->settings->emailCount = 1;
        Yii::app()->settings->emailStartTime = time();
        Yii::app()->settings->emailInterval = 1000;
        $can = $cmb->mailIsStillDeliverable();
        // This should be human-readable and make sense (it's the waiting message)
        //        print_r($cmb->status); 
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_BULKLIMIT,$cmb->stateChangeType);
        $cmb->stateChange = false;
        Yii::app()->settings->attributes = $admAttr;
        // Temporary arrangement, in case the app's current settings are
        // actually going to interfere with the test:
        Yii::app()->settings->emailBatchSize = 10000000;

        // Mail was sent already:
        $cmb->listItem->sent = time();
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_SENT,$cmb->stateChangeType);
        $cmb->listItem->sent = 0;
        $cmb->listItem->sending = 0;
        $cmb->listItem->update(array('sending','sent'));
        $cmb->stateChange = false;

        // Contact switched to "do not email"
        $cmb->recipient->doNotEmail = 1;
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_DONOTEMAIL,$cmb->stateChangeType);
        $cmb->listItem->sending = 0;
        $cmb->listItem->update(array('sending'));
        $cmb->stateChange = false;
        $cmb->recipient->doNotEmail = 0;

        // List item abruptly switched to unsubscribed
        $cmb->listItem->unsubscribed = 1;
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_DONOTEMAIL,$cmb->stateChangeType);
        $cmb->listItem->sending = 0;
        $cmb->listItem->update(array('sending'));
        $cmb->stateChange = false;

        // Blank email:
        $oldEmail = $cmb->recipient->email;
        $cmb->recipient->email = null;
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_NULLADDRESS,$cmb->stateChangeType);
        $cmb->recipient->email = $oldEmail;
        $cmb->stateChange = false;

        // "Sending" flag enabled!!!
        $cmb->listItem->sending = 1;
        $cmb->listItem->update(array('sending'));
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_RACECOND,$cmb->stateChangeType);

        // All clear
        $cmb->stateChange = false;
        $cmb->listItem->sending = 0;
        $cmb->listItem->unsubscribed = 0;
        $cmb->listItem->update(array('sending','unsubscribed'));
        $can = $cmb->mailIsStillDeliverable();
        $this->assertTrue($can);
    }

    /**
     * Cause line break replacement to fail and ensure that exception is thrown
     */
    public function testLineBreakReplacementError () {
        // Fails to trigger exception in php7
        $this->markTestIncomplete ();

        $this->setExpectedException (
            'StringUtilException', '',
            StringUtilException::PREG_REPLACE_ERROR);

        ini_set('pcre.backtrack_limit', '0');
        ini_set('pcre.recursion_limit', '0');

        TestingAuxLib::loadControllerMock ('localhost', '/index-test.php');
        $cmb = $this->instantiate();
        $contact = $this->contacts('testUser_unsent');
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('redirectLinkGeneration'), $contact);
    }

    /**
     * Cause line break replacement to fail and ensure that exception is thrown
     */
    public function testUnsubTokenReplacementError () {
        // error can't be triggered using current method of reducing the backtrack_limit and 
        // recursion_limit since there's a call to createExternalUrl directly preceeding the
        // code which does the token replacement
        $this->markTestIncomplete ();

        $this->setExpectedException (
            'StringUtilException', '',
            StringUtilException::PREG_REPLACE_ERROR);

        ini_set('pcre.backtrack_limit', '0');
        ini_set('pcre.recursion_limit', '0');

        TestingAuxLib::loadControllerMock ('localhost', '/index-test.php');
        $cmb = $this->instantiate();
        $contact = $this->contacts('testUser_unsent');
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('testUser'), $contact, false);
    }


}

?>
