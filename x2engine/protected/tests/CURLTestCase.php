<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

require_once('WebTestConfig.php');

/**
 * Base class for running quick back & forth web tests with cURL, i.e. for
 * testing X2CRM's remote API.
 * 
 * @package X2CRM.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class CURLTestCase extends X2DbTestCase {

	public function assertResponseCodeIs($code,$ch,$message='') {
		$this->assertEquals($code,curl_getinfo($ch,CURLINFO_HTTP_CODE),$message);
	}

	public function getCurlResponse($params,$postData=array()) {
		return curl_exec($this->getCurlHandle($params,$postData));
	}
	
	public function getCurlHandle($params,$postData=array()) {
		$post = count($postData) > 0;
		$ch = curl_init(TEST_BASE_URL . $this->url($params));
		curl_setopt_array($ch,array(
			CURLOPT_POST => $post,
			CURLOPT_RETURNTRANSFER => true, // Return the response data from curl_exec()
			CURLOPT_HTTP200ALIASES => array(400,401,403,404,413,500,501), // Allows responses w/error codes, so that we can examine the contents of the response even if the request failed
		));
		if ($post)
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		return $ch;
	}
	
	public function url($params = array()) {
		return strtr($this->urlFormat(), $params);
	}

	public abstract function urlFormat();
}

?>
