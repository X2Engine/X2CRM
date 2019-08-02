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




Yii::import("application.tests.mocks.X2AuthManagerMock");

/**
 * Auxilliary Library for unit testing. A catch-all class for miscellaneous utility methods.
 * 
 * @package application.tests
 */
class TestingAuxLib  {

     
    /**
     * Method used by TestingAuxLibTest to test setPublic 
     */
    private function privateMethod ($arg1, $arg2) {
        return array ($arg1, $arg2);
    }

    private static $_caseTimer;
    public static function getCaseTimer () {
        if (!isset (self::$_caseTimer)) {
            self::$_caseTimer = new TimerUtil;
        }
        return self::$_caseTimer;
    }

    private static $_classTimer;
    public static function getClassTimer () {
        if (!isset (self::$_classTimer)) {
            self::$_classTimer = new TimerUtil;
        }
        return self::$_classTimer;
    }

    /**
     * Updates timestamps of session records 
     */
    public static function setUpSessions ($sessions) {
        foreach ($sessions as $session) {
            $model = Session::model ()->findByAttributes ($session);
            $model->lastUpdated = time ();
            $model->save ();
        }
    }

    public static function log ($str) {
        $logMessage = print_r ($str, true);
        if(X2_TEST_DEBUG_LEVEL > 0){
            /**/println("\n[".date ('H:i:s', time ())."] ".$logMessage);
        }
        Yii::log ($logMessage, 'info', 'system.test-output');
    }

    /**
     * Used to invoke methods which are protected or private.
     * @param string|object $classNameOrInstance
     * @param string $methodName 
     * @param function $wrapper will be passed reflection method and class name. Function should 
     *  return another function which itself returns $method->invokeArgs (). Allows for more 
     *  flexibile argument passing. Useful for cases where method expects references.
     * @return function public, standalone version of specified method
     */
    public static function setPublic (
        $classNameOrInstance, $methodName, $static=false, $wrapper=null) {
        if (!$static && is_string ($classNameOrInstance)) {
            $class = new $classNameOrInstance ();
            $className = $classNameOrInstance;
        } elseif (!$static) {
            $class = $classNameOrInstance;
            $className = get_class ($class);
        } else {
            $class = null;
            $className = $classNameOrInstance;
        }
        $method = new ReflectionMethod ($className, $methodName);
        $method->setAccessible (TRUE);

        if ($wrapper) {
            return $wrapper ($method, $class);
        } else {
            return function () use ($method, $class) {
                return $method->invokeArgs ($class, func_get_args ());
            };
        }
    }

    public static function setPrivateProperty ($className, $propertyName, $value, $instance=null) {
        $relectionClass = new ReflectionClass ($className);
        $reflectionProperty = $relectionClass->getProperty ($propertyName);
        $reflectionProperty->setAccessible (true);
        if (!$instance) {
            $reflectionProperty->setValue ($value);
        } else {
            $reflectionProperty->setValue ($instance, $value);
        }
    }

//    public static function getPrivateProperty ($className, $propertyName, $instance=null) {
//        $relectionClass = new ReflectionClass ($className);
//        $reflectionProperty = $relectionClass->getProperty ($propertyName);
//        $reflectionProperty->setAccessible (true);
//        if (!$instance) {
//            return $reflectionProperty->getValue ();
//        } else {
//            return $reflectionProperty->getValue ($instance);
//        }
//    }


    /**
     * Log in with the specified credentials.
     *
     * NOTE: in a non-web environment (i.e. command line, running PHPUnit)
     * this is not guaranteed to work, because Yii::app()->user is designed for
     * web sessions. To authenticate in the established web-or-console-agnostic
     * method, use {@link ApplicationConfigBehavior::setSuModel} (or
     * {@link suLogin}) instead.
     *
     * @return bool true if login was successful, false otherwise
     */
    public static function login ($username, $password) {
        $identity = new UserIdentity($username, $password);
        $identity->authenticate ();
		if($identity->errorCode === UserIdentity::ERROR_NONE) {
            if (Yii::app()->user->login ($identity, 2592000)) {
                if ($username === 'admin') {
                    Yii::app()->params->isAdmin = true;
                } else {
                    Yii::app()->params->isAdmin = false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Sets the substitute user model property of the application singleton
     *
     * This establishes a pseudo-session so that non-web-specific components'
     * methods that need userspace data to run properly can be executed from the
     * command line and do not need to depend on web-session-specific components.
     *
     * @param type $username
     */
    public static function suLogin($username) {
        $user = User::model()->findByAlias($username);
        if(!($user instanceof User)) {
            throw new CException ('failed to login as '.$username);
        }
        $profile = $user->profile;
        Yii::app()->setSuModel($user);
        Yii::app()->params->profile = $profile;
        return true;
    }

    /**
     * Login with curl and return the PHP session id (which can be used to make curl requests to 
     * pages that require authentication)
     * @return string PHP session id
     */
    public static function curlLogin ($username, $password) {
        // login and extract session id from response header
        $data = array (
            'LoginForm[username]' => $username,
            'LoginForm[password]' => $password,
            'LoginForm[rememberMe]' => 0,
        );
        $curlHandle = curl_init (TEST_BASE_URL.'site/login');
        curl_setopt ($curlHandle, CURLOPT_POST, true);
        curl_setopt ($curlHandle, CURLOPT_HEADER, true);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($curlHandle, CURLOPT_POSTFIELDS, http_build_query ($data));
        ob_start ();
        $result = curl_exec ($curlHandle);
        $matches = array ();
        preg_match_all ('/PHPSESSID=([^;]+);/', $result, $matches);
        //print_r ($matches);
        $sessionId = array_pop (array_pop ($matches)); // get the last match
        ob_clean ();
        return $sessionId;
    }

    public static function printExec ($command) {
        $output = array (); 
        $retVar;
        $retVal = exec ($command, $output, $retVar);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($output);
        return $output;
    }   

    public static function runCronCommand () {
        self::printExec ('curl -s '.TEST_BASE_URL.'api/x2cron &>/dev/null');
    }

// not tested yet, might eventually be useful
//    public function curlLogout ($sessionId) {
//        $cookies = "PHPSESSID=$sessionId; path=/;";
//        $curlHandle = curl_init ('localhost/index.php/site/logout');
//        curl_setopt ($curlHandle, CURLOPT_HEADER, true);
//        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
//        ob_start ();
//        $result = curl_exec ($curlHandle);
//        ob_clean ();
//        //AuxLib::debugLogR ($result);
//        return $sessionId;
//
//    }

    private static $_oldAuthManagerComponent;
    public static function loadAuthManagerMock () {
        if (!isset (self::$_oldAuthManagerComponent) && 
            Yii::app()->getComponent ('authManager') instanceof X2AuthManager) {

            self::$_oldAuthManagerComponent = Yii::app()->getComponent ('authManager');
        }
        Yii::app()->setComponent ('authManager', array ( 
            'class' => 'X2AuthManagerMock',
            'connectionID' => 'db',
            'defaultRoles' => array('guest', 'authenticated', 'admin'),
            'itemTable' => 'x2_auth_item',
            'itemChildTable' => 'x2_auth_item_child',
            'assignmentTable' => 'x2_auth_assignment',
        ));
        return Yii::app()->authManager;
    }

    public static function restoreX2AuthManager () {
        if (isset (self::$_oldAuthManagerComponent)) {
            Yii::app()->setComponent ('authManager', self::$_oldAuthManagerComponent);
        } 
    }

    private static $_oldUserComponent;
    public static function loadX2NonWebUser () {
        if (!isset (self::$_oldUserComponent) && 
            Yii::app()->getComponent ('user') instanceof X2WebUser) {

            self::$_oldUserComponent = Yii::app()->getComponent ('user');
        }
        Yii::app()->setComponent ('user', array ( 
            'class' => 'X2NonWebUser',
        ));
    }

    public static function restoreX2WebUser () {
        if (isset (self::$_oldUserComponent)) {
            Yii::app()->setComponent ('user', self::$_oldUserComponent);
        } 
    }

    private static $_oldController;
    private static $_oldServerVals;

    /**
     * Load controller mock, as well as related mocks and $_SERVER values which might be needed 
     * in cases where a controller is needed.
     */
    public static function loadControllerMock (
        $serverName='localhost', $scriptName='/index-test.php') {

        self::$_oldController = Yii::app ()->controller;
        self::$_oldServerVals = $_SERVER;
        $_SERVER['SCRIPT_FILENAME'] = realpath ('../../index-test.php'); 
        $_SERVER['DOCUMENT_ROOT'] = realpath ('../..'); 
        $_SERVER['SERVER_NAME'] = $serverName; 
        $_SERVER['SCRIPT_NAME'] = $scriptName; 
        $_SERVER['REQUEST_URI'] = '/index.php/controllerMock/actionMock'; 
        Yii::app()->controller = new ControllerMock (
            'moduleMock', new ModuleMock ('moduleMock', null));
        Yii::app()->controller->action = new ActionMock (Yii::app()->controller, 'actionMock');
        // clear the url property caches so they will get regenerated using the
        // properties of $_SERVER
        self::setPrivateProperty (
            'CHttpRequest', '_scriptUrl', null, Yii::app()->request);
        self::setPrivateProperty (
            'CHttpRequest', '_baseUrl', null, Yii::app()->request);
        self::setPrivateProperty (
            'CHttpRequest', '_hostInfo', null, Yii::app()->request);
        self::setPrivateProperty (
            'CUrlManager', '_baseUrl', null, Yii::app()->getUrlManager ());
        self::setPrivateProperty (
            'ApplicationConfigBehavior', 
            '_absoluteBaseUrl', 
            Yii::app()->getBaseUrl (true),
            // accesses app config behavior, which is indexed by 0 due to way that it's specified
            // in main.php
            Yii::app()->asa (0));
    }

    public static function restoreController () {
        //if(isset(self::$_oldController)){
            Yii::app()->controller = self::$_oldController;
        //}
        if(isset(self::$_oldServerVals)){
            $_SERVER = self::$_oldServerVals;
        }
    }

    public static function setConstant ($constName, $val) {
        $testConstantsFile = 'testconstants.php';
        $contents = file_get_contents ($testConstantsFile);
        $contents = preg_replace ('/('.$constName.'\',[ ]*)[^)]+/', '\1'.$val, $contents);
        file_put_contents ($testConstantsFile, $contents);
    }

    /**
     * Uses imap to assert that an email with the specified subject is present in the specified
     * mailbox
     * @param CTestCase $owner
     * @param Credentials $credentials
     * @param string $subject unique email subject
     * @param int $tries number of imap request attempts
     */
    public static function assertEmailReceived ($owner, Credentials $credentials, $subject, $tries = 1) {
        Yii::import ('application.tests.components.EmailTestingUtil');
        $emailTestUtil = new EmailTestingUtil;
        $emailTestUtil->credentials = $credentials;
        $owner->assertTrue ($emailTestUtil->open ());
        $stream = $emailTestUtil->getStream ();
        $searchString = 'SUBJECT "'.$subject.'"';
        for ($i = 0; $i < $tries; $i++) {
            $uids = imap_search ($stream, $searchString, SE_UID);
            if ($uids) break;
            sleep (3);
        }
        $owner->assertTrue (is_array ($uids));
        $owner->assertEquals (1, count ($uids));
        $emailTestUtil->close ();
    }

    public static function getTestBaseUri () {
        return '/'.preg_replace ('/'.preg_quote (TEST_WEBROOT_URL, '/').'(.*)index-test.php\//', '$1', TEST_BASE_URL);
    }

}

?>
