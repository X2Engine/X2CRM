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




class ProfileTest extends X2WebTestCase {

    public $autoLogin = false;

    /**
     * Copies a test controller into the controllers directory.
     */
    public static function setUpBeforeClass () {
        // ensure that a directory with the same name isn't already in the web root
        exec ('ls ../controllers', $output);
        if (in_array ('ProfileTestController.php', $output)) {
            X2_TEST_DEBUG_LEVEL > 1 && println ('Warning: tests are being aborted because file '.
                '"ProfileTestController" already exists in the protected/controllers');
            self::$skipAllTests = true;
        } else {
            // copy over webscripts and perform replacement on URL tokens
            exec ('cp -n webscripts/ProfileTestController.php ../controllers');
        }
        parent::setUpBeforeClass ();
    }

    /**
     * Remove all the test pages that were copied over 
     */
    public static function tearDownAfterClass () {
        if (!self::$skipAllTests)
            exec ('rm ../controllers/ProfileTestController.php');
        parent::tearDownAfterClass ();
    }

    /**
     * Visit a test action in a test controller whose sole purpose is to echo out the current 
     * user profile's username. Since autoLogin is disabled, the username should be the guest 
     * profile username.
     */
    public function testGuestProfile () {
        $this->openX2('profileTest/testGuestProfile');
        $this->assertTextPresent (Profile::GUEST_PROFILE_USERNAME);
    }
}



?>
