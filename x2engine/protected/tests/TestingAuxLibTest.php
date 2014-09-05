<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

class TestingAuxLibTest extends X2DbTestCase {

    public $fixtures = array (
        'authItems' => array (':x2_auth_item', '.MassDeleteTest'),
        'authItemChildren' => array (':x2_auth_item_child', '.MassDeleteTest'),
        'users' => 'User',
        'profiles' => 'Profile',
    );

    public function testSetPublic () {
        $fn = TestingAuxLib::setPublic ('TestingAuxLib', 'privateMethod');
        $this->assertTrue ($fn (array (1, 2)) === array (1, 2));
    }

    /**
     * Attempt to login with curlLogin and ensure that a page which requires login can be viewed 
     */
    public function testCurlLogin () {
        // ensure that page which should require login can't be viewed before logging in
        $sessionId = uniqid ();
        $cookies = "PHPSESSID=$sessionId; path=/;";
        $curlHandle = curl_init (TEST_BASE_URL.'profile/settings');
        curl_setopt ($curlHandle, CURLOPT_HTTPGET, 1);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
        ob_start ();
        $result = curl_exec ($curlHandle);
        ob_clean ();
        $this->assertFalse ((bool) preg_match ('/Change Personal Settings/', $result));

        // log in and then request the same page 
        $sessionId = TestingAuxLib::curlLogin ('testuser', 'password');
        $cookies = "PHPSESSID=$sessionId;";
        $curlHandle = curl_init (TEST_BASE_URL.'profile/settings');
        curl_setopt ($curlHandle, CURLOPT_HTTPGET, 1);
        curl_setopt ($curlHandle, CURLOPT_HEADER, 1);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
        ob_start ();
        $result = curl_exec ($curlHandle);
        ob_clean ();
        //print_r ("document.cookie = 'PHPSESSID=$sessionId; path=/;';\n");
        //print_r ($result);
        $this->assertTrue ((bool) preg_match ('/Change Personal Settings/', $result));
    }
}

?>
