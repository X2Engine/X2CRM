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
 * 
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2HooksTest extends Api2TestBase {

    public static $scriptPath;

    public static function setUpBeforeClass() {
        copy(self::webscriptsBasePath().DIRECTORY_SEPARATOR.'api2HooksTest.php',
             self::$scriptPath = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..','api2HooksTest.php')));
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(){
        parent::tearDownAfterClass();
        if(file_exists(self::$scriptPath)) {
            unlink(self::$scriptPath);
        }
    }
    
    public function urlFormat() {
        return 'api2/Contacts/hooks{suffix}';
    }


    /**
     * Test subscribing and then receiving data:
     */
    public function testSubscription() {
        // 1: Request to subscribe:
        $hookName = 'ContactsCreate';
        $hook = array(
            'event' => 'RecordCreateTrigger',
            'target_url' => TEST_WEBROOT_URL.'/api2HooksTest.php?name='.$hookName
        );
        $ch = $this->getCurlHandle('POST',array('{suffix}'=>''),'admin',$hook,array(CURLOPT_HEADER=>1));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(201, $ch,X2_TEST_DEBUG_LEVEL > 1?$response:'');
        $trigger = ApiHook::model()->findByAttributes($hook);

        // 2. Create a contact
        $contact = array(
            'firstName' => 'Walter',
            'lastName' => 'White',
            'email' => 'walter.white@sandia.gov',
            'visibility' => 1
        );
        $ch = curl_init(TEST_BASE_URL.'api2/Contacts');
        $options = array(
            CURLOPT_POSTFIELDS => json_encode($contact),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true
        );
        foreach($this->authCurlOpts() as $opt=>$optVal)
            $options[$opt] = $optVal;
        curl_setopt_array($ch,$options);
        $response = curl_exec($ch);
        $c = Contacts::model()->findByAttributes($contact);
        $this->assertResponseCodeIs(201, $ch);
        $this->assertNotEmpty($c);

        // 3. Test that the receiving end got the payload
        $outputDir = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'output'
        ));
        $this->assertFileExists($outputDir.DIRECTORY_SEPARATOR."hook_$hookName.json");
        $contactPulled = json_decode(file_get_contents($outputDir.
                DIRECTORY_SEPARATOR."hook_pulled_$hookName.json"),1);
        foreach($contact as $field=>$value) {
            $this->assertEquals($value,$contactPulled[$field]);
        }
    }

}

?>
