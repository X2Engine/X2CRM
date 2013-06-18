<?php

// Import the main models to be used:
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.docs.models.Docs');
Yii::import('application.modules.opportunities.models.Opportunity');
Yii::import('application.modules.products.models.Product');
Yii::import('application.modules.services.models.Services');
Yii::import('application.modules.users.models.*');
Yii::import('application.models.*');

/**
 * CRUD test for X2CRM's remote API
 * @package X2CRM.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ApiControllerTest extends CURLTestCase {

	const TEST_LEVEL = 1;

	public $fixtures = array(
		'accounts' => 'Accounts',
		'actions' => 'Actions',
		'contacts' => 'Contacts',
		'docs' => 'Docs',
		'opportunities' => 'Opportunity',
		'products' => 'Product',
		'services' => 'Services',
		'tags' => 'Tags',
	);

	/**
	 * Starting template for data parameters (GET or POST or otherwise)
	 * @var type 
	 */
	public $param = array(
		'user' => 'testuser',
		'userKey' => '5f4dcc3b5aa765d61d8327deb882cf99',
	);

	/**
	 * Starting template for URL parameters
	 * @var array 
	 */
	public $urlParam = array(
		'{action}' => '',
		'{model}' => '',
		'{params}' => '',
	);

	/**
	 * Asserts that the appropriate response was given for a user during an
	 * action.
	 * @param type $authAction
	 * @param type $ch
	 * @return type
	 */
	public function assertAuthControlCorrectness($authAction, $ch,$apiModel){
		$access = $apiModel->checkAccess($authAction);
		$expectedResponse = $access ? 200 : 403;
		$this->assertResponseCodeIs($expectedResponse,$ch, $access ? "User does not have permission to $authAction but should." : "Failed asserting testuser does not have access to $authAction");
		return $access;
	}

	public function urlFormat() {
		return 'api/{action}/model/{model}{params}';
	}

	public function newModel($user,$key) {
		return new APIModel($user,$key,rtrim(TEST_BASE_URL,'/'));
	}


	/**
	 * Test the model getter..
	 */
	public function testGetModel() {
		$model = $this->contacts('testUser');
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'tags';
		$urlParam['{model}'] = 'Contacts';
		$urlParam['{params}'] = '';
		$postData = $this->param;
		// Test missing primary key error:
		$ch = $this->getCurlHandle($urlParam, $postData);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html', $cr);
		$this->assertResponseCodeIs(400,$ch,'Failed asserting that "no primary key specified" error was triggered properly.');
		$this->assertRegExp('/No parameters matching primary key/',$cr,'Failed asserting that "no primary key specified" error was triggered properly.');
		// Try again with nonexistent record:
		$urlParam['{params}'] = '/id/666';
		$ch = $this->getCurlHandle($urlParam, $postData);
		file_put_contents('api_response.html', $cr);
		$cr = curl_exec($ch);
		$this->assertResponseCodeIs(404,$ch,'Failed asserting that the error of using a nonexistent record was triggered properly.');
		$this->assertRegExp('/No record of model/',$cr,'Failed asserting that the error of using a nonexistent record was triggered properly.');
	}

	/**
	 * Test the tags action
	 */
	public function testActionTags() {
		$model = $this->contacts('testUser');
		// append tags to the above model
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'tags';
		$urlParam['{model}'] = 'Contacts';
		$urlParam['{params}'] = '';
		$postData = $this->param;
		// try without any tags parameter, using an existing record:
		$urlParam['{params}'] = '/id/'.$model->id;
		$ch = $this->getCurlHandle($urlParam, $postData);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html', $cr);
		$this->assertResponseCodeIs(400,$ch,'Failed asserting that "no tags parameter specified" error was triggered properly.');
		$this->assertRegExp('/Parameter .* requried/',$cr,'Failed asserting that "no tags parameter specified" error was triggered properly.');
		// Now test adding
		$tags = array('#this','#that');
		$postData['tags'] = json_encode($tags);
		$ch = $this->getCurlHandle($urlParam, $postData);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html', $cr);
		$this->assertResponseCodeIs(200,$ch,'Failed asserting that a request to add tags succeeded.');
		// Check that tags were added:
		$model->refresh();
		$modelTags = $model->getTags();
		$tagsNotAdded = array_diff($tags,$modelTags);
		$this->assertEmpty($tagsNotAdded,'Failed asserting that tags were added: '.implode(',',$tagsNotAdded).'; model tags = '.json_encode($modelTags).' model id = '.$model->id);
		// Test getting tags:
		$urlParam['{params}'] = '?'.http_build_query(array_merge($this->param, array('id'=>$model->id)));
		$ch = $this->getCurlHandle($urlParam);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html', $cr);
		$this->assertResponseCodeIs(200,$ch,'Failed asserting that a request to query tags succeeded.');
		$tagsOnServer = json_decode($cr,1);
		$setDiff = array_diff($tags,$tagsOnServer);
		$this->assertEmpty($setDiff);
		
		// Test deletion of those tags:
		foreach($tags as $tag) {
			$urlParam['{params}'] = '?'.http_build_query(array_merge($this->param, array('id'=>$model->id,'tag'=>ltrim($tag,'#'))));
			$ch = $this->getCurlHandle($urlParam);
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');
			$cr = curl_exec($ch);
			file_put_contents('api_response.html', $cr);
			$this->assertResponseCodeIs(200,$ch,'Failed asserting that a request to delete a tag succeeded.');
		}

		// The model's _tags property has been set, so this time we'll need to look it up:
		$newTagSet = Yii::app()->db->createCommand()->select('tag')->from(CActiveRecord::model('Tags')->tableName())->where('itemId=:itemId AND type=:itemType',array(':itemId'=>$model->id,':itemType'=>get_class($model)))->queryColumn();
		$this->assertEquals($tags,array_diff($tags,$newTagSet),'Failed asserting that tags were deleted.');
	}

	public function testCRUD() {
		if(self::TEST_LEVEL >= 2) {
			$this->assertCRUD();
		} else {
			$this->markTestSkipped('Skipping test because TEST_LEVEL is not set >= 2');
		}
	}
	
	/**
	 * Test the creation, reading, updating and deletion of records through the 
	 * API, and validation errors.
	 */
	public function assertCRUD() {
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'create';
		$param = $this->param;
		$modelAttrs = array(
			'Accounts' => array(
				'name' => 'ApiAccount',
				'type' => 'business',
			),
			'Actions' => array(
				'assignedTo' => 'testuser',
				'dueDate' => time()+86400,
			),
			'Contacts' => array(
				'firstName' => 'ApiContactFirstName',
				'lastName' => 'ApiContactLastName',
				'email' => 'apiemail@address.com',
				'visibility' => 1,
			),
			'Docs' => array(
				'name' => 'Excellent birds',
				'subject' => 'Test subject',
				'text' => 'Vel lorem risus, massa, sociis sagittis! Turpis magna sed, tristique? Pid massa integer facilisis...',
			),
			'Opportunity' => array(
				'name' => 'Falling snow',
				'description' => 'Urna dolor? Sed tortor arcu mid porttitor egestas pulvinar etiam, diam? Aliquam, cras ultricies elementum?...'
			),
			'Product' => array(
				'name' => 'Excellent snow',
				'description' => "Enim amet! Porttitor, amet pulvinar augue sed auctor, phasellus pellentesque nunc nunc amet proin...",
				'type' => 'watch it fall',
			),
			'Services' => array(
				'contactId' => $this->contacts('testUser')->id,
				'description' => 'This is the picture! (of the error). Fix it.',
				'impact' => '1 - Severe',
				'status' => 'new',
			),
		);
		$classModulesMap = array_combine(array_keys($modelAttrs),array_keys($modelAttrs));
		$classModulesMap['Product'] = 'Products';
		$classModulesMap['Opportunity'] = 'Opportunities';

		// Stash models in here:
		$models = array();
		// Users to try the actions for:
		$users = array(
			array(
				'user' => 'testuser',
				'userKey' => '5f4dcc3b5aa765d61d8327deb882cf99',
			),
			array(
				'user' => 'admin',
				'userKey' => '21232f297a57a5a743894a0e4a801fc3'
			)
		);
		foreach($users as $userParam){
			$param = $userParam;
			$apiModel = $this->newModel($userParam['user'],$userParam['userKey']);
			foreach($modelAttrs as $class => $attrs){
//			echo "Testing creation of $class record through API\n";
				$urlParam['{model}'] = $class;
				$ch = $this->getCurlHandle($urlParam, array_merge($param, $attrs));
				$cr = curl_exec($ch);
				file_put_contents('api_response.html', $cr);
				$authAction = $classModulesMap[$class].'Create';
				$access = $this->assertAuthControlCorrectness($authAction,$ch,$apiModel);
				if($access){
					$models[$class] = X2Model::model($class)->findByAttributes($attrs);
					$this->assertTrue((bool) $models[$class], "Model of class $class not created. The response was: $cr");
					foreach($attrs as $attr => $value)
						$this->assertEquals($value, $models[$class]->$attr);
					// Test that createDate was set properly:
					if($models[$class]->hasAttribute('createDate'))
						$this->assertNotNull($models[$class]->createDate);
					// Test that the username attributes were set properly. In the case
					// of a service module case: test that it's assigned to whoever is
					// assigned the contact associated with the case.
					foreach(array('createdBy', 'assignedTo', 'updatedBy') as $attr){
						if($models[$class]->hasAttribute($attr)){
							// echo "$class::$attr = {$models[$class]->$attr}\n";
							$models[$class]->refresh();
							$this->assertEquals($this->param['user'], $models[$class]->$attr, "Failed asserting $attr was set properly on creation of {$class}");
						}
					}
				}
			}
		}
		
		// We've got our models. Now let's test finding by attributes ("lookup"):
		$urlParam['{action}'] = 'lookup';
		// We're going to need primary keys for the direct "view" read action:
		$pkValues = array();
		foreach($users as $userParam){
			$param = $userParam;
			$apiModel = $this->newModel($userParam['user'],$userParam['userKey']);
			foreach($modelAttrs as $class => $attrs){
				$urlParam['{model}'] = $class;
				$ch = $this->getCurlHandle($urlParam, array_merge($param, $attrs));
				$cr = curl_exec($ch);
				file_put_contents('api_response.html', $cr);
				$authAction = $classModulesMap[$class].'View';
				$access = $this->assertAuthControlCorrectness($authAction, $ch,$apiModel);
				if($access){
					$this->assertResponseCodeIs(200, $ch);
					$queriedModel = CJSON::decode($cr);
					// Response must be valid JSON:
					$this->assertEquals('array', gettype($queriedModel));
					// Test that the attributes are all equal. This is pretty much overkill:
					foreach($queriedModel as $attr => $value){
						$this->assertEquals($models[$class]->$attr, $value);
					}
					// This will be useful for the next tests (lookup by pk, update & delete):
					$pkValues[$class] = $models[$class]->primaryKey;
				}
			}
		}
		
		// Test "view": lookup by ID. We already know that access control for the
		// "view" action is already correct; it was tested just previously. So
		// this test shall use the admin user:
		$urlParam['{action}'] = 'view';
		$param = $users[1];
		foreach($pkValues as $class => $pk){

			$urlParam['{model}'] = $class;
			$get = array();
			if(is_array($pk)) // Composite primary key
				$get = array_merge($get, $pk);
			else // Single-column primary key
				$get[$models[$class]->tableSchema->primaryKey] = $pk;
			$urlParam['{params}'] = '?'.http_build_query($get);
			$ch = $this->getCurlHandle($urlParam, $param);
			$cr = curl_exec($ch);
			file_put_contents('api_response.html', $cr);


			$this->assertResponseCodeIs(200, $ch);
			$queriedModel = CJSON::decode($cr);
			$this->assertEquals('array', gettype($queriedModel), 'Failed asserting that the response from the server was valid JSON.');
		}

		// Test "update": modify record by ID:
		$urlParam['{action}'] = 'update';
		$urlParam['{params}'] = '';
		$modelAttrs = array(
			'Accounts' => array(
				'description' => 'I have now added a description to this account.',
			),
			'Actions' => array(
				'assignedTo' => 'testuser',
				'dueDate' => time()+86400,
			),
			'Contacts' => array(
				'firstName' => 'ApiContactFirstNameEdited',
				'lastName' => 'ApiContactLastNameEdited',
				'email' => 'apiemailedited@address.com',
			),
			'Docs' => array(
				'name' => 'Excellent.',
				'subject' => 'Test subject 2',
				'text' => 'Edited...',
			),
			'Opportunity' => array(
				'name' => 'Falling snow',
				'description' => 'Urna dolor? Aliquam, cras ultricies elementum?...Edited'
			),
			'Product' => array(
				'name' => 'Esnow',
				'description' => "Enim amet! Edited",
				'type' => 'watchfall',
			),
			'Services' => array(
				'description' => 'This is edited.',
			),
		);

		foreach($users as $userParam){
			$param = $userParam;
			$apiModel = $this->newModel($userParam['user'],$userParam['userKey']);
			foreach($pkValues as $class => $pk){
				$urlParam['{model}'] = $class;
				$post = array_merge($param, $modelAttrs[$class]);
				$get = array();
				if(is_array($pk)) // Composite primary key
					$get = array_merge($get, $pk);
				else // Single-column primary key
					$get[$models[$class]->tableSchema->primaryKey] = $pk;
				$urlParam['{params}'] = '?'.http_build_query($get);
				$ch = $this->getCurlHandle($urlParam, $post);
				$cr = curl_exec($ch);
				file_put_contents('api_response.html', $cr);
				// Choose the expected response code based on the permissions:
				$authAction = $classModulesMap[$class].'Update';
				$access = $this->assertAuthControlCorrectness($authAction, $ch,$apiModel);

				// Refresh the stowed model and verify that it was updated properly:
				$models[$class]->refresh();
				if($access){
					foreach($modelAttrs[$class] as $attr => $value){
						$this->assertEquals($value, $models[$class]->$attr, "Failed asserting that attribute $attr was updated in model $class.");
					}
				}
			}
		}

		// Test validation errors.
		$class = 'Docs';
		$oldModelAttrs = $modelAttrs[$class]['subject'];
		$modelAttrs[$class]['subject'] = implode(',',range(1,10000));
		$urlParam['{model}'] = $class;
		$post = array_merge($param,$modelAttrs[$class]);
		$get = array();
		$pk = $pkValues[$class];
		if(is_array($pk)) // Composite primary key
			$get = array_merge($get,$pk);
		else // Single-column primary key
			$get[$models[$class]->tableSchema->primaryKey] = $pk;
		$urlParam['{params}'] = '?'.http_build_query($get);
		$ch = $this->getCurlHandle($urlParam,$post);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html',$cr);
		$this->assertResponseCodeIs(500,$ch,'Failed asserting that a validation error could be triggered in the API');

		// Test incorrect primary key errors:
		$class = 'Docs';
		$modelAttrs[$class]['subject'] = $oldModelAttrs;
		$pk = 38923;
		$urlParam['{model}'] = $class;
		$get = array();
		$get['id'] = $pk;
		$urlParam['{params}'] = '?'.http_build_query($get);
		$ch = $this->getCurlHandle($urlParam,$post);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html',$cr);
		$this->assertResponseCodeIs(404,$ch,'Failed asserting proper error code in an update operation with incorrect primary key.');
		$urlParam['{action}'] = 'delete';
		$ch = $this->getCurlHandle($urlParam,$post);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html',$cr);
		$this->assertResponseCodeIs(404,$ch,'Failed asserting proper error code in a delete operation with incorrect primary key.');
		$urlParam['{action}'] = 'view';
		$ch = $this->getCurlHandle($urlParam,$post);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html',$cr);
		$this->assertResponseCodeIs(404,$ch,'Failed asserting proper error code in a view operation with incorrect primary key.');

		// Test missing primary key errors:
		$get = array();
		$urlParam['{params}'] = '?'.http_build_query($get);
		$ch = $this->getCurlHandle($urlParam,$post);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html',$cr);
		$this->assertResponseCodeIs(400,$ch,'Failed asserting proper error code in an update operation missing primary key.');
		$urlParam['{action}'] = 'delete';
		$ch = $this->getCurlHandle($urlParam,$post);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html',$cr);
		$this->assertResponseCodeIs(400,$ch,'Failed asserting proper error code in a delete operation missing primary key.');
		$urlParam['{action}'] = 'view';
		$ch = $this->getCurlHandle($urlParam,$post);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html',$cr);
		$this->assertResponseCodeIs(400,$ch,'Failed asserting proper error code in a view operation missing primary key.');
		

		// Test deletion. We'll need use the admin user to avoid 403's. Comment
		// out these next two lines to check that RBAC filtering works properly.
		// When doing so, the test should fail, and the error should be "Failed
		// asserting that 403 matches expected 200" (when attempting to delete
		// an "Accounts" record, which ordinary users can't do).
		$param['user'] = 'admin';
		$param['userKey'] = '21232f297a57a5a743894a0e4a801fc3';
		$urlParam['{action}'] = 'delete';
		$urlParam['{params}'] = '';
		foreach($pkValues as $class => $pk){
			$urlParam['{model}'] = $class;
			$post = $param;
			if(is_array($pk)) // Composite primary key
				$post = array_merge($post, $pk);
			else // Single-column primary key
				$post[$models[$class]->tableSchema->primaryKey] = $pk;
			$ch = $this->getCurlHandle($urlParam, $post);
			$cr = curl_exec($ch);
			file_put_contents('api_response.html', $cr);
			$this->assertResponseCodeIs(200, $ch,'Failed asserting that deletion request succeeded.');
			$model = X2Model::model($class)->findByPk($pk);
			// No more model matching PK?
			$this->assertFalse((bool) $model, 'Failed asserting that the deletion target exists.');
		}
	}
}

?>
