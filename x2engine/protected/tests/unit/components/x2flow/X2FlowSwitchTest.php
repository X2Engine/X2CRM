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

Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.modules.workflow.models.*');
Yii::import ('application.components.*');
Yii::import ('application.components.x2flow.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

/**
 * @package application.tests.unit.components.x2flow.triggers
 */
class X2FlowSwitchTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.WorkflowTests'),
        'actions' => array ('Actions', '.WorkflowTests'),
    );

    public static function referenceFixtures(){
        return array(
            'x2flow' => array ('X2Flow', '.WorkflowTests'),
            'workflows' => array ('Workflow', '.WorkflowTests'),
            'workflowStages' => array ('WorkflowStage', '.WorkflowTests'),
        );
    }

    /**
     * Tests the stage completed condition
     */
    public function testWorkflowConditionCompleted () {
        $flow10 = $this->x2flow ('flow10');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow10, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has completed the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);

        $flow11 = $this->x2flow ('flow11');
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow11, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not completed the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);
    }

    /**
     * Should work exactly like testWorkflowConditionCompleted except assertions are swapped
     */
    public function testWorkflowConditionNotCompleted () {
        $flow = $this->x2flow ('flow12');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has completed the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);

        $flow = $this->x2flow ('flow13');
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not completed the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);
    }

    public function testWorkflowConditionStarted () {
        $flow = $this->x2flow ('flow14');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has started the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);

        $flow = $this->x2flow ('flow15');
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not started the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);
    }


    public function testWorkflowConditionNotStarted () {
        $flow = $this->x2flow ('flow16');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has started the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);

        $flow = $this->x2flow ('flow17');
        $retVal = X2FlowTestingAuxLib::executeFlow ($flow, $params);
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($retVal['trace']));
        $trace = X2FlowTestingAuxLib::flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not started the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);
    }
}

?>
