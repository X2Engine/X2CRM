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




Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.modules.workflow.models.*');
Yii::import ('application.components.*');
Yii::import ('application.components.x2flow.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

/**
 * @package application.tests.unit.components.x2flow.triggers
 */
class X2FlowSwitchTest extends X2FlowTestBase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.X2FlowSwitchTest'),
        'actions' => array ('Actions', '.WorkflowTests'),
        'workflows' => array ('Workflow', '.WorkflowTests'),
        'workflowStages' => array ('WorkflowStage', '.WorkflowTests'),
        'x2flow' => array ('X2Flow', '.X2FlowSwitchTest'),
    );

    /**
     * Tests the stage completed condition
     */
    public function testWorkflowConditionCompleted () {
        if (!self::$loadFixtures) return;

        $flow10 = $this->x2flow ('flow10');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow10, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has completed the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);

        $flow11 = $this->x2flow ('flow11');
        $retVal = $this->executeFlow ($flow11, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has not completed the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);
    }

    /**
     * Should work exactly like testWorkflowConditionCompleted except assertions are swapped
     */
    public function testWorkflowConditionNotCompleted () {
        if (!self::$loadFixtures) return;

        $flow = $this->x2flow ('flow12');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has completed the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);

        $flow = $this->x2flow ('flow13');
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has not completed the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);
    }

    public function testWorkflowConditionStarted () {
        if (!self::$loadFixtures) return;

        $flow = $this->x2flow ('flow14');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has started the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);

        $flow = $this->x2flow ('flow15');
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has not started the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);
    }


    public function testWorkflowConditionNotStarted () {
        if (!self::$loadFixtures) return;

        $flow = $this->x2flow ('flow16');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has started the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);

        $flow = $this->x2flow ('flow17');
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // this contact has not started the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);
    }

    public function testHasTagsCondition () {
        $flow = $this->x2flow ('flow18');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($this->contacts ('contact935')->getTags ());
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertFalse ($trace[1]['branch']);

        $this->contacts ('contact935')->clearTags ();
        $this->contacts ('contact935')->addTags (array ('test', 'test2'));
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertTrue ($trace[1]['branch']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        $flow = $this->x2flow ('flow19');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        $this->contacts ('contact935')->addTags (array ('test'));
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertTrue ($trace[2]['branch']);
        $this->contacts ('contact935')->removeTags (array ('test'));
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertFAlse ($trace[2]['branch']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
    }
}

?>
