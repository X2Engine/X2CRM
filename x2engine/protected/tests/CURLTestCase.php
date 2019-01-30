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




require_once('WebTestConfig.php');

/**
 * Non-database web request test case.
 *
 * This is hideously duplicated code from {@link CURLDbTestCase} because PHP
 * does not natively support mixins, and since we're working with child classes
 * of the PHPUnit framework classes, Yii behaviors are not an option. A distinct
 * class for doing non-database URL tests is necessary for performance's sake.
 *
 * Note, the one difference is that this uses TEST_WEBROOT_URL instead of
 * TEST_BASE_URL, to permit testing outside of X2Engine, i.e. ad-hoc scripts.
 *
 * @package application.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class CURLTestCase extends X2TestCase {

    /**
     * Allows responses w/error codes, so that we can examine the contents of the response even if the request failed
     * @return type
     */
    public function getHttp200Aliases(){
        return array(
            201,
            204,
            304,
            400,
            401,
            402,
            403,
            404,
            405,
            410,
            415,
            422,
            429,
            500,
            501,
            503,
        );
    }

    public $outsideX2 = true;

    public static function webscriptsBasePath() {
        return implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'tests','webscripts'));
    }

    /**
     * Run a request and check that a header field is present in the response
     * @param type $params
     * @param type $field
     * @param type $value
     * @param type $postData
     * @param type $options
     */
    public function assertHasHeaders($params,$fields,$postData=array()) {
        $ch = $this->getCurlHandle($params,$postData,array(
            CURLOPT_HEADER=>1,
        ));
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        foreach($fields as $field => $value){
            $this->assertRegExp(
                    sprintf('/^%s: %s/m', preg_quote($field,'/'), preg_quote($value,'/')),
                    $header,
                    "Header $field not found in response, or was not equal to \"$value\". Response body = $response");
        }
    }

    public function assertResponseCodeIs($code, $ch, $message = ''){
        $this->assertEquals($code, curl_getinfo($ch, CURLINFO_HTTP_CODE), $message);
    }

    public function getCurlResponse($params, $postData = array()){
        return curl_exec($this->getCurlHandle($params, $postData));
    }

    public function getCurlHandle($params, $postData = array(),$options = array()){
        $post = count($postData) > 0;
		$ch = curl_init(($this->outsideX2 ? TEST_WEBROOT_URL : TEST_BASE_URL). $this->url($params));
        $allOpts = array(
            CURLOPT_POST => $post,
            CURLOPT_RETURNTRANSFER => true, // Return the response data from curl_exec()
            CURLOPT_HTTP200ALIASES => $this->getHttp200Aliases(),
        );
        foreach($options as $const => $opt) {
            $allOpts[$const] = $opt;
        }
        curl_setopt_array($ch, $allOpts);
        if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        return $ch;
    }

    public function url($params = array()){
        return strtr($this->urlFormat(), $params);
    }

    public abstract function urlFormat();
}

?>
