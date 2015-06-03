<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

    /**
     * Used to invoke methods which are protected or private.
     * @param string|object $classNameOrInstance
     * @param string $methodName 
     * @return function Takes an array of arguments as a parameter and calls
     *  the specified method with those arguments.
     */
    public static function setPublic ($classNameOrInstance, $methodName, $static=false) {
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
        return function () use ($method, $class) {
            return $method->invokeArgs ($class, func_get_args ());
        };
    }

    public static function setPrivateProperty ($className, $propertyName, $value) {
        $relectionClass = new ReflectionClass ($className);
        $reflectionProperty = $relectionClass->getProperty ($propertyName);
        $reflectionProperty->setAccessible (true);
        $reflectionProperty->setValue ($propertyName, $value);
    }

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
        VERBOSE_MODE && print_r ($output);
        return $output;
    }   

    public static function runCronCommand () {
        self::printExec ('curl '.TEST_BASE_URL.'api/x2cron &>/dev/null');
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
        } else {
            throw new CException ('X2AuthManager component could not be restored'); 
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
        } else {
            throw new CException ('X2WebUser component could not be restored'); 
        }
    }

}

?>
