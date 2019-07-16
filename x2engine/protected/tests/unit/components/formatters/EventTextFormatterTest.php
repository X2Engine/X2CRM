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




Yii::import('application.modules.accounts.models.Accounts');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.Contacts');

Yii::import('application.modules.emailInboxes.models.EmailInboxes');

Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.quotes.models.*');
Yii::import('application.modules.topics.models.*');
Yii::import('application.modules.services.models.Services');
Yii::import('application.modules.workflow.models.*');

class EventTextFormatterTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'events' => array('Events', '.GetText'),
            'eventsToMedia' => ':x2_events_to_media',
            'users' => 'User',
            'contacts' => 'Contacts',
            'notifications' => 'Notification',
            
            'emailInbox' => 'EmailInboxes',
            
            'actions' => array('Actions','.EventText'),
            'actionText' => array('ActionText','.EventText'),
            'actionToRecord' => 'ActionToRecord',
            'accounts' => 'Accounts',
            'workflows' => array('Workflow', '.WorkflowTests'),
            'workflowStages' => array('WorkflowStage', '.WorkflowTests'),
            'quotes' => 'Quote',
            
            'anonContacts' => array('AnonContact', '.FingerprintTest'),
            
            'services' => 'Services',
            'media' => 'Media',
            'profile' => 'Profile',
            'topics' => 'Topics',
            'topicReplies' => 'TopicReplies',
            'docs' => 'Docs'
        );
    }

    public function setUp() {
        TestingAuxLib::loadControllerMock();
        parent::setUp();
    }

    public function tearDown() {
        Yii::app()->params->isMobileApp = false;
        TestingAuxLib::restoreController();
        parent::tearDown();
    }

    public function testGetText() {
        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('longEvent');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> : Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas maximus dui et dolor convallis malesuada. Nulla aliquam congue mi, in vestibulum enim dictum in. Nullam in nulla vitae ex malesuada feugiat. Integer quis diam ornare, molestie nibh ullamcorper, sollicitudin velit. In placerat ex non lacus dapibus congue. Curabitur tristique ligula mi, non hendrerit arcu dignissim in. Duis pulvinar felis a velit auctor imperdiet. Morbi porttitor sapien et purus porttitor mattis.

Etiam eget iaculis nisl. Duis id malesuada orci. Mauris imperdiet ut elit rhoncus finibus. Mauris lacinia non sem a suscipit. Etiam id nibh sit amet nunc suscipit dictum ut eget.',
                $text);

        $truncatedText = $event->getText(array('truncated' => true));
        $this->assertNotEmpty($truncatedText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> : Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas maximus dui et dolor convallis malesuada. Nulla aliquam congue mi, in vestibulum enim dictum in. Nullam in nulla vitae ex ...',
                $truncatedText);
    }

    public function testGetAuthorText() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('feedEvent');

        $youAuthorText = EventTextFormatter::getAuthorText($event);
        $this->assertNotEmpty($youAuthorText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> ',
                $youAuthorText);

        $event2 = $this->events('feedEvent2');
        $otherAuthorText = EventTextFormatter::getAuthorText($event2);
        $this->assertNotEmpty($otherAuthorText);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/2"><span>Sales Rep</span></a> ',
                $otherAuthorText);

        TestingAuxLib::suLogin('testuser');

        $testUserAuthorText = EventTextFormatter::getAuthorText($event2);
        $this->assertNotEmpty($testUserAuthorText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/2">You</a> ',
                $testUserAuthorText);

        $testUserAdminAuthorText = EventTextFormatter::getAuthorText($event);
        $this->assertNotEmpty($testUserAdminAuthorText);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/1"><span>Web Admin</span></a> ',
                $testUserAdminAuthorText);
    }

    public function testRenderFrameLink() {
        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('recordCreateActionCall');
        $frameLink = EventTextFormatter::renderFrameLink($event, array());
        $this->assertNotEmpty($frameLink);
        $this->assertEquals('<a class="action-frame-link" data-action-id="3" data-action-type="call" data-text-only="1" href="#">comment</a>',
                $frameLink);

        $event2 = $this->events('recordCreateActionNoRecord');
        $noRecordFrameLink = EventTextFormatter::renderFrameLink($event2,
                        array());
        $this->assertNotEmpty($noRecordFrameLink);
        $this->assertEquals('action', $noRecordFrameLink);

        $event3 = $this->events('recordCreateAction');
        $actionFrameLink = EventTextFormatter::renderFrameLink($event3, array());
        $this->assertNotEmpty($actionFrameLink);
        $this->assertEquals('<a class="action-frame-link" data-action-id="1" href="#">action</a>',
                $actionFrameLink);

        Yii::app()->params->isMobileApp = true;
        $mobileFrameLink = EventTextFormatter::renderFrameLink($event, array());
        $this->assertNotEmpty($mobileFrameLink);
        $this->assertEquals('action', $mobileFrameLink);
    }

    public function testFormatNotif() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('notifEvent');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('Test notification', $text);

        $event->associationId = null;
        $text2 = $event->getText();
        $this->assertNotEmpty($text2);
        $this->assertEquals('Notification not found', $text2);
    }

    public function testFormatRecordCreate() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('recordCreateEvent');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> created a new contact, <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>',
                $text);

        $event2 = $this->events('recordCreateEvent2');
        $notFoundText = $event2->getText();
        $this->assertNotEmpty($notFoundText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> created a new contact, but it could not be found.',
                $notFoundText);

        
        $event3 = $this->events('recordCreateEmailInbox');
        $emailInboxText = $event3->getText();
        $this->assertNotEmpty($emailInboxText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> created a new email inbox',
                $emailInboxText);
        

        $event4 = $this->events('recordCreateNoAuthor');
        $noAuthorText = $event4->getText();
        $this->assertNotEmpty($noAuthorText);
        $this->assertEquals('A new contact, <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>, has been created.',
                $noAuthorText);

        $event5 = $this->events('recordCreateNoAuthorNoRecord');
        $noAuthorNoRecordText = $event5->getText();
        $this->assertNotEmpty($noAuthorNoRecordText);
        $this->assertEquals('A contact was created, but it could not be found.',
                $noAuthorNoRecordText);

        $event6 = $this->events('recordCreateWithDeletion');

        $deletionEvent = new Events();
        $deletionEvent->type = 'record_deleted';
        $deletionEvent->associationType = $event6->associationType;
        $deletionEvent->associationId = $event6->associationId;
        $deletionEvent->text = 'Deleted Contact';
        $deletionEvent->user = 'admin';
        $deletionEvent->save();

        $recordCreatedDeletedText = $event6->getText();
        $this->assertNotEmpty($recordCreatedDeletedText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> created a new contact, Deleted Contact. It has been deleted.',
                $recordCreatedDeletedText);

        $event7 = $this->events('recordCreateWithDeletionNoAuthor');
        $recordCreatedDeletedNoAuthorText = $event7->getText();
        $this->assertNotEmpty($recordCreatedDeletedNoAuthorText);
        $this->assertEquals('A contact, Deleted Contact, was created. It has been deleted.',
                $recordCreatedDeletedNoAuthorText);

        $deletionEvent->delete();
    }

    public function testFormatRecordCreateAction() {

        $event = $this->events('recordCreateAction');
        $actionText = $event->getText();
        $this->assertNotEmpty($actionText);
        $this->assertEquals('A new <a class="action-frame-link" data-action-id="1" href="#">action</a> associated with the contact <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> has been assigned to <a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/1"><span>Web Admin</span></a>',
                $actionText);

        $event2 = $this->events('recordCreateActionUnassigned');
        $actionTextUnassigned = $event2->getText();
        $this->assertNotEmpty($actionTextUnassigned);
        $this->assertEquals('A new <a class="action-frame-link" data-action-id="2" href="#">action</a> associated with the contact <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> has been created.',
                $actionTextUnassigned);

        $event3 = $this->events('recordCreateActionCall');
        $callActionText = $event3->getText();
        $this->assertNotEmpty($callActionText);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/1"><span>Web Admin</span></a> logged a call (duration unknown) with <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>: Test call action',
                $callActionText);

        $event4 = $this->events('recordCreateActionCall2');
        $unassignedCallActionText = $event4->getText();
        $this->assertNotEmpty($unassignedCallActionText);
        $this->assertEquals('Someone logged a call (duration unknown) with <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>: Test unassigned call action',
                $unassignedCallActionText);

        $event5 = $this->events('recordCreateActionNote');
        $noteActionText = $event5->getText();
        $this->assertNotEmpty($noteActionText);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/1"><span>Web Admin</span></a> posted a comment on <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>: Test note action',
                $noteActionText);

        $event6 = $this->events('recordCreateActionNote2');
        $unassignedNoteActionText = $event6->getText();
        $this->assertNotEmpty($unassignedNoteActionText);
        $this->assertEquals('Someone posted a comment on <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>: Test unassigned note action',
                $unassignedNoteActionText);

        $event7 = $this->events('recordCreateActionTime');
        $timeActionText = $event7->getText();
        $this->assertNotEmpty($timeActionText);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/1"><span>Web Admin</span></a> logged 0 minutes on <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>: Test time action',
                $timeActionText);

        $event8 = $this->events('recordCreateActionTime2');
        $unassignedTimeActionText = $event8->getText();
        $this->assertNotEmpty($unassignedTimeActionText);
        $this->assertEquals('Someone logged 0 minutes on <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>: Test unassigned time action',
                $unassignedTimeActionText);

        $event9 = $this->events('recordCreateAccountAction');
        $accountActionText = $event9->getText();
        $this->assertNotEmpty($accountActionText);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/1"><span>Web Admin</span></a> created a new action, <a class="action-frame-link" data-action-id="9" href="#">test</a>',
                $accountActionText);

        $event10 = $this->events('recordCreateAccountAction2');
        $targetedAccountActionText = $event10->getText();
        $this->assertNotEmpty($targetedAccountActionText);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/1"><span>Web Admin</span></a> created a new action for <a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/2"><span>Sales Rep</span></a>, <a class="action-frame-link" data-action-id="10" href="#">test</a>',
                $targetedAccountActionText);

        $event11 = $this->events('recordCreateAccountAction3');
        $unassignedAccountAction = $event11->getText();
        $this->assertNotEmpty($unassignedAccountAction);
        $this->assertEquals('A new action, <a class="action-frame-link" data-action-id="11" href="#">test</a>, has been created.',
                $unassignedAccountAction);
    }

    public function testFormatWebleadCreate() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('webleadCreate');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('A new web lead has come in: <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>',
                $text);

        $event2 = $this->events('webleadCreateDeletedNoRecord');
        $webleadNotFoundText = $event2->getText();
        $this->assertNotEmpty($webleadNotFoundText);
        $this->assertEquals('A new web lead has come in, but it could not be found.',
                $webleadNotFoundText);

        $event3 = $this->events('webleadCreateDeleted');

        $deletionEvent = new Events();
        $deletionEvent->type = 'record_deleted';
        $deletionEvent->associationType = $event3->associationType;
        $deletionEvent->associationId = $event3->associationId;
        $deletionEvent->text = 'Deleted Weblead';
        $deletionEvent->user = 'admin';
        $deletionEvent->save();

        $webleadDeletedText = $event3->getText();
        $this->assertNotEmpty($webleadDeletedText);
        $this->assertEquals('A new web lead has come in: Deleted Weblead. It has been deleted.',
                $webleadDeletedText);

        $deletionEvent->delete();
    }

    public function testFormatRecordDeleted() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('recordDeleted');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> deleted the contact, Deleted Contact.',
                $text);

        $event->user = null;
        $text2 = $event->getText();
        $this->assertNotEmpty($text2);
        $this->assertEquals('The contact, Deleted Contact, was deleted.', $text2);
    }

    public function testFormatWorkflowStart() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('workflowStart');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> started the process stage "Received Resume" for the contact <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>',
                $text);

        $event2 = $this->events('workflowStartNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> started a process stage, but the associated contact was not found.',
                $noRecordText);

        $event3 = $this->events('workflowStartNoWorkflowAction');
        $noActionText = $event3->getText();
        $this->assertNotEmpty($noActionText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> started a process stage, but the process record could not be found.',
                $noActionText);
        
        $event4 = $this->events('workflowStartWorkflowActionNoStage');
        $noStageText = $event4->getText();
        $this->assertNotEmpty($noStageText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> started a process stage, but the process record could not be found.',
                $noStageText);
    }

    public function testFormatWorkflowComplete() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('workflowComplete');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> completed the process stage "Received Resume" for the contact <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>',
                $text);

        $event2 = $this->events('workflowCompleteNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> completed a process stage, but the associated contact was not found.',
                $noRecordText);

        $event3 = $this->events('workflowCompleteNoWorkflowAction');
        $noActionText = $event3->getText();
        $this->assertNotEmpty($noActionText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> completed a process stage, but the process record could not be found.',
                $noActionText);
    }

    public function testFormatWorkflowRevert() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('workflowRevert');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> reverted the process stage "Received Resume" for the contact <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>',
                $text);

        $event2 = $this->events('workflowRevertNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> reverted a process stage, but the associated contact was not found.',
                $noRecordText);

        $event3 = $this->events('workflowRevertNoWorkflowAction');
        $noActionText = $event3->getText();
        $this->assertNotEmpty($noActionText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> reverted a process stage, but the process record could not be found.',
                $noActionText);
    }

    public function testFormatStructuredFeed() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('structuredFeedEvent');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> : Self social post',
                $text);

        $event2 = $this->events('structuredFeedEvent2');
        $text2 = $event2->getText();
        $this->assertNotEmpty($text2);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/2"><span>Sales Rep</span></a>  &raquo; <a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/3"><span>Sales2 Rep2</span></a>: Targeted social post',
                $text2);
    }

    public function testFormatFeed() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('feedEvent');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> : Self social post',
                $text);

        $event2 = $this->events('feedEvent2');
        $text2 = $event2->getText();
        $this->assertNotEmpty($text2);
        $this->assertEquals('<a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/2"><span>Sales Rep</span></a>  &raquo; <a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/3"><span>Sales2 Rep2</span></a>: Targeted social post',
                $text2);
    }

    public function testFormatEmailSent() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('quoteEmailEvent');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> issued the quote "<a href="http://localhost/index-test.php/quotes/8"><span>Pro, Training &amp; Data Migration</span></a>" via email',
                $text);

        $event2 = $this->events('quoteEmailEvent2');
        $quoteEmailNoRecordText = $event2->getText();
        $this->assertNotEmpty($quoteEmailNoRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> issued a quote by email, but that record could not be found.',
                $quoteEmailNoRecordText);

        $quoteDeletionEvent = new Events();
        $quoteDeletionEvent->type = 'record_deleted';
        $quoteDeletionEvent->text = 'Deleted Quote';
        $quoteDeletionEvent->associationType = $event2->associationType;
        $quoteDeletionEvent->associationId = $event2->associationId;
        $quoteDeletionEvent->user = 'admin';
        $quoteDeletionEvent->save();

        $quoteEmailNoRecordWithDeletionText = $event2->getText();
        $this->assertNotEmpty($quoteEmailNoRecordWithDeletionText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> issued a quote by email, but that record has been deleted.',
                $quoteEmailNoRecordWithDeletionText);

        $quoteDeletionEvent->delete();

        $event3 = $this->events('invoiceEmailEvent');
        $invoiceText = $event3->getText();
        $this->assertNotEmpty($invoiceText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> issued the invoice "<a href="http://localhost/index-test.php/quotes/8"><span>Pro, Training &amp; Data Migration</span></a>" via email',
                $invoiceText);

        $event4 = $this->events('invoiceEmailEvent2');
        $invoiceEmailNoRecordText = $event4->getText();
        $this->assertNotEmpty($invoiceEmailNoRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> issued an invoice by email, but that record could not be found.',
                $invoiceEmailNoRecordText);

        $invoiceDeletionEvent = new Events();
        $invoiceDeletionEvent->type = 'record_deleted';
        $invoiceDeletionEvent->text = 'Deleted Invoice';
        $invoiceDeletionEvent->associationType = $event4->associationType;
        $invoiceDeletionEvent->associationId = $event4->associationId;
        $invoiceDeletionEvent->user = 'admin';
        $invoiceDeletionEvent->save();

        $invoiceEmailNoRecordWithDeletionText = $event4->getText();
        $this->assertNotEmpty($invoiceEmailNoRecordWithDeletionText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> issued an invoice by email, but that record has been deleted.',
                $invoiceEmailNoRecordWithDeletionText);

        $invoiceDeletionEvent->delete();

        $event5 = $this->events('genericEmailEvent');
        $genericEmailText = $event5->getText();
        $this->assertNotEmpty($genericEmailText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> sent an email to the contact <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>',
                $genericEmailText);

        $event6 = $this->events('genericEmailEvent2');
        $genericEmailNoRecordText = $event6->getText();
        $this->assertNotEmpty($genericEmailNoRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> sent an email to a contact, but that record could not be found.',
                $genericEmailNoRecordText);

        $genericDeletionEvent = new Events();
        $genericDeletionEvent->type = 'record_deleted';
        $genericDeletionEvent->text = 'Deleted Email';
        $genericDeletionEvent->associationType = $event6->associationType;
        $genericDeletionEvent->associationId = $event6->associationId;
        $genericDeletionEvent->user = 'admin';
        $genericDeletionEvent->save();

        $genericDeletedEmailText = $event6->getText();
        $this->assertNotEmpty($genericDeletedEmailText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> sent an email to a contact, but that record has been deleted.',
                $genericDeletedEmailText);

        $genericDeletionEvent->delete();
    }

    public function testFormatEmailOpen() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('quoteEmailOpen');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> has opened a quote email!',
                $text);

        $event2 = $this->events('quoteEmailOpen2');
        $quoteNotFoundText = $event2->getText();
        $this->assertNotEmpty($quoteNotFoundText);
        $this->assertEquals('A contact has opened a quote email, but that contact cannot be found.',
                $quoteNotFoundText);

        $event3 = $this->events('invoiceEmailOpen');
        $invoiceText = $event3->getText();
        $this->assertNotEmpty($invoiceText);
        $this->assertEquals('<a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> has opened an invoice email!',
                $invoiceText);

        $event4 = $this->events('invoiceEmailOpen2');
        $invoiceNotFoundText = $event4->getText();
        $this->assertNotEmpty($invoiceNotFoundText);
        $this->assertEquals('A contact has opened an invoice email, but that contact cannot be found.',
                $invoiceNotFoundText);

        $event5 = $this->events('genericEmailOpen');
        $genericEmailText = $event5->getText();
        $this->assertNotEmpty($genericEmailText);
        $this->assertEquals('<a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> has opened an email!',
                $genericEmailText);

        $event6 = $this->events('genericEmailOpen2');
        $genericEmailNotFoundText = $event6->getText();
        $this->assertNotEmpty($genericEmailNotFoundText);
        $this->assertEquals('A contact has opened an email, but that contact cannot be found.',
                $genericEmailNotFoundText);
    }

    public function testFormatEmailClick() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('emailClicked');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> opened a link in an email campaign and is visiting your website!',
                $text);

        $event2 = $this->events('emailClicked2');
        $text2 = $event2->getText();
        $this->assertNotEmpty($text2);
        $this->assertEquals('A contact has opened a link in an email campaign, but that contact cannot be found.',
                $text2);
    }

    public function testFormatWebActivity() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('webActivity');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> is currently on your website!',
                $text);

        $event2 = $this->events('webActivity2');
        $webActivityNoRecordText = $event2->getText();
        $this->assertNotEmpty($webActivityNoRecordText);
        $this->assertEquals('A contact was on your website, but that contact cannot be found.',
                $webActivityNoRecordText);

        
        $event3 = $this->events('webActivityAnonContact');
        $webActivityAnonContactText = $event3->getText();
        $this->assertNotEmpty($webActivityAnonContactText);
        $this->assertEquals('Anonymous contact <a href="http://localhost/index-test.php/marketing/marketing/anonContactView/1"><span>1</span></a> is currently on your website!',
                $webActivityAnonContactText);
        
    }

    public function testFormatCaseEscalated() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('caseEscalated');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> escalated service case <a href="http://localhost/index-test.php/services/1"><span>1</span></a> to <a style="text-decoration:none;" href="http://localhost/index-test.php/profile/view/2"><span>Sales Rep</span></a>',
                $text);

        $event2 = $this->events('caseEscalated2');
        $caseEscalatedNoRecordText = $event2->getText();
        $this->assertNotEmpty($caseEscalatedNoRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> escalated a service case but that case could not be found.',
                $caseEscalatedNoRecordText);
    }

    public function testFormatCalendarEvent() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('calendarEvent');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index.php/calendar/calendar/index">Calendar</a> event: Test action',
                $text);

        $event2 = $this->events('calendarEventNoRecord');
        $calendarEventNoRecordText = $event2->getText();
        $this->assertNotEmpty($calendarEventNoRecordText);
        $this->assertEquals('<a href="http://localhost/index.php/calendar/calendar/index">Calendar</a> event: event not found.',
                $calendarEventNoRecordText);

        Yii::app()->params->isMobileApp = true;
        $mobileText = $event->getText();
        $this->assertNotEmpty($mobileText);
        $this->assertEquals('Calendar event: Test action', $mobileText);
    }

    public function testFormatActionReminder() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('actionReminder');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('Reminder! The following action is due now: <a class="action-frame-link" data-action-id="1" href="#">test</a>',
                $text);

        $event2 = $this->events('actionReminderNoRecord');
        $reminderNoRecordText = $event2->getText();
        $this->assertNotEmpty($reminderNoRecordText);
        $this->assertEquals('An action is due now, but the record could not be found.',
                $reminderNoRecordText);
    }

    public function testFormatActionComplete() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('actionComplete');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> completed the following action: <a class="action-frame-link" data-action-id="1" href="#">test</a>',
                $text);

        $event2 = $this->events('actionCompleteNoRecord');
        $completeNoRecordText = $event2->getText();
        $this->assertNotEmpty($completeNoRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> completed an action, but the record could not be found.',
                $completeNoRecordText);
    }

    public function testFormatDocUpdate() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('docUpdate');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> updated a document, <a href="http://localhost/index-test.php/docs/1"><span>quis</span></a>',
                $text);

        $event2 = $this->events('docUpdateNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> updated a document, but the record could not be found.',
                $noRecordText);
    }

    public function testFormatEmailFrom() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('emailFrom');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> received an email from a contact, <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>',
                $text);

        $event2 = $this->events('emailFromNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> received an email from a contact, but that record could not be found.',
                $noRecordText);

        $deletionEvent = new Events();
        $deletionEvent->type = 'record_deleted';
        $deletionEvent->associationType = $event2->associationType;
        $deletionEvent->associationId = $event2->associationId;
        $deletionEvent->user = 'admin';
        $deletionEvent->save();

        $noRecordDeletionEventText = $event2->getText();
        $this->assertNotEmpty($noRecordDeletionEventText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> received an email from a contact, but that record has been deleted.',
                $noRecordDeletionEventText);

        $deletionEvent->delete();

        $event3 = $this->events('emailFromWithAction');
        $withActionText = $event3->getText();
        $this->assertNotEmpty($withActionText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> received an email <a class="action-frame-link" data-action-id="14" href="#">test</a> from the contacts, <a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a>, <a class="contact-name" href="http://localhost/index-test.php/contacts/67890"><span>Testfirstnametwo Testlastnametwo</span></a>, regarding the service <a href="http://localhost/index-test.php/services/1"><span>1</span></a>',
                $withActionText);

        $event4 = $this->events('emailFromWithActionNoRecord');
        $withActionNoRecordText = $event4->getText();
        $this->assertNotEmpty($withActionNoRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> received an email from a action, but that record could not be found.',
                $withActionNoRecordText);
    }

    public function testFormatVoipCall() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('voipCall');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a class="contact-name" href="http://localhost/index-test.php/contacts/12345"><span>Testfirstname Testlastname</span></a> called.',
                $text);

        $event2 = $this->events('voipCallNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('Call from a contact whose record could not be found.',
                $noRecordText);

        $deletionEvent = new Events();
        $deletionEvent->type = 'record_deleted';
        $deletionEvent->associationType = $event2->associationType;
        $deletionEvent->associationId = $event2->associationId;
        $deletionEvent->user = 'admin';
        $deletionEvent->save();

        $noRecordTextWithDeletion = $event2->getText();
        $this->assertNotEmpty($noRecordTextWithDeletion);
        $this->assertEquals('A contact called, but the contact record has been deleted.',
                $noRecordTextWithDeletion);

        $deletionEvent->delete();
    }

    public function testFormatMedia() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('media');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a>: <br><br><img class="attachment-img" src="http://localhost/index-test.php/media/media/getFile?id=1&key=d84e7834f79223ad17981fe3f9e61b12ae5c012345cbc29bcfe1d7b982edc9b9" alt="" /><br>',
                $text);

        $truncatedText = $event->getText(array('truncated' => true));
        $this->assertNotEmpty($truncatedText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a>: <br><br>',
                $truncatedText);
        
        $event2 = $this->events('mediaNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a>: <br>Media file not found.',
                $noRecordText);
    }

    public function testFormatTopicReply() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');
        TestingAuxLib::loadControllerMock();

        $event = $this->events('topicReply');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> posted a new reply to <a href="http://localhost/index-test.php/topics/topics/view/1?replyId=1">Test Topic</a>.',
                $text);

        $event2 = $this->events('topicReplyNoRecord');
        $noRecordText = $event2->getText();
        $this->assertNotEmpty($noRecordText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> posted a new reply to a topic, but that reply has been deleted.',
                $noRecordText);

        Yii::app()->params->isMobileApp = true;
        $mobileText = $event->getText();
        $this->assertNotEmpty($mobileText);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/mobileView/1">You</a> posted a new reply to Test Topic.',
                $mobileText);
    }

    public function testFormatDefault() {

        TestingAuxLib::loadX2NonWebUser();
        TestingAuxLib::suLogin('admin');

        $event = $this->events('invalidType');
        $text = $event->getText();
        $this->assertNotEmpty($text);
        $this->assertEquals('<a href="http://localhost/index-test.php/profile/1">You</a> This event has a type not covered by the formatter.',
                $text);
    }

}
