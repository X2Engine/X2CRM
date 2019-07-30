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

/* @edition:pro */

/**
 * @file 1396564775-create-missing-action-timers.php
 *
 * Creates action timer records for all actions of a "timed" type.
 *
 * The idea behind this is to "fix" data of previous versions caused by a bug in
 * how action timer records are created for action records of the "timed"
 * varieties (time logged, call logged). The bug was that no timer record would
 * be created for the action (when one would need to be, for future reports that
 * operate on timer records and require this data). It would be created if the
 * user updated the action, but not upon initial creation.
 */

$createMissingActionTimers = function(){
            // No more than this number of records will be inserted at a time:
            $batchSize = 100;

            // Snapshot of X2Model::$associationModels as of April 3, 2014
            $associationModels = array(
                'media' => 'Media',
                'actions' => 'Actions',
                'calendar' => 'X2Calendar',
                'contacts' => 'Contacts',
                'accounts' => 'Accounts',
                'product' => 'Product',
                'products' => 'Product',
                'Campaign' => 'Campaign',
                'marketing' => 'Campaign',
                'quote' => 'Quote',
                'quotes' => 'Quote',
                'opportunities' => 'Opportunity',
                'social' => 'Social',
                'services' => 'Services',
                '' => ''
            );
            
            // Wrapper for accessing the above array in a safe manner
            $getAssociationModelName = function($n) use($associationModels) {
                return array_key_exists($n,$associationModels)?$associationModels[$n]:false;
            };
            
            // The CDbCommand for finding all actions in need of action records:
            $getterCommand = Yii::app()->db->createCommand()
                    ->from('x2_actions a')
                    ->leftJoin('x2_action_timers t', 't.actionId=a.id')
                    ->leftJoin('x2_users u', 'a.assignedTo=u.username')
                    ->where("
                        a.type IN ('time','call')
                        AND t.id IS NULL
                        AND u.id IS NOT NULL
                        AND a.completeDate-a.dueDate > 0");

            // Get a count of the number of records that need to be inserted:
            $getCount = clone $getterCommand;
            $actionCount = (integer) $getCount->select('COUNT(*)')->queryScalar();

            // Get all the necessary data:
            $getActionData = clone $getterCommand;
            $getActionData->select('
                        a.id as actionId,
                        a.dueDate as timestamp,
                        a.completeDate as endtime,
                        u.id as userId,
                        a.associationType as associationType,
                        a.associationId as associationId');
            $actionData = $getActionData->query();
            
            // Columns in the above query:
            $actionTimerColumns = array(
                'actionId',
                'timestamp',
                'endtime',
                'userId',
                'associationType',
                'associationId'
            );

            // Insert records
            $actionTimerRecords = array();
            $actionTimerParams = array();
            $rowCount = 0;

            // Fetch the rows one at a time to avoid exceeding PHP memory limit
            // (i.e. on systems with hundreds of thousands of action records)
            while($row = $actionData->read()) {
                // Parameters in the current record:
                $thisRow = array();
                foreach($actionTimerColumns as $col) {
                    // Parameter name:
                    $param = ":$col$rowCount";
                    $thisRow[] = $param;
                    // If it's the association type column, set it properly to
                    // a model name.
                    // 
                    // Otherwise, just set it.
                    $actionTimerParams[$param] = $col == 'associationType'
                        ? $getAssociationModelName($row[$col])
                        : $row[$col];
                }
                // Parameterized record:
                $actionTimerRecords[] = '('.implode(',',$thisRow).')';
                // Increment row count so that parameter names stay unique and
                // the batch limit can be respected:
                $rowCount++;
                
                // Insert all records or the current batch, whichever number
                // is reached first:
                if($rowCount == $actionCount || $rowCount == $batchSize){
                    $insert = 'INSERT INTO `x2_action_timers`
                        (`'.implode('`,`', $actionTimerColumns).'`)
                        VALUES
                        '.implode(',', $actionTimerRecords);
                    Yii::app()->db->createCommand($insert)
                            ->execute($actionTimerParams);
                    
                    // Reset parameters for another batch:
                    $actionTimerRecords = array();
                    $actionTimerParams = array();
                    $rowCount = 0;
                }
            }

        };

$createMissingActionTimers();

?>
