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
 * @package application.tests.unit.modules.contacts.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2ListTest extends X2DbTestCase {

    public $fixtures = array(
        'contacts' => array('Contacts', '.list'),
        'lists' => 'X2List',
        'listItems' => 'X2ListItem',
        'listCriteria' => 'X2ListCriterion',
    );

    public function testStaticDuplicate_dynamic() {
        // Static clone of a dynamic list:
        $that = $this;
        $expectedContactEmailAddresses = array_map(function($i)use($that){
            return $that->contacts("listTest$i")->email;
        },array(1,2,3));
        
        $dyn = $this->lists('staticDuplicateDynamic');
        $modelName = $dyn->modelName;
        $dynClone = $dyn->staticDuplicate();
        $this->assertNotEmpty($dynClone);
        $this->assertTrue($dynClone instanceof X2List);
        $this->assertEquals($dyn->count,$dynClone->count);
        $this->assertEquals($dyn->modelName,$dynClone->modelName);
        // A cursory check that the criteria generation works:
        $expectedContacts = $dyn->queryCommand()->queryAll(true);
        $this->assertEquals($expectedContactEmailAddresses, array_map(function($c){
            return $c['email'];
        }, $expectedContacts));
        $this->assertEquals(3,count($expectedContacts));
        // Test that the static clone has all the correct list items.
        //
        // The reference to contacts happens in a roundabout way because the
        // "contact" relation in X2ListItem is expected to some day be
        // removed, when lists can be used for more than just contacts.
        $this->assertEquals($expectedContactEmailAddresses, array_map(function($i)use($modelName){
            return X2Model::model($modelName)->findByPk($i->contactId)->email;
        }, $dynClone->listItems));

        // Static clone of a static list:
        $expectedContactEmailAddresses = array_map(function($i)use($that){
            return $that->contacts("listTest$i")->email;
        },array(1,2,3));

        $static = $this->lists('staticDuplicateStatic');
        $modelName = $static->modelName;
        $staticClone = $static->staticDuplicate();
        $modelName = $static->modelName;
        $this->assertNotEmpty($staticClone);
        $this->assertEquals(3,count($staticClone->listItems));
        $this->assertTrue($staticClone instanceof X2List);
        $this->assertEquals($static->count,$staticClone->count);
        $this->assertEquals($static->modelName,$staticClone->modelName);
        $emailsInList = function($i)use($modelName){
                    return X2Model::model($modelName)->findByPk($i->contactId)->email;
                };
        $this->assertEquals(array_map($emailsInList, $static->listItems)
                , array_map($emailsInList, $staticClone->listItems));

        $expectedEmailAddresses = array_map(function($i)use($that){
            return $that->listItems("subscriber$i")->emailAddress;
        },array(4,5,6,7));

        // Static clone of a newsletter list:
        $static = $this->lists('staticDuplicateNewsletter');

        $modelName = $static->modelName;
        $staticClone = $static->staticDuplicate();
        $staticClone->refresh ();
        $modelName = $static->modelName;
        $this->assertNotEmpty($staticClone);
        $this->assertEquals(4,count($staticClone->listItems));
        $this->assertTrue($staticClone instanceof X2List);
        $this->assertEquals($static->count,$staticClone->count);
        $this->assertEquals($static->modelName,$staticClone->modelName);
        $emailsInList = function($i)use($modelName){
                    return $i->emailAddress;
                };
        $this->assertEquals(array_map($emailsInList, $static->listItems)
                , array_map($emailsInList, $staticClone->listItems));
    }
    
    public function testAddIds() {
        $contact = $this->contacts('listTest3');
        $staticList = $this->lists('testUser');
        $newsletter = $this->lists('testNewsletter');
        $prevStaticCount = $staticList->count;
        $prevNewsletterCount = $newsletter->count;

        $this->assertTrue($staticList->addIds($contact->id));
        $this->assertEquals($staticList->count, $prevStaticCount + 1);

        // Should fail to add a contact to a newsletter without the $allowNewsletter param
        $this->assertFalse($newsletter->addIds($contact->id));
        $this->assertEquals($newsletter->count, $prevNewsletterCount);

        // Should now succeed when adding the contact
        $this->assertTrue($newsletter->addIds($contact->id, true));
        $this->assertEquals($newsletter->count, $prevNewsletterCount + 1);
    }
}

?>
