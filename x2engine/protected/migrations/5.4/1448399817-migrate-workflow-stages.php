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




$migrateWorkflowStages = function(){
    //Remove duplicate records
    $deleteDuplicateSql = 'DELETE a FROM x2_actions a, x2_actions b '
            . 'WHERE a.type = "workflow" AND b.type="workflow" '
            . 'AND a.workflowId = b.workflowId '
            . 'AND a.stageNumber = b.stageNumber '
            . 'AND a.associationType = b.associationType '
            . 'AND a.associationId = b.associationId '
            . 'AND a.id != b.id';
    $cmd = Yii::app()->db->createCommand();
    $cmd->setText($deleteDuplicateSql);
    $cmd->execute();
    
    //Remove records which refer to workflows or workflow stages that don't exist
    Yii::app()->db->createCommand()
            ->delete('x2_actions','workflowId IS NOT NULL AND workflowId NOT IN (SELECT id FROM x2_workflows)');
    
    $deleteBadStageNumberSql = 'DELETE a FROM x2_actions a '
            . 'WHERE a.stageNumber IS NOT NULL AND a.stageNumber NOT IN (SELECT b.stageNumber FROM '
            . 'x2_workflow_stages b WHERE a.workflowId = b.workflowId)';
    $cmd->setText($deleteBadStageNumberSql);
    $cmd->execute();
    
    //Convert stage number to stage id
    $updateStageNumberSql = 'UPDATE x2_actions a SET a.stageNumber = (SELECT b.id FROM '
            . 'x2_workflow_stages b WHERE b.workflowId = a.workflowId AND '
            . 'a.stageNumber = b.stageNumber) WHERE a.type = "workflow"';
    $cmd->setText($updateStageNumberSql);
    $cmd->execute();
    
    $fk1PrepSql = 'ALTER TABLE x2_actions MODIFY COLUMN workflowId INT';
    $cmd->setText($fk1PrepSql);
    $cmd->execute();
    //Apply constraints
    $fk1Sql = 'ALTER TABLE x2_actions ADD CONSTRAINT fk_actions_workflow_id '
            . 'FOREIGN KEY (workflowId) REFERENCES x2_workflows(id)';
    $cmd->setText($fk1Sql);
    $cmd->execute();
    
    $fk2PrepSql = 'ALTER TABLE x2_actions MODIFY COLUMN stageNumber INT';
    $cmd->setText($fk2PrepSql);
    $cmd->execute();
    $fk2Sql = 'ALTER TABLE x2_actions ADD CONSTRAINT fk_actions_workflow_stage_id '
            . 'FOREIGN KEY (stageNumber) REFERENCES x2_workflow_stages(id)';
    $cmd->setText($fk2Sql);
    $cmd->execute();
    
    $uniqueConstraintSql = 'ALTER TABLE x2_actions ADD CONSTRAINT workflow_action '
            . 'UNIQUE (associationType, associationId, workflowId, stageNumber)';
    $cmd->setText($uniqueConstraintSql);
    $cmd->execute();
    
    
};

$migrateWorkflowStages();