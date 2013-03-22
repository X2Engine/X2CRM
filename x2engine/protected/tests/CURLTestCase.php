<?php
require_once('WebTestConfig.php');

/**
 * Base class for running quick back & forth web tests with cURL
 * 
 * @package X2CRM.tests
 */
abstract class CURLTestCase extends X2DbTestCase {

	public function getCurlResponse($params,$postData=array()) {
		return curl_exec($this->getCurlHandle($params,$postData));
	}
	
	public function getCurlHandle($params,$postData=array()) {
		$ch = curl_init(TEST_BASE_URL . $this->url($params));
		$post = count($postData) > 0;
		curl_setopt($ch, CURLOPT_POST, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
