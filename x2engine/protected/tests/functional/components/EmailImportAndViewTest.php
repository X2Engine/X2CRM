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






Yii::import('application.tests.WebTestCase');
Yii::import('application.components.*');
Yii::import('application.models.*');
Yii::import('application.modules.users.models.User');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.users.models.User');

/**
 * Test for the end result of an email import (viewing the actual contact page)
 * 
 * @package application.tests.functional.components 
 */
class EmailImportAndViewTest extends X2WebTestCase {

	public $fixtures = array(
		'contact' => 'Contacts',
		'actions' => 'Actions'
	);
        
	/**
	 * This will create a new contact named "Testtwo Contacttwo" in and attach the email-type action
	 */
	public function testCCNewContactImport() {
                $contact = X2Model::model('Contacts')->findByAttributes(array('firstName' => 'Testtwo', 'lastName' => 'Contacttwo', 'email' => 'customer2@prospect.com'));
                $this->assertFalse((bool) $contact);
		$command = new EmailImportBehavior();
		$file = fopen(Yii::app()->basePath . '/tests/data/email/CC_Test_new.eml', 'r');
		$command->eml2records($file);
		fclose($file);
		$contact = X2Model::model('Contacts')->findByAttributes(array('firstName' => 'Testtwo', 'lastName' => 'Contacttwo', 'email' => 'customer2@prospect.com'));
		$this->assertEquals($contact->name, 'Testtwo Contacttwo');
		$this->assertEquals($contact->email, 'customer2@prospect.com');
		$action = X2Model::model('Actions')->findByAttributes(array('type' => 'email', 'associationType' => 'contacts', 'associationId' => $contact->id));
		$this->assertTrue((bool) $action);
		$this->assertRegExp('/%123%/m', $action->actionDescription);
		$this->openX2('contacts/view/' . $contact->id);
		$this->assertTextPresent('first dropbox test');
                $action->delete();
	}

	/**
	 * This should put an email-type action on the preexisting contact named "Testfirstname Testlastname"
	 */
	public function testCCPreexistContactImport() {
		$command = new EmailImportBehavior();
		$file = fopen(Yii::app()->basePath . '/tests/data/email/CC_Test_preexist.eml', 'r');
		$command->eml2records($file);
		fclose($file);
		$contact = X2Model::model('Contacts')->findByAttributes(array('firstName' => 'Testfirstname', 'lastName' => 'Testlastname', 'email' => 'contact@test.com'));
		$this->assertTrue((bool) $contact);
		$action = X2Model::model('Actions')->findByAttributes(array('type' => 'email', 'associationType' => 'contacts', 'associationId' => $contact->id));
		$this->assertTrue((bool) $action);
		$this->assertRegExp('/%456%/m', $action->actionDescription);
		// Test that it's there, on the page
		$this->openX2('contacts/' . $contact->id);
		$this->assertTextPresent('second dropbox test');
                $action->delete();
	}

	// More test ideas:
	// 
	// Test missing last name
	// Test importing action to a contact not assigned to the user
}

?>
