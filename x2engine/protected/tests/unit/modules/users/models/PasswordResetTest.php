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
 * 
 * @package application.tests.unit.modules.users.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordResetTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'user' => 'User'
        );
    }

    public $fixtures = array(
        'resets' => 'PasswordReset'
    );

    public function testGetIsExpired() {
        $this->assertFalse($this->resets('1')->isExpired);
        $this->assertTrue($this->resets('9')->isExpired);
    }

    public function testGetLimitReached() {
        $reset = new PasswordReset;
        $reset->ip = '127.0.0.1';
        $this->assertTrue($reset->limitReached);
        $reset = new PasswordReset;
        $reset->ip = '127.0.0.2';
        $this->assertFalse($reset->limitReached);
    }

    public function testBeforeSave() {
        // Fixture data should contain five expired reset requests, which should
        // be cleared out in beforeSave()
        $n0 = PasswordReset::model()->countByAttributes(array('ip'=>'127.0.0.1'));
        $reset = new PasswordReset;
        $reset->email = $this->user('testUser')->emailAddress;
        $reset->ip = '127.0.0.1';
        $reset->beforeSave();
        $n1 = PasswordReset::model()->countByAttributes(array('ip'=>'127.0.0.1'));
        $this->assertEquals($this->user('testUser')->id,$reset->userId);
        $this->assertEquals(5,$n0-$n1);
    }

    public function validUserId() {
        $reset = new PasswordReset;
        $reset->email = $this->user('testUser')->emailAddress;
        $reset->validUserId('email');
        $this->assertFalse($reset->hasErrors());
        $this->assertEquals($this->user('testUser')->id,$reset->userId);
        $reset = new PasswordReset;
        $reset->email = 'a00000000@a99999999999.com';
        $reset->validUserId('email');
        $this->assertTrue($reset->hasErrors());
        $this->assertEmpty($reset->userId);
    }

}

?>
