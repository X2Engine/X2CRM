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




Yii::import("application.components.permissions.*");
Yii::import("application.modules.actions.models.*");
Yii::import("application.modules.users.models.*");
Yii::import("application.modules.groups.models.*");
Yii::import("application.models.*");


/**
 * Test the X2PermissionsBehavior Class
 *
 * @author raymond
 */

class X2PermissionsBehaviorTest extends X2DbTestCase {

    public $fixtures = array(
        'contacts' => array ('Contacts', '.X2PermissionsBehaviorTest'),
        'users' => array('User','_1'),
        'groups' => array('Groups','_1'),
        'phoneNumbers' => 'PhoneNumber',
        'groupToUser' => array('GroupToUser','_1')
    );
    

    public function testGetAccessConditions() {
        TestingAuxLib::suLogin ('testUser1');
        $action = new Actions;
        $this->assertEquals(true, is_array($action->getAccessConditions(0, false , 'testUser1')));
        
        $accessLevel3 = $action->getAccessConditions(3, 'visibility', 'testUser1');
        $this->assertEquals(
            "NOT (visibility.visibility=0 AND visibility.assignedTo='Anyone')",
            $accessLevel3[0]['condition']);
        $this->assertEquals('OR', $accessLevel3[0]['operator']);
        
        $accessLevel2 = $action->getAccessConditions(1, 'visibility', 'testUser1');
        $this->assertRegExp("/^.*REGEXP BINARY.*$/", $accessLevel2[0]['condition']);
        $this->assertEquals('OR', $accessLevel2[0]['operator']);
        
    }

    public function testGetAssignedTo() {
        // Flush the cache; we're doing group membership tests, which will use 
        // the cache, and we've loaded fixtures that affect that:
        $cacheVal = Yii::app()->cache->get('user_groups');
        $this->assertTrue (Yii::app()->cache->flush());
        $this->assertTrue (
            $cacheVal === false || $cacheVal !== Yii::app()->cache->get('user_groups'));

        // Direct single-user assignment:
        $action = new Actions;
        $action->assignedTo = 'testUser1';
        $this->assertTrue($action->isAssignedTo('testUser1'));
        
        // Assignment via single-group association:
        $action = new Actions;
        $action->assignedTo = '2';
        //$assignees = explode(', ','2');
        //$groupIds = array_filter($assignees,'ctype_digit');
        //$userGroupsAssigned = array_intersect($groupIds,Groups::getUserGroups(1));
        $this->assertTrue($action->isAssignedTo('testUser1'));
        
        // Assigned via "Anyone" or null assignment:
        $action = new Actions;
        $action->assignedTo = 'Anyone';
        $this->assertTrue($action->isAssignedTo('testUser1'));
        $action = new Actions;
        $action->assignedTo = '';
        $this->assertTrue($action->isAssignedTo('testUser1'));

        // Assigned via multiple assignment (username):
        $action = new Actions;
        $action->assignedTo = 'testUser1, testUser2';
        $this->assertTrue($action->isAssignedTo('testUser1'));

        // Assigned via multiple assignment (group):
        $action = new Actions;
        $action->assignedTo = '2, testUser2';
        $this->assertTrue($action->isAssignedTo('testUser1'));

        // Not assigned (single):
        $action = new Actions;
        $action->assignedTo = 'testUser2';
        $this->assertFalse($action->isAssignedTo('testUser1'));

        // Not assigned (multiple)
        $action = new Actions;
        $action->assignedTo = 'testUser2, testUser3';
        $this->assertFalse($action->isAssignedTo('testUser1'));
    }

    /**
     * Swap db auth manager component with a testing mock to prevent us from having to set up
     * complicated db state
     */
    public function testAuthManagerMock () {
        $auth = TestingAuxLib::loadAuthManagerMock ();
        $auth->setAccess ('test', 1, array (), true);
        $this->assertTrue ($auth->checkAccess ('test', 1, array ()));
        $auth->setAccess ('test', 2, array (), true);
        $this->assertTrue ($auth->checkAccess ('test', 2, array ()));
        $auth->setAccess ('test', 2, array ('test'), false);
        $this->assertFalse ($auth->checkAccess ('test', 2, array ('test')));
        TestingAuxLib::restoreX2AuthManager ();
    }

    /**
     * Test visibility and access criteria for each access level 
     */
    public function testReadAccessLevels () {
        $auth = TestingAuxLib::loadAuthManagerMock ();
        $user = $this->users ('user2');

        $contactGroupmate = $this->contacts ('contactGroupmate');
        $contactGroup = $this->contacts ('contactGroup');
        $contactAnyone = $this->contacts ('contactAnyone');
        $contactUserPrivate = $this->contacts ('contactUserPrivate');
        $contactOtherPrivate = $this->contacts ('contactOtherPrivate');
        $contactInvisible = $this->contacts ('contactInvisible');

        // private read only access
        $auth->setAccess ('ContactsReadOnlyAccess', $user->id, array (), false);
        $auth->setAccess ('ContactsPrivateReadOnlyAccess', $user->id, array (), true);
        TestingAuxLib::suLogin ('testUser2');

        $accessLevel = Contacts::model ()->getAccessLevel ();
        $this->assertEquals (X2PermissionsBehavior::QUERY_SELF, $accessLevel);

        $contactGroup->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactGroup->isVisibleTo (Yii::app()->getSuModel ()));

        $contactGroupmate->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactGroupmate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactAnyone->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactAnyone->isVisibleTo (Yii::app()->getSuModel ()));

        $contactUserPrivate->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactUserPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactOtherPrivate->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactOtherPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactInvisible->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactInvisible->isVisibleTo (Yii::app()->getSuModel ()));

        $criteria = Contacts::model ()->getAccessCriteria ();
        $contacts = Contacts::model ()->findAll ($criteria);
        $this->assertEquals (2, count ($contacts));
        $this->assertEquals (2, count (array_intersect (
            array (
                $contactGroup->id,
                $contactUserPrivate->id
            ),
            array_map (function ($contact) { return $contact->id; }, $contacts)
        )));


        // read only access
        $auth->setAccess ('ContactsReadOnlyAccess', $user->id, array (), true);
        $auth->setAccess ('ContactsPrivateReadOnlyAccess', $user->id, array (), false);

        $accessLevel = Contacts::model ()->getAccessLevel ();
        $this->assertEquals (X2PermissionsBehavior::QUERY_PUBLIC, $accessLevel);

        $contactGroup->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactGroup->isVisibleTo (Yii::app()->getSuModel ()));

        $contactGroupmate->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactGroupmate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactAnyone->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactAnyone->isVisibleTo (Yii::app()->getSuModel ()));

        $contactUserPrivate->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactUserPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactOtherPrivate->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactOtherPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactInvisible->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactInvisible->isVisibleTo (Yii::app()->getSuModel ()));


        $criteria = Contacts::model ()->getAccessCriteria ();
        $contacts = Contacts::model ()->findAll ($criteria);
        $this->assertEquals (4, count ($contacts));
        $this->assertEquals (4, count (array_intersect (
            array (
                $contactGroup->id,
                $contactGroupmate->id,
                $contactAnyone->id,
                $contactUserPrivate->id,
            ),
            array_map (function ($contact) { return $contact->id; }, $contacts)
        )));


        // no access
        $auth->setAccess ('ContactsReadOnlyAccess', $user->id, array (), false);
        $auth->setAccess ('ContactsPrivateReadOnlyAccess', $user->id, array (), false);

        $accessLevel = Contacts::model ()->getAccessLevel ();
        $this->assertEquals (X2PermissionsBehavior::QUERY_NONE, $accessLevel);

        $contactGroup->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactGroup->isVisibleTo (Yii::app()->getSuModel ()));

        $contactGroupmate->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactGroupmate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactAnyone->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactAnyone->isVisibleTo (Yii::app()->getSuModel ()));

        $contactUserPrivate->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactUserPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactOtherPrivate->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactOtherPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactInvisible->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactInvisible->isVisibleTo (Yii::app()->getSuModel ()));


        $criteria = Contacts::model ()->getAccessCriteria ();
        $contacts = Contacts::model ()->findAll ($criteria);
        $this->assertEquals (0, count ($contacts));
        $this->assertEquals (0, count (array_intersect (
            array (
            ),
            array_map (function ($contact) { return $contact->id; }, $contacts)
        )));


        // all access
        $auth->setAccess ('ContactsAdmin', $user->id, array (), true);
        $auth->setAccess ('AdminIndex', $user->id, array (), true);
        $auth->setAccess ('ContactsReadOnlyAccess', $user->id, array (), true);
        $auth->setAccess ('ContactsBasicAccess', $user->id, array (), true);
        $auth->setAccess ('ContactsFullAccess', $user->id, array (), true);
        $auth->setAccess ('ContactsUpdateAccess', $user->id, array (), true);

        $accessLevel = Contacts::model ()->getAccessLevel ();
        $this->assertEquals (X2PermissionsBehavior::QUERY_ALL, $accessLevel);

        $contactGroup->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactGroup->isVisibleTo (Yii::app()->getSuModel ()));

        $contactGroupmate->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactGroupmate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactAnyone->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactAnyone->isVisibleTo (Yii::app()->getSuModel ()));

        $contactUserPrivate->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactUserPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactOtherPrivate->asa ('permissions')->clearCache ();
        $this->assertTrue ($contactOtherPrivate->isVisibleTo (Yii::app()->getSuModel ()));

        $contactInvisible->asa ('permissions')->clearCache ();
        $this->assertFalse ($contactInvisible->isVisibleTo (Yii::app()->getSuModel ()));

        $criteria = Contacts::model ()->getAccessCriteria ();
        $contacts = Contacts::model ()->findAll ($criteria);
        $this->assertEquals (5, count ($contacts));
        $this->assertEquals (5, count (array_intersect (
            array (
                $contactGroup->id,
                $contactGroupmate->id,
                $contactAnyone->id,
                $contactUserPrivate->id,
                $contactOtherPrivate->id,
            ),
            array_map (function ($contact) { return $contact->id; }, $contacts)
        )));

        $criteria = Contacts::model ()->getAccessCriteria ('t', 'X2PermissionsBehavior', true);
        $contacts = Contacts::model ()->findAll ($criteria);
        $this->assertEquals (6, count ($contacts));
        $this->assertEquals (6, count (array_intersect (
            array (
                $contactGroup->id,
                $contactGroupmate->id,
                $contactAnyone->id,
                $contactUserPrivate->id,
                $contactOtherPrivate->id,
                $contactInvisible->id,
            ),
            array_map (function ($contact) { return $contact->id; }, $contacts)
        )));

        TestingAuxLib::restoreX2AuthManager ();
    }

    /**
     * Tests hidden condition as well as a query inside ApiController that uses the hidden 
     * condition (since action that contains the query cannot be easily tested without a refactor)
     */
    public function testHiddenCondition () {
        $contact = $this->contacts ('contactGroupmate');
        $number = '2349182348';
        $phoneCrit = new CDbCriteria(array(
            'condition' => "modelType='Contacts' AND number LIKE :number",
            'params' => array(':number'=>"%$number%")
        ));
        $phoneCrit->join =
            'join x2_contacts on modelId=x2_contacts.id AND '.
            Contacts::model ()->getHiddenCondition ('x2_contacts');
        $phoneNumber = PhoneNumber::model()->find($phoneCrit);

        $contact->markAsDuplicate ();
        $this->assertNull (PhoneNumber::model()->find($phoneCrit));
    }

}
