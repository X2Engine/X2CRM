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

    public static function referenceFixtures() {
        return array(
            'users' => array('User','_1'),
            'groups' => array('Groups','_1'),
            'groupToUser' => array('GroupToUser','_1')
        );
    }
    
    public function testGetAccessConditions() {
        $action = new Actions;
        $this->assertEquals(true, is_array($action->getAccessConditions(0, false , 'admin')));
        
        $accessLevel3 = $action->getAccessConditions(3, 'visibility', 'admin');
        $this->assertEquals(
            "NOT (visibility.visibility=0 AND visibility.assignedTo='Anyone')",
            $accessLevel3[0]['condition']);
        $this->assertEquals('OR', $accessLevel3[0]['operator']);
        
        $accessLevel2 = $action->getAccessConditions(1, 'visibility', 'admin');
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
}
