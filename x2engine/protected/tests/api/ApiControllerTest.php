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
 * CRUD test for X2Engine's remote API
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ApiControllerTest extends CURLDbTestCase {

	const TEST_LEVEL = 2;

	public $fixtures = array(
		'accounts' => 'Accounts',
		'actions' => 'Actions',
		'contacts' => 'Contacts',
		'docs' => 'Docs',
		'opportunities' => 'Opportunity',
		'products' => 'Product',
		'services' => 'Services',
		'relationships' => 'Relationships',
		'tags' => 'Tags',
	);

	public static function referenceFixtures(){
		return array(
			'users'=>'User',
            'roles'=>array('Roles','.empty'),
            'roleToUser' =>array('RoleToUser','.empty'),
            'roleToPermission' => array('RoleToPermission','.empty'),
        );
	}

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

	public function testActionRelationship() {
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'relationship';
		$urlParam['{model}'] = 'Accounts';
		// Step 1: Test lookup:
		$urlParam['{params}'] = '?'.http_build_query(array_merge($this->param,array('firstId'=>$this->accounts('testQuote')->id,'secondType'=>'Contacts','secondId'=>$this->contacts('testUser')->id)),'','&');
		$ch = $this->getCurlHandle($urlParam);
		$cr = curl_exec($ch);
		$cr = json_decode($cr,1);
		$this->assertTrue(is_array($cr),'Request to read relationships failed utterly');
		$this->assertArrayNotHasKey('message',$cr,"Unsuccessful API request; unexpected message. json = ".json_encode($cr));
		$this->assertEquals(1,count($cr),'Number of relationships is inconsistent with values expected in fixture data (might want to double-check that)');
		foreach(Relationships::model()->attributeNames() as $name){
			$this->assertArrayHasKey($name,$cr[0],"Attribute $name missing in returned array of relationships");
			$this->assertEquals($this->relationships('blackMesaContact')->$name,$cr[0][$name],'API returned relationship record inconsistent with fixture data.');
		}
		// Step 2: Test creation:
		$urlParam['{params}'] = '';
		$postData = array_merge($this->param,array(
			'firstId' => $this->accounts('testQuote')->id,
			'secondId' => $this->contacts('testAnyone')->id,
			'secondType' => 'Contacts',
		));
		$ch = $this->getCurlHandle($urlParam,$postData);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html', $cr);
		$cr = json_decode($cr,1);
		$this->assertTrue(is_array($cr),'Request to put relationships failed utterly');
		$this->assertResponseCodeIs(200,$ch,'Something went wrong.');
		$relatedModels = $this->accounts('testQuote')->relatedX2Models;
		$contactId = $this->contacts('testAnyone')->id;
		$newRelated = array_filter($relatedModels,function($m)use($contactId){return get_class($m) == 'Contacts' && $m->id == $contactId;});
		$this->assertEquals(1,count($newRelated),'Failed asserting that a new relationship was added.');
		// Step 3: Test deletion (delete the one that was just created):
		$urlParam['{params}'] = '?'.http_build_query($postData,'','&');
		$ch = $this->getCurlHandle($urlParam);
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');
		$cr = curl_exec($ch);
		$this->assertResponseCodeIs(200, $ch,"Couldn't delete. Response from server: ".$cr);
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
		$model = Contacts::model()->findByPk($this->contacts['testUser']['id']);
		$modelTags = $model->getTags();
		$tagsNotAdded = array_diff($tags,$modelTags);
		$this->assertEmpty($tagsNotAdded,'Failed asserting that tags were added: '.implode(',',$tagsNotAdded).'; model tags = '.json_encode($modelTags).' model id = '.$model->id);
		
		// Test getting tags:
		$urlParam['{params}'] = '?'.http_build_query(array_merge($this->param, array('id'=>$model->id)),'','&');
		$ch = $this->getCurlHandle($urlParam);
		$cr = curl_exec($ch);
		file_put_contents('api_response.html', $cr);
		$this->assertResponseCodeIs(200,$ch,'Failed asserting that a request to query tags succeeded.');
		$tagsOnServer = json_decode($cr,1);
		$setDiff = array_diff($tags,$tagsOnServer);
		$this->assertEmpty($setDiff);
		
		// Test deletion of those tags:
		foreach($tags as $tag) {
			$urlParam['{params}'] = '?'.http_build_query(array_merge($this->param, array('id'=>$model->id,'tag'=>ltrim($tag,'#'))),'','&');
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
            Yii::app()->cache->flush();
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
                'assignedTo' => 'testuser'
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
                'assignedTo' => 'admin'
			),
			'Docs' => array(
				'name' => 'Excellent birds',
				'subject' => 'Test subject',
				'text' => 'Vel lorem risus, massa, sociis sagittis! Turpis magna sed, tristique? Pid massa integer facilisis...',
			),
			'Opportunity' => array(
				'name' => 'Falling snow',
				'description' => 'Urna dolor? Sed tortor arcu mid porttitor egestas pulvinar etiam, diam? Aliquam, cras ultricies elementum?...',
                'assignedTo' => 'testuser'
			),
			'Product' => array(
				'name' => 'Excellent snow',
				'description' => "Enim amet! Porttitor, amet pulvinar augue sed auctor, phasellus pellentesque nunc nunc amet proin...",
				'type' => 'watch it fall'
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
		$n_users = count($this->users);
		$i = 0;
		foreach($this->users as $userParam){
			$param = array('user'=>$userParam['username'],'userKey'=>$userParam['userKey']);
			$apiModel = $this->newModel($userParam['username'],$userParam['userKey']);
			$i++;
			foreach($modelAttrs as $class => $attrs){
//			echo "Testing creation of $class record through API\n";
				$urlParam['{model}'] = $class;
				$ch = $this->getCurlHandle($urlParam, array_merge($param, $attrs));
				$cr = curl_exec($ch);
				file_put_contents('api_response.html', $cr);
				$authAction = $classModulesMap[$class].'Create';
				$this->assertResponseCodeIs(200,$ch,'Failed create operation. Response = '.$cr);
				$access = $this->assertAuthControlCorrectness($authAction,$ch,$apiModel);
				if($access){
					$userAttrs = array();
					foreach(array('createdBy','updatedBy') as $name)
						if(X2Model::model($class)->hasAttribute($name))
							$userAttrs[$name] = $param['user'];
                    if($class == 'Services') // Test setting a linktype field and having it turn into a nameId reference:
                        $attrs['contactId'] = Fields::nameId($this->contacts('testUser')->name,$this->contacts('testUser')->id);
					$models[$class] = X2Model::model($class)->findByAttributes(array_merge($attrs,$userAttrs));
					$this->assertTrue((bool) $models[$class], "Model of class $class not created properly when user = {$param['user']}. The response was: $cr.");
					foreach($attrs as $attr => $value)
						$this->assertEquals($value, $models[$class]->$attr);
					// Test that createDate was set properly:
					if($models[$class]->hasAttribute('createDate'))
						$this->assertNotNull($models[$class]->createDate);
				}
			}
		}
		
		// We've got our models. Now let's test finding by attributes ("lookup"):
		$urlParam['{action}'] = 'view';
		// We're going to need primary keys for the direct "view" read action:
		$pkValues = array();
		foreach($this->users as $userParam){
			$param = array('user'=>$userParam['username'],'userKey'=>$userParam['userKey']);
			$apiModel = $this->newModel($userParam['username'],$userParam['userKey']);
			foreach($modelAttrs as $class => $attrs){
				$urlParam['{model}'] = $class;
				$ch = $this->getCurlHandle($urlParam, array_merge($param, array('id'=>$models[$class]->id)));
				$cr = curl_exec($ch);
				file_put_contents('api_response.html', $cr);
				$authAction = $classModulesMap[$class].'View';
				$access = $this->assertAuthControlCorrectness($authAction, $ch,$apiModel);
				if($access){
					$this->assertResponseCodeIs(200, $ch);
					$queriedModel = CJSON::decode($cr);
					// Response must be valid JSON:
					$this->assertEquals('array', gettype($queriedModel));
					$models[$class]->refresh();
					// Test that the attributes are all equal. This is pretty much overkill.
					foreach($queriedModel as $attr => $value){
						$this->assertEquals($models[$class]->$attr, $value,"Failed asserting attribute equality for $class.$attr");
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
		$param = array('user'=>$userParam['username'],'userKey'=>$userParam['userKey']);
		foreach($pkValues as $class => $pk){

			$urlParam['{model}'] = $class;
			$get = array();
			if(is_array($pk)) // Composite primary key
				$get = array_merge($get, $pk);
			else // Single-column primary key
				$get[$models[$class]->tableSchema->primaryKey] = $pk;
			$urlParam['{params}'] = '?'.http_build_query($get,'','&');
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

		foreach($this->users as $userParam){
			$param = array('user'=>$userParam['username'],'userKey'=>$userParam['userKey']);
			$apiModel = $this->newModel($userParam['username'],$userParam['userKey']);
			foreach($pkValues as $class => $pk){
				$urlParam['{model}'] = $class;
				$post = array_merge($param, $modelAttrs[$class]);
				$get = array();
				if(is_array($pk)) // Composite primary key
					$get = array_merge($get, $pk);
				else // Single-column primary key
					$get[$models[$class]->tableSchema->primaryKey] = $pk;
				$urlParam['{params}'] = '?'.http_build_query($get,'','&');
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
		
		// Test errors.
		// We'll need use the admin user to avoid unnecessary 403's.

		// Test validation errors.
		$class = 'Docs';
		$oldModelAttrs = $modelAttrs[$class]['subject'];
		$modelAttrs[$class]['subject'] = implode(',',range(1,10000));
		$urlParam['{model}'] = $class;
		$param = array('user' => $this->users('admin')->username,'userKey' => $this->users('admin')->userKey);
		$post = array_merge($param,$modelAttrs[$class]);
		$get = array();
		$pk = $pkValues[$class];
		if(is_array($pk)) // Composite primary key
			$get = array_merge($get,$pk);
		else // Single-column primary key
			$get[$models[$class]->tableSchema->primaryKey] = $pk;
		$urlParam['{params}'] = '?'.http_build_query($get,'','&');
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
		$urlParam['{params}'] = '?'.http_build_query($get,'','&');
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
		$urlParam['{params}'] = '?'.http_build_query($get,'','&');
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
		

		// Test deletion. 
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
