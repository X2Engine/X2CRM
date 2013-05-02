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

/**
 * CRUD test for X2CRM's REST API
 */
class ApiControllerTest extends CURLTestCase {
	
	public $fixtures = array(
		'accounts' => 'Accounts',
		'actions' => 'Actions',
		'contacts' => 'Contacts',
		'docs' => 'Docs',
		'opportunities' => 'Opportunity',
		'products' => 'Product',
		'services' => 'Services',
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

	public function urlFormat() {
		return 'api/{action}/model/{model}{params}';
	}
	
	/**
	 * Test the creation, reading, updating and deletion of records through the 
	 * API, and validation errors.
	 */
	public function testCRUD() {
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

		// Stash models in here:
		$models = array();
		// Test the creation of model records:
		foreach ($modelAttrs as $class=>$attrs) {
//			echo "Testing creation of $class record through API\n";
			$urlParam['{model}'] = $class;
			$ch = $this->getCurlHandle($urlParam, array_merge($param, $attrs));
			curl_setopt($ch,CURLOPT_HTTP200ALIASES,array(500));
			$cr = curl_exec($ch);
			file_put_contents('api_response.html',$cr);
			$models[$class] = X2Model::model($class)->findByAttributes($attrs);
			$this->assertTrue((bool) $models[$class],"Model of class $class not created. The response was: $cr");
			foreach ($attrs as $attr => $value)
				$this->assertEquals($value, $models[$class]->$attr);
			// Test that createDate was set properly:
			if ($models[$class]->hasAttribute('createDate'))
				$this->assertNotNull($models[$class]->createDate);
			// Test that the username attributes were set properly. In the case
			// of a service module case: test that it's assigned to whoever is
			// assigned the contact associated with the case.
			foreach (array('createdBy','assignedTo','updatedBy') as $attr) {
				if ($models[$class]->hasAttribute($attr)) {
					// echo "$class::$attr = {$models[$class]->$attr}\n";
					$models[$class]->refresh();
					$this->assertEquals($this->param['user'], $models[$class]->$attr,"Failed asserting $attr was set properly on creation of {$class}");
				}
			}
		}
		
		// We've got our models. Now let's test finding by attributes ("lookup"):
		$urlParam['{action}'] = 'lookup';
		// We're going to need primary keys for the direct "view" read action:
		$pkValues = array();
		foreach($modelAttrs as $class=>$attrs) {
			$urlParam['{model}'] = $class;
			$ch = $this->getCurlHandle($urlParam,array_merge($param,$attrs));
			curl_setopt($ch,CURLOPT_HTTP200ALIASES,array(500));
			$cr = curl_exec($ch);
			file_put_contents('api_response.html',$cr);
			$this->assertEquals(200,  curl_getinfo($ch,CURLINFO_HTTP_CODE));
			$queriedModel = CJSON::decode($cr);
			// Response must be valid JSON:
			$this->assertEquals('array',  gettype($queriedModel));
			// Test that the attributes are all equal. This is pretty much overkill:
//			foreach($cr as $attr=>$value) {
//				$this->assertEquals($models[$class]->$attr,$queriedModel[$attr]);
//			}
			// This will be useful for the next tests (lookup by pk, update & delete):
			$pkValues[$class] = $models[$class]->primaryKey;
		}
		
		// Test "view": lookup by ID:
		$urlParam['{action}'] = 'view';
		foreach($pkValues as $class=>$pk) {
			$urlParam['{model}'] = $class;
			$get = array();
			if(is_array($pk)) // Composite primary key
				$get = array_merge($get,$pk);
			else // Single-column primary key
				$get[$models[$class]->tableSchema->primaryKey] = $pk;
			$urlParam['{params}'] = '?'.http_build_query($get);
			$ch = $this->getCurlHandle($urlParam,$param);
			curl_setopt($ch,CURLOPT_HTTP200ALIASES,array(500));
			$cr = curl_exec($ch);
			file_put_contents('api_response.html',$cr);
			$this->assertEquals(200,curl_getinfo($ch,CURLINFO_HTTP_CODE));
			$queriedModel = CJSON::decode($cr);
			$this->assertEquals('array', gettype($queriedModel));
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
		foreach($pkValues as $class=>$pk) {
			$urlParam['{model}'] = $class;
			$post = array_merge($param,$modelAttrs[$class]);
			$get = array();
			if(is_array($pk)) // Composite primary key
				$get = array_merge($get,$pk);
			else // Single-column primary key
				$get[$models[$class]->tableSchema->primaryKey] = $pk;
			$urlParam['{params}'] = '?'.http_build_query($get);
			$ch = $this->getCurlHandle($urlParam,$post);
			curl_setopt($ch,CURLOPT_HTTP200ALIASES,array(500));
			$cr = curl_exec($ch);
			file_put_contents('api_response.html',$cr);
			$this->assertEquals(200,curl_getinfo($ch,CURLINFO_HTTP_CODE));
			// Refresh the stowed model and verify that it was updated properly:
			$models[$class]->refresh();
			foreach($modelAttrs[$class] as $attr=>$value) {
				$this->assertEquals($value,$models[$class]->$attr);
			}
		}
		
		// Test deletion. We'll need use the admin user to avoid 403's. Comment
		// out these next two lines to check that RBAC filtering works properly.
		// When doing so, the test should fail, and the error should be "Failed
		// asserting that 403 matches expected 200" (when attempting to delete
		// an "Accounts" record, which ordinary users can't do).
		$param['user'] = 'admin';
		$param['userKey'] = '21232f297a57a5a743894a0e4a801fc3';
		$urlParam['{action}'] = 'delete';
		$urlParam['{params}'] = '';
		foreach ($pkValues as $class => $pk) {
			$urlParam['{model}'] = $class;
			$post = $param;
			if (is_array($pk)) // Composite primary key
				$post = array_merge($post, $pk);
			else // Single-column primary key
				$post[$models[$class]->tableSchema->primaryKey] = $pk;
			$ch = $this->getCurlHandle($urlParam, $post);
			curl_setopt($ch, CURLOPT_HTTP200ALIASES, array(500));
			$cr = curl_exec($ch);
			file_put_contents('api_response.html', $cr);
			$this->assertEquals(200, curl_getinfo($ch, CURLINFO_HTTP_CODE));
			$model = X2Model::model($class)->findByPk($pk);
			// No more model matching PK?
			$this->assertFalse((bool)$model);
		}
	}
}

?>
