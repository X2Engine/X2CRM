<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.opportunities.models.*');
Yii::import('application.modules.quotes.models.*');

/**
 * Test certain features specific to {@link X2Model}.
 *
 * Note, this will use subclasses. This is a shortcut that should probably in the
 * future be replaced with mocks, fake tables and special fixtures in order to
 * generally test {@link X2Model} without relying on ephemeral data.
 *
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2ModelTest extends X2DbTestCase {

    public $fixtures = array(
        'contact' => 'Contacts',
    );

    public static function referenceFixtures(){
        return array(
            'account' => 'Accounts'
        );
    }

    private $_nameFields;

    public function nameFields(){
        if(!isset($this->_nameFields)){
            $this->_nameFields = array();
            $this->_nameFields[] = Fields::model()->findByAttributes(array('fieldName' => 'firstName', 'modelName' => 'Contacts'));
            $this->_nameFields[] = Fields::model()->findByAttributes(array('fieldName' => 'lastName', 'modelName' => 'Contacts'));
        }
        return $this->_nameFields;
    }

    public function setDefaultName(){
        list($firstName, $lastName) = $this->nameFields();
        $firstName->defaultValue = 'Gustavo';
        $lastName->defaultValue = 'Fring';
        $firstName->save();
        $lastName->save();
        Yii::app()->cache->flush();
    }

    public function resetNameFields(){
        list($firstName, $lastName) = $this->nameFields();
        $firstName->defaultValue = '';
        $lastName->defaultValue = '';
        $firstName->save();
        $lastName->save();
        Yii::app()->cache->flush();
    }

    public function setUp(){
        parent::setUp();
        $this->setDefaultName();
    }

    public function tearDown(){
        $this->resetNameFields();
        parent::tearDown();
    }

    /**
     * Test setting default values in new records
     */
    public function testDefaultValues(){
        foreach(X2Model::model('Contacts')->getFields() as $field){
            // Retrieve new values:
            $field->refresh();
        }

        // Setting default values in the constructor
        $contact = new Contacts;
        $this->assertEquals('Gustavo', $contact->firstName);
        $this->assertEquals('Fring', $contact->lastName);

        // Setting default values in setX2Fields
        $contact->firstName = '';
        $contact->lastName = '';
        $input = array();
        $contact->setX2Fields($input);
        $this->assertEquals('Gustavo', $contact->firstName);
        $this->assertEquals('Fring', $contact->lastName);
    }

    public function testFindByEmail(){
        $c = Contacts::model()->findByEmail($this->contact('testAnyone')->email);
        $this->assertTrue((bool) $c);
        $this->assertEquals($this->contact('testAnyone')->id, $c->id);
    }

    /**
     * A cursory test of the auto-ref update for the link-type fields refactor.
     */
    public function testUpdateNameIdRefs(){
        $account = $this->account('testQuote');
        $contact = $this->contact('testAnyone');
        // Test name change:
        $account->refresh();
        $account->name = 'A smouldering crater left behind by the G-man';
        $account->save();
        $contact->refresh();
        $this->assertEquals(Fields::nameId($account->name, $account->id), $contact->company);
        // Test deletion:
        $account->delete();
        $contact->refresh();
        $this->assertEquals($account->name, $contact->company);
    }

    public function testMassUpdateNameId(){
        $contact = $this->contact('testAnyone');
        // First, need to break all the nameIds...
        Contacts::model()->updateAll(array('nameId'=>null));
        // Try with the mass update method, one ID:
        X2Model::massUpdateNameId('Contacts', array($contact->id));
        $contact->refresh();
        $this->assertEquals(Fields::nameId($contact->name,$contact->id),$contact->nameId);
        // Again, but with the "ids" parameter an int instead of an array
        X2Model::massUpdateNameId('Contacts', $contact->id);
        $contact->refresh();
        $this->assertEquals(Fields::nameId($contact->name,$contact->id),$contact->nameId);
        // Try again, multiple records:
        $contact2 = $this->contact('testUser');
        Contacts::model()->updateAll(array('nameId'=>null));
        X2Model::massUpdateNameId('Contacts',array($contact->id,$contact2->id));
        $contact->refresh();
        $contact2->refresh();
        $this->assertEquals(Fields::nameId($contact->name,$contact->id),$contact->nameId);
        $this->assertEquals(Fields::nameId($contact2->name,$contact2->id),$contact2->nameId);
        // Try one last time, all records:
        Contacts::model()->updateAll(array('nameId'=>null));
        X2Model::massUpdateNameId('Contacts');
        $contact->refresh();
        $contact2->refresh();
        $this->assertEquals(Fields::nameId($contact->name,$contact->id),$contact->nameId);
        $this->assertEquals(Fields::nameId($contact2->name,$contact2->id),$contact2->nameId);
    }

}

?>
