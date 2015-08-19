<?php

Yii::import('application.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.users.models.*');

/**
 * Test suite for APIModel
 * 
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class APIModelTest extends CURLDbTestCase {

	public function urlFormat() {
		return '';
	}

	public $fixtures = array(
		'contacts' => 'Contacts'
	);

	public static function referenceFixtures() {
		return array(
            'users'=>'User',
            'roles'=>array('Roles','.empty'),
            'roleToUser' =>array('RoleToUser','.empty'),
            'roleToPermission' => array('RoleToPermission','.empty'),
        );
	}

	public function newModel() {
        $user = $this->users ('testUser');
		return new APIModel($user->username,$user->userKey,rtrim(TEST_BASE_URL,'/'));
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

        /**
         * @group failing
         */
	public function testTags() {
		$model = $this->newModel();
		$tags = array('#this','#that');
		// Test adding:
//		ob_start();
		$realModel = $this->contacts('testAnyone');
		$response = $model->addTags ('Contacts',$realModel->id,$tags);
		$tagTable = CActiveRecord::model('Tags')->tableName();
		$tagsOnServer = Yii::app()->db->createCommand()
            ->select('tag')
            ->from($tagTable)
            ->where(
                'itemId=:itemId AND type=:itemType',
                array(':itemId'=>$realModel->id,':itemType'=>get_class($realModel))
            )->queryColumn();
        
//		var_dump($tags);
//		var_dump($tagsOnServer);
//		print_r(json_decode($response,1));

		$tagsNotAdded = array_diff($tags,$tagsOnServer);
		$this->assertEmpty($tagsNotAdded,'Failed asserting that tags were saved on the server.');

		// Test getting:
		$tagsFromServer = $model->getTags('Contacts',$this->contacts('testAnyone')->id);
//		ob_end_clean();
//		var_dump($tagsFromServer);
//		var_dump($tagsOnServer);
		$tagsNotRetrieved = array_diff($tagsOnServer,$tagsFromServer);
		$this->assertEmpty($tagsNotRetrieved,'Failed asserting that all tags were properly retrieved.');
		// Test deleting:
		$response = $model->removeTag('Contacts',$this->contacts('testAnyone')->id,'#this');
		$tagsOnServer = Yii::app()->db->createCommand()->select('tag')->from($tagTable)->where('itemId=:itemId AND type=:itemType',array(':itemId'=>$realModel->id,':itemType'=>get_class($realModel)))->queryColumn();
		$tagsDeleted = array_diff($tags,$tagsOnServer);
//		var_dump($response);
//		var_dump($tagsDeleted);
		$this->assertEquals(1,count($tagsDeleted),'Failed asserting that one and only one tag was deleted.');
		$this->assertEquals('#this',$tagsDeleted[0],'Failed asserting that the right tag got deleted.');
	}

        /**
         * @group failing
         */
	public function testContactCRUD() {
        Yii::app()->cache->flush();
		$model = $this->newModel();
		// Trigger a validation error and test that the error feedback works
		$model->attributes = array('firstName'=> 'John', 'lastName'=>'Doe','email'=>'lfdkjslfdjs','visibility'=>1);
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
			$this->assertEquals($contact->$name,$model->$name,"Failed asserting that the API model received the attributes of the model from the server; attribute $name does not match.");
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
//		var_dump($lookupModel->attributes);
		foreach($contact->attributes as $name=>$value) {
			$this->assertArrayHasKey($name, $lookupModel->attributes, "Failed asserting that attribute $name was set in contact lookup. Response: ".json_encode($lookupModel->responseObject));
			$this->assertEquals($value,$lookupModel->$name, "Failed asserting that attribute is not the same between existing model and looked-up model. What the heck is happening here?");
		}
        // Test using the "view" method instead:
        $id = $lookupModel->id;
		$lookupModel = $this->newModel();
		$lookupModel->id = $id;
		$lookupModel->contactLookup();
		foreach($contact->attributes as $name=>$value) {
			$this->assertArrayHasKey($name, $lookupModel->attributes, "Failed asserting that attribute $name was set in contact lookup. Response: ".json_encode($lookupModel->responseObject));
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
