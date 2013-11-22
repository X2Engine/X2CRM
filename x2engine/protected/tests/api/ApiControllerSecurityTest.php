<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import('application.modules.users.models.User');

/**
 * Remote API authentication tests.
 * 
 * This is kept separate from ApiControllerTest to make it faster; it doesn't
 * require all the same fixtures.
 *
 * @package X2CRM.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ApiControllerSecurityTest extends CURLTestCase {
	
	public $fixtures = array(
		'users' => 'User',
	);

	public static function referenceFixtures(){
		return array();
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

	private $_urlFormat = 'api/{action}/model/{model}{params}';

	public function urlFormat() {
		return $this->_urlFormat;
	}
	
	public function testAuthenticate() {
		// This filter should be run before the validModel filter and hence,
		// it's safe to assume that a response code of 400 when requesting 
		// with an empty model parameter means that authentication succeeded.
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'create';
		
		// Test with no credentials (but empty array will designate it as a GET
		// request, so put something in the sending parameters)
		$param = array('foo'=>1);
		$ch = $this->getCurlHandle($urlParam,$param);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertResponseCodeIs(401,$ch);
		$this->assertRegExp('/No user credentials provided/',$response);

		// Test with invalid user:
		$param = $this->param;
		$param['user'] = 'idonotexist';
		$ch = $this->getCurlHandle($urlParam,$param);
		$response = curl_exec($ch);
		file_put_contents('api_response.html',$response);
		$this->assertResponseCodeIs(401,$ch,'Response is not what is expected for there being an invalid user');
		$this->assertRegExp('/Invalid user credentials/',$response);
		
		// Test user with empty API key
		$user = $this->users('testUser');
		$user->userKey = '';
		$user->save();
		$param = $this->param;
		$param['userKey'] = '';
		$ch = $this->getCurlHandle($urlParam,$param);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertResponseCodeIs(403,$ch);
		$this->assertRegExp('/cannot use API; userKey not set/',$response);

		// Test access permissions:
		$origUrlFormat = $this->_urlFormat;
		$param = array();
		$this->_urlFormat = 'api/checkPermissions/action/{action}/username/{username}/api/1';
		$urlParam['{username}'] = 'testuser';

		$auth = Yii::app()->authManager;
		$roles = RoleToUser::model()->findAllByAttributes(array('userId' => $this->users('testUser')->id));		
		foreach(array('Contacts','Actions','Quotes','Opportunities','Accounts','Products') as $module){
			foreach(array('Create','Update','View','Delete') as $action) {
				// Get response:
				$urlParam['{action}'] = $module.$action;
				$ch = $this->getCurlHandle($urlParam,$param);
				$apiAccess = curl_exec($ch) == 'true';
				$access = false;
				foreach ($roles as $role) {
					$access = $access || $auth->checkAccess($urlParam['{action}'], $role->roleId);
				}
				$this->assertEquals($access,$apiAccess,'Failed asserting consistency between API-reported permissions and internal app permissions.');
			}
		}
		$this->_urlFormat = $origUrlFormat;
	}
	
	public function testValidModel() {
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'create';
		
		// Missing model parameter
		$ch = $this->getCurlHandle($urlParam,$this->param);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(400, curl_getinfo($ch,CURLINFO_HTTP_CODE));

		// Model class doesn't exist
		$urlParam['{model}'] = 'CockadoodleDoo';
		$ch = $this->getCurlHandle($urlParam,$this->param);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(501, curl_getinfo($ch,CURLINFO_HTTP_CODE));
		
		// Model class exists but isn't a child of X2Model
		$urlParam['{model}'] = 'Admin'; // Nobody should be able to change this!
		$ch = $this->getCurlHandle($urlParam,$this->param);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(403, curl_getinfo($ch,CURLINFO_HTTP_CODE));
	}

	public function testListUsers() {
		$this->_urlFormat = 'api/listUsers';
		$urlParam = array();
		$this->users('testUser')->refresh();
		$this->param['user'] = $this->users('testUser')->username;
		$this->param['userKey'] = $this->users('testUser')->userKey;
		// First test retrieving user list with nonprivileged user:
		$ch = $this->getCurlHandle($urlParam,$this->param);
		$list = CJSON::decode(curl_exec($ch));
		$this->assertEquals('array',gettype($list),'Failed asserting API responded with valid JSON');
		foreach($list as $user) {
			foreach(array('password','userKey') as $restrictedField) {
				$this->assertArrayNotHasKey($restrictedField, $user, "Failed asserting non-priveleged user cannot see $restrictedField.");
			}
		}
		// Now test getting with admin user (WARNING: will respond with API keys!)
		$this->param['user'] = $this->users('admin')->username;
		$this->param['userKey'] = $this->users('admin')->userKey;
		$ch = $this->getCurlHandle($urlParam,$this->param);
		$list = CJSON::decode(curl_exec($ch));
		$this->assertEquals('array',gettype($list),'Failed asserting API responded with valid JSON');
		foreach($list as $user) {
			foreach(array('password','userKey') as $restrictedField) {
				$this->assertArrayHasKey($restrictedField, $user, "Failed asserting admin can see $restrictedField.");
			}
		}
	}

}

?>
