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
 * @package application.tests.unit.components.x2flow.triggers
 * @author Demitri Morgan <demitri@x2engine.com>
 * @author Derek Mueller <derek@x2engine.com>
 */
class X2FlowTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowTriggerTest'),
        'lists' => 'X2List',
        'listItems' => 'X2ListItem',
        'contacts' => 'Contacts',
    );


    public static function tearDownAfterClass () {
        $leadSourceDropdown = Dropdowns::model ()->findByPk (103);
        $leadSourceDropdown->multi = 0;
        if (!$leadSourceDropdown->save ()) throw new CException ('failed to restore dropdown');

        $field = Fields::model ()->findByAttributes (array (
            'modelName' => 'Contacts',
            'fieldName' => 'assignedTo',
        ));
        $field->linkType = 'multiple';
        if (!$field->save ()) throw new CException ('failed to restore link type');
        return parent::tearDownAfterClass ();
    }

    public function testGetTriggerInstances() {
        $this->assertGetInstances(
            $this, 'Trigger',array(
                'X2FlowTrigger',
                'X2FlowSwitch',
                'X2FlowSplitter',
                'MultiChildNode',
                'BaseTagTrigger',
                'BaseUserTrigger',
                'BaseWorkflowStageTrigger',
                'BaseWorkflowTrigger'
            ));
    }

    /**
     * Ensure that on list condition works properly
     */
    public function testOnListCondition () {
        $contact = $this->contacts ('testUser');
        $list = $this->lists ('testUser');
        $this->assertTrue ($list->hasRecord ($contact));

        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flowOnListCondition'), $params);

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal['trace']);

        // assert flow executed without errors since contact is on list
        $this->assertTrue ($this->checkTrace ($retVal['trace']));


        $contact = $this->contacts ('testAnyone');
        $this->assertFalse ($list->hasRecord ($contact));

        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flowOnListCondition'), $params);

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal['trace']);

        // assert flow executed with errors since contact is not on list
        $this->assertFalse ($this->checkTrace ($retVal['trace']));
    }

    public function testEqualityComparison () {
        /* 
        cases:
            1. array value, array subject (multi dropdown)
            2. scalar value, array subject (multi dropdown)

            3. array value, array subject (multi assignment dropdown)
            4. scalar value, array subject (multi assignmentdropdown)

            5. array value, scalar subject 
            6. scalar value, scalar subject
        */

        $leadSourceDropdown = Dropdowns::model ()->findByPk (103);
        $leadSourceDropdown->multi = 1;
        $leadSourceDropdown->save ();
        $field = Fields::model ()->findByAttributes (array (
            'modelName' => 'Contacts',
            'fieldName' => 'leadSource',
        ));
        $this->assertNotNull ($field);

        // case 1
        $this->assertTrue (X2FlowTrigger::evalComparison (
            CJSON::encode (array (
                'Google',
                'Facebook',
            )),
            '=',
            array (
                'Google',
                'Facebook',
            ),
            $field
        ));
        $this->assertFalse (X2FlowTrigger::evalComparison (
            CJSON::encode (array (
                'Google',
            )),
            '=',
            array (
                'Google',
                'Facebook',
            ),
            $field
        ));

        // case 2
        $this->assertTrue (X2FlowTrigger::evalComparison (
            'Google',
            '=',
            array (
                'Google',
            ),
            $field
        ));
        $this->assertFalse (X2FlowTrigger::evalComparison (
            'Google',
            '=',
            array (
                'Facebook',
            ),
            $field
        ));

        $field = Fields::model ()->findByAttributes (array (
            'modelName' => 'Contacts',
            'fieldName' => 'assignedTo',
        ));
        $this->assertNotNull ($field);
        $field->linkType = 'multiple';
        $this->assertSaves ($field);

        // case 3
        $this->assertTrue (X2FlowTrigger::evalComparison (
            implode (Fields::MULTI_ASSIGNMENT_DELIM, array (
                'chames',
                'admin',
            )),
            '=',
            array (
                'chames',
                'admin',
            ),
            $field
        ));
        $this->assertFalse (X2FlowTrigger::evalComparison (
            implode (Fields::MULTI_ASSIGNMENT_DELIM, array (
                'chames',
            )),
            '=',
            array (
                'chames',
                'admin',
            ),
            $field
        ));

        // case 4
        $this->assertTrue (X2FlowTrigger::evalComparison (
            'chames',
            '=',
            array (
                'chames',
            ),
            $field
        ));
        $this->assertFalse (X2FlowTrigger::evalComparison (
            'chames',
            '=',
            array (
                'admin',
            ),
            $field
        ));

        // case 5
        $this->assertTrue (X2FlowTrigger::evalComparison (
            'chames',
            '=',
            array (
                'chames',
            )
        ));
        $this->assertFalse (X2FlowTrigger::evalComparison (
            'chames',
            '=',
            array (
                'admin',
            )
        ));

        // case 6
        $this->assertTrue (X2FlowTrigger::evalComparison (
            'chames',
            '=',
            'chames'
        ));
        $this->assertFalse (X2FlowTrigger::evalComparison (
            'chames',
            '=',
            'admin'
        ));
    }

    
}

?>
