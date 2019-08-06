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




Yii::import('application.components.util.ResponseUtil');

/**
 * Test of the standalone response utility.
 *
 * Copies an ad-hoc script into the web root during the test; it is necessary
 * to test that it creates proper HTTP responses.
 *
 * @package application.tests.unit.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ResponseUtilTest extends CURLTestCase {

    public function getHttp200Aliases(){
        return array_keys(ResponseUtil::getStatusMessages());
    }

    public static $scriptPath;

    public function getResponseObject($args) {
        return json_decode($this->getCurlResponse($args),1);
    }


    public static function setUpBeforeClass() {
        copy(self::webscriptsBasePath().DIRECTORY_SEPARATOR.'responseUtilTest.php',
             self::$scriptPath = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..','responseUtilTest.php')));
        parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass(){
        parent::tearDownAfterClass();
        if(file_exists(self::$scriptPath)) {
            unlink(self::$scriptPath);
        }
    }

    public function urlFormat(){
        return 'responseUtilTest.php?case={case}';
    }
    
    public function testEnd() {
        $prevShutdown = ResponseUtil::$shutdown;
        // Test with string
        ResponseUtil::$shutdown = 'echo "responded";';
        ob_start();
        try {
            ResponseUtil::respond('I have ',true,true);
            $echoed = ob_get_clean();
        }catch(Exception $e){
            ob_end_flush();
            $this->assertTrue(false,'Ran into error in ResponseUtil::respond! '.$e->getMessage());
        }
        $this->assertEquals("I have\nresponded", $echoed,'Shutdown code (string) did not run properly!');

        // Test with a closure:
        ResponseUtil::$shutdown = function(){echo "responded";};
        ob_start();
        try {
            ResponseUtil::respond('I have',true,true);
            $echoed = ob_get_clean();
        }catch(Exception $e){
            ob_end_flush();
            $this->assertTrue(false,'Ran into error in ResponseUtil::respond! '.$e->getMessage());
        }
        $this->assertEquals("I have\nresponded", $echoed,'Shutdown code (anonymous function) did not run properly!');
        ResponseUtil::$shutdown = $prevShutdown;
    }

    public function testGetObject() {
        $this->assertFalse(ResponseUtil::getObject());
    }

    public function testIsCli() {
        $this->assertTrue(ResponseUtil::isCli());
        $notCli = $this->getCurlResponse(array('{case}'=>'isCli'));
        $this->assertEquals(1,$notCli);
    }

    public function testRespond(){
        // Test non-ending responses (ending ones were tested in testEnd)
        ob_start();
        try{
            ResponseUtil::respond('responded');
            $echoed = ob_get_clean();
        }catch(Exception $e){
            ob_end_flush();
            $this->assertTrue(false, 'Ran into error in ResponseUtil::respond! '.$e->getMessage());
        }
        $this->assertEquals("responded\n",$echoed,'ResponseUtil::respond() did not respond!');
        
        // A basic web response, without error:
        $ch = $this->getCurlHandle(array('{case}'=>'respond.errFalse'));
        $response = json_decode(curl_exec($ch),1);
        if(X2_TEST_DEBUG_LEVEL > 1) {
            var_dump($response);
        }
        $this->assertTrue(is_array($response));
        $this->assertFalse($response['error']);
        $this->assertEquals('errFalse', $response['message']);
        $this->assertResponseCodeIs(200, $ch);
        // With error:
        $ch = $this->getCurlHandle(array('{case}'=>'respond.errTrue'));
        $response = json_decode(curl_exec($ch),1);
        if(X2_TEST_DEBUG_LEVEL > 1) {
            var_dump($response);
        }
        $this->assertTrue(is_array($response));
        $this->assertTrue($response['error']);
        $this->assertEquals('errTrue', $response['message']);
        $this->assertResponseCodeIs(400, $ch);
        // With extra attribute:
        $ch = $this->getCurlHandle(array('{case}'=>'respond.property'));
        if(X2_TEST_DEBUG_LEVEL > 1) {
            var_dump($response);
        }
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(200, $ch);
        $this->assertArrayHasKey('property', $response);
        $this->assertEquals('value',$response['property']);
    }

    public function testRespondWithError() {
        // Skip over non-fatal error
        $r = $this->getResponseObject(array('{case}'=>'respondWithError.nonFatalFalse'));
        $this->assertEquals('All clear!',$r['message'],"Non-fatal error triggered response");
        // Catch non-fatal error
        $r = $this->getResponseObject(array('{case}'=>'respondWithError.nonFatalTrue'));
        $this->assertRegExp('/Error \['.E_USER_NOTICE.'\]: Ad\-hoc error/',$r['message'],"Non-fatal error didn't trigger resopnse");
        // Catch non-fatal error and include a long error trace
        $r = $this->getResponseObject(array('{case}'=>'respondWithError.longErrorTrace'));
        $this->assertTrue(strpos($r['message'],"Trace:\n")!==false,"Didn't respond with long trace");
    }

    public function testRespondFatalError() {
        $r = $this->getResponseObject(array('{case}'=>'respondFatalErrorMessage.parse'));
        $this->assertRegExp('/PHP parse error \['.E_PARSE.'\]/',(string) $r['message']);
        $r = $this->getResponseObject(array('{case}'=>'respondFatalErrorMessage.class'));
        $this->assertRegExp('/PHP fatal error \['.E_ERROR.'\]/',(string) $r['message']);
    }

    public function testRespondWithException() {
        $r = $this->getResponseObject(array('{case}'=>'respondWithException.normal'));
        $this->assertTrue(strpos($r['message'],"Exception: \"I'm dyin' here.\"") === 0);
        $r = $this->getResponseObject(array('{case}'=>'respondWithException.long'));
        $this->assertTrue(strpos($r['message'],"Trace:\n")!==false,"Didn't respond with long trace");
    }

    public function testCatchDouble() {
        $r = $this->getResponseObject(array('{case}'=>'catchDouble'));
        $this->assertTrue(strpos($r['message'],'Exception: "A response has already been declared."') === 0);
    }

    public function testSendHttp() {
        // Setting response codes:
        foreach($this->getHttp200Aliases() as $code) {
            $ch = $this->getCurlHandle(array('{case}' => 'sendHttp.'.$code));
            curl_exec($ch);
            $this->assertResponseCodeIs($code, $ch,'ResponseUtil did not set the proper HTTP response code.');
        }
        // Using an invalid HTTP response status code
        $ch = $this->getCurlHandle(array('{case}'=>'sendHttp.badCode'));
        $r = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(500,$ch);
        $this->assertEquals('Internal server error: invalid or non-numeric HTTP response status code specifed.',$r['message']);
        
        // Setting headers:
        $this->assertHasHeaders(array('{case}' => 'sendHttp.extraHeader'), array(
            'Content-Type' => 'application/json',
            'Content-MD5' => 'Y2M5ZmQ3OTU3ZGY1ZjJmNmVhOGY5YzhmMzUzOWE2MWI='
        ));

        // Setting the body in raw format
        $this->assertHasHeaders(array('{case}'=>'sendHttp.raw'),array(
            'Content-Type' => 'text/plain'
        ));

        // Using the raw body override
        $r = $this->getCurlResponse(array('{case}'=>'sendHttp.raw'));
        $this->assertEquals('The message in plain text.',$r);
    }

    public function testSetProperties() {
        $r = $this->getResponseObject(array('{case}'=>'setProperties'));
        $this->assertEquals(array(
            'foo'=>'bar',
            'message'=>'ni',
            'error' => false,
            'status' => 200
        ),$r);
        // array('foo'=>'bar','message'=>'ni')
    }

}

?>
