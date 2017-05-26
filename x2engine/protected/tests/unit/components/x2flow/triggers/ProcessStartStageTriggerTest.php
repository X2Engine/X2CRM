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
 * @package application.tests.unit.components.x2flow.triggers
 */
class WorkflowStartStageTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.WorkflowTests'),
        'actions' => array ('Actions', '.WorkflowTests'),
    );

    public static function referenceFixtures(){
        return array(
            'x2flow' => array ('X2Flow', '.WorkflowStartStageTriggerTest'),
            'workflows' => array ('Workflow', '.WorkflowTests'),
            'workflowStages' => array ('WorkflowStage', '.WorkflowTests'),
            'roleToWorkflow' => array (':x2_role_to_workflow', '.WorkflowTests'),
        );
    }
    
    public function setUp(){
        TestingAuxLib::loadControllerMock ();
        return parent::setUp();
    }
    
    public function tearDown(){
        TestingAuxLib::restoreController();
        parent::tearDown();
    }

    /**
     * Trigger the flow by completing a workflow stage on the contact. Assert that the flow 
     * executes without errors.
     */
    public function testFlowExecution () {
        $this->clearLogs ();
        $workflow = $this->workflows ('workflow2'); 
        $model = $this->contacts ('contact935');
        // complete stage 4, autostarting stage 5. This should trigger the flow
        $retVal = Workflow::completeStage (
            $workflow->id, 4, $model, 'test comment');
        $newLog = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertTrue ($this->checkTrace ($newLog));

        // complete stage 5. This shouldn't trigger the flow since the flow checks that stage
        // 4 was completed
        $this->clearLogs ();
        $retVal = Workflow::completeStage (
            $workflow->id, 5, $model, '');
        $newLog = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertFalse ($this->checkTrace ($newLog));
    }

}

?>
