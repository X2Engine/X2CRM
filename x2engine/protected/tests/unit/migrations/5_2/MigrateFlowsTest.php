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






Yii::import ('application.models.*');

class MigrateFlows extends X2DbTestCase {

    // skipped since migration script tests aren't relevant after corresponding release
    protected static $skipAllTests = true;

    public $fixtures = array (
        'flows' => array ('X2Flow', '.MigrateFlows'),
    );

    /**
     * depth-first traversal ensuring that each id is as expected
     */
    private function assertIdsAreCorrect (array $data, &$expectedId) {
        $ids = array ();
        if (isset ($data['trigger'])) {
            $this->assertEquals ($expectedId++, $data['trigger']['id']);
            $items = $data['items'];
        } else {
            $items = $data; 
        }
        foreach ($items as $item) {
            $this->assertEquals ($expectedId++, $item['id']);
            $ids[] = $item['id'];
            if ($item['type'] === 'X2FlowSwitch') {
                if (isset ($item['trueBranch'])) {
                    $ids = array_merge (
                        $ids, $this->assertIdsAreCorrect ($item['trueBranch'], $expectedId));
                }
                if (isset ($item['falseBranch'])) {
                    $ids = array_merge (
                        $ids, $this->assertIdsAreCorrect ($item['falseBranch'], $expectedId));
                }
            }
        }
        return $ids;
    }

    private function removeIds (array &$data) {
        if (isset ($data['trigger'])) {
            unset ($data['trigger']['id']);
            $items = &$data['items'];
        } else {
            $items = &$data;
        }
        foreach ($items as &$item) {
            if (!isset ($item['type'])) break;
            unset ($item['id']);
            if ($item['type'] === 'X2FlowSwitch') {
                if (isset ($item['falseBranch'])) {
                    $this->removeIds ($item['falseBranch']);
                }
                if (isset ($item['trueBranch'])) {
                    $this->removeIds ($item['trueBranch']);
                }
            }
        }
    }

    private $expected6 = array (
        'id' => '12',
        'active' => '1',
        'name' => 'test',
        'description' => 'test',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => '{"version":"5.2","idCounter":8,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"test"}}},{"id":3,"type":"X2FlowSwitch","options":[],"trueBranch":[{"id":4,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"test"}}},{"id":5,"type":"X2FlowSwitch","options":[],"trueBranch":[{"id":6,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"test"}}}],"falseBranch":[{"id":7,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"test"}}},{"id":8,"type":"X2FlowSwitch","options":[],"trueBranch":[],"falseBranch":[]}]}],"falseBranch":[]}],"flowName":"test"}',
        'createDate' => '1439859426',
        'lastUpdated' => '1439859426',
    );

    /**
     * Runs migration script 
     * Asserts that pre-5.0 reports were correctly migrated to post-5.0 reports
     */
    public function testMigrationScript () {
        $oldFlows = array (
            $this->flows ('0')->getAttributes (), 
            $this->flows ('1')->getAttributes (),
            $this->flows ('2')->getAttributes (),
            $this->flows ('3')->getAttributes (),
            $this->flows ('4')->getAttributes (),
            $this->flows ('5')->getAttributes (),
            $this->flows ('6')->getAttributes (),
        );
        $oldBadFlows = array (
            'badFlowJSON' => $this->flows ('badFlowJSON')->getAttributes (),
            'badFlowMissingItems' => $this->flows ('badFlowMissingItems')->getAttributes (),
            'badFlowNoVersion' => $this->flows ('badFlowNoVersion')->getAttributes (),
            'badFlowMissingActionType' => 
                $this->flows ('badFlowMissingActionType')->getAttributes (),
        );

        // run migration script
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
            'migrations/5.2/1437098140-migrate-flows.php';
        $return_var;
        $output = array ();
        if (X2_TEST_DEBUG_LEVEL > 1) 
            X2_TEST_DEBUG_LEVEL > 1 && print_r (exec ($command, $return_var, $output));
        else 
            exec ($command, $return_var, $output);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($return_var);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($output);

        // ensure that ids were added to valid flows
        $flows = array (
            $this->flows ('0'),
            $this->flows ('1'),
            $this->flows ('2'),
            $this->flows ('3'),
            $this->flows ('4'),
            $this->flows ('5'),
            $this->flows ('6'),
        );

        $flow6 = $this->flows ('6');
        $flow6->refresh ();
        $commandBuilder = Yii::app()->db->getCommandBuilder ();
        $commandBuilder->createInsertCommand('x2_flows',$this->expected6)->execute();
        $expectedFlow6 = X2Flow::model ()->findByPk ($this->expected6['id']);
        unset ($expectedFlow6->description);
        $this->assertEquals (
            array_diff_key ($expectedFlow6->getAttributes (), array ('id' => 1)),
            array_diff_key ($flow6->getAttributes (), array ('id' => 1))
        );

        $i = 0;
        foreach ($flows as $flow) {
            $flow->refresh ();
            $flowData = $flow->getFlow ();
            $this->assertEquals ('5.2', $flowData['version']);
            $this->assertTrue (isset ($flowData['idCounter']));
            $expectedId = 1;
            $ids = $this->assertIdsAreCorrect ($flowData, $expectedId);
            $this->assertEquals (max ($ids), $flowData['idCounter']);

            // ensure that adding ids was the only change made
            $this->removeIds ($flowData);

            unset ($flowData['idCounter']);
            $flowData['version'] = '3.0.1';
            $flow->setFlow ($flowData);
            $this->assertEquals ($oldFlows[$i], $flow->getAttributes ());
            $i++;
        }

        // ensure that invalid flow wasn't touched
        $badFlow = $this->flows ('badFlowJSON');
        $badFlow->refresh ();
        $this->assertEquals ($oldBadFlows['badFlowJSON'], $badFlow->getAttributes ());

        // ensure that flow with missing items has version update and idCount
        $badFlow = $this->flows ('badFlowMissingItems');
        $badFlow->refresh ();
        $flowData = $badFlow->getFlow (true);
        $this->assertEquals ('5.2',$flowData['version']);
        $flowData['version'] = '3.0.1';
        $this->assertTrue (isset ($flowData['idCounter']));
        unset ($flowData['idCounter']);
        unset ($flowData['trigger']['id']);
        $badFlow->setFlow ($flowData);
        $this->assertEquals ($oldBadFlows['badFlowMissingItems'], $badFlow->getAttributes ());

        // ensure that flow without version wasn't touched
        $badFlow = $this->flows ('badFlowNoVersion');
        $badFlow->refresh ();
        $this->assertEquals ($oldBadFlows['badFlowNoVersion'], $badFlow->getAttributes ());

        // ensure that flow with missing action type has version update and idCount
        $badFlow = $this->flows ('badFlowMissingActionType');
        $badFlow->refresh ();
        $flowData = $badFlow->getFlow (true);
        $this->assertEquals ('5.2',$flowData['version']);
        $flowData['version'] = '3.0.1';
        $this->assertTrue (isset ($flowData['idCounter']));
        unset ($flowData['idCounter']);
        $badFlow->setFlow ($flowData);
        // ensure that some of the ids were set
        $this->assertNotEquals (
            $oldBadFlows['badFlowMissingActionType'], $badFlow->getAttributes ());
        $this->removeIds ($flowData);
        $badFlow->setFlow ($flowData);
        $this->assertEquals ($oldBadFlows['badFlowMissingActionType'], $badFlow->getAttributes ());
    }

}


?>
