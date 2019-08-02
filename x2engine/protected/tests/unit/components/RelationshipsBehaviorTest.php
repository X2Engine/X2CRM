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
Yii::import('application.modules.accounts.models.*');

class RelationshipsBehaviorTest extends X2DbTestCase {
    
    public $fixtures = array(
        'contact' => 'Contacts',
        'account' => array('Accounts','_1'),
        'events' => array('Events','.DummyData'),
        'users' => array('User','_1'),
        'relationships' => 'Relationships'
    );
    
    public function testHasRelationship(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        
        // No relationship to start
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $this->assertFalse($otherContact->relationships->hasRelationship($contact));
        
        // Create relationship record manually
        $rel = new Relationships;
        $rel->firstType = get_class($contact);
        $rel->firstId = $contact->id;
        $rel->secondType = get_class($otherContact);
        $rel->secondId = $otherContact->id;
        $rel->save();
        
        // Verify that relationship exists and is symmetrical
        $this->assertTrue($contact->relationships->hasRelationship($otherContact));
        $this->assertTrue($otherContact->relationships->hasRelationship($contact));
        
        $rel->delete();
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $this->assertFalse($otherContact->relationships->hasRelationship($contact));
    }
    
    public function testIsValidTarget(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        $account = $this->account('account1');
        
        // Valid targets are children of CActiveRecord with the relationships
        // behavior and a non-empty ID field.
        $this->assertFalse($contact->relationships->isValidTarget(null));
        $this->assertFalse($contact->relationships->isValidTarget(false));
        $this->assertFalse($contact->relationships->isValidTarget(''));
        $this->assertFalse($contact->relationships->isValidTarget(new Contacts()));
        
        $user = User::model()->findByPk(1);
        $this->assertFalse($contact->relationships->isValidTarget($user));
        $this->assertFalse($contact->relationships->isValidTarget($contact));
        $sameContact = Contacts::model()->findByPk($contact->id);
        // Can't create a relationship to itself
        $this->assertFalse($contact->relationships->isValidTarget($sameContact));
        
        $this->assertTrue($contact->relationships->isValidTarget($otherContact));
        $this->assertTrue($contact->relationships->isValidTarget($account));
    }
    
    public function testGetRelationship(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $this->assertNull($contact->relationships->getRelationship($otherContact));
        $this->assertNull($otherContact->relationships->getRelationship($contact));
        
        $rel = new Relationships;
        $rel->firstType = get_class($contact);
        $rel->firstId = $contact->id;
        $rel->secondType = get_class($otherContact);
        $rel->secondId = $otherContact->id;
        $rel->save();
        
        // Assert that relationship eixsts and is symmetrical
        $this->assertNotNull($contact->relationships->getRelationship($otherContact));
        $this->assertNotNull($otherContact->relationships->getRelationship($contact));
        $this->assertEquals($rel->id, $contact->relationships->getRelationship($otherContact)->id);
        $this->assertEquals($rel->id, $otherContact->relationships->getRelationship($contact)->id);
        
        $rel->delete();
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $this->assertNull($contact->relationships->getRelationship($otherContact));
        $this->assertNull($otherContact->relationships->getRelationship($contact));
    }

    public function testGetRelationships(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        $account = $this->account('account1');
        
        $contact1Relationships = count($contact->relationships->getRelationships());
        $contact2Relationships = count($otherContact->relationships->getRelationships());
        $accountRelationships = count($account->relationships->getRelationships());
        
        $rel = new Relationships;
        $rel->firstType = get_class($contact);
        $rel->firstId = $contact->id;
        $rel->secondType = get_class($otherContact);
        $rel->secondId = $otherContact->id;
        $rel->save();
        
        $this->assertEquals($contact1Relationships+1, count($contact->relationships->getRelationships(true)));
        $this->assertEquals($contact2Relationships+1, count($otherContact->relationships->getRelationships(true)));
        
        $rel2 = new Relationships;
        $rel2->firstType = get_class($contact);
        $rel2->firstId = $contact->id;
        $rel2->secondType = get_class($account);
        $rel2->secondId = $account->id;
        $rel2->save();
        
        $this->assertEquals($contact1Relationships+2, count($contact->relationships->getRelationships(true)));
        $this->assertEquals($contact2Relationships+1, count($otherContact->relationships->getRelationships(true)));
        $this->assertEquals($accountRelationships+1, count($account->relationships->getRelationships(true)));
        
        $rel->delete();
        $rel2->delete();
        
        // Verify that cache is preserved unless we manually refresh it
        $this->assertEquals($contact1Relationships+2, count($contact->relationships->getRelationships()));
        $this->assertEquals($contact2Relationships+1, count($otherContact->relationships->getRelationships()));
        $this->assertEquals($accountRelationships+1, count($account->relationships->getRelationships()));
        $this->assertEquals($contact1Relationships, count($contact->relationships->getRelationships(true)));
        $this->assertEquals($contact2Relationships, count($otherContact->relationships->getRelationships(true)));
        $this->assertEquals($accountRelationships, count($account->relationships->getRelationships(true)));
    }
    
    public function testCreateRelationship(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        
        $this->assertFalse($contact->relationships->createRelationship(null));
        $this->assertTrue($contact->relationships->createRelationship($otherContact));
        //Only create relationship once
        $this->assertFalse($contact->relationships->createRelationship($otherContact));
        
        $this->assertTrue($contact->relationships->hasRelationship($otherContact));
        $contact->relationships->getRelationship($otherContact)->delete();
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
    }
    
    public function testDeleteRelationship(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $contact->relationships->createRelationship($otherContact);
        $this->assertTrue($contact->hasRelationship($otherContact));
        
        $this->assertEquals(1, $contact->relationships->deleteRelationship($otherContact));
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
    }
    
    public function testAfterSave(){
        $contact = $this->contact('testAnyone');
        $account = $this->account('account1');
        $account2 = $this->account('account2');
        
        $this->assertFalse($contact->relationships->hasRelationship($account));
        
        $contact->company = Fields::nameId($account->name, $account->id);
        $this->assertSaves($contact);
        $this->assertTrue($contact->relationships->hasRelationship($account));
        
        //Contact needs to be reloaded to refresh oldAttributes for afterSave
        $contact = Contacts::model()->findByPk($contact->id);
        $contact->company = Fields::nameId($account2->name, $account2->id);
        $this->assertSaves($contact);
        $this->assertFalse($contact->relationships->hasRelationship($account));
        $this->assertTrue($contact->relationships->hasRelationship($account2));
        
        $contact = Contacts::model()->findByPk($contact->id);
        $contact->company = NULL;
        $this->assertSaves($contact);
        $this->assertFalse($contact->relationships->hasRelationship($account));
        $this->assertFalse($contact->relationships->hasRelationship($account2));
    }
    
    public function testAfterDelete(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $contact->relationships->createRelationship($otherContact);
        $this->assertTrue($contact->relationships->hasRelationship($otherContact));
        
        $rel = $contact->relationships->getRelationship($otherContact);
        $this->assertNotNull($rel);
        $contact->delete();
        $this->assertFalse($otherContact->relationships->hasRelationship($contact));
        $this->assertNull(Relationships::model()->findByPk($rel->id));
    }
    
    public function testGetRelatedX2Models(){
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $contact->relationships->createRelationship($otherContact);
        $this->assertTrue($contact->relationships->hasRelationship($otherContact));
        
        $relatedModels = $contact->relationships->getRelatedX2Models(true);
        $this->assertEquals(1, count($relatedModels));
        $this->assertInstanceOf(get_class($otherContact), $relatedModels[0]);
        $this->assertEquals($otherContact->id, $relatedModels[0]->id);
        
        $contact->relationships->deleteRelationship($otherContact);
        //Verify that cache is preserved unless we manually refresh it
        $relatedModels = $contact->relationships->getRelatedX2Models();
        $this->assertEquals(1, count($relatedModels));
        $this->assertInstanceOf(get_class($otherContact), $relatedModels[0]);
        $this->assertEquals($otherContact->id, $relatedModels[0]->id);
        
        $this->assertEmpty($contact->relationships->getRelatedX2Models(true));
    }
    
    public function testGetVisibleRelatedX2Models(){
        TestingAuxLib::loadControllerMock ();
        TestingAuxLib::suLogin ('testUser2');
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        
        $this->assertFalse($contact->relationships->hasRelationship($otherContact));
        $contact->relationships->createRelationship($otherContact);
        $this->assertTrue($contact->relationships->hasRelationship($otherContact));
        
        $visibleRelatedModels = $contact->relationships->getVisibleRelatedX2Models(true);
        $this->assertEquals(1, count($visibleRelatedModels));
        $this->assertInstanceOf(get_class($otherContact), $visibleRelatedModels[0]);
        $this->assertEquals($otherContact->id, $visibleRelatedModels[0]->id);
        
        $otherContact->visibility = 0;
        $otherContact->assignedTo = 'test';
        $this->assertSaves($otherContact);
        Yii::app()->params->isAdmin = false;
        
        $visibleRelatedModels = $contact->relationships->getVisibleRelatedX2Models();
        $this->assertEquals(1, count($visibleRelatedModels));
        $this->assertInstanceOf(get_class($otherContact), $visibleRelatedModels[0]);
        $this->assertEquals($otherContact->id, $visibleRelatedModels[0]->id);
        $this->assertEmpty($contact->relationships->getVisibleRelatedX2Models(true));
        
        TestingAuxLib::restoreController();
    }
}
