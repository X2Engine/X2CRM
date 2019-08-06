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




Yii::import ('application.controllers.*');
Yii::import ('application.modules.contacts.*');
Yii::import ('application.modules.contacts.controllers.*');
Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.modules.accounts.models.*');
Yii::import ('application.modules.products.models.*');
Yii::import ('application.modules.groups.models.*');

class FieldFormatterTest extends X2DbTestCase {

//    public $fixtures = array (
//        'fields' => array ('Fields', '.FieldFormatterTest'),
//    );

    public static function referenceFixtures () {
        return array (
            'contacts' => 'Contacts',
            'products' => 'Product',
        );
    }
    
    private static $_oldServer;
    private static $_oldController;

    /**
     * Add columns for custom fields
     */
    public static function setUpBeforeClass () {
        parent::setUpBeforeClass ();
        
        self::$_oldServer = $_SERVER;
        self::$_oldController = Yii::app()->controller;
        
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $_SERVER['SERVER_NAME'] = 'http://localhost';
        Yii::app()->cache->flush ();
        $contacts = Contacts::model ();
        self::tryAddCol ($contacts, 'c_TestInt', 'int');
        self::tryAddCol ($contacts, 'c_TestPercentage', 'percentage');
        self::tryAddCol ($contacts, 'c_TestFloat', 'float');
        self::tryAddCol ($contacts, 'c_TestTimerSum', 'timerSum');
        self::tryAddCol ($contacts, 'c_TestCustom', 'custom');
        self::tryAddCol ($contacts, 'c_TestUrlEmptyLinkType', 'url');
        self::tryAddCol ($contacts, 'c_TestCustom2', 'custom');
        self::tryAddCol ($contacts, 'c_TestCustom3', 'custom');
        Yii::app()->db->schema->refresh ();
        Yii::app()->cache->flush ();
        Contacts::model ()->resetFieldsPropertyCache ();
        Contacts::model ()->refreshMetaData ();
    }

    public function setUp () {
        TestingAuxLib::loadControllerMock ();
        return parent::setUp ();
    }
    
    public function tearDown(){
        TestingAuxLib::restoreController();
        parent::tearDown();
    }

    private static function tryAddCol ($model, $col, $type) {
        if (!Fields::model ()->findByAttributes (array (
            'fieldName' => $col,
            'modelName' => get_class ($model),
            ))) {

            $field = new Fields;
            $field->setAttributes (array (
                'modelName' => get_class ($model),
                'fieldName' => $col,
                'attributeLabel' => $col,
                'modified' => '1',
                'custom' => 1,
                'type' => $type,
                'required' => '0',
                'uniqueConstraint' => '0',
                'safe' => '1',
                'readOnly' => '0',
                'linkType' => NULL,
                'searchable' => '0',
                'relevance' => 'Low',
                'isVirtual' => '0',
                'defaultValue' => NULL,
                'keyType' => '',
                'data' => NULL,
            ), false);
            if (!$field->save ()) {
                AuxLib::debugLogR ('$col = ');
                AuxLib::debugLogR ($col);
                AuxLib::debugLogR ($field->getErrors ());
            }
        }
    }

    /**
     * Clean up custom field columns 
     */
    public static function tearDownAfterClass () {
        $fields = Fields::model ()->findAllByAttributes (array (
            'custom' => 1,
        ));
        foreach ($fields as $field) assert ($field->delete ());
        Yii::app()->db->schema->refresh ();
        Yii::app()->cache->flush ();
        Contacts::model ()->refreshMetaData ();
        Contacts::model ()->resetFieldsPropertyCache ();
       AuxLib::debugLogR ('Contacts::model ()->getAttributes () = ');
        AuxLib::debugLogR (Contacts::model ()->getAttributes ());
        
        $_SERVER = self::$_oldServer;
        Yii::app()->controller = self::$_oldController;
        
        parent::tearDownAfterClass ();
    }

    /**
     * Call render at least once for each field type  
     */
    public function testRender () {
        Yii::app()->cache->flush ();
        TestingAuxLib::suLogin ('admin');
        $contact = Contacts::model ()->findByPk (12345);
        $fieldTypes = Fields::getFieldTypes ();
        foreach ($fieldTypes as $type => $info) {
            $fieldsOfType = $contact->getFields (false, function ($field) use ($type) {
                return strtolower ($field->type) === strtolower ($type);
            });
            X2_TEST_DEBUG_LEVEL > 1 && println ('type='.$type);
            $this->assertTrue (count ($fieldsOfType) > 0);
            foreach ($fieldsOfType as $field) {
                $contact->formatter->renderAttribute ($field->fieldName, true, true, true);
            }
        }
    }

    protected function assertRender ($model, $fieldName) {
        foreach (array (true, false) as $makeLinks) {
            foreach (array (true, false) as $textOnly) {
                foreach (array (true, false) as $encode) {
                    $model->formatter->renderAttribute (
                        $fieldName, $makeLinks, $textOnly, $encode);
                }
            }
        }
    }

    protected function getFieldOfType ($model, $type) {
        $fieldsOfType = $model->getFields (false, function ($field) use ($type) {
            return strtolower ($field->type) === strtolower ($type);
        });
        $field = array_pop ($fieldsOfType);
        $fieldName = $field->fieldName;
        return array ($field, $fieldName);
    }

    protected function getAllFieldsOfType ($model, $type) {
        return $model->getFields (false, function ($field) use ($type) {
            return strtolower ($field->type) === strtolower ($type);
        });
    }

    public function testDate () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'date');
        $contact->$fieldName = '';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = '1234';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = '1234asdf';
        $this->assertRender ($contact, $fieldName);
    }

    public function testDateTime () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'dateTime');
        $contact->$fieldName = '';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = '1234';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = '1234asdf';
        $this->assertRender ($contact, $fieldName);
    }

    // TODO
//    public function testRating () {
//        $contact = Contacts::model ()->findByPk (12345);
//        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'rating');
//        $contact->$fieldName = 5;
//        $this->assertRender ($contact, $fieldName);
//        $contact->$fieldName = null;
//        $this->assertRender ($contact, $fieldName);
//    }

    public function testAssignment () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'assignment');
        $contact->$fieldName = 'chames';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = 'chames, 1';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = 'chames, 1, Email';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = 'chames, 1, Email, Anyone';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = array ('chames', 1, 'Email', 'Anyone');
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = 2;
        $this->assertRender ($contact, $fieldName);
    }

    public function testVisibility () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'visibility');
        $contact->$fieldName = '1';
        $this->assertRender ($contact, $fieldName);
    }

    public function testEmail () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'email');
        $contact->$fieldName = '';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = 'test@example.com';
        $this->assertRender ($contact, $fieldName);
    }

    public function testPhone () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'phone');
        $contact->$fieldName = '123-123-4567';
        $this->assertRender ($contact, $fieldName);
    }

    public function testUrl () {
        $contact = Contacts::model ()->findByPk (12345);
        $fields  = $this->getAllFieldsOfType ($contact, 'url');
        foreach ($fields as $field) {
            $fieldName = $field->fieldName;
            $contact->$fieldName = '';
            $this->assertRender ($contact, $fieldName);
            $contact->$fieldName = 'www.example.com';
            $this->assertRender ($contact, $fieldName);
        }
    }

    public function testLink () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'link');
        $contact->$fieldName = null;
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = $contact->nameId;
        $this->assertRender ($contact, $fieldName);
    }

    public function testBoolean () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'boolean');
        $contact->$fieldName = true;
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = false;
        $this->assertRender ($contact, $fieldName);
    }

    public function testCurrency () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'currency');
        $contact->$fieldName = '';
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = 1234;
        $this->assertRender ($contact, $fieldName);

        $product = Product::model ()->findByPk (14);
        list ($field, $fieldName) = $this->getFieldOfType ($product, 'currency');
        $product->$fieldName = '';
        $this->assertRender ($product, $fieldName);
        $product->$fieldName = 1234;
        $this->assertRender ($product, $fieldName);
    }

    public function testPercentage () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'percentage');
        $contact->$fieldName = 123;
        $this->assertRender ($contact, $fieldName);
        $contact->$fieldName = null;
        $this->assertRender ($contact, $fieldName);
    }

    public function testDropdown () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'dropdown');
        $this->assertRender ($contact, $fieldName);
    }

    // TODO
    public function testParentCase () {
    }

    public function testText () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'text');
        $this->assertRender ($contact, $fieldName);
    }

    // TODO
    public function testCredentials () {
    }

    public function testTimerSum () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'timerSum');
        $contact->$fieldName = 123;
        $this->assertRender ($contact, $fieldName);
    }

    public function testInt () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'int');
        $contact->$fieldName = 123;
        $this->assertRender ($contact, $fieldName);
    }

    public function testFloat () {
        $contact = Contacts::model ()->findByPk (12345);
        list ($field, $fieldName) = $this->getFieldOfType ($contact, 'float');
        $contact->$fieldName = 123.123;
        $this->assertRender ($contact, $fieldName);
    }

    public function testCustom () {
        $contact = Contacts::model ()->findByPk (12345);
        $fields  = $this->getAllFieldsOfType ($contact, 'custom');
        foreach ($fields as $field) {
            $fieldName = $field->fieldName;
            if ($field->linkType === 'display') {
                $contact->$fieldName = '<b>{firstName}</b>';
            } elseif ($field->linkType === 'formula') {
                $contact->$fieldName = '={dealValue} + 5';
            } else {
                $contact->$fieldName = '';
            }
            $this->assertRender ($contact, $fieldName);
        }
    }
}

?>
