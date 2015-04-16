<?php

Yii::import("application.components.permissions.*");
Yii::import("application.modules.actions.models.*");
Yii::import("application.modules.users.models.*");
Yii::import("application.modules.groups.models.*");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Test the X2PermissionsBehavior Class
 *
 * @author raymond
 */

class X2PermissionsBehaviorTest extends X2DbTestCase {

    public $fixtures = array(
        'contacts' => 'Contacts',
        'users' => array('User','_1'),
        'groups' => array('Groups','_1'),
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
    }

    public function testIsVisibleTo () {
        $auth = TestingAuxLib::loadAuthManagerMock ();

        // private read only access
        $user = $this->users ('user2');
        $auth->setAccess ('ContactsAdmin', $user->id, array (), false);
        $auth->setAccess ('AdminIndex', $user->id, array (), false);
        $auth->setAccess ('ContactsReadOnlyAccess', $user->id, array (), false);
        $auth->setAccess ('ContactsPrivateReadOnlyAccess', $user->id, array (), true);
        TestingAuxLib::suLogin ('testUser2');

        // private contact assigned to user
        $contact = $this->contacts ('testAnyone');
        $contact->assignedTo = $user->username;
        $contact->visibility = 0;
        $this->assertSaves ($contact);
        $contact->refresh ();
        $this->assertTrue ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // private contact not assigned to user
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'not'.$user->username;
        $this->assertSaves ($contact);
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // contact is invisible
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'Anyone';
        $this->assertSaves ($contact);
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));



        // read only access
        $user = $this->users ('user2');
        $auth->setAccess ('ContactsAdmin', $user->id, array (), false);
        $auth->setAccess ('AdminIndex', $user->id, array (), false);
        $auth->setAccess ('ContactsReadOnlyAccess', $user->id, array (), true);
        $auth->setAccess ('ContactsPrivateReadOnlyAccess', $user->id, array (), false);

        // private contact assigned to user
        $contact->asa ('permissions')->clearCache ();
        $contact = $this->contacts ('testAnyone');
        $contact->assignedTo = $user->username;
        $contact->visibility = 0;
        $this->assertSaves ($contact);
        $contact->refresh ();
        $this->assertTrue ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // private contact not assigned to user
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'not'.$user->username;
        $this->assertSaves ($contact);
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // public contact not assigned to user
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'not'.$user->username;
        $contact->visibility = 1;
        $this->assertSaves ($contact);
        $this->assertTrue ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // contact is invisible
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'Anyone';
        $contact->visibility = 0;
        $this->assertSaves ($contact);
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));



        // no access
        $user = $this->users ('user2');
        $auth->setAccess ('ContactsAdmin', $user->id, array (), false);
        $auth->setAccess ('AdminIndex', $user->id, array (), false);
        $auth->setAccess ('ContactsReadOnlyAccess', $user->id, array (), false);
        $auth->setAccess ('ContactsPrivateReadOnlyAccess', $user->id, array (), false);

        // private contact assigned to user
        $contact->asa ('permissions')->clearCache ();
        $contact = $this->contacts ('testAnyone');
        $contact->assignedTo = $user->username;
        $contact->visibility = 0;
        $this->assertSaves ($contact);
        $contact->refresh ();
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // private contact not assigned to user
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'not'.$user->username;
        $this->assertSaves ($contact);
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // public contact not assigned to user
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'not'.$user->username;
        $contact->visibility = 1;
        $this->assertSaves ($contact);
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));

        // contact is invisible
        $contact->asa ('permissions')->clearCache ();
        $contact->assignedTo = 'Anyone';
        $contact->visibility = 0;
        $this->assertSaves ($contact);
        $this->assertFalse ($contact->isVisibleTo (Yii::app()->getSuModel ()));
    }

}
