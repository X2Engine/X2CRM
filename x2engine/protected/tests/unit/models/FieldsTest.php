<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

Yii::import('application.modules.bugReports.models.*');

/**
 * Test case for the {@link Fields} model class.
 * 
 * @package X2CRM.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class FieldsTest extends X2TestCase {

    private $_testColumnName;
    private $_testTableName;

    /**
     * This defines the model to which columns will be added.
     * @return string 
     */
    public function getTestModelName() {
        Yii::import('application.modules.contacts.models.*');
        return 'Contacts';
    }

    public function getTestColumnName() {
        if(!isset($this->_testColumnName)) {
            $this->_testColumnName = 'testColumn_'.time();
        }
        return $this->_testColumnName;
    }

    public function getTestTableName() {
        if(!isset($this->_testTableName)) {
            $this->_testTableName = X2Model::model($this->getTestModelName())->tableName();
        }
        return $this->_testTableName;
    }

    public function tearDownTestColumn() {
        $sql = 'ALTER TABLE `'.$this->getTestTableName().'` DROP COLUMN `'.$this->getTestColumnName().'`';
        try {
            Yii::app()->db->createCommand($sql)->execute();
        } catch(CDbException $e) {
            // Do nothing; column doesn't exist, so there's nothing left to do
        }
    }

	public function testStrToNumeric() {
		$cur =  Yii::app()->locale->getCurrencySymbol(Yii::app()->params->admin->currency);
		$input = " $cur 123.45 % ";
		$this->assertEquals(123.45,Fields::strToNumeric($input,'currency'));
		$this->assertEquals(123,Fields::strToNumeric($input,'int'));
		$this->assertEquals(123.45,Fields::strToNumeric($input,'float'));
		$this->assertEquals(123.45,Fields::strToNumeric($input,'percentage'));
		$this->assertEquals(0,Fields::strToNumeric(null,'float'));
		$type = 'notanint';
		$value = Fields::strToNumeric($input, $type);
		$this->assertEquals(123.45, $value);



		// Randumb string comes back as itself
		$input = 'cockadoodledoo';
		$value = Fields::strToNumeric($input,'int');
		$this->assertEquals($input,$value);

		// Null always evaluates to zero
		$value = Fields::strToNumeric('');
		$this->assertEquals(0,$value);

		// Parsing of parenthesized notation for negative currency values
		$value = Fields::strToNumeric('($45.82)','currency');
		$this->assertEquals(-45.82,$value);

		// Negative percentage values:
		$value = Fields::strToNumeric('-12.5%','percentage');
		$this->assertEquals(-12.5,$value);

		// Comma notation for thousands:
		$value = Fields::strToNumeric('$9,888.77','currency');
		$this->assertEquals(9888.77,$value);
		// Comma plus parentheses notation
		$value = Fields::strToNumeric('($9,888.77)','currency');
		$this->assertEquals(-9888.77,$value);
		// Comma and minus sign notation:
		$value = Fields::strToNumeric('-$9,888.77','currency');
		$this->assertEquals(-9888.77,$value);
		// Rounded to integer, over 10^6:
		$value = Fields::strToNumeric('$10,000,000','currency');
		$this->assertEquals(10000000,$value);
		// ...negative
		$value = Fields::strToNumeric('($10,000,000)','currency');
		$this->assertEquals(-10000000,$value);
		// ...with decimal places
		$value = Fields::strToNumeric('($10,000,000.01)','currency');
		$this->assertEquals(-10000000.01,$value);

		// Multibyte support:
		$curSym = Yii::app()->locale->getCurrencySymbol('INR');
		$value = Fields::strToNumeric("($curSym"."9,888.77)",'currency',$curSym);
		$this->assertEquals(-9888.77,$value,'Failed asserting proper conversion of multibyte strings to numbers.');
	}

    public function testCreateAndDropColumn() {
        $field = new Fields('test');
        $field->modelName = $this->getTestModelName();
        $field->fieldName = $this->getTestColumnName();
        $field->type = 'varchar';
        $field->custom = 0;
        $tableName = X2Model::model($field->modelName)->tableName();
        try {
            $field->createColumn();
        } catch(Exception $e) {
            $this->tearDownTestColumn();
            throw $e;
        }
        Yii::app()->db->schema->refresh();
        $columnsAfterAdd = Yii::app()->db->schema->tables[$tableName]->columnNames;
        try {
            $field->dropColumn();
        } catch(Exception $e) {
            $this->tearDownTestColumn();
            throw $e;
        }
        Yii::app()->db->schema->refresh();
        $columnsAfterDrop = Yii::app()->db->schema->tables[$tableName]->columnNames;
        $this->tearDownTestColumn();
        $this->assertTrue(in_array($field->fieldName,$columnsAfterAdd),"Column {$field->fieldName} was not created.");
        $this->assertTrue(!in_array($field->fieldName,$columnsAfterDrop),"Column {$field->fieldName} was not dropped.");
    }

    public function testDropColumn() {
        $field = new Fields;
        $field = new Fields('test');
        $field->modelName = $this->getTestModelName();
        $field->fieldName = $this->getTestColumnName();
        $field->type = 'varchar';
        $field->custom = 0;
        $field->createColumn();
    }

    public function testNonReserved() {
        $field = new Fields('test');
        $field->fieldName = 'SCHEMA';
        $field->validate(array('fieldName'));
        $this->assertTrue($field->hasErrors('fieldName'));
    }

    public function testUniqueFieldName() {
        $field = new Fields('test');
        // These two fields should always exist:
        $field->custom = 0;
        $field->modelName = $this->getTestModelName();
        $field->fieldName = 'firstName';
        $field->validate(array('fieldName'));
        $this->assertTrue($field->hasErrors('fieldName'),'No validation error for a field with a duplicate name: '.$field->fieldName);
    }

    public function testValidDefault() {
        $field = new Fields;
        $field->fieldName = 'testName';
        $field->type = 'email';
        $field->defaultValue = 'not an email address';
        $field->validate(array('defaultValue'));
        $this->assertTrue($field->hasErrors('defaultValue'));
        // Now test (this is more a feature of AmorphousModel than of Fields)
        // that the "required" validator is disabled
        $field->clearErrors();
        $field->defaultValue = '';
        $field->required = 1;
        $field->validate(array('defaultValue'));
        $this->assertFalse($field->hasErrors('defaultValue'));
    }

}

?>
