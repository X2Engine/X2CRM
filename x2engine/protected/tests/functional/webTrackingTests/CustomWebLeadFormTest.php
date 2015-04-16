<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::import('application.tests.functional.webTrackingTests.WebTrackingTestBase');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');
Yii::import('application.modules.users.models.User');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class CustomWebLeadFormTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
        'users' => array ('User', '.CustomWebLeadFormTest'),
        'contacts' => 'Contacts',
    );

     


    /**
     * Submits the custom web lead form and ensures successful submission
     */
    protected function submitCustomWebForm ($formVersion='') {
        if ($formVersion === 'differentDomain') {
            $this->openPublic('x2WebTrackingTestPages/customWebFormTestDifferentDomain.html');
        } else if ($formVersion === 'differentSubdomain') {
            $this->openPublic('x2WebTrackingTestPages/customWebFormTestDifferentSubdomain.html');
        } else {
            $this->openPublic('x2WebTrackingTestPages/customWebFormTest.html');
        }

        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
		$this->click("css=#submit");
        // wait for response
        $this->waitForCondition (
            "selenium.browserbot.getCurrentWindow(document.getElementById ('success'))",
            4000);
        sleep (5); // wait for iframe to load
    }



    /**
     * Submit web lead form and wait for success message
     */
    public function testSubmitCustomWebLeadForm () {
        $this->deleteAllVisibleCookies ();
        $this->submitCustomWebForm ();
         
        $this->assertContactCreated ();
    }

          

}

?>
