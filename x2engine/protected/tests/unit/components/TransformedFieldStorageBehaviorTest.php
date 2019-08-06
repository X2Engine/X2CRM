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




Yii::import('application.modules.contacts.models.*');

/**
 * Tests the field transforming behavior using mocks & stubs in PHPUnit
 *
 * @package application.tests.unit.components
 * @author Demitri Morgan
 */
class TransformedFieldStorageBehaviorTest extends X2DbTestCase {

    public static function referenceFixtures(){
        return array ();
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
