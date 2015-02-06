<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
        return  array (
            'fields' => array ('Fields', '.FieldFormatterTest'),
        );
    }

    /**
     * Add columns for custom fields
     */
    public static function setUpBeforeClass () {
        parent::setUpBeforeClass ();
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $_SERVER['SERVER_NAME'] = 'http://localhost';
        Yii::app()->cache->flush ();
        $contacts = Contacts::model ();
        try {
            Contacts::model()->c_TestInt;
        } catch (Exception $e) {
            Yii::app()->db->createCommand ("
                alter table x2_contacts add column c_TestInt bigint(20) default null;
            ")->execute ();
        }
        try {
            Contacts::model()->c_TestPercentage;
        } catch (Exception $e) {
            Yii::app()->db->createCommand ("
                alter table x2_contacts add column c_TestPercentage float default null;
            ")->execute ();
        }
        try {
            Contacts::model()->c_TestFloat;
        } catch (Exception $e) {
            Yii::app()->db->createCommand ("
                alter table x2_contacts add column c_TestFloat float default null;
            ")->execute ();
        }
        try {
            Contacts::model()->c_TestTimerSum;
        } catch (Exception $e) {
            Yii::app()->db->createCommand ("
                alter table x2_contacts add column c_TestTimerSum int(11) default null;
            ")->execute ();
        }
        try {
            Contacts::model()->c_TestCustom;
        } catch (Exception $e) {
            Yii::app()->db->createCommand ("
                alter table x2_contacts add column c_TestCustom varchar(255) default null;
            ")->execute ();
        }
        try {
            Contacts::model()->c_TestUrlEmptyLinkType;
        } catch (Exception $e) {
            Yii::app()->db->createCommand ("
                alter table x2_contacts add column c_TestUrlEmptyLinkType varchar(32) default null;
            ")->execute ();
        }
        self::tryAddCol ($contacts, 'c_TestCustom2', 'varchar(255)');
        self::tryAddCol ($contacts, 'c_TestCustom3', 'varchar(255)');
        Yii::app()->db->schema->refresh ();
        Contacts::model ()->refreshMetaData ();
    }

    private static function tryAddCol ($model, $col, $type) {
        try {
            Contacts::model()->$col;
        } catch (Exception $e) {
            Yii::app()->db->createCommand ("
                alter table x2_contacts add column $col $type default null;
            ")->execute ();
        }
    }

    /**
     * Clean up custom field columns 
     */
    public static function tearDownAfterClass () {
        Yii::app()->db->createCommand ("
            alter table x2_contacts drop column c_TestInt;
            alter table x2_contacts drop column c_TestPercentage;
            alter table x2_contacts drop column c_TestFloat;
            alter table x2_contacts drop column c_TestTimerSum;
            alter table x2_contacts drop column c_TestCustom;
            alter table x2_contacts drop column c_TestCustom2;
            alter table x2_contacts drop column c_TestCustom3;
            alter table x2_contacts drop column c_TestUrlEmptyLinkType;
        ")->execute ();
        parent::tearDownAfterClass ();
    }

    /**
     * Call render atleast once for each field type  
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
            VERBOSE_MODE && println ('type='.$type);
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
