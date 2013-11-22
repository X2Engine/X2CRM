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
