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
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class X2IdentityTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        'fingerprints' => 'Fingerprint',
        'contacts' => array ('Contacts', '.WebTrackingTestBase'),
        'admin' => array ('Admin', '.X2IdentityTest'),
    );

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugTrack () {
        $this->assertDebugTrack ();
    }

    protected function clearAnonContacts () {
        Yii::app()->db->createCommand ('delete from x2_anon_contact where 1=1')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_anon_contact where 1=1')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Asserts that anon contact was created 
     * @param bool $getAnonContact
     * @return null|AnonContact the anon contact that was created or null if getAnonContact is false
     */
    protected function assertAnonContactGeneration ($getAnonContact=false) {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_anon_contact
             where 1=1')
             ->queryScalar ();
        X2_TEST_DEBUG_LEVEL > 1 && println ($newCount);
        $this->assertTrue ($newCount === '1');
        if ($getAnonContact) {
            return AnonContact::model ()->findByAttributes (array ());
        }
    }

    /**
     * Asserts that the anon contact was tracked by checking if anon contact web activity was
     * generated.
     */
    protected function assertAnonContactWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity" and associationType="anoncontact"')
             ->queryScalar ();
        X2_TEST_DEBUG_LEVEL > 1 && println ($newCount);
        $this->assertTrue ($newCount === '1');
    }

    protected function assertNoAnonContactWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity" and associationType="anoncontact"')
             ->queryScalar ();
        X2_TEST_DEBUG_LEVEL > 1 && println ($newCount);
        $this->assertTrue ($newCount === '0');
    }


    /**
     * Visit the page with the web tracker and assert that an anonymous contact was generated 
     */
    public function testWebTrackerAnonContactGeneration () {
        $this->clearAnonContacts ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        sleep (5);
        $this->assertAnonContactGeneration ();
    }

    /**
     * Visit the web tracker page twice, once to generate the fingerprint record, and the second
     * time to assert that a fingerprint match is found.
     */
    public function testFingerprintBasedTrackingUsingWebTracker () {
        $this->deleteAllVisibleCookies ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        sleep (5);
        $this->clearWebActivity ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        sleep (5);
        $this->assertAnonContactWebActivityGeneration ();
    }

    /**
     * Visit the web tracker page to initiate fingerprint-based tracking, then visit the web form
     * and assert that tracking does not work since the tracker embedded in the web form only uses
     * cookie-based tracking.
     */
    public function testFingerprintBasedTrackingUsingWebForm () {
        $this->deleteAllVisibleCookies ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->clearWebActivity ();
        $this->openPublic('x2WebTrackingTestPages/webFormTestDifferentDomain.html');
        sleep (5);
        $this->assertNoAnonContactWebActivityGeneration ();
    }

    /**
     * Initiate fingerprint-based tracking and then submit the web form. Assert that anon contact
     * gets converted into a contact.
     */
    public function testAnonymousContactConversion () {
        $this->deleteAllVisibleCookies ();
        $this->clearAnonContacts ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        sleep (5);
        $anonContact = $this->assertAnonContactGeneration (true);
        $anonContact->leadscore = 3;
        $anonContact->email = '1@1.com';
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($anonContact->getAttributes());
        $this->assertTrue ($anonContact->save ());

        $this->submitWebForm ('differentDomain');
        $contact = $this->assertContactCreated ();

        // fingerprint and lead score should be migrated from anon contact
        $this->assertTrue ($anonContact->fingerprintId === $contact->fingerprintId);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($contact->leadscore);
        $this->assertTrue ($contact->leadscore == 3);

        // email should not be overwritten
        $this->assertTrue ($contact->email === 'test@test.com');
    }

}

?>
