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






/**
 * Base test class for tests with Selenium.
 *
 * Change the following URL based on your server configuration
 * Make sure the URL ends with a slash so that we can use relative URLs in test cases
 * @package application.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
include('WebTestConfig.php');
Yii::import ('application.components.permissions.*');
Yii::import ('application.components.modules.users.models.*');
Yii::import ('application.components.behaviors.*');

/**
 * @package application.tests
 */
abstract class X2WebTestCase extends CWebTestCase {

    public $fixtures = array(); // forces all fixtures without suffixes to be loaded
    public $autoLogin = true;
    public $localSeleneseDir;

    /**
     * Default account for testing the app
     * @var array
     */
    public $login = array(
        'username' => 'admin',
        'password' => 'admin',
    );
    
    protected static $loadFixtures = X2_LOAD_FIXTURES;
    protected static $loadFixturesForClassOnly = X2_LOAD_FIXTURES_FOR_CLASS_ONLY;
    protected $captureScreenshotOnFailure = false;
    protected $screenshotPath = null;
    protected $screenshotUrl = null;
    protected static $skipAllTests = false;

    private static $_referenceFixtureRecords = array();

    private static $_referenceFixtureRows = array();

    public $firstLogin = true;

    public static function referenceFixtures() {
        return array();
    }

    public static function getPath ($arg) {
        $reflect = new ReflectionClass ($arg);
        return ltrim (
            preg_replace ('/^'.preg_quote (__DIR__, '/').'/', '', $reflect->getFileName ()), '/');
    }

    public static function getTestHost () {
        return preg_replace ('/^https?:\/\/([^\/]+)\/.*$/', '$1', TEST_WEBROOT_URL);
    }

    public function waitForPageToLoad () {
        $this->waitForCondition (
            "window.document.readyState === 'complete'", 5000);
    }

    /**
     * Asserts that the correct user is logged in.
     */
    public function assertCorrectUser(array $login = null) {
        if (!$login) $login = $this->login;
        $this->waitForCondition (
            "window.document.querySelector ('#profile-dropdown > span:first-child')", 5000);
        $user = User::model ()->findByAttributes (array (
            'username' => $login['username'],
        ));
        $alias = $user->alias;
        if ($alias === null) $alias = $login['username'];
        $this->assertElementContainsText(
            'css=#profile-dropdown > span:first-child', $alias);
    }

    /**
     * Runs a Selenese script from the same directory as the test case file
     * 
     * @param string $filename 
     */
    public function localSelenese($filename) {
        $this->runSelenese($this->localSeleneseDir . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Logs in to the web app 
     * 
     * Uses the current user credentials in {@link $login} to log into the web app.
     */
    public function login(array $login = null) {
        if (!$login) $login = $this->login;
        $this->openX2('site/login');
        foreach ($login as $fld => $val)
            $this->type("name=LoginForm[$fld]", $val);
        $this->clickAndWait("css=#signin-button");
        // Finally, make sure the login succeeded
        X2_TEST_DEBUG_LEVEL > 1 && println ('login');
    }

    public function loginAs ($username, $password) {
        $this->login (array ('username' => $username, 'password' => $password));
    }

    /**
     * Logs out of the web app 
     */
    public function logout() {
        $this->deleteAllVisibleCookies();
        $this->openX2('site/logout');
    }

    /**
     * Open a URI within the app
     * 
     * @param string $r_uri
     */
    private $_currentPage;
    public function openX2($r_uri) {
        $this->_currentPage = $r_uri;
        return $this->open(TEST_BASE_URL . $r_uri);
    }

    /**
     * Open a URI within the app
     * 
     * @param string $r_uri
     */
    public function openPublic($r_uri) {
        X2_TEST_DEBUG_LEVEL > 1 && print ('openPublic: '.TEST_WEBROOT_URL . $r_uri."\n");
        return $this->open(TEST_WEBROOT_URL . $r_uri);
    }

    /**
     * Logs in as the user specified in {@link login}; does nothing otherwise.
     * 
     * If the browser is not logged in, this logs it in according to the current
     * value of {@link login}.
     */
    public function session() {
        if(!$this->autoLogin){
            $this->logout();
            $this->firstLogin = true;
            return 0;
        }
        // Test if logged in, log in if not, log in.
        try {
            $this->assertElementPresent('css=ul#user-menu');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            /* If this isn't the first time we've logged in, we have a problem;
             * the user should have been logged in throughout the life of the
             * test case class. Append t
             */
            if (!$this->firstLogin)
                array_push($this->verificationErrors, $e->toString());
                $this->firstLogin = false;
                $this->login();
            return 0;
        }
        try {
            $this->assertCorrectUser();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            // The browser is logged in but not as the correct user.
                $this->logout();
                $this->login();
                $this->firstLogin = false;
            return 0;
        }
        // Indicator of whether the session was already initialized properly
        return 1;
    }

    /**
     * Obtains the directory that the test case lives in
     */
    public function setSeleneseDir() {
        $theTestClass = new ReflectionClass(get_called_class());
        $this->localSeleneseDir = dirname($theTestClass->getFileName());
    }
    
    /**
     * @return bool true if browser that's currently being used is Chrome, false otherwise
     */
    protected function isChrome () {
        $this->storeEval (
            "!!window.navigator.userAgent.match(/Chrome/i)", 'isChrome');
        return $this->getExpression ('${isChrome}') === 'true';
    }
    
    public static function setUpBeforeClass() {
        if (!YII_UNIT_TESTING) throw new CException ('YII_UNIT_TESTING must be set to true');
        $testClass = get_called_class();
        if(X2_TEST_DEBUG_LEVEL > 0){
            println("\nrunning test class: ".self::getPath ($testClass));
        }
        // Load "reference fixtures", needed for reference, which do not need
        // to be reloaded after every single test method:
        $testClass = get_called_class();
        if(X2_TEST_DEBUG_LEVEL > 0){
            /**/println($testClass);
        }
        $refFix = call_user_func("$testClass::referenceFixtures");
        $fm = Yii::app()->getComponent('fixture');
        self::$_referenceFixtureRows = array();
        self::$_referenceFixtureRecords = array();
        if(is_array($refFix)){
            Yii::import('application.components.X2Settings.*');
            $fm->load($refFix);
            if(self::$loadFixtures || self::$loadFixturesForClassOnly){
                foreach($refFix as $alias => $table){
                    $tableName = is_array($table) ? $table[0] : $table;
                    self::$_referenceFixtureRows[$alias] = $fm->getRows($alias);
                    if(strpos($tableName, ':') !== 0){
                        foreach(self::$_referenceFixtureRows[$alias] as $rowAlias => $row){
                            $model = CActiveRecord::model($tableName);
                            $key = $model->getTableSchema()->primaryKey;
                            if(is_string($key))
                                $pk = $row[$key];
                            else{
                                foreach($key as $k)
                                    $pk[$k] = $row[$k];
                            }
                            self::$_referenceFixtureRecords[$alias][$rowAlias] = 
                                $model->findByPk($pk);
                        }
                    }
                }
            }
        }

        parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass() {
        if(X2_TEST_DEBUG_LEVEL > 0){
            println("");
        }
        Yii::app()->getComponent('fixture')->load(array(
            'user'=>'User',
            'profile'=>'Profile',
        ));
        parent::tearDownAfterClass();
    }

    private $_oldSession;
    /**
     * Sets up before each test method runs.
     * 
     * This mainly sets the base URL for the test application, and sets the 
     * Selenese path to make it easier to locate/use Selenese HTML scripts.
     */
    public function setUp() {
        if(X2_TEST_DEBUG_LEVEL > 1){
            /**/println("\nrunning test case: ".$this->getName ());
        }
        if (X2_SKIP_ALL_TESTS || self::$skipAllTests) {
            $this->markTestSkipped ();
        }

        if (self::$loadFixturesForClassOnly)
            $this->getFixtureManager ()->loadFixtures = true;

        $fixtures = is_array ($this->fixtures) ? $this->fixtures : array ();
        $this->fixtures = array_merge ($fixtures, static::referenceFixtures ());

        if(X2_TEST_DEBUG_LEVEL > 1){
            println(' '.$this->getName());
        }

        X2DbTestCase::setUpAppEnvironment (true);
        if(isset($_SESSION)){
            $this->_oldSession = $_SESSION;
        }
        parent::setUp();
        $this->setSeleneseDir();
        // Set the screenshot path to one visible from the web.
        //$this->screenshotPath = Yii::app()->basePath . implode(DIRECTORY_SEPARATOR, array('', '..', 'uploads', 'testing'));
        //$this->screenshotUrl = rtrim(TEST_BASE_URL, 'index-test.php') . 'uploads/testing';
        $this->setBrowserUrl(TEST_BASE_URL);
        $this->prepareTestSession();
        $this->session();
    }
    
    public function tearDown() {
        if(isset($this->_oldSession)){
            $_SESSION = $this->_oldSession;
        }
        self::$skipAllTests = false;
        self::$loadFixtures = X2_LOAD_FIXTURES;
        self::$loadFixturesForClassOnly = X2_LOAD_FIXTURES_FOR_CLASS_ONLY;
        parent::tearDown();
    }

    public function clearSessions () {
        Yii::app()->db->createCommand ("
            delete from x2_sessions;
        ")->execute ();
    }

    public function assertJSCondition ($jsCond, $expected) {
        $this->storeEval ($jsCond, 'retVal');
        $retVal = $this->getExpression ('${retVal}');
        $this->assertEquals ($retVal, $expected);
    }

    /**
     * Visits page and checks for PHP/JS errors
     * @param string $page URI of page
     */
    protected function assertNoErrors () {
        if (YII_DEBUG)
		    $this->assertElementNotPresent('css=.xdebug-error');
        if (X2_TEST_DEBUG_LEVEL > 1) {
            // get stack trace and error message
            $this->storeEval (
                "window.document.querySelector ('#error-form') ? 
                    window.document.querySelector ('#error-form').innerHtml : null",
                'errorInfo');
            $errorMessage = $this->getExpression ('${errorInfo}');
            // #error-form is always on site/bugReport
            if ($errorMessage && $errorMessage !== 'null' && 
                $this->_currentPage !== 'site/bugReport') {
                println ($errorMessage);
            }
        }
        $this->assertElementNotPresent('css=#x2-php-error');
        $this->storeEval (
            "window.document.body.attributes['x2-js-error'] ? 'true' : 'false'", 
            'hasJsErrorAttr');
        $hasJsErrorAttr = $this->getExpression ('${hasJsErrorAttr}');
        if ($hasJsErrorAttr === 'true') {
            if (X2_TEST_DEBUG_LEVEL > 1) {
                $this->storeAttribute ('dom=document.body@x2-js-error', 'errorMessage');
                $errorMessage = $this->getExpression ('${errorMessage}');
                println ($errorMessage);
                $this->assertTrue (false, $errorMessage);
            } else {
                $this->assertTrue (false, Yii::t('app', 'Encountered JS error'));
            }
        } 
        $this->assertHttpOK ();
    }
    
    /**
     * Checks that two arrays have the same values regardless of order
     */
    public function assertArrayEquals(array $a, array $b) {
        $equality = false;
        if (count(array_diff($a, $b)) === 0) {
            foreach ($a as $k => $v) {
                if (!in_array($v, $b)) {
                    break;
                }
            }
            $equality = true;
        }
        $this->assertTrue($equality);
    }

    public function getHttpErrorResponse () {
        // get the label for the action
        $this->storeEval (
            "window.document.querySelector ('#content > .page-title > h2') ?
             window.document.querySelector ('#content > .page-title > h2').innerHTML : null", 
            'responseCode');
        return $this->getExpression ('${responseCode}');
    }

    public function assertHttpResponse ($expected) {
        $responseCode = $this->getHttpErrorResponse ();
        $this->assertRegexp ('/Error \d+/', $responseCode);
        $this->assertEquals ($expected, preg_replace ('/Error /', '', $responseCode));
    }

    public function assertHttpOK () {
        $responseCode = $this->getHttpErrorResponse ();
        $this->assertNotRegexp ('/Error \d+/', $responseCode);
    }
}

