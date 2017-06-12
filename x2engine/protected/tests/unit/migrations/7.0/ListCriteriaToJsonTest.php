<?php

/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

/**
 * Tests the 7.0 migration to update existing X2ListCriterion to store the "value"
 * of "list" and "notList" comparisons as JSON instead of a comma-separated list.
 */
class ListCriteriaToJsonMigrationTest extends X2DbTestCase {
    public $fixtures = array (
        'criteria' => array ('X2ListCriterion', '.MigrateCriteriaToJson'),
    );

    public function testMigrationScript() {
        $multiple = $this->criteria('testMultiple');
        $single = $this->criteria('testSingle');
        $none = $this->criteria('testNone');
        $excluded = $this->criteria('excluded');
        $this->runMigrationScript();
        $this->assertValueReencoded($multiple);
        $this->assertValueReencoded($single);
        $this->assertValueReencoded($none);
        $this->assertValueIntact($excluded);
    }

    /**
     * Asserts that the model's new JSON-encoded field can be properly unpacked and
     * reconstructed as the previous comma-separated value
     * @param X2ListCriterion $model List criteria model before migrating (comma-separated)
     */
    public function assertValueReencoded($model) {
        $modified = X2ListCriterion::model()->findByPk($model->id);
        $this->assertTrue($modified instanceof X2ListCriterion);
        $this->assertTrue(AuxLib::isJson($modified->value));
        $this->assertEquals($model->value, implode(',', CJSON::decode($modified->value)));
    }

    /**
     * Asserts that a model's value field was left intact
     */
    public function assertValueIntact($model) {
        $modified = X2ListCriterion::model()->findByPk($model->id);
        $this->assertTrue($modified instanceof X2ListCriterion);
        $this->assertEquals($model->value, $modified->value);
    }

    public function runMigrationScript() {
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
                'migrations/pending/1497047296-list-criteria-to-json.php';
        $return_var;
        $output = array();
        if (X2_TEST_DEBUG_LEVEL > 1) {
            print_r(exec($command, $return_var, $output));
        } else {
            exec($command, $return_var, $output);
        }
        X2_TEST_DEBUG_LEVEL > 1 && print_r($return_var);
        X2_TEST_DEBUG_LEVEL > 1 && print_r($output);
    }
}
