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





class GuestProfileFixTest extends X2DbTestCase {
    
    // skipped since migration script tests aren't relevant after corresponding release
    protected static $skipAllTests = true;

    /**
     * Contains dump of profile and users table at 4.2 Platinum after creating a new users after
     * a fresh install
     */
//    public $fixtures = array (
//        'profiles' => array ('Profile', '.GuestProfileFixTest'), 
//        'users' => array ('User', '.GuestProfileFixTest'), 
//    );

    public static function setUpBeforeClass () {
        // must be set to true so that the command uses the test database
        if (!YII_UNIT_TESTING || !YII_DEBUG) {
            self::$skipAllTests = true;
        }
        parent::setUpBeforeClass ();
    }

    /**
     * Runs 4.2.1 migration script 
     * Asserts that guest profile is properly deleted and recreated with correct id.
     * Asserts that user with missing profile is given a profile with correctly set attributes
     */
    public function testMigrationScript () {
        // ensure test user doesn't have a profile
        $userWithoutProfile = User::model ()->findByAttributes (array (
            'username' => 'test'
        ));
        $badProfile = $userWithoutProfile->profile;
        $this->assertEquals (Profile::GUEST_PROFILE_USERNAME, $badProfile->username);

        // run guest profile fix migration script
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
            'migrations/pending/1410382532-guest-profile-fix.php';
        $return_var;
        $output = array ();
        X2_TEST_DEBUG_LEVEL > 1 && print_r (exec ($command, $return_var, $output));
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($return_var);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($output);
            
        // ensure that guest profile has correct id
        $guestProfile = Profile::model ()->findByPk (-1);
        $this->assertNotEquals (null, $guestProfile);
        $this->assertEquals (Profile::GUEST_PROFILE_USERNAME, $guestProfile->username);

        // ensure that user which formerly had no profile now has a profile
        $userWithoutProfile = User::model ()->findByAttributes (array (
            'username' => 'test'
        ));
        $this->assertNotEquals (null, $userWithoutProfile->profile);

        // ensure that test user profile has correctly set attributes
        $newProfile = $userWithoutProfile->profile;
        $newProfileAttributes = $newProfile->getAttributes ();
        $this->assertEquals ($userWithoutProfile->username, $userWithoutProfile->profile->username);
        $this->assertEquals (
            $userWithoutProfile->firstName.' '.$userWithoutProfile->lastName, 
            $userWithoutProfile->profile->fullName);
        $this->assertEquals (
            $userWithoutProfile->emailAddress, $userWithoutProfile->profile->emailAddress);
        $this->assertEquals ($userWithoutProfile->id, $userWithoutProfile->profile->id);
        $this->assertEquals (1, $userWithoutProfile->profile->status);
        $this->assertEquals (1, $userWithoutProfile->profile->allowPost);

        // delete test user profile and create a new profile in the way that it would be created
        // by actionCreate () in the user controller and ensure that it's attributes match those
        // of the profile created by the migration script
        $newProfile->delete ();
        $profile = new Profile;
        $profile->fullName = $userWithoutProfile->firstName." ".$userWithoutProfile->lastName;
        $profile->username = $userWithoutProfile->username;
        $profile->allowPost = 1;
        $profile->emailAddress = $userWithoutProfile->emailAddress;
        $profile->status = $userWithoutProfile->status;
        $profile->id = $userWithoutProfile->id;
        $profile->save();
        $this->assertEquals ($newProfileAttributes, $profile->getAttributes ());

    }

}


?>
