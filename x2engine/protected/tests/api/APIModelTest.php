<?php

Yii::import('application.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.users.models.*');

/**
 * Test suite for APIModel
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class APIModelTest extends CURLTestCase {

	public function urlFormat() {
		return '';
	}

	public $fixtures = array(
		'contacts' => 'Contacts',
		'users' => 'User',
	);

	public function newModel() {
		return new APIModel('testuser','5f4dcc3b5aa765d61d8327deb882cf99',rtrim(TEST_BASE_URL,'/'));
	}

	public function testInternalMethods() {
		$response = CJSON::encode(array(
			'error' => 1,
			'model' => array('name'=>'Me Too','id'=>1),
			'message' => 'Bad request'
		));
		$model = new APIModel();
		// Test the response object getter/setter:
		$model->responseObject = $response;
		$this->assertArrayHasKey('model', $model->responseObject);
	}

	public function testContactCRUD() {
		$model = $this->newModel();
		// Trigger a validation error and test that the error feedback works
		$model->attributes = array('firstName'=> 'John', 'lastName'=>'Doe','email'=>'lfdkjslfdjs');
		$model->contactCreate(false);
		$this->assertEquals(500,$model->responseCode,'Failed asserting response code was correct for validation errors.');
		$this->assertEquals(1,$model->responseObject['error'],'Failed asserting that the API reported validation errors.');
		$this->assertArrayHasKey('modelErrors',$model->responseObject);
		$this->assertArrayHasKey('email',$model->responseObject['modelErrors']);
		// Now, test actually saving it:
		$model->attributes['email'] = 'john123456789@doe.com';
		$oldAttr = $model->attributes;
		$model->contactCreate(false); // Force assignedTo
		$this->assertEquals(200,$model->responseCode,'Failed asserting that the request succeeded (response code not 200).');
		$this->assertEquals(0,$model->responseObject['error'],'Failed asserting that the API reported no errors.');
		$contact = Contacts::model()->findByAttributes($oldAttr);
		$this->assertNotEmpty($contact,'Failed asserting that the model was saved properly.');
		foreach($contact->attributes as $name=>$noVal) {
			$this->assertEquals($contact->$name,$model->$name,'Failed asserting that the API model received the attributes of the model from the server');
		}
		$this->assertEquals('testuser',$contact->assignedTo,'Failed asserting proper assignment of the new model.');
		// Now test updating... With validation error.
		$model->attributes['email'] = 'alkdjfs98v3928m;a';
		$model->contactUpdate();
		$this->assertEquals(500,$model->responseCode,'Failed asserting that the API reported validation errors.');
		// Test updating again:
		$model->attributes['email'] = 'lskdfsdlfs@lkdj.net';
		$oldAttr['email'] = $model->attributes['email'];
		$model->contactUpdate();
		$this->assertEquals(200,$model->responseCode,'Failed asserting that the model was saved successfully (response code not 200).');
		$contact->refresh();
		$this->assertEquals($model->email,$contact->email,'Failed asserting that an attribute (email) was saved properly.');
		// Test looking up an individual model by attributes:
		$lookupModel = $this->newModel();
		$lookupModel->attributes = $oldAttr;
		$lookupModel->contactLookup();
		foreach($contact->attributes as $name=>$value) {
			$this->assertArrayHasKey($name, $lookupModel->attributes, "Failed asserting that attribute $name was set in contact lookup.");
			$this->assertEquals($value,$lookupModel->$name, "Failed asserting that attribute is not the same between existing model and looked-up model. What the heck is happening here?");
		}

		// Test getting permissions.
		$this->assertFalse($model->checkAccess('ContactsDelete'),'Failed asserting proper response regarding permissions of unprivileged user.');
		// Test deleting with an unprivileged user (which should fail).
		$model->contactDelete();
		$this->assertEquals(403,$model->responseCode,'Failed asserting "unauthorized" response code during attempt to delete contact with unprivileged user.');
		$contact = Contacts::model()->findByAttributes($oldAttr);		
		$this->assertNotEmpty($contact,'Failed asserting that deletion of the model was skipped due to lack of proper permissions.');
		$this->assertEquals(403,$model->responseCode,'Failed asserting "unauthorized" response code during attempt to delete contact with unprivileged user.');
		// Now we're really going to delete the contact:
		$model->_user = 'admin';
		$model->_userKey = '21232f297a57a5a743894a0e4a801fc3';
		$model->contactDelete();
		$this->assertEquals(200,$model->responseCode,'Failed asserting that the "delete" action returned successfully.');
		$contact = Contacts::model()->findByAttributes($oldAttr);
		$this->assertEmpty($contact,'Failed asserting successful deletion.');

	}
}

?>
