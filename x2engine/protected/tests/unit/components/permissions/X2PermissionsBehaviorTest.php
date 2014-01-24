<?php

Yii::import("application.components.permissions.*");

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

class X2PermissionsBehaviorTest extends X2TestCase {
    
    public function testGetAccessConditions() {
        $behavior = new X2PermissionsBehavior;
        $this->assertEquals(true, is_array($behavior->getAccessConditions(0, 0, 'admin')));
        
        $accessLevel3 = $behavior->getAccessConditions(3, 1, 'admin');
        $this->assertEquals('TRUE', $accessLevel3[0]['condition']);
        $this->assertEquals('AND', $accessLevel3[0]['operator']);
        
        $accessLevel2 = $behavior->getAccessConditions(1, 1, 'admin');
        $this->assertEquals('t.assignedTo="admin"', $accessLevel2[0]['condition']);
        $this->assertEquals('OR', $accessLevel2[0]['operator']);
        
    }
}
