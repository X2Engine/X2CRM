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




Yii::import('application.modules.users.models.User');

/**
 * Remote API authentication tests.
 * 
 * This is kept separate from ApiControllerTest to make it faster; it doesn't
 * require all the same fixtures.
 *
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ApiControllerSecurityTest extends CURLDbTestCase {
	
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
		$param = $this->param;
		$user->userKey = $param['userKey'];
		$user->save();

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
                $access = $auth->checkAccess($urlParam['{action}'], $user->id);
                X2_TEST_DEBUG_LEVEL > 1 && println ('Action:');
                X2_TEST_DEBUG_LEVEL > 1 && print_r ($urlParam);
                X2_TEST_DEBUG_LEVEL > 1 && println ((int) $access);
                X2_TEST_DEBUG_LEVEL > 1 && println ((int) $apiAccess);
				$this->assertEquals((int) $access, (int) $apiAccess,'Failed asserting consistency between API-reported permissions and internal app permissions.');
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
        $this->assertTrue(
            501 == curl_getinfo($ch,CURLINFO_HTTP_CODE) ||
            preg_match ('/open_basedir restriction in effect/', $response)); 

		
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
