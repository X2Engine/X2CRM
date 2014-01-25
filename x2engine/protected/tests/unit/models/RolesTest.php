<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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
 * @package X2CRM.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class RolesTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'user' => 'User',
            'role' => 'Roles',
            'roleToUser' => 'RoleToUser'
        );
    }

    /**
     *
     */
    public function testGetUserTimeout() {
        Yii::app()->cache->flush();
        $defaultTimeout = 60;
        Yii::app()->params->admin->timeout = $defaultTimeout;
        // admin's timeout should be the big one based on role
        $this->assertEquals($this->role('longTimeout')->timeout, Roles::getUserTimeout($this->user('admin')->id));
        // testuser's timeout should also be the big one, and not the "Peon"
        // role's timeout length
        $this->assertEquals($this->role('longTimeout')->timeout, Roles::getUserTimeout($this->user('testUser')->id));
        // testuser2's timeout should be the "Peon" role's timeout length
        // because that user has that role, and that role has a timeout longer
        // than the default timeout
        $this->assertEquals($this->role('shortTimeout')->timeout, Roles::getUserTimeout($this->user('testUser2')->id));
        // testuser3 should have no role. Here, let's ensure that in case the
        // fixtures have been modified otherwise
        RoleToUser::model()->deleteAllByAttributes(array('userId'=>$this->user('testUser3')->id));
        $this->assertEquals($defaultTimeout,Roles::getUserTimeout($this->user('testUser3')->id));
    }
}

?>
