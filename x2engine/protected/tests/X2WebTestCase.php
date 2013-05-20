<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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



/**
 * Base test class for tests with Selenium.
 *
 * Change the following URL based on your server configuration
 * Make sure the URL ends with a slash so that we can use relative URLs in test cases
 * @package X2CRM.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
include('WebTestConfig.php');

/**
 * @package X2CRM.tests
 */
abstract class X2WebTestCase extends CWebTestCase {

	public $localSeleneseDir;

	/**
	 * Default account for testing the app
	 * @var array
	 */
	public $login = array(
		'username' => 'admin',
		'password' => 'admin',
	);
	
	protected $captureScreenshotOnFailure = true;
	protected $screenshotPath = null;
	protected $screenshotUrl = null;
	public $firstLogin = true;

	/**
	 * Asserts that the correct user is logged in.
	 */
	public function assertCorrectUser() {
		$this->assertElementPresent('css=#profile-dropdown > span:first-child');
		$this->assertElementContainsText('css=#profile-dropdown > span:first-child', $this->login['username']);
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
	public function login() {
		$this->openX2('/site/login');
		foreach ($this->login as $fld => $val)
			$this->type("name=LoginForm[$fld]", $val);
		$this->clickAndWait("//input[@type='submit']");
		// Finally, make sure the login succeeded
		$this->assertCorrectUser();
	}

	/**
	 * Logs out of the web app 
	 */
	public function logout() {
		$this->openX2('/site/logout');
	}

	/**
	 * Open a URI within the app
	 * 
	 * @param string $r_uri
	 */
	public function openX2($r_uri) {
		return $this->open(TEST_BASE_URL . $r_uri);
	}

	/**
	 * Logs in as the user specified in {@link login}; does nothing otherwise.
	 * 
	 * If the browser is not logged in, this logs it in according to the current
	 * value of {@link login}.
	 */
	public function session() {
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
			/**
			 * The browser is logged in but not as the correct user.
			 */
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
	 * Sets up before each test method runs.
	 * 
	 * This mainly sets the base URL for the test application, and sets the 
	 * Selenese path to make it easier to locate/use Selenese HTML scripts.
	 */
	protected function setUp() {
		parent::setUp();
		$this->setSeleneseDir();
		// Set the screenshot path to one visible from the web.
		$this->screenshotPath = Yii::app()->basePath . implode(DIRECTORY_SEPARATOR, array('', '..', 'uploads', 'testing'));
		$this->screenshotUrl = rtrim(TEST_BASE_URL, 'index-test.php') . 'uploads/testing';
		$this->setBrowserUrl(TEST_BASE_URL);
		$this->prepareTestSession();
		$this->openX2('/site/login');
		$this->session();
	}

}

