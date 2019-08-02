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






Yii::import('application.components.*');
Yii::import('application.components.util.*');
Yii::import('application.models.*');
Yii::import('application.modules.users.models.User');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.users.models.User');
Yii::import('application.modules.services.models.Services');

/**
 * Test for email import behavior (backend only)
 * 
 * @package application.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailImportBehaviorTest extends X2DbTestCase {

    public static function referenceFixtures(){
        return array(
            'credentials' => 'Credentials',
            'credentialsDefault' => ':x2_credentials_default',
            'cases' => 'Services',
            'users' => 'User'
        );
    }

    public $fixtures = array(
        'contact' => 'Contacts',
        'actions' => 'Actions',
        'actionText' => 'ActionText',
        'events' => 'Events'
    );

    /**
     * Assert that an email history action got attached to a record, and that
     * an event record in the activity feed was also created.
     * 
     * @param X2Model $record The record to which the email should be attached
     * @param string $type The type of email record
     */
    public function assertHasEmail(X2Model $record, $type,$pattern = '/%123%/m'){
        // First, the record must exist.
        $actionAttr = array(
            'type' => $type,
            'assignedTo' => 'testuser',
            'completedBy' => 'testuser',
            'visibility' => 1,
            'associationType' => $record->module,
            'associationId' => $record->id,
            'associationName' => $record->name
        );
        $action = Actions::model()->findByAttributes($actionAttr);
        $this->assertInstanceOf('Actions', $action, 'Failed asserting that the action history record got created.');
        $eventAttr = array(
            'associationId' => $action->associationId,
            'associationType' => $action->associationType,
            'type' => EmailImportBehavior::$typeMap[$type]
        );
        $event = Events::model()->findByAttributes($eventAttr);
        $this->assertInstanceOf('Events',$event,'Failed asserting that the event was created.');
        if($pattern) {
            $this->assertRegExp($pattern, $action->actionDescription, "Failed asserting the email has the identifying pattern $pattern in it.");
        }
    }

    /**
     * Runs a few tests on an instance of the behvaior to see if it correctly
     * recognized the case attachment scenario.
     * 
     * @param EmailImportBehavior $command
     * @param integer|bool $expCaseId The case ID (or false)
     * @param bool $expIsNewRecord Expected return value of
     *  {@link CActiveRecord.getIsNewRecord()}
     */
    public function assertCheckForCaseAttachment(EmailImportBehavior $command,$expCaseId,$expIsNewRecord) {
        $this->assertInstanceOf('Services', $command->case);
        $this->assertEquals($expCaseId,$command->caseId,'Case Id not as expected');
        $this->assertEquals($expIsNewRecord,$command->case->isNewRecord,
                'Is not a new record when it should be or vice versa');
    }

    public function openEmailFile($file){
        return fopen(FileUtil::rpath(Yii::app()->basePath."/tests/data/email/$file"), 'r');
    }

    /**
     * Prepare an email-handling object that will be used for case-associated emails.
     * @param string $fileCont The contents of the email file
     */
    public function prepareCaseEmail($fileCont) {
        $command = new EmailImportBehavior();
        $command->parser = new EmlParse($fileCont);
        $command->checkForCaseAttachment();
        return $command;
    }

    /**
     * Test instantiation of classes and population of their fields.
     */
    public function testInstantiateContactAndAction(){
        $user = User::model()->findByPk(1); // admin
        $contactFromEmail = (object) array('address' => 'customer@prospect.com', 'name' => 'Test Contact');
        $fullName = EmlRegex::fullName($contactFromEmail->name);
        $command = new EmailImportBehavior();
        $command->user = $user;
        $contact = $command->instantiateContact($contactFromEmail);
        $this->assertEquals($contactFromEmail->name, $contact->name);
        $this->assertEquals($contactFromEmail->address, $contact->email);
        $this->assertEquals($user->username, $contact->assignedTo);
        $contact->id = 999;
        $action = $command->createAction($contact,'email','',false);
        $this->assertTrue($action->validate(),sprintf('An action of type %s did not pass validation. The errors were: %s. The attributes were: %s', $action->type, CJSON::encode($action->errors),CJSON::encode($action->attributes)));
        $this->assertEquals('contacts', $action->associationType);
        $this->assertEquals('email', $action->type);
        $this->assertEquals($user->username, $action->assignedTo);
    }

    /**
     * Testing Import (contact and action)
     * 
     * Test importing a contact "from scratch" and importing an email to a 
     * contact record as an action.
     */
    public function testFwNewContactImport(){
        $command = new EmailImportBehavior();
        // Test import from scratch:
        $file = $this->openEmailFile('GMail1.eml');
        $command->eml2records($file);
        fclose($file);
        $contactAttr = array(
            'firstName' => 'Test',
            'lastName' => 'Contact',
            'email' => 'customer@prospect.com',
            'assignedTo' => 'testuser',
            'name' => 'Test Contact'
        );
        $contact = Contacts::model()->findByAttributes($contactAttr);
        $action = $this->assertHasEmail($contact, 'emailFrom');
        
    }

    /**
     * Testing import (action only; preexisting contact)
     */
    public function testFwPreexistingContactImport(){
        $command = new EmailImportBehavior();
        // Test import, having it tied to the preexisting contact in the fixture
        $file = $this->openEmailFile('GMail1_fixture_testAnyone.eml');
        $command->eml2records($file);
        fclose($file);
        $action = X2Model::model('Actions')->findByAttributes(array('associationType' => 'Contacts', 'associationId' => $this->contact('testAnyone')->id));
        $this->assertTrue((bool) $action); // Action exists
        $this->assertEquals('testuser', $action->assignedTo);
        $this->assertEquals($action->associationName, $this->contact('testAnyone')->name);
        $this->assertEquals('emailFrom', $action->type);
        $this->assertEquals($action->associationId, $this->contact('testAnyone')->id);
        $this->assertRegExp('/%123%/m', $action->actionDescription);
        $event = Events::model()->findByAttributes(array('associationId' => $action->associationId, 'associationType' => $action->associationType));
        $this->assertTrue((bool) $event, 'Failed asserting that the event was created.');
        $this->assertEquals('email_from', $event->type);
    }

    /**
     * Test email capture for a contact not assigned to the sender. 
     */
    public function testEmailContactNotAssigned(){
        $command = new EmailImportBehavior();
        $testContact = $this->contact('testUser');
        $contact = X2Model::model('Contacts')->findByPk($testContact->id);
        $contact->assignedTo = 'admin';
        $contact->save();
        $file = $this->openEmailFile('GMail1_fixture_testUser.eml');
        $command->eml2records($file);
        fclose($file);
        $action = X2Model::model('Actions')->findByAttributes(array('associationType' => 'Contacts', 'associationId' => $testContact->id));
        $this->assertTrue((bool) $action); // Action exists
        $this->assertEquals('testuser', $action->assignedTo);
        $this->assertEquals($action->associationName, $testContact->name);
        $this->assertEquals('emailFrom', $action->type);
        $this->assertEquals($action->associationId, $testContact->id);
        $this->assertRegExp('/%123%/m', $action->actionDescription);
        $event = Events::model()->findByAttributes(array('associationId' => $action->associationId, 'associationType' => $action->associationType));
        $this->assertTrue((bool) $event,'Failed asserting that the event was created.');
        $this->assertEquals('email_from', $event->type);
    }

    public function testCCNewContact(){
        $admin = &Yii::app()->settings;
        $command = new EmailImportBehavior();
        $file = $this->openEmailFile('CC_Test_new.eml');
        $command->eml2records($file);
        fclose($file);
        $contact = Contacts::model()->findByAttributes(array('firstName' => 'Testtwo', 'lastName' => 'Contacttwo'));
        $this->assertTrue((bool) $contact);
        $this->assertEquals('testuser', $contact->assignedTo);
        $action = X2Model::model('Actions')->findByAttributes(array('associationType' => 'Contacts'));
        $this->assertTrue((bool) $action); // Action exists
        $this->assertEquals('testuser', $action->assignedTo);
        $this->assertEquals($action->associationName, $contact->name);
        $this->assertEquals('email', $action->type);
        $this->assertEquals($action->associationId, $contact->id);
        $this->assertRegExp('/%123%/m', $action->actionDescription);
        $event = Events::model()->findByAttributes(array('associationId' => $action->associationId, 'associationType' => $action->associationType, 'type' => 'email_sent'));
        $this->assertTrue((bool) $event,'Failed asserting that the event was created.');
    }

    public function testResolveContact(){
        $admin = &Yii::app()->settings;

        // No new contact.
        $admin->emailDropbox->createContact = false;
        $admin->emailDropbox->emptyContact = false;
        $command = new EmailImportBehavior();
        $file = $this->openEmailFile('CC_Test_new_emptyname.eml');
        // Not inserted into the database.
        $this->assertFalse((bool) Contacts::model()->findByAttributes(array('email' => 'new@contact.com')), 'Failed asserting that the contact was not created when it should not have been.');

        // Empty name, empty contact disabled
        $admin->emailDropbox->createContact = true;
        $admin->emailDropbox->emptyContact = false;
        $command = new EmailImportBehavior();
        $file = $this->openEmailFile('CC_Test_new_emptyname.eml');
        $command->eml2records($file);
        // Not inserted into the database.
        $this->assertFalse((bool) Contacts::model()->findByAttributes(array('email' => 'new@contact.com')), 'Failed asserting that the contact was not created when it should not have been.');

        // Empty contact disabled, but with only first name present (should still ignore and not create)
        $admin->emailDropbox->createContact = false;
        $admin->emailDropbox->emptyContact = false;
        $command = new EmailImportBehavior();
        $file = $this->openEmailFile('CC_Test_new_emptylastname.eml');
        // Not inserted into the database.
        $this->assertFalse((bool) Contacts::model()->findByAttributes(array('email' => 'new@contact.com')), 'Failed asserting that the contact was not created when it should not have been.');


        // Empty name, empty contact enabled
        $admin->emailDropbox->createContact = true;
        $admin->emailDropbox->emptyContact = true;
        $command = new EmailImportBehavior();
        $file = $this->openEmailFile('CC_Test_new_emptyname.eml');
        $command->eml2records($file);
        $contact = Contacts::model()->findByAttributes(array('email' => 'new@contact.com'));
        // Inserted into the database.
        $this->assertTrue((bool) $contact, 'Failed asserting that contact with empty first/last name was created.');
        $this->assertEquals('UnknownFirstName UnknownLastName', "{$contact->firstName} {$contact->lastName}");

        // Empty last name, empty contact enabled
        $admin->emailDropbox->createContact = true;
        $admin->emailDropbox->emptyContact = true;
        $command = new EmailImportBehavior();
        $file = $this->openEmailFile('CC_Test_new_emptylastname.eml');
        $command->eml2records($file);
        $contact = Contacts::model()->findByAttributes(array('email' => 'new2@contact.com'));
        // Inserted into the database.
        $this->assertTrue((bool) $contact, 'Failed asserting that contact with empty last name was created.');
        $this->assertEquals('New UnknownLastName', "{$contact->firstName} {$contact->lastName}");
    }

    public function testSendErrorEmail(){
        $command = new EmailImportBehavior();
        $file = Yii::app()->basePath.'/tests/data/email/GMail1.eml';
        $command->parser = new EmlParse(file_get_contents($file));
        // Forwarded message failure (most common)
        $mailer = $command->sendErrorEmail('', 'forward', false);
//		var_dump($mailer);

        $this->assertEquals('Unrecognized forwarded email format', $mailer->Subject);
        $this->assertRegExp('/The email capture script was not able to recognize the format of the forwarded message/', $mailer->Body);
//		echo "\n------------------------------\n";
//		echo $mailer->Body;

        $mailer = $command->sendErrorEmail('Cockadoodledoo', '', false);
//		var_dump($mailer);

        $this->assertEquals('Error while attempting to import data from an email.', $mailer->Subject);
        $this->assertRegExp('/An unexpected error occurred while attempting to import an email/', $mailer->Body);
        $this->assertRegExp('/Cockadoodledoo/', $mailer->Body);
//		echo "\n------------------------------\n";
//		echo $mailer->Body;
//		echo $mailer->Body."\n\n";
//		$mailer = $command->sendErrorEmail('Test test 123.','',false);
    }

    public function testActivityFeed(){
        $command = new EmailImportBehavior();
        $file = $this->openEmailFile('SocialFeed_direct.eml');
        $command->eml2records($file);
        $event = Events::model()->find(array('condition' => 'user="testuser"', 'limit' => 1, 'order' => 'id DESC'));
        $this->assertRegExp('/HELLO EVERYONE, I AM SENDING THIS MESSAGE TO THE X2Engine ACTIVITY FEED/', $event->text);
        $this->assertEquals(1, $event->visibility);
        $this->assertEquals('feed', $event->type);
        $this->assertEquals('Social Post', $event->subtype);
    }

    public function testCheckForCaseAttachment() {
        Yii::app()->settings->emailDropbox->caseFlag = 'case #';
        // Test association with new case
        $file = $this->openEmailFile('GMail1_case_fwd.eml');
        $fileCont = stream_get_contents($file);
        $command = $this->prepareCaseEmail($fileCont);
        $this->assertCheckForCaseAttachment($command,false,true);

        // Now test association with a preexisting case:
        $caseA = $this->cases('dropboxTest_a');
        $fileCont = str_replace('case #','case #'.$caseA->id,$fileCont);
        $command = $this->prepareCaseEmail($fileCont);
        $this->assertCheckForCaseAttachment($command,$caseA->id,false);

        // Now test association with a preexisting case, with instances of the
        // code from earlier in the thread showing up further down:
        $fileCont = str_replace('case #'.$caseA->id,"case #".$caseA->id."\n\ncase#",$fileCont);
        // Run through the same tests as earlier:
        $command = $this->prepareCaseEmail($fileCont);
        $this->assertCheckForCaseAttachment($command,$caseA->id,false);

        // Switch cases by specifying a different case ID:
        $caseB = $this->cases('dropboxTest_b');
        $fileCont = str_replace("case #{$caseA->id}","case #{$caseB->id}\n\ncase #{$caseA->id}\n\ncase#",$fileCont);
        $command = $this->prepareCaseEmail($fileCont);
        $this->assertCheckForCaseAttachment($command, $caseB->id, false);
    }

    public function testCreateCase() {
        $file = $this->openEmailFile('GMail1_case_fwd.eml');
        $fileCont = stream_get_contents($file);
        $caseA = $this->cases('dropboxTest_a');
        $command = $this->prepareCaseEmail($fileCont);
        $command->user = $this->users('testUser');
        $command->createCase($this->contact('testUser'),'emailFrom');
        $this->assertHasEmail($command->case,'emailFrom',false);
    }

    // More test ideas:
    //
    // Test importing action to a contact not assigned to the user
}

?>
