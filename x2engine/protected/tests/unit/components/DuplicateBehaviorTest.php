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




Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.accounts.models.*');

/**
 * @package application.tests.unit.components
 */
class DuplicateBehaviorTest extends X2DbTestCase {

    public $fixtures = array(
        'contacts' => array('Contacts', '.DuplicateTest'),
        'accounts' => array('Accounts', '.DuplicateTest'),
    );

    public function testCheckDuplicates() {
        // First contact has duplicates
        $contact = $this->contacts('contact1');
        $this->assertTrue($contact->checkForDuplicates());
        // Contact 6 is unique
        $uniqueContact = $this->contacts('contact6');
        $this->assertFalse($uniqueContact->checkForDuplicates());

        // Same deal for accounts
        $account = $this->accounts('account1');
        $this->assertTrue($account->checkForDuplicates());
        $uniqueAccount = $this->accounts('account6');
        $this->assertFalse($uniqueAccount->checkForDuplicates());
    }

    public function testDuplicateField() {
        $contact = $this->contacts('contact1');
        $this->assertTrue($contact->checkForDuplicates());
        // After setting dupeCheck to 1, we shouldn't find any duplicates
        $contact->duplicateChecked();
        $this->assertFalse($contact->checkForDuplicates());
        // Resetting dupeCheck means we find duplicates again
        $contact->resetDuplicateField();
        $this->assertTrue($contact->checkForDuplicates());

        // Same deal for accounts
        $account = $this->accounts('account1');
        $this->assertTrue($account->checkForDuplicates());

        $account->duplicateChecked();
        $this->assertFalse($account->checkForDuplicates());

        $account->resetDuplicateField();
        $this->assertTrue($account->checkForDuplicates());
    }

    public function testAfterSave() {
        // After save if a duplicate defining field (name, email) is changed,
        // dupeCheck should be reset
        $contact = $this->contacts('contact1');
        $this->assertTrue($contact->checkForDuplicates());

        $contact->duplicateChecked();
        $this->assertFalse($contact->checkForDuplicates());

        $contact->email = 'alpha@gamma.com';
        $contact->save();
        $this->assertTrue($contact->checkForDuplicates());

        // Same for accounts, but fields are name, tickerSymbol, website.
        $account = $this->accounts('account1');
        $this->assertTrue($account->checkForDuplicates());

        $account->duplicateChecked();
        $this->assertFalse($account->checkForDuplicates());

        $account->tickerSymbol = 'TEST';
        $account->save();
        $this->assertTrue($account->checkForDuplicates());
    }

    public function testGetDuplicates() {
        // We have 8 total duplicates
        $contact = $this->contacts('contact1');
        $this->assertEquals(8, $contact->countDuplicates());
        // The getDuplicates method only returns 5
        $duplicates = $contact->getDuplicates();
        $this->assertEquals(5, count($duplicates));
        // Unless we pass the optional getAll parameter
        $allDuplicates = $contact->getDuplicates(true);
        $this->assertEquals(8, count($allDuplicates));
    }

    public function testMarkDuplicate() {
        // Confirm that markDuplicate field sets all relevant fields correctly
        Yii::app()->params->adminProf = Profile::model()->findByPk(1);
        $contact = $this->contacts('contact1');
        $this->assertEquals(0, $contact->dupeCheck);
        $this->assertEquals(1, $contact->visibility);
        $this->assertEquals('Anyone', $contact->assignedTo);
        $contact->markAsDuplicate();
        $this->assertEquals(1, $contact->dupeCheck);
        $this->assertEquals(0, $contact->visibility);
        $this->assertEquals('Anyone', $contact->assignedTo);

        $contact->markAsDuplicate('delete');
        $contact = Contacts::model()->findByPk(1);
        $this->assertEquals(null, $contact);

        // Same for accounts
        $account = $this->accounts('account1');
        $this->assertEquals(0, $account->dupeCheck);
        $this->assertEquals('Anyone', $account->assignedTo);
        $account->markAsDuplicate();
        $this->assertEquals(1, $account->dupeCheck);
        $this->assertEquals('Anyone', $account->assignedTo);

        $account->markAsDuplicate('delete');
        $account = Accounts::model()->findByPk(1);
        $this->assertEquals(null, $account);
    }

    public function testHideDuplicates() {
        // Hiding duplicates shouldn't delete any contacts
        Yii::app()->params->adminProf = Profile::model()->findByPk(1);
        $contact = $this->contacts('contact1');
        $this->assertEquals(8, $contact->countDuplicates());

        $duplicates = $contact->getDuplicates(true);
        $this->assertEquals(8, count($duplicates));

        $newDuplicates = $contact->getDuplicates(true);
        $contact->hideDuplicates();
        $this->assertEquals(0, $contact->countDuplicates());

        $this->assertEquals(8, count($newDuplicates));

        // Spot check the fields of one of the duplicates
        $dupeContact = $this->contacts('contact2');
        $this->assertEquals(1, $dupeContact->dupeCheck);
        $this->assertEquals(0, $dupeContact->visibility);
        $this->assertEquals('Anyone', $dupeContact->assignedTo);
    }

    public function testDeleteDuplicates() {
        // Deleting duplicates should remove them
        $contact = $this->contacts('contact1');
        $this->assertEquals(8, $contact->countDuplicates());
        $duplicates = $contact->getDuplicates(true);
        $this->assertEquals(8, count($duplicates));
        $contact->deleteDuplicates();
        $this->assertEquals(0, $contact->countDuplicates());
        $newDuplicates = $contact->getDuplicates(true);
        $this->assertEquals(0, count($newDuplicates));
        // Spot check a duplicate to ensure deletion was successful
        $dupeContact = $this->contacts('contact2');
        $this->assertEquals(null, $dupeContact);
    }

}
?>
