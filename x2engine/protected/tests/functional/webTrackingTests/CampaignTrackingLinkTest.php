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






Yii::import('application.tests.functional.webTrackingTests.WebTrackingTestBase');
Yii::import('application.modules.accounts.models.Accounts');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.marketing.components.*');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class CampaignTrackingLinkTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        'campaign' => 'Campaign',
        'lists' => 'X2List',

        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
        'credentials' => 'Credentials',
        'users' => 'User',
        'profile' => array('Profile','.marketing'),
        'listItem' => 'X2ListItem',
        'contacts' => 'Contacts'
    );

    /**
     * Used in conjunction with assertClickActivityGeneration (). 
     * Clears web activity actions so that we can easily test later that a new email_clicked action
     * was generated
     */
    protected function clearClickActivity () {
        Yii::app()->db->createCommand ('delete from x2_actions where type="email_clicked"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="email_clicked"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Used in conjunction with clearClickActivity (). Ensures that a email_clicked action was 
     * generated.
     */
    protected function assertClickActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="email_clicked"')
             ->queryScalar ();
        X2_TEST_DEBUG_LEVEL > 1 && println ($newCount);
        $this->assertTrue ($newCount === '1');
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

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugTrack () {
        $this->assertDebugTrack ();
    }

    /**
     * Assert that tracking links correctly track contacts. Also ensures that tracking link-based
     * tracking does not interfere with cookie-based tracking.
     */
    public function testTrackingLinks () {
        $this->deleteAllVisibleCookies ();

        // first initiate cookie based tracking
        $this->submitWebForm ();
        $this->assertContactCreated ();
        $this->assertWebTrackerTracksWithCookie ();

        // next, generate the tracking link and attempt to track the user with a campaign click 
        $cmb = $this->instantiate ();
        $contact = $this->contacts('testUser_unsent');

        // Set URL/URI to verify proper link generation:
        $admin = Yii::app()->settings;
        $admin->externalBaseUrl = TEST_WEBROOT_URL_ALIAS_1;
        $admin->externalBaseUri = '';

        // generate unique id and associate it with the test contact
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('testUser'),$contact,$this->listItem('testUser_unsent')->emailAddress);
        $cmb->markEmailSent ($uniqueId, true);
        X2_TEST_DEBUG_LEVEL > 1 && println($uniqueId);

        // visit page with tracking link, specifying campaign tracking key
        $this->clearClickActivity ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTest.html?x2_key='.$uniqueId);
        sleep (5); // wait for iframe to load
        $this->assertClickActivityGeneration ();

        // ensure that even after tracking the contact with a campaign click, we can still track 
        // them with their tracking cookie
        $this->assertWebTrackerTracksWithCookie ();
    }

    /**
     * First track the contact with a campaign click, then check if the tracking cookie has been 
     * properly set in the contact's browser
     */
    public function testThatCampaignClickInitiatesCookieTracking () {
        Yii::app()->settings->refresh ();
        $this->assertFalse ((bool) Yii::app()->settings->enableFingerprinting);
        $this->deleteAllVisibleCookies (TEST_WEBROOT_URL_ALIAS_1);
        // we haven't simulated the tracking link click yet so tracking should fail
        $this->assertWebTrackerCannotTrackWithCookie ();

        // next, generate the tracking link and attempt to track the user with a campaign click 
        $cmb = $this->instantiate ();
        $contact = $this->contacts('testUser_unsent');

        // Set URL/URI to verify proper link generation:
        $admin = Yii::app()->settings;
        $admin->externalBaseUrl = TEST_WEBROOT_URL_ALIAS_1; 
        $admin->externalBaseUri = '';

        // generate unique id and associate it with the test contact
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('testUser'),$contact,$this->listItem('testUser_unsent')->emailAddress);
        $cmb->markEmailSent ($uniqueId, true);

        X2_TEST_DEBUG_LEVEL > 1 && println($uniqueId."\n");

        // visit page with tracking link, specifying campaign tracking key
        $this->clearClickActivity ();
        $this->openPublic ('/x2WebTrackingTestPages/webTrackerTest.html?x2_key='.$uniqueId);
        sleep (5); // wait for iframe to load
        $this->assertClickActivityGeneration ();

        // assert that key was set on the server after campaign click track
        $this->assertCookie ('regexp:.*x2_key.*'); 

        // ensure that campaign click initiated cookie-based tracking
        $this->assertWebTrackerTracksWithCookie ();
    }


}

?>
