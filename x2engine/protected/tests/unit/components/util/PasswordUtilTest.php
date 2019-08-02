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




Yii::import('application.components.util.*');

/**
 * 
 * @package application.tests.unit.components.util
 * @author Jake Houser <jake@x2engine.com>
 */
class PasswordUtilTest extends X2TestCase {

    public $passwords = array(
        'test' => 'sha256:32768:gMzKK339NGLmZ5/9Vo2D7j0LfqH1fR5I:+ApuGcLTo1qtR8x0rU3xs4g7RFCK8Uen',
        'passw0rd' => 'sha256:32768:e6eX/LkRj3AIlBKTlyKxE4NHfHmaHNJY:9nU4+D/UNvSSsNcapCz0iiduUdbw/2ES',
        '13375p34|(' => 'sha256:32768:P9rdAAC7tS5SxWWcC3vqnXNyhe35ro1h:Iwq8stDNSMg1KvLGHPe8EVzh8fBGzirN',
        'jT$#rLOK*Ca$vdGdTj7r' => 'sha256:32768:PMPeY/Z4ORvN7riPFuEp3nxaClmPs1Ov:vST0T4tE/FM7RwxJo/HiGTlA8awUffOt',
    );

    public function testCreateHash() {
        for ($i = 0; $i < 20; $i++) {
            $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                    . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                    . '0123456789!@#$%^&*()');
            shuffle($seed);
            $password = '';
            foreach (array_rand($seed, 16) as $k) {
                $password .= $seed[$k];
            }
            $hash = PasswordUtil::createHash($password);
            $pieces = explode(':', $hash);
            $this->assertEquals(count($pieces), PasswordUtil::HASH_SECTIONS);
            $this->assertTrue(in_array($pieces[PasswordUtil::HASH_ALGORITHM_INDEX], hash_algos()));
            $this->assertTrue(PasswordUtil::validatePassword($password, $hash));
        }
        $a = PasswordUtil::createHash('test');
        $b = PasswordUtil::createHash('test');
        $this->assertNotEquals($a, $b);
    }

    public function testValidatePassword() {
        $this->assertFalse(PasswordUtil::validatePassword(null, null));
        $this->assertFalse(PasswordUtil::validatePassword('', ''));
        $this->assertFalse(PasswordUtil::validatePassword('password', 'hash'));
        foreach ($this->passwords as $pw => $hash) {
            $this->assertTrue(PasswordUtil::validatePassword($pw, $hash));
        }
        $this->assertFalse(PasswordUtil::validatePassword('test', $this->passwords['passw0rd']));
    }

    public function testSlowEquals() {
        // Test null values
        $a = null;
        $b = null;
        $this->assertFalse(PasswordUtil::slowEquals($a, $b));

        // Test empty strings
        $a = '';
        $b = '';
        $this->assertTrue(PasswordUtil::slowEquals($a, $b));

        $a = '';
        $b = null;
        $this->assertFalse(PasswordUtil::slowEquals($a, $b));

        $a = 'Array';
        $b = array();
        $this->assertFalse(PasswordUtil::slowEquals($a, $b));

        $a = 'this is a string';
        $b = 'this is a string';
        $this->assertTrue(PasswordUtil::slowEquals($a, $b));

        $a = 'this is a string';
        $b = 'this is a string ';
        $this->assertFalse(PasswordUtil::slowEquals($a, $b));
    }
    
    public function testCreateSalt(){
        //Simple test to ensure salts are non-null and different with each call
        $salt1 = PasswordUtil::createSalt();
        $this->assertNotNull($salt1);
        
        for($i = 0; $i < 1000; $i++){
            $this->assertNotEquals(PasswordUtil::createSalt(), PasswordUtil::createSalt());
        }
    }

}

?>
