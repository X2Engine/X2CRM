<?php

Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.quotes.models.*');
Yii::import('application.modules.user.models.*');
Yii::import('application.components.ResponseBehavior');

/**
 * Test of the {@link InlineEmail} class.
 *
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class InlineEmailTest extends X2DbTestCase {
    // Set to 1 to enable testing actual sending of email.

    const TESTDELIVERY = 0;

    public static function referenceFixtures(){
        return array(
            'docs' => array ('Docs', '.InlineEmailTest'),
            'quote' => 'Quote',
            'contacts' => 'Contacts',
            'accounts' => array ('Accounts', '.InlineEmailTest'),
            'profile' => 'Profile',
            'user' => 'User',
            'credentials' => 'Credentials'
        );
    }

    public $fixtures = array(
        'actions' => 'Actions',
        'actionText' => 'ActionText',
        'trackEmail' => 'TrackEmail',
        'events' => 'Events',
    );

    public $method = 'mail'; // Set to the delivery type...

    public $sender = array('name' => "", 'address' => ''); // Sender email..
    
    // Recipient email addresses...
    public $recipient = array(array(/* Name: */ 'test email name', /* Address: */ TEST_EMAIL_TO)); 
    /**
     * Email model.
     * @var InlineEmail
     */

    public $eml;

    public function testGetCredentials(){
        $this->eml = new InlineEmail();
        $this->eml->credId = $this->credentials('gmail1')->id;
        $this->assertTrue($this->eml->credentials instanceof Credentials);
        $this->assertEquals($this->credentials('gmail1')->id, $this->eml->credentials->id);
        $this->assertEquals($this->credentials('gmail1')->auth->senderName, $this->eml->credentials->auth->senderName);
    }

    /**
     * Test the magic PHPMailer getter
     */
    public function testGetMailer(){
        $this->eml = new InlineEmail();
        $cred = $this->credentials('gmail1');
        $this->eml->credId = $cred->id;
        $mailer = $this->eml->mailer;
        $this->assertTrue($mailer instanceof PHPMailer, 'Failed asserting PHPMailer instantiated.');
        $this->assertEquals('smtp', $mailer->Mailer);
        $this->assertEquals($cred->auth->security, $mailer->SMTPSecure, 'Security type not set properly');
        $this->assertEquals($cred->auth->senderName, $mailer->FromName, 'From name not set properly');
        $this->assertEquals($cred->auth->email, $mailer->Sender, 'Sender address not set properly');
        $this->assertTrue($mailer->SMTPAuth, 'SMTP auth not set to true and it needs to be');
    }

    public function assertActionCreated($type, $message = null){
        $action = Actions::model()->findBySql("SELECT * FROM x2_actions WHERE type='$type' ORDER BY createDate DESC,id DESC LIMIT 1");
        $this->assertTrue((bool) $action, "Failed asserting that an action was created. $type");
        $associatedModel = X2Model::getAssociationModel($action->associationType, $action->associationId);
        // Test that the models are identical:
        $this->eml->targetModel->refresh();
        foreach(array('myModelName', 'id', 'name', 'lastUpdated', 'createDate', 'assignedTo', 'status') as $property){
            if($this->eml->targetModel->hasProperty($property) && $associatedModel->hasProperty($property))
                $this->assertEquals($this->eml->targetModel->$property, $associatedModel->$property, "Failed asserting that an action's associated model record was the same, property: $property. $message");
        }
        // Assert that the username fields are set properly:
        foreach(array('assignedTo', 'completedBy') as $attr){
            $this->assertEquals('testuser', $action->assignedTo, "Failed asserting that $attr was set properly on the action record. $message");
        }
    }

    public function assertBodyHasTrackingImage($message = null){
        $tt = InlineEmail::TRACKTAG;
        include(realpath(Yii::app()->basePath.'/../webConfig.php'));
        $url = Yii::app()->getAbsoluteBaseUrl();
        $image = "<img src=\"".rtrim($url,'/')."/index.php/actions/emailOpened?uid={$this->eml->uniqueId}&type=open\"/>";
        $fullImage = InlineEmail::insertedPattern('track', $image);
        $this->assertEquals(
            $image, $this->eml->trackingImage,
            'Failed asserting that the tracking image is as expected. '.$message);
        $this->assertTrue(
            strpos($this->eml->message, $fullImage) !== false,
            'Failed asserting that the body contains the tracking image; body ='.
                $this->eml->message.$message);
        $this->assertRegExp(
            InlineEmail::UIDREGEX, $this->eml->trackingImage,
            'Failed asserting that the tracking image has a unique id.'.$message);
        preg_match(InlineEmail::UIDREGEX, $this->eml->trackingImage, $matchId);
        $this->assertEquals(
            $this->eml->uniqueId, $matchId[1],
            "Failed asserting that the UID in the image tracking tag matches the one in the model.".
            $message);
    }

    public function assertModelUpdated($modelFixture, $modelAlias, $message = null){
        $model = $this->$modelFixture($modelAlias);
        $model->refresh();
        // The whole insertion process should NOT take more than ten seconds!
        if($model->hasProperty('lastActivity'))
            $this->assertLessThan(10, abs($model->lastActivity - time()), 'Failed asserting lastActivity was set on the contact. '.$message);
    }

    /**
     * To make tests faster, all the non-database intensive tests (i.e. body
     * insertion) are consolidated in here.
     *
     * @return type
     */
    public function testFormattingFunctions(){
        // Test body insertion:
        $this->eml = new InlineEmail();
        $this->eml->message = '<html><head></head><body></body>';
        $contact = $this->contacts('testAnyone');
        $this->eml->to = "\"$contact->name\" <{$contact->email}>";
        $this->eml->parseMailingList('to');
        $this->eml->targetModel = $contact;
        $this->eml->insertTrackingImage();
        $this->assertBodyHasTrackingImage(' On case 1: user sumbitting new/blank email without tracking image.');
        $message = $this->eml->message;
        $this->eml = new InlineEmail();
        $this->eml->message = $message;
        $this->eml->to = "\"$contact->name\" <{$contact->email}>";
        $this->eml->parseMailingList('to');
        $this->eml->targetModel = $contact;
        $this->eml->insertTrackingImage();
        $this->assertBodyHasTrackingImage(' On case 2: user submitting modified body with preexisting tracking image.');

        // Test the validator parseMailingList: parsing the recipients out of address headers
        $this->eml = new InlineEmail();
        // Put it together and take it apart again:
        $toList = array(
            array('This That', 'this.that@gmail.com'), array('Fruit Fly', 'fruit_@fly.com'));
        $this->eml->to = implode(', ', array_map(function($t){
            return "\"{$t[0]}\" <{$t[1]}>";
        }, $toList));
        $this->eml->parseMailingList('to');
        $this->assertEquals($toList, $this->eml->mailingList['to'], "Failed asserting that the addressee list was parsed properly.");
    }

    public function testRecordEmailSent(){
        $profile = Profile::model()->findByAttributes(array('username' => 'testuser'));
        $case = 'Use case 1: plain email';
        $this->eml = new InlineEmail('custom');
        $this->eml->subject = 'test email subject';
        $this->eml->message = '<html><head></head><body><h1>testing 123</h1></body></html>';
        $this->eml->to = '"Testfirstname Testlastname" <contact@test.com>';
        $this->eml->from = array (
            'name' => 'Sales Rep',
            'address' => 'sales@rep.com',
        );
        $this->eml->modelId = 12345;
        $this->eml->modelName = 'Contacts';
        $this->eml->userProfile = $profile;
        $this->eml->validate();
        $this->eml->insertTrackingImage();
        $this->eml->recordEmailSent();
        $this->assertModelUpdated('contacts', 'testAnyone', $case);
        $this->assertActionCreated('email', $case);

        $case = 'Use case 2: quote issued';
        $this->eml = new InlineEmail('custom');
        $this->eml->message = '<html><head></head><body><h1>testing 123</h1></body></html>';
        $this->eml->to = '"Testfirstname Testlastname" <contact@test.com>';
        $this->eml->from = array (
            'name' => 'Sales Rep',
            'address' => 'sales@rep.com',
        );
        $quote = $this->quote('docsTest');
        $this->eml->modelId = $quote->id;
        $this->eml->modelName = 'Quote';
        $this->eml->userProfile = $profile;
        $this->eml->validate();
        $this->eml->insertTrackingImage();
        $this->eml->recordEmailSent();
        $this->assertModelUpdated('quote', 'docsTest', $case);
        $this->assertActionCreated('email_quote', $case);
    }

    public function testPrepareBody(){
        $this->eml = new InlineEmail('template');
        $template = $this->docs('testEmailTemplate');
        $this->eml->template = $template->id;
        $this->eml->modelId = $this->contacts('testAnyone')->id;
        $this->eml->modelName = 'Contacts';
        $this->eml->userProfile = $profile = $this->profile('testProfile');
        $this->assertEquals($template->id, $this->eml->templateModel->id, 'Failed asserting that the template was properly chosen.');
        $this->eml->prepareBody();
        $this->assertEquals(str_replace('{name}', $this->contacts['testAnyone']['name'], $template->subject), $this->eml->subject);
        $this->assertEquals(str_replace('{name}', $this->contacts['testAnyone']['name'], $template->text), $this->eml->message);
    }

    public function testPrepareBodyWithAccountTemplate(){
        $this->eml = new InlineEmail('template');
        $template = $this->docs('testAccountEmailTemplate');
        $this->eml->template = $template->id;
        $this->eml->modelId = $this->accounts('testAccount')->id;
        $this->eml->modelName = 'Accounts';
        $this->eml->userProfile = $profile = $this->profile('testProfile');
        $this->assertEquals($template->id, $this->eml->templateModel->id, 'Failed asserting that the template was properly chosen.');
        $this->eml->prepareBody();

        // assert that {description} insertable attribute, when placed inside the 'To:' field in the
        // email template gets properly replaced with the account's attribute
        $this->assertEquals(str_replace('{description}', $this->accounts['testAccount']['description'], $template->emailTo), $this->eml->to);
    }

    public function testDeliver(){
        if(self::TESTDELIVERY){
            Yii::app()->settings->emailType = $this->method;
            $this->eml = new InlineEmail();
            try{
                $this->eml->credId = $this->credentials('liveDeliveryTest')->id;
            }catch(Exception $e){
                $this->markTestSkipped('You have not defined the liveDeliveryTest alias in protected/tests/fixtures/x2_credentials-local.php !');
            }
            $this->eml->userProfile = Profile::model()->findByAttributes(array('username' => 'testuser'));
            $this->eml->mailingList = $this->recipient;
            $this->eml->subject = 'Test email';
            $this->eml->message = '<html><head></head><body>Test email body</body></html>';
            $this->eml->attachments = array();
            $status = $this->eml->deliver();
            $this->assertTrue(in_array('200', $status), 'Failed asserting successful return code. Status = '.CJSON::encode($status));
            println("Check email at address ".TEST_EMAIL_TO. ' for the delivered test message.');
            // No further assertions in this method. Chiggity check yo inbox.
        }
    }

    public function testActionHeader(){
        $this->eml = new InlineEmail('template');
        $template = $this->docs('testEmailTemplate');
        $this->eml->template = $template->id;
        $this->eml->modelId = $this->contacts('testAnyone')->id;
        $this->eml->modelName = 'Contacts';
        $this->eml->subject = 'Test Email Subject';
        $this->eml->from = array (
            'name' => 'Sales Rep',
            'address' => 'sales@rep.com',
        );
        $this->eml->to = '"Testfirstname Testlastname" <contact@test.com>';
        $this->eml->prepareBody();
        $record = $this->eml->insertInBody($this->eml->actionHeader, 1, 1);
        $this->assertTrue((bool) preg_match('/<body>(.*)<\/body>/um', $record, $matches), "Body isn't an HTML document. What's going on here? Body = ".$record);
        $content = $matches[1];
//		$contentLines = explode('<br />', $content);
//		$subjectLine = $contentLines[0];
//		$fromLine = $contentLines[1];
//		$toLine = $contentLines[2];
        $this->assertRegExp('/(<strong>Subject: <\/strong>.*)<br \/>/u', $content);
        $this->assertRegExp('/(<strong>From: <\/strong>.*)<br \/>/u', $content);
        $this->assertRegExp('/(<strong>To: <\/strong>.*)<br \/>/u', $content);
    }

}

?>
