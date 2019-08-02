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




Yii::import('application.components.util.*');

/**
 * @package application.tests.unit.components.util
 */
class RequestUtilTest extends X2TestCase {

    private static $_appFileUtilState = array ();

    public static function setUpBeforeClass () {
        self::$_appFileUtilState['alwaysCurl'] = AppFileUtil::$alwaysCurl;
        self::$_appFileUtilState['neverCurl'] = AppFileUtil::$neverCurl;
        return parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        AppFileUtil::$alwaysCurl = self::$_appFileUtilState['alwaysCurl'];
        AppFileUtil::$neverCurl = self::$_appFileUtilState['neverCurl'];
        return parent::tearDownAfterClass ();
    }

    public function testFileGetContentsRequest () {
        AppFileUtil::$neverCurl = true;
        $url = X2_TESTING_UPDATE_SERVER . '/installs/registry/getLicenseKeyInfo';
        $this->assertFalse (AppFileUtil::tryCurl ($url));
        $response = CJSON::decode (RequestUtil::request (array (
            'url' => $url,
            'method' => 'POST',
            'content' => array (
                'unique_id' => 'invalid',
            )
        )));
        if(X2_TEST_DEBUG_LEVEL > 1) {
            var_dump($response);
        }
        $this->assertTrue(array_key_exists('dateExpires',$response));
        $this->assertTrue(array_key_exists('maxUsers',$response));
    }

    public function testFileGetContentsRequestGET () {
        AppFileUtil::$neverCurl = true;
        $url = X2_TESTING_UPDATE_SERVER . '/installs/updates/updateCheck';
        $this->assertFalse (AppFileUtil::tryCurl ($url));
        $response = RequestUtil::request (array (
            'url' => $url,
            'method' => 'GET',
        ));
        if(X2_TEST_DEBUG_LEVEL > 1) {
            var_dump($response);
        }
        $this->assertEquals (1, preg_match ('/^\d(\.\d)*$/', $response));
    }

    public function testCurlRequest () {
        AppFileUtil::$neverCurl = false;
        AppFileUtil::$alwaysCurl = true;
        $url = X2_TESTING_UPDATE_SERVER . '/installs/registry/getLicenseKeyInfo';
        $this->assertTrue (AppFileUtil::tryCurl ($url));
        $response = CJSON::decode (RequestUtil::request (array (
            'url' => $url,
            'method' => 'POST',
            'content' => array (
                'unique_id' => 'invalid',
            )
        )));
        if(X2_TEST_DEBUG_LEVEL > 1) {
            var_dump($response);
        }
        $this->assertTrue(array_key_exists('dateExpires',$response));
        $this->assertTrue(array_key_exists('maxUsers',$response));
    }

    public function testCurlRequestGET () {
        AppFileUtil::$neverCurl = false;
        AppFileUtil::$alwaysCurl = true;
        $url = X2_TESTING_UPDATE_SERVER . '/installs/updates/updateCheck';
        $this->assertTrue (AppFileUtil::tryCurl ($url));
        $response = RequestUtil::request (array (
            'url' => $url,
            'method' => 'GET',
        ));
        if(X2_TEST_DEBUG_LEVEL > 1) {
            var_dump($response);
        }
        $this->assertEquals (1, preg_match ('/^\d(\.\d)*$/', $response));
    }

}

?>
