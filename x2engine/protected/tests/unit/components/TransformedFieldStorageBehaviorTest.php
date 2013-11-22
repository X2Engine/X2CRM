<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import('application.modules.contacts.models.*');

/**
 * Tests the field transforming behavior using mocks & stubs in PHPUnit
 *
 * @package X2CRM.tests.unit.components
 * @author Demitri Morgan
 */
class TransformedFieldStorageBehaviorTest extends X2DbTestCase {

    public static function referenceFixtures(){
        return null;
    }

    public $fixtures = array('contacts' => 'Contacts');

    public function testPackUnpack(){
        // We'll be working with a simple x10, x 1/10 pack/unpack operation.
        $valueMap = array(
            array('name', 10),
            array('email', 20),
        );
        $invValueMap = array(
            array('name', 1),
            array('email', 2),
        );
        $tfsb = $this->getMockForAbstractClass('TransformedFieldStorageBehavior');
        $tfsb->expects($this->any())->method('packAttribute')->will($this->returnValueMap($valueMap));
        $tfsb->expects($this->any())->method('unpackAttribute')->will($this->returnValueMap($invValueMap));
        $tfsb->transformAttributes = array('name', 'email');
        $contact = $this->contacts('testUser');
        $contact->name = 1;
        $contact->email = 2;
        $tfsb->attach($contact);
        // First test by calling manually...
        $tfsb->packAll();
        $this->assertEquals(10, $contact->name);
        $this->assertEquals(20, $contact->email);
        $tfsb->unpackAll();
        $this->assertEquals(1, $contact->name);
        $this->assertEquals(2, $contact->email);
        // Test that saving doesn't impact its value as seen from within the code (transparency):
        $contact->save();
        $this->assertEquals(1, $contact->name);
        $this->assertEquals(2, $contact->email);
    }

}

?>
