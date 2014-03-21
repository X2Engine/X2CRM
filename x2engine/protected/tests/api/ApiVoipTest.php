<?php
// Import the main models to be used:
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.users.models.*');
Yii::import('application.models.*');

/**
 * Test of {@link ApiController::actionVoip()}
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ApiVoipTest extends CURLDbTestCase {

	public $fixtures = array(
		'notifications' => 'Notification',
		'contacts' => 'Contacts',
	);

	public static function referenceFixtures() {
		return array('phoneNumbers' => 'PhoneNumber',);
	}

	public function urlFormat() {
		return 'api/voip?data={data}';
	}

	public function testVoip() {
		// Lookup
		$ch = $this->getCurlHandle(array('{data}'=>$this->phoneNumbers('testAnyone_phone')->number));
		$response = json_decode(curl_exec($ch),1);
		$this->assertResponseCodeIs(200, $ch,'Failed asserting that server responded with 200');
		file_put_contents('api_response.html',$response);
		$this->assertRegExp('/Notifications created for user.*/',$response['message']);
	}
}

?>
