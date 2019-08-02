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
 * @package application.tests.functional.modules.contacts
 */
class WebLeadFormTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
        'webForms' => array ('WebForm', '.WebLeadFormTest'),
    );

     
    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugTrack () {
        $this->assertDebugTrack ();
    }
     

    protected function assertLeadCreated () {
        $lead = X2Leads::model()->findByAttributes (array (
            'name' => 'test test',
            'leadSource' => 'facebook',
        ));
        $this->assertTrue ($lead !== null);
        X2_TEST_DEBUG_LEVEL > 1 && println (
            'lead created');
        return $lead;
    }

    protected function assertAccountCreated () {
        $lead = Accounts::model()->findByAttributes (array (
            'name' => 'testAccount',
        ));
        $this->assertTrue ($lead !== null);
        X2_TEST_DEBUG_LEVEL > 1 && println (
            'account created');
        return $lead;
    }

    protected function clearLead () {
        Yii::app()->db->createCommand ('delete from x2_x2leads where name="test test"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_x2leads
             where name="test test"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    protected function clearAccount () {
        Yii::app()->db->createCommand ('delete from x2_accounts where name="testAccount"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_accounts
             where name="testAccount"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    protected function assertLeadNotCreated () {
        $lead = X2Leads::model()->findByAttributes (array (
            'name' => 'test test',
            'leadSource' => 'Facebook',
        ));
        $this->assertTrue ($lead === null);
        return $lead;
    }


    /**
     * Submit web lead form and wait for success message
     */
    public function testSubmitWebLeadForm () {
        $this->deleteAllVisibleCookies ();
         
        $this->assertNotCookie ('regexp:.*x2_key.*');
         

        $this->submitWebForm ();
        sleep (5); // wait for iframe to load and for cookie to be set
         
        $this->assertCookie ('regexp:.*x2_key.*');
         
        $this->assertContactCreated ();
    }

     
    /**
     * Test web lead form tracking by revisiting the page with the web lead form after submission
     */
    public function testWebLeadFormTracking () {
        $this->deleteAllVisibleCookies ();
        $this->assertNotCookie ('regexp:.*x2_key.*');

        $this->submitWebForm ();
        sleep (5); // wait for iframe to load and for cookie to be set
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->assertContactCreated ();


        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webFormTest.html');
        sleep (5); // wait for iframe to load
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->assertWebActivityGeneration ();
    }
     

    /**
     * Submit a web form that was created with the generate lead option checked. Assert that
     * a lead gets generated.
     */
    public function testGenerateLead () {
        $this->clearContact ();
        $this->clearLead ();

        $this->openPublic('x2WebTrackingTestPages/webFormTestGenerateLead.html');
        sleep (5);
        $this->selectFrame('web-form-iframe');
        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->click("css=#submit");
        sleep (5); // wait for iframe to load

        $this->assertContactCreated ();
        $this->assertLeadCreated ();
    }

     
    public function testGenerateAccount () {
        $this->clearContact ();
        $this->clearAccount ();

        $this->openPublic('x2WebTrackingTestPages/webFormTestGenerateAccount.html');
        sleep (5);
        $this->selectFrame('web-form-iframe');
        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->type("name=Contacts[company]", 'testAccount');
        $this->click("css=#submit");
        sleep (5); // wait for iframe to load

        $this->assertContactCreated ();
        $this->assertAccountCreated ();
    }
     

    /**
     * Submit a web form that was created with the generate lead option unchecked. Assert that
     * a lead doesn't get generated.
     */
    public function testDontGenerateLead () {
        $this->clearContact ();
        $this->clearLead ();

        $this->openPublic('x2WebTrackingTestPages/webFormTestDontGenerateLead.html');
        sleep (5);
        $this->selectFrame('web-form-iframe');
        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->click("css=#submit");
        sleep (5); // wait for iframe to load

        $this->assertContactCreated ();
        $this->assertLeadNotCreated ();
    }


}

?>
