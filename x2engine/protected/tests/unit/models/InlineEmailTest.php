<?php

Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.quotes.models.*');
Yii::import('application.modules.user.models.*');
Yii::import('application.components.ResponseBehavior');

/**
 * Test of the {@link InlineEmail} class.
 *
 * @package X2CRM.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class InlineEmailTest extends X2DbTestCase {
	// Set to 1 to enable testing actual sending of email.

	const TESTDELIVERY = 0;
	// Set to 1 to enable tests that print out and require testing by sight
	const SIGHT = 1;

	public $method = 'mail'; // Set to the delivery type...
	public $sender = array('name' => "", 'address' => ''); // Sender email..
	public $recipient = array(array(/* Name: */ '', /* Address: */ '')); // Recipient email addresses...
	public $fixtures = array(
		'actions' => 'Actions',
		'contacts' => 'Contacts',
		'quote' => 'Quote',
		'trackEmail' => 'TrackEmail',
		'events' => 'Events',
		'docs' => 'Docs'
	);

	/**
	 * Email model.
	 * @var InlineEmail
	 */
	public $eml;

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
		include(realpath(Yii::app()->basePath.'/../webLeadConfig.php'));
		$image = "<img src=\"$url/index.php/actions/emailOpened?uid={$this->eml->uniqueId}&type=open\"/>";
		$fullImage = InlineEmail::insertedPattern('track', $image);
		$this->assertEquals($this->eml->trackingImage, $image, 'Failed asserting that the tracking image is as expected. '.$message);
		$this->assertTrue(strpos($this->eml->message, $fullImage) !== false, 'Failed asserting that the body contains the tracking image; body ='.$this->eml->message.$message);
		$this->assertRegExp(InlineEmail::UIDREGEX, $this->eml->trackingImage, 'Failed asserting that the tracking image has a unique id.'.$message);
		preg_match(InlineEmail::UIDREGEX, $this->eml->trackingImage, $matchId);
		$this->assertEquals($this->eml->uniqueId, $matchId[1], "Failed asserting that the UID in the image tracking tag matches the one in the model.".$message);
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
		$this->eml->insertTrackingImage();
		$this->assertBodyHasTrackingImage(' On case 1: user sumbitting new/blank email without tracking image.');
		$message = $this->eml->message;
		$this->eml = new InlineEmail();
		$this->eml->message = $message;
		$this->eml->insertTrackingImage();
		$this->assertBodyHasTrackingImage(' On case 2: user submitting modified body with preexisting tracking image.');

		// Test the validator parseMailingList: parsing the recipients out of address headers
		$this->eml = new InlineEmail();
		// Put it together and take it apart again:
		$toList = array(array('This That', 'this.that@gmail.com'), array('Fruit Fly', 'fruit_@fly.com'));
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
		$this->eml->from = '"Sales Rep" <sales@rep.com>';
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
		$this->eml->from = '"Sales Rep" <sales@rep.com>';
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
		$this->eml->userProfile = Profile::model()->findByAttributes(array('username' => 'testuser'));
		$this->assertEquals($template->id,$this->eml->templateModel->id,'Failed asserting that the template was properly chosen.');
		$this->eml->prepareBody();
		$this->assertEquals(str_replace('{name}', $this->contacts['testAnyone']['name'], $template->subject), $this->eml->subject);
		$this->assertEquals(str_replace('{name}', $this->contacts['testAnyone']['name'], $template->text), $this->eml->message);
	}

	public function testDeliver(){
		if(self::TESTDELIVERY){
			Yii::app()->params->admin->emailType = $this->method;
			$this->eml = new InlineEmail();
			$this->eml->userProfile = Profile::model()->findByAttributes(array('username' => 'testuser'));
			$this->eml->mailingList = $this->recipient;
			$this->eml->subject = 'Test email';
			$this->eml->message = '<html><head></head><body>Test email body</body></html>';
			$this->eml->attachments = array();
			$this->eml->from = $this->sender;
			$status = $this->eml->deliver();
			$this->assertTrue(in_array('200', $status), 'Failed asserting successful return code. Status = '.CJSON::encode($status));
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
		$this->eml->from = '"Sales Rep" <sales@rep.com>';
		$this->eml->to = '"Testfirstname Testlastname" <contact@test.com>';
		$this->eml->prepareBody();
		$record = $this->eml->insertInBody($this->eml->actionHeader, 1, 1);
		$this->assertTrue((bool) preg_match('/<body>(.*)<\/body>/um', $record, $matches), "Body isn't an HTML document. What's going on here?");
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
