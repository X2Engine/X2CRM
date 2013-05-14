<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::import('application.modules.users.models.User');

/**
 * Remote API authentication tests.
 * 
 * This is kept separate from ApiControllerTest to make it faster; it doesn't
 * require all the same fixtures.
 *
 * @package X2CRM.tests.functional.controllers
 */
class ApiControllerSecurityTest extends CURLTestCase {
	
	public $fixtures = array(
		'users' => 'User',
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

	private $_urlFormat = 'api/{action}/model/{model}{params}';

	public function urlFormat() {
		return $this->_urlFormat;
	}
	
	public function testAuthenticate() {
		$alias200 = array(400,401,403,500,501);
		// This filter should be run before the validModel filter and hence,
		// it's safe to assume that a response code of 400 when requesting 
		// with an empty model parameter means that authentication succeeded.
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'create';
		
		// Test with no credentials (but empty array will designate it as a GET
		// request, so put something in the sending parameters)
		$param = array('foo'=>1);
		$ch = $this->getCurlHandle($urlParam,$param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(401,curl_getinfo($ch,CURLINFO_HTTP_CODE));
		$this->assertRegExp('/No user credentials provided/',$response);

		// Test with invalid user:
		$param = $this->param;
		$param['user'] = 'idonotexist';
		$ch = $this->getCurlHandle($urlParam,$param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(401,curl_getinfo($ch,CURLINFO_HTTP_CODE));
		$this->assertRegExp('/Invalid user credentials/',$response);
		
		// Test user with empty API key
		$user = $this->users('testUser');
		$user->userKey = '';
		$user->save();
		$param = $this->param;
		$param['userKey'] = '';
		$ch = $this->getCurlHandle($urlParam,$param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(403,curl_getinfo($ch,CURLINFO_HTTP_CODE));
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
		$alias200 = array(400,401,403,404,500,501);
		$urlParam = $this->urlParam;
		$urlParam['{action}'] = 'create';
		
		// Missing model parameter
		$ch = $this->getCurlHandle($urlParam,$this->param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(400, curl_getinfo($ch,CURLINFO_HTTP_CODE));

		// Model class doesn't exist
		$urlParam['{model}'] = 'CockadoodleDoo';
		$ch = $this->getCurlHandle($urlParam,$this->param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(501, curl_getinfo($ch,CURLINFO_HTTP_CODE));
		
		// Model class exists but isn't a child of X2Model
		$urlParam['{model}'] = 'Admin'; // Nobody should be able to change this!
		$ch = $this->getCurlHandle($urlParam,$this->param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
		$response = curl_exec($ch);
//		file_put_contents('api_response.html',$response);
		$this->assertEquals(403, curl_getinfo($ch,CURLINFO_HTTP_CODE));
	}

	public function testListUsers() {
		$this->_urlFormat = 'api/listUsers';
		$alias200 = array(400,401,403,404,500,501);
		$urlParam = array();
		// First test retrieving user list with nonprivileged user:
		$ch = $this->getCurlHandle($urlParam,$this->param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
		$list = CJSON::decode(curl_exec($ch));
		$this->assertEquals('array',gettype($list),'Failed asserting API responded with valid JSON');
		foreach($list as $user) {
			foreach(array('password','userKey') as $restrictedField) {
				$this->assertArrayNotHasKey($restrictedField, $user, "Failed asserting non-priveleged user cannot see $restrictedField.");
			}
		}
		// Now test getting with admin user (WARNING: will respond with API keys!)
		$this->param['user'] = 'admin';
		$this->param['userKey'] = '21232f297a57a5a743894a0e4a801fc3';
		$ch = $this->getCurlHandle($urlParam,$this->param);
		curl_setopt($ch,CURLOPT_HTTP200ALIASES,$alias200);
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
