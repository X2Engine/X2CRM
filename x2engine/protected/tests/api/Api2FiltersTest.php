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




Yii::import('application.tests.api.Api2TestBase');

/**
 * Filter-level access tests for the 2nd-gen REST API
 *
 * This test is distinct from the more full test in order to be more efficient,
 * since many of the fixtures needed in that aren't needed in this.
 * 
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2FiltersTest extends Api2TestBase {

    
    /**
     * Default (built-in) {@link Api2Settings} attribute values
     * @var array
     */
    public static $defaultSettings;
    /**
     * Currently-active {@link Api2Settings} in the database as of before test
     * @var array
     */
    public static $oldSettings;

    /**
     * Reset the API settings to those given.
     */
    public static function settings($settings=null) {
        if(!is_array($settings)) {
            $settings = self::$defaultSettings;
        }
        Yii::app()->settings->api2->setAttributes($settings, false);
        Yii::app()->settings->save();
    }

    public static function setUpBeforeClass(){
        self::$oldSettings = Yii::app()->settings->api2->attributes;
        $defaultSettings = new Api2Settings;
        self::$defaultSettings = $defaultSettings->attributes;
        self::settings();
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(){
        parent::tearDownAfterClass();
        self::settings(self::$oldSettings);
    }
    

    public function urlFormat(){
        return 'api2/{action}';
    }

    /**
     * Authenticate improperly a number of times.
     * @param type $n Fail authenticating this many times
     * @param boolean $assert If not false, assert the response code matches
     * @return type
     */
    public function failAtAuthenticating($n=1,$assert=401) {
        $param = array(
            '{action}' => 'appInfo.json'
        );
        foreach(range(1,3) as $i){
            $ch = $this->getCurlHandle('GET', $param);
            curl_setopt($ch, CURLOPT_USERPWD, 'admin:notMyKey');
            curl_exec($ch);
            if($assert)
                $this->assertResponseCodeIs($assert, $ch);
        }
    }

    /**
     * Retrieves content from the "hello world" action.
     */
    public function fetchAppInfo($assert = 200,$message = ''){
        $ch = $this->getCurlHandle('GET', array('{action}' => 'appInfo.json'));
        $response = json_decode(curl_exec($ch), 1);
        if($assert){
            $this->assertResponseCodeIs($assert, $ch,'Response = '.json_encode($response).(empty($message)?'':('; '.$message)));
            $this->assertTrue(is_array($response));
            if($assert == 200){
                $this->assertArrayHasKey('clientAddress', $response);
            }
        }
        return $response;
    }

    /**
     * Basic app entry
     */
    public function testFilters(){
        $paramOk = array('{action}'=>'appInfo.json');
        $param404 = array('{action}' => 'noaction');

        // TEST: filterAvailable
        //
        // Test that 503 is properly issued during full app lock-down
        Yii::app()->locked = time();
        $ch = $this->getCurlHandle('GET', $paramOk);
        curl_exec($ch);
        $this->assertResponseCodeIs(503, $ch);
        Yii::app()->locked = false;
        
        
        //////////////////////////////////////////////////////////
        // PLATINUM-ONLY ADVANCED ACCESS CONTROL SETTINGS TESTS //
        //////////////////////////////////////////////////////////

        // Disabling the API via the settings
        self::settings(array('enabled' => false));
        $ch = $this->getCurlHandle('GET', $paramOk);
        curl_exec($ch);
        $this->assertResponseCodeIs(503, $ch);
        
        // TEST: filterRestrictions
        //
        // First we're going to need the client IP address, which could vary
        // based on the test configuration. While doing this we can perform
        // another few simple tests:
        self::settings();
        $response = $this->fetchAppInfo();
        $clientIp = $response['clientAddress'];
        // Plain old blacklisting of IP:
        Yii::app()->settings->api2->banIP($clientIp);
        Yii::app()->settings->save();
        $this->fetchAppInfo(403);
        Yii::app()->cache->flush();
        // Lock-out from failed authentication attempts:
        self::settings(array_merge(self::$defaultSettings, array(
            'maxAuthFail' => 3, // 3 strikes and yer out
            'lockoutTime' => 3600
        )));

        $this->failAtAuthenticating(3);
        // There have been three unsuccessful authentications. The IP should be
        // locked out by now:
        $this->failAtAuthenticating(1,403);
        // This resets the auth failure cooldown:
        Yii::app()->cache->flush();
        // This time, auto-ban!
        self::settings(array(
            'permaBan' => true
        ));
        $this->failAtAuthenticating(3);
        $this->failAtAuthenticating(1,403);
        // Check that this IP address has been blacklisted:
        Yii::app()->settings->refresh();
        $this->assertTrue(Yii::app()->settings->api2->isIpBlocked($clientIp));

        // Test rate limiting:
        Yii::app()->cache->flush();
        self::settings(array_merge(self::$defaultSettings,array(
            'maxRequests' => 3,
            'requestInterval' => 3600
         )));
        foreach(range(1,3) as $i) {
            $this->fetchAppInfo(200,"request number $i");
        }
        // On the fourth request there should be status 429
        $this->fetchAppInfo(429);

        Yii::app()->cache->flush();
        self::settings();

        /////////////////////////////
        // END PLATINUM-ONLY TESTS //
        /////////////////////////////
        

        // TEST: filterAuthenticate
        $this->failAtAuthenticating();
        $this->fetchAppInfo();

        // TEST: filterMethods
        //
        // Send a POST request to the hello world action, which doesn't support
        // anything except GET requests
        $ch = $this->getCurlHandle('POST', $paramOk, 'admin',
                array('name'=>'A different application name'));
        curl_exec($ch);
        $this->assertResponseCodeIs(405, $ch);
        // Do the same to the "fields" action:
        $ch = $this->getCurlHandle('POST', array('{action}'=>'fields'), 'admin',
                array('fieldName'=>'somethingElse'));
        curl_exec($ch);
        $this->assertResponseCodeIs(405, $ch);
        
        // TEST: filterContentType
        // 
        // Send a plain old URL-encoded data to contacts. No good.
        $ch = $this->getCurlHandle('PUT', array('{action}'=>'Contacts'), 'admin',
                array('this'=>'that'),
                array(CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded')));
        curl_exec($ch);
        $this->assertResponseCodeIs(415, $ch);
    }




}

?>
