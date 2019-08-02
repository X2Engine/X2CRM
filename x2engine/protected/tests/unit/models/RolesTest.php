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




Yii::import('application.modules.users.models.*');
Yii::import('application.modules.groups.models.*');

/**
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class RolesTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'user' => 'User',
            'role' => 'Roles',
            'roleToUser' => 'RoleToUser',
            'groupToUser' => 'GroupToUser',
            'group' => 'Groups'
        );
    }

    public function testGetUserTimeout() {
        $this->assertTrue(Yii::app()->cache->flush());
        $defaultTimeout = 60;
        Yii::app()->settings->timeout = $defaultTimeout;
        // admin's timeout should be the big one based on role
        $this->assertEquals(
                $this->role('longTimeout')->timeout, Roles::getUserTimeout($this->user('admin')->id, false));
        // testuser's timeout should also be the big one, and not the "Peon"
        // role's timeout length
        $this->assertEquals(
                $this->role('longTimeout')->timeout, Roles::getUserTimeout($this->user('testUser')->id, false));
        // testuser2's timeout should be the "Peon" role's timeout length
        // because that user has that role, and that role has a timeout longer
        // than the default timeout
        $this->assertEquals(
                $this->role('shortTimeout')->timeout, Roles::getUserTimeout($this->user('testUser2')->id, false));
        // testuser3 should have no role. Here, let's ensure that in case the
        // fixtures have been modified otherwise
        RoleToUser::model()->deleteAllByAttributes(array('userId' => $this->user('testUser3')->id));
        $this->assertEquals(
                $defaultTimeout, Roles::getUserTimeout($this->user('testUser3')->id, false));
    }

    /**
     * Ensure that upon deletion of roleToUser records, roles update immediately
     * (do not use an outdated cache entry)
     */
    public function testGetUserRoles() {
        $userId = $this->user['testUser']['id'];
        $userRoles = Roles::getUserRoles($userId, false);

        // Assert that user has roles
        $this->assertTrue(sizeof($userRoles) > 0);
        // Specifically, these (user groups only):
        $this->assertEquals(array(
            1, 2
                ), $userRoles);

        // Test group-inherited user roles; fixture entry "testUser5" is a
        // member of a group:
        $userRoles = Roles::getUserRoles($this->user['testUser5']['id'], false);
        $this->assertEquals(array(3), $userRoles);

        // Iterate over and remove records explicitly to raise the afterDelete event
        $records = RoleToUser::model()->findAllByAttributes(array(
            'userId' => $userId,
            'type' => 'user'));
        foreach ($records as $record) {
            $record->delete();
        }
        $userRoles = Roles::getUserRoles($userId, false);

        // assert that user has no roles
        $this->assertTrue(sizeof($userRoles) === 0);
    }

    /**
     * Ensure that updating user relationships functions correctly.
     */
    public function testUpdateUsers() {
        $role = $this->role('longTimeout');
        $initialPermissions = array(
            'view' => $role->getFieldPermissions(1),
            'edit' => $role->getFieldPermissions(2),
            'none' => $role->getFieldPermissions(0)
        );

        $role->setUsers(array());
        $role->save();
        $this->assertEquals(0, count($role->getUsers()));
        $this->checkFieldPermissions($role, $initialPermissions);

        $role->setUsers(array('testuser', 'admin', 'testuser2'));
        $role->save();
        $this->assertEquals(3, count($role->getUsers()));
        $this->checkFieldPermissions($role, $initialPermissions);

        $role->setUsers(array('admin', '', null, 'testuser', 'fakeuser', 1, 2));
        $role->save();
        $this->assertEquals(4, count($role->getUsers()));
        $this->checkFieldPermissions($role, $initialPermissions);
    }

    /**
     * Assert that all permissions have not changed.
     */
    private function checkFieldPermissions($role, $permissions) {
        $this->assertEquals($permissions['view'], $role->getFieldPermissions(1));
        $this->assertEquals($permissions['edit'], $role->getFieldPermissions(2));
        $this->assertEquals($permissions['none'], $role->getFieldPermissions(0));
    }

    /**
     * Ensure that view/edit permissions are calculated and updated correctly.
     */
    public function testUpdatePermissions() {
        //For some reason, re-using longTimeout preserves the state changes to
        //the private internal variables and makes this test fail, so using
        //shortTimeout role instead.
        $role = $this->role('shortTimeout');
        $initialUsers = $role->getUsers();
        $totalFields = Fields::model()->count();
        
        //Test no permissions
        $role->setViewPermissions(array());
        $role->setEditPermissions(array());
        $role->save();
        $this->checkPermissionUpdates($role, $totalFields, array(0, 0, $totalFields));
        $this->assertEquals($initialUsers, $role->getUsers());
        
        //Test all permissions
        $role->setViewPermissions(range(1, $totalFields));
        $role->setEditPermissions(range(1, $totalFields));
        $role->save();
        $this->checkPermissionUpdates($role, $totalFields, array(0, $totalFields, 0));
        $this->assertEquals($initialUsers, $role->getUsers());

        //Test view-only permissions
        $role->setViewPermissions(range(1, $totalFields));
        $role->setEditPermissions(array());
        $role->save();
        $this->checkPermissionUpdates($role, $totalFields, array($totalFields, 0, 0));
        $this->assertEquals($initialUsers, $role->getUsers());

        //Test bad input permissions
        $role->setViewPermissions(array());
        $role->setEditPermissions(range(1, $totalFields));
        $role->save();
        $this->checkPermissionUpdates($role, $totalFields, array(0, 0, $totalFields));
        $this->assertEquals($initialUsers, $role->getUsers());

        //Test random permissions
        $range = range(1, $totalFields);
        shuffle($range);
        $newView = array_slice($range, 0, rand(1, $totalFields - 1));
        shuffle($range);
        $newEdit = array_slice($range, 0, rand(1, $totalFields - 1));
        $edit = array_intersect($newView, $newEdit);
        $view = array_diff($newView, $newEdit);
        $none = array_diff(range(1, $totalFields), $newView);
        //Have to do pre-processing to get accurate counts, but that screws up
        //the same preprocessing happening in the roles model. View = view + edit
        //fixes the double array_intersect creating an empty array.
        $role->setViewPermissions($view + $edit);
        $role->setEditPermissions($edit);
        $role->save();
        $this->checkPermissionUpdates($role, $totalFields, array(count($view), count($edit), count($none)));
        $this->assertEquals($initialUsers, $role->getUsers());
    }

    /**
     * Helper function to validate permissions changes.
     */
    private function checkPermissionUpdates($role, $fieldCount, Array $counts) {
        $this->assertEquals($counts[0], count($role->getFieldPermissions(1)));
        $this->assertEquals($counts[1], count($role->getFieldPermissions(2)));
        $this->assertEquals($counts[2], count($role->getFieldPermissions(0)));
        $this->assertEquals($fieldCount, array_sum($counts));
    }

}

?>
