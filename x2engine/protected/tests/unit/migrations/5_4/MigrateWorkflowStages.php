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




class MigrateWorkflowStages extends X2DbTestCase {
    
    protected static $skipAllTests = true;
    
    public $fixtures = array (
        'contacts' => array ('Contacts', '.WorkflowTests'),
        'actions' => array ('Actions', '.WorkflowMigrationTests'),
        'workflows' => array ('Workflow', '.WorkflowTests'),
        'workflowStages' => array ('WorkflowStage', '.WorkflowTests'),
    );
    
    public function testMigration(){
        $model = $this->contacts ('contact935');
        $workflowActions = Actions::model()->findAllByAttributes(array(
            'associationType'=>'contacts',
            'associationId' => $model->id,
            'type' => 'workflow',
        ));
        $this->assertEquals(4, count($workflowActions));
        $stage1 = $this->workflowStages('stage1');
        $stage1Action = Actions::model()->findByAttributes(array(
            'associationType'=>'contacts',
            'associationId' => $model->id,
            'type' => 'workflow',
            'workflowId' => 2,
            'stageNumber' => 1,
        ));
        $this->assertInstanceOf('Actions',$stage1Action);
        $this->assertEquals($stage1->workflowId, $stage1Action->workflowId);
        $this->assertEquals($stage1->stageNumber, $stage1Action->stageNumber);
        
        //Confirm that saving an action with non-existent stageNumber is okay
        $badAction = new Actions();
        $badAction->type = 'workflow';
        $badAction->workflowId = 2;
        $badAction->stageNumber = -1;
        $badAction->associationType = 'contacts';
        $badAction->associationId = $model->id;
        $this->assertSaves($badAction);
        
        //Confirm that saving an action with non-existent workflowId is okay
        $badAction->stageNumber = 1;
        $badAction->workflowId = -1;
        $this->assertSaves($badAction);
        
        //Confirm that saving an action with non-existent workflowId and stageNumber is okay
        $badAction->stageNumber = -1;
        $this->assertSaves($badAction);
        
        //Confirm that saving duplicate workflow stages is okay
        $badAction2 = new Actions();
        $badAction2->type = 'workflow';
        $badAction2->workflowId = -1;
        $badAction2->stageNumber = -1;
        $badAction2->associationType = 'contacts';
        $badAction2->associationId = $model->id;
        $this->assertSaves($badAction2);
        
        //Non-duplicate action to confirm deletion of actions with bad workflowId
        $badAction3 = new Actions();
        $badAction3->type = 'workflow';
        $badAction3->workflowId = -99;
        $badAction3->stageNumber = 1;
        $badAction3->associationType = 'contacts';
        $badAction3->associationId = $model->id;
        $this->assertSaves($badAction3);
        
        //Non-duplicate action to confirm deletion of actions with bad stageNumber
        $badAction4 = new Actions();
        $badAction4->type = 'workflow';
        $badAction4->workflowId = 2;
        $badAction4->stageNumber = -99;
        $badAction4->associationType = 'contacts';
        $badAction4->associationId = $model->id;
        $this->assertSaves($badAction4);
        
        $workflowActions = Actions::model()->findAllByAttributes(array(
            'associationType'=>'contacts',
            'associationId' => $model->id,
            'type' => 'workflow',
        ));
        $this->assertEquals(8, count($workflowActions));
        
        $this->runMigrationScript();
        
        $workflowActions = Actions::model()->findAllByAttributes(array(
            'associationType'=>'contacts',
            'associationId' => $model->id,
            'type' => 'workflow',
        ));
        $this->assertEquals(4, count($workflowActions));
        $this->assertNull(Actions::model()->findByPk($badAction->id));
        $this->assertNull(Actions::model()->findByPk($badAction2->id));
        $this->assertNull(Actions::model()->findByPk($badAction3->id));
        $this->assertNull(Actions::model()->findByPk($badAction4->id));
        
        $stage1ActionPostMigrate = Actions::model()->findByPK($stage1Action->id);
        $this->assertEquals($stage1->workflowId, $stage1ActionPostMigrate->workflowId);
        $this->assertEquals($stage1->id, $stage1ActionPostMigrate->stageNumber);
        
        //Fails new foreign key constraint
        $badAction5 = new Actions();
        $badAction5->type = 'workflow';
        $badAction5->workflowId = 2;
        $badAction5->stageNumber = -1;
        $badAction5->associationType = 'contacts';
        $badAction5->associationId = $model->id;
        try{
            $badAction5->save();
            $this->assertFalse(true);
        } catch (CDbException $e){
            $this->assertTrue(true);
        }
        
        //Fails new unique constraint
        $badAction6 = new Actions();
        $badAction6->type = 'workflow';
        $badAction6->workflowId = 2;
        $badAction6->stageNumber = 5;
        $badAction6->associationType = 'contacts';
        $badAction6->associationId = $model->id;
        try{
            $badAction6->save();
            $this->assertFalse(true);
        } catch (CDbException $e){
            $this->assertTrue(true);
        }
    }

    
    public function runMigrationScript() {
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
                'migrations/5.4/1448399817-migrate-workflow-stages.php';
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
    
    public function tearDown(){
        $cmd = Yii::app()->db->createCommand();
        
        $uniqueSql = 'ALTER TABLE x2_actions DROP INDEX workflow_action';
        $cmd->setText($uniqueSql);
        $cmd->execute();
        
        $fk1Sql = 'ALTER TABLE x2_actions DROP FOREIGN KEY fk_actions_workflow_id';
        $cmd->setText($fk1Sql);
        $cmd->execute();
        
        $fk2Sql = 'ALTER TABLE x2_actions DROP FOREIGN KEY fk_actions_workflow_stage_id';
        $cmd->setText($fk2Sql);
        $cmd->execute();
    }
    
}
