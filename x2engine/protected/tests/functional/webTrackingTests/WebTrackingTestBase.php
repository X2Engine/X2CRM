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
 * Base class for all web tracker-related tests. Includes utility methods to facilitate the 
 * testing of web forms and web trackers.
 *
 * For these tests to function properly, it's necessary to add the following lines to
 * your hosts file:
 *
 * <test installation ip>    www.x2engingtestdomain.com
 * <test installation ip>    www2.x2enginetestdomain.com
 * <test installation ip>    www.x2enginetestdomain2.com
 *
 * With that hosts file configured, the following constants should be defined in your 
 * WebTestConfig.php file:
 *
 * define('TEST_BASE_URL_ALIAS_1','http://www.x2enginetestdomain.com/index-test.php/');
 * define('TEST_BASE_URL_ALIAS_2','http://www.x2enginetestdomain2.com/index-test.php/');
 * define('TEST_BASE_URL_ALIAS_3','http://www2.x2enginetestdomain.com/index-test.php/');
 * define('TEST_WEBROOT_URL_ALIAS_1','http://www.x2enginetestdomain.com/');
 * define('TEST_WEBROOT_URL_ALIAS_2','http://www.x2enginetestdomain2.com/');
 * define('TEST_WEBROOT_URL_ALIAS_3','http://www2.x2enginetestdomain.com/');
 *
 * @package application.tests.functional.modules.contacts
 * @requires OS Linux 
 */
abstract class WebTrackingTestBase extends X2WebTestCase {

    private static $_webTrackingTestBaseUrl;
    private static $_webTrackingTestWebrootUrl;
    protected static $skipAllTests = false;

    /**
     * Copy over all the test pages to the web root 
     */
    public static function setUpBeforeClass () {
        // ensure that a directory with the same name isn't already in the web root
        exec ('ls ../../', $output);
        if (TEST_BASE_URL_ALIAS_1 === '' ||
            TEST_BASE_URL_ALIAS_2 === '' ||
            TEST_BASE_URL_ALIAS_3 === '' ||
            TEST_WEBROOT_URL_ALIAS_1 === '' ||
            TEST_WEBROOT_URL_ALIAS_2 === '' ||
            TEST_WEBROOT_URL_ALIAS_3 === '') {

            X2_TEST_DEBUG_LEVEL > 1 && println ('Warning: tests are being aborted because the web tracking '.
                'test constants have not been properly configured.');
            self::$skipAllTests = true;
        } else if (in_array ('x2WebTrackingTestPages', $output)) {
            X2_TEST_DEBUG_LEVEL > 1 && println ('Warning: tests are being aborted because the directory '.
                '"x2WebTrackingTestPages" already exists in the webroot');
            self::$skipAllTests = true;
        } else {
            // copy over webscripts and perform replacement on URL tokens
            exec ('cp -rn webscripts/x2WebTrackingTestPages ../../');
            exec ('find ../../x2WebTrackingTestPages -type f', $files);
            // perform URL token replacements
            foreach ($files as $file) {
                $content = file_get_contents ($file);
                $content = preg_replace (
                    '/TEST_BASE_URL_ALIAS_1/', TEST_BASE_URL_ALIAS_1, $content);
                $content = preg_replace (
                    '/TEST_BASE_URL_ALIAS_2/', TEST_BASE_URL_ALIAS_2, $content);
                $content = preg_replace (
                    '/TEST_BASE_URL_ALIAS_3/', TEST_BASE_URL_ALIAS_3, $content);
                $content = preg_replace (
                    '/TEST_WEBROOT_URL_ALIAS_1/', TEST_WEBROOT_URL_ALIAS_1, $content);
                $content = preg_replace (
                    '/TEST_WEBROOT_URL_ALIAS_2/', TEST_WEBROOT_URL_ALIAS_2, $content);
                $content = preg_replace (
                    '/TEST_WEBROOT_URL_ALIAS_3/', TEST_WEBROOT_URL_ALIAS_3, $content);
                file_put_contents ($file, $content);
            }
        }
        parent::setUpBeforeClass ();
    }

     
    public function assertDebugTrack () {
        $this->assertTrue ((bool) WebListenerAction::DEBUG_TRACK);
    }
     

    public function setUp () {
        $this->setBaseUrl (TEST_WEBROOT_URL_ALIAS_1);
        if (self::$skipAllTests) {
            $this->markTestSkipped ();
        }
        parent::setUp ();
    }

    /**
     * Remove all the test pages that were copied over 
     */
    public static function tearDownAfterClass () {
        if (!self::$skipAllTests)
            exec ('rm -r ../../x2WebTrackingTestPages');
        parent::tearDownAfterClass ();
    }

    /**
     * Open a URI within the app
     * 
     * @param string $r_uri
     */
    public function openX2($r_uri) {
        return $this->open(TEST_BASE_URL_ALIAS_1 . $r_uri);
    }

    private $_baseUrl;
    public function getBaseUrl () {
        if (!isset ($this->_baseUrl)) {
            $this->_baseUrl = TEST_WEBROOT_URL_ALIAS_1;
        }
        return $this->_baseUrl;
    }

    public function setBaseUrl ($baseUrl) {
        $this->_baseUrl = $baseUrl;
    }

    /**
     * Open a URI within the app
     * 
     * @param string $r_uri
     */
    public function openPublic($r_uri) {
        X2_TEST_DEBUG_LEVEL > 1 && print ('openPublic: '.$this->getBaseUrl () . $r_uri."\n");
        return $this->open($this->getBaseUrl () . $r_uri);
    }

    /**
     * Cookies cannot be deleted in ie8 unless ie8 is visiting a page with the domain associated
     * with the cookies.
     */
    public function deleteAllVisibleCookies ($url='') {
        if ($this->isIE8 ()) {
            $this->open ($url);
        } 
        parent::deleteAllVisibleCookies ();
    }

    /**
     * During FF selenium tests, checking for indexedDB throws an error, but only outside of 
     * iframes. This causes exact fingerprint matches to fail.
     * @return bool true if checking indexedDB would cause an error, false otherwise 
     */
    protected function checkForIndexedDBError () {
        $this->storeEval (
            "try { window.indexedDB; false; } catch (e) { true; } ", 'error');
        return $this->getExpression ('${error}') === 'true';
    }

    /**
     * @return bool true if browser that's currently being used is ie8, false otherwise
     */
    protected function isIE8 () {
        $this->storeEval (
            "!!window.navigator.userAgent.match(/msie 8/i)", 'isIE8');
        return $this->getExpression ('${isIE8}') === 'true';
    }

    /**
     * @return bool true if browser that's currently being used is Opera, false otherwise
     */
    protected function isOpera () {
        $this->storeEval (
            "!!window.navigator.userAgent.match(/opera/i)", 'isOpera');
        return $this->getExpression ('${isOpera}') === 'true';
    }

     
    protected function setIdentityThreshold ($threshold) {
        $admin = Admin::model()->findByPk (1);
        $admin->identityThreshold = $threshold;
        return $admin->save ();
    }
     

    /**
     * Submits the web lead form and ensures successful submission
     */
    protected function submitWebForm ($formVersion='') {
        if ($formVersion === 'differentDomain') {
            $this->openPublic('x2WebTrackingTestPages/webFormTestDifferentDomain.html');
        } else if ($formVersion === 'differentSubdomain') {
            $this->openPublic('x2WebTrackingTestPages/webFormTestDifferentSubdomain.html');
        } else {
            $this->openPublic('x2WebTrackingTestPages/webFormTest.html');
        }

        // the waitFor condition doesn't seem to work on Opera, so just wait a fixed amount of time
        sleep (5);
        
        $this->selectFrame('web-form-iframe');
        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->click("css=#submit");

        // wait for iframe to load new page
        sleep(5);
    }

    /**
     * To be called after submitWebForm to assert that contact was created by web form submission 
     * @return Contact the contact that was created
     */
    protected function assertContactCreated () {
        $contact = Contacts::model()->findByAttributes (array (
            'firstName' => 'test',
            'lastName' => 'test',
            'email' => 'test@test.com',
        ));
        $this->assertTrue ($contact !== null);
        X2_TEST_DEBUG_LEVEL > 1 && println (
            'contact created. new contact\'s tracking key = '.$contact->trackingKey);
        return $contact;
    }

    protected function clearContact () {
        Yii::app()->db->createCommand ('delete from x2_contacts where email="test@test.com"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_contacts
             where email="test@test.com"')
             ->queryScalar ();
        $this->assertEquals ('0', $count);
    }

    /**
     * Used in conjunction with assertWebActivityGeneration (). 
     * Clears web activity actions so that we can easily test later that a new web activity action
     * was generated
     */
    protected function clearWebActivity () {
        Yii::app()->db->createCommand ('delete from x2_actions where type="webactivity"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity"')
             ->queryScalar ();
        $this->assertEquals ('0', $count);
    }

    /**
     * Used in conjunction with clearWebActivity (). Ensures that a web activity action was 
     * generated.
     */
    protected function assertWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity"')
             ->queryScalar ();
        X2_TEST_DEBUG_LEVEL > 1 && println ($newCount);
        $this->assertEquals ('1', $newCount);
    }

     
    /**
     * Visits page with web tracker on it and asserts that the contact is being tracked
     */
    public function assertWebTrackerTracksWithCookie (
        $page='x2WebTrackingTestPages/webTrackerTest.html') {

        $this->clearWebActivity ();

        // visit the page with the web tracker on it
        $this->openPublic($page);
        $this->assertCookie ('regexp:.*x2_key.*');
        sleep (5); // wait for iframe to load
        $this->assertWebActivityGeneration ();
    }

    /**
     * Visits page with the legacy web tracker on it and asserts that the contact is being tracked
     */
//    public function assertLegacyWebTrackerTracksWithCookie () {
//        $this->clearWebActivity ();
//
//        // visit the page with the web tracker on it
//        $this->openPublic('x2WebTrackingTestPages/legacyWebTrackerTest.html');
//        $this->assertCookie ('regexp:.*x2_key.*');
//        sleep (5); // wait for iframe to load
//        $this->assertWebActivityGeneration ();
//    }

    /**
     * Used in conjunction with clearWebActivity (). Ensures that a web activity action was not
     * generated.
     */
    protected function assertNoWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity"')
             ->queryScalar ();
        X2_TEST_DEBUG_LEVEL > 1 && println ($newCount);
        $this->assertTrue ($newCount === '0');
    }

    /**
     * Visits page with web tracker on it and asserts that the contact is being note tracked
     */
    public function assertWebTrackerCannotTrackWithCookie () {
        $this->clearWebActivity ();

        // visit the page with the web tracker on it
        $this->openPublic('x2WebTrackingTestPages/webTrackerTest.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        sleep (5); // wait for iframe to load
        $this->assertNoWebActivityGeneration ();
    }

}

?>
