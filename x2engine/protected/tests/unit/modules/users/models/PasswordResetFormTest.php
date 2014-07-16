<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * 
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordResetFormTest extends X2DbTestCase {

    public $fixtures = array(
        'user' => 'User',
        'resets' => 'PasswordReset'
    );


    public function testSave() {
        $user = $this->user('testUser');
        $form = new PasswordResetForm($user);
        $form->password = 'a really bad password';
        $expectmd5 = md5('a really bad password');
        $form->confirm = $form->password;
        $form->save();
        $user->refresh();
        $this->assertEquals($expectmd5,$user->password);
        $this->assertEquals(0,PasswordReset::model()->countByAttributes(array('userId'=>$user->id)));

        // Test validation as well, as a "bonus", since there needn't be any
        // fixture loading for it, and it thus saves a few seconds when running
        // the test:
        $form = new PasswordResetForm($user);
        $passwords = array(
            false => array(
                'n#6', // 3 character classes but too short
                'ninininini' // long enough but not enough character classes
            ),
            true => array(
                'D83*@)1', // 5 characters long and multiple character classes
                'this that and the next thing', // only two characters but very long
            )
        );
        foreach($passwords as $good => $passes) {
            foreach($passes as $pass) {
                $form->password = $pass;
                $form->confirm = $pass;
                $this->assertEquals($good,$form->validate(array('password')));
            }
        }
    }
}

?>
