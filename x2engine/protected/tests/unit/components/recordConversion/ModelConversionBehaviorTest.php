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




class ModelConversionBehaviorTest extends X2DbTestCase {

    public $fixtures = array(
        'x2Leads' => array('X2Leads', '.ModelConversionBehaviorTest'),
    );

    private static $_oldFieldTypes = array ();
    public static function setUpBeforeClass () {
        self::$_oldFieldTypes = array (
            array (
                'modelName' => 'X2Leads',
                'fieldName' => 'description',
                'type' => Yii::app()->db->createCommand ()
                    ->select ('type')
                    ->from ('x2_fields')
                    ->where ('modelName="X2Leads" and fieldName="description"')
                    ->queryScalar ()
            )
        );
        return parent::setUpBeforeClass ();
    }

    private static function restoreFields () {
        foreach (self::$_oldFieldTypes as $oldField) {
            $field = Fields::model ()->findByAttributes (
                array (
                    'modelName' => $oldField['modelName'],
                    'fieldName' => $oldField['fieldName'],
                )
            );
            $field->setAttributes (array ('type' => $oldField['type']));
            $field->save ();
            $modelName = $oldField['modelName'];
            Yii::app()->cache->flush ();
            $modelName::model ()->resetFieldsPropertyCache ();
        }

        $field = Fields::model ()->findByAttributes (
            array (
                'modelName' => $oldField['modelName'],
                'fieldName' => 'c_extraField',
            )
        );
        if ($field) $field->delete ();
        Yii::app()->cache->flush ();
        Yii::app()->db->schema->refresh ();
        X2Leads::model ()->resetFieldsPropertyCache ();
        X2Leads::model ()->refreshMetaData ();
    }

    public static function tearDownAfterClass () {
        self::restoreFields ();
        return parent::tearDownAfterClass ();
    }

    public function testMapFields () {
        $attrs = array (
            'accountName' => null,
            'quoteAmount' => null,
            'salesStage' => null,
            'probability' => null,
            'description' => null,
        );
        $expected = array (
            'company' => null,
            'dealvalue' => null,
            'dealstatus' => null,
            'backgroundInfo' => null,
        );
        $conversionBehavior = X2Leads::model ()->asa ('ModelConversionBehavior');
        $this->assertEquals (
            $expected,
            $conversionBehavior->mapFields ($attrs, 'Contacts', true));
        $this->assertEquals (
            array_values (array_keys ($expected)),
            array_values ($conversionBehavior->mapFields (array_keys ($attrs), 'Contacts', false)));
    }

    public function testLeadToContact () {
        $lead = $this->x2Leads ('1');
        $leadAttrs = $lead->getAttributes ();
        $contact = $lead->convert ('Contacts');
        $contactAttrs = $contact->getAttributes ();
        $conversionBehavior = X2Leads::model ()->asa ('ModelConversionBehavior');
        $fieldMap = $conversionBehavior->getFieldMap ('Contacts');
        unset ($leadAttrs['id']);
        unset ($leadAttrs['nameId']);
        unset ($leadAttrs['createDate']);
        $mappedFields = $conversionBehavior->mapFields ($leadAttrs, 'Contacts', true);

        foreach ($mappedFields as $attr => $val) {
            if (isset ($contactAttrs[$attr])) {
                $this->assertEquals (
                    $val, $contactAttrs[$attr]);
            }
        }
    }

    public function testCheckConversionCompatibility () {
        $field = Fields::model ()->findByAttributes (
            array (
                'modelName' => 'X2Leads',
                'fieldName' => 'description',
            )
        );
        $field->type = 'boolean';
        $this->assertSaves ($field);
        Yii::app()->cache->flush ();
        X2Leads::model ()->resetFieldsPropertyCache ();

        // ensure that conversion fails due to field type mismatch
        $lead = $this->x2Leads ('1');
        $leadAttrs = $lead->getAttributes ();
        $this->assertFalse ($lead->checkConversionCompatibility ('Contacts'));
        $this->assertFalse ($lead->checkConversionCompatibility ('Opportunity'));

        self::restoreFields ();
        $this->assertTrue ($lead->checkConversionCompatibility ('Contacts'));
        $this->assertTrue ($lead->checkConversionCompatibility ('Opportunity'));

        // ensure that conversion fails due presence of extra field
        $extraField = new Fields;
        $extraField->setAttributes (array (
            'modelName' => 'X2Leads',
            'fieldName' => 'extraField',
            'type' => 'boolean',
            'attributeLabel' => 'Extra Field',
        ));
        $this->assertSaves ($extraField);
        Yii::app()->db->schema->refresh ();
        $lead->refreshMetaData ();
        Yii::app()->cache->flush ();
        X2Leads::model ()->resetFieldsPropertyCache ();
        $lead->c_extraField = true;

        $this->assertFalse ($lead->checkConversionCompatibility ('Contacts'));
        $this->assertFalse ($lead->checkConversionCompatibility ('Opportunity'));

        self::restoreFields ();
        $lead->refresh ();
        $this->assertTrue ($lead->checkConversionCompatibility ('Contacts'));
        $this->assertTrue ($lead->checkConversionCompatibility ('Opportunity'));
    }

    public function testForceConversion () {
        $field = Fields::model ()->findByAttributes (
            array (
                'modelName' => 'X2Leads',
                'fieldName' => 'description',
            )
        );
        $field->type = 'boolean';
        $this->assertSaves ($field);
        Yii::app()->cache->flush ();
        X2Leads::model ()->resetFieldsPropertyCache ();

        // ensure that conversion fails due to incompatibility 
        $lead = $this->x2Leads ('1');
        $leadAttrs = $lead->getAttributes ();

        $contact = $lead->convert ('Contacts');
        $this->assertFalse ($contact);

        // force conversion
        $contact = $lead->convert ('Contacts', true);
        $contactAttrs = $contact->getAttributes ();
        $conversionBehavior = X2Leads::model ()->asa ('ModelConversionBehavior');
        $fieldMap = $conversionBehavior->getFieldMap ('Contacts');
        unset ($leadAttrs['id']);
        unset ($leadAttrs['nameId']);
        unset ($leadAttrs['createDate']);
        $mappedFields = $conversionBehavior->mapFields ($leadAttrs, 'Contacts', true);
        foreach ($mappedFields as $attr => $val) {
            if (isset ($contactAttrs[$attr])) {
                $this->assertEquals (
                    $val, $contactAttrs[$attr]);
            }
        }

        self::restoreFields ();
    }

    public function testLeadToOpportunity () {
        $lead = $this->x2Leads ('1');
        $this->assertConversionCompatibility ($lead, 'Opportunity');
        $leadAttrs = $lead->getAttributes ();

        $contact = $lead->convert ('Opportunity');

        $targetAttrs = $contact->getAttributes ();
        $conversionBehavior = X2Leads::model ()->asa ('ModelConversionBehavior');
        $fieldMap = $conversionBehavior->getFieldMap ('Opportunity');
        unset ($leadAttrs['id']);
        unset ($leadAttrs['nameId']);
        unset ($leadAttrs['createDate']);
        $mappedFields = $conversionBehavior->mapFields ($leadAttrs, 'Opportunity', true);
        foreach ($mappedFields as $attr => $val) {
            if (isset ($targetAttrs[$attr])) {
                $this->assertEquals (
                    $val, $targetAttrs[$attr]);
            }
        }
    }

    private function assertConversionCompatibility ($record, $targetClass) {
        if (!$record->checkConversionCompatibility ($targetClass)) {
            $this->assertTrue (false);
        }
    }

}

?>
