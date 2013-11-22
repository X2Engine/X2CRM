<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/



/**
 * Base test class for tests with Selenium.
 *
 * Change the following URL based on your server configuration
 * Make sure the URL ends with a slash so that we can use relative URLs in test cases
 * @package X2CRM.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
include('WebTestConfig.php');
Yii::import ('application.components.permissions.*');

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

