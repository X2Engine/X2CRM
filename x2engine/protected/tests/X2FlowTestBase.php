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
Yii::import ('application.components.x2flow.actions.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

/**
 * @package application.tests.unit.components.x2flow.triggers
 */
class X2FlowTestBase extends X2DbTestCase {
    /**
     * Clears all trigger logs
     */
    public function clearLogs () {
        Yii::app()->db->createCommand ('delete from x2_trigger_logs where 1=1')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_trigger_logs
             where 1=1')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Returns trace of log for specified flow 
     * @return null|array
     */
    public function getTraceByFlowId ($flowId) {
        $log = TriggerLog::model()->findByAttributes (array (
            'flowId' => $flowId,
        ));
        if ($log) {
            $decodedLog = CJSON::decode ($log->triggerLog);
            return $decodedLog[1];
        } else {
            return $log;
        }
    }

    /**
     * Decodes flow from flow fixture record. 
     * @param X2FlowTestBase $context
     * @param string $rowAlias The row within the fixture to get
     * @param string $fixtureName Name of the fixture from which to get data
     * @return array decoded flow JSON string
     */
    public function getFlow ($context,$rowAlias=null,$fixtureName = 'x2flow') {
        if (!$rowAlias) {
            $aliases = array_keys ($context->{$fixtureName});
            $rowAlias = $aliases[0];
        }
        return CJSON::decode ($context->{$fixtureName}[$rowAlias]['flow']);
    }

    /**
     * Checks each entry in triggerLog looking for errors
     * @param array $trace One of the return value of executeFlow ()
     * @return bool true if an error was found in the log, false otherwise
     */
    public function checkTrace ($trace) {
        if (!$trace[0]) return false;
        $trace = $trace[1];
        while (true) {
            $complete = true;
            foreach ($trace as $action) {
                if ($action[0] === 'X2FlowSwitch') {
                    $trace = $action[2];
                    $complete = false;
                    break;
                }
                if (!$action[1][0]) return false;
            }
            if ($complete) break;
        }
        return true;
    }

    /**
     * Flattens the X2Flow trace, making it much easier to read programmatically. 
     * @param array $trace One of the return value of executeFlow ()
     * @return array flattened trace
     */
    public function flattenTrace ($trace) {
        if (!$trace[0]) return false;
        $flattenedTrace = array (array ('action' => 'start', 'error' => !$trace[0]));
        $trace = $trace[1];
        while (true) {
            $complete = true;
            foreach ($trace as $action) {
                if ($action[0] === 'X2FlowSwitch') {
                    array_push ($flattenedTrace, array (
                        'action' => $action[0],
                        'branch' => $action[1],
                    ));
                    $trace = $action[2];
                    $complete = false;
                    break;
                } else {
                    array_push ($flattenedTrace, array (
                        'action' => $action[0],
                        'error' => !$action[1][0],
                        'message' => $action[1][1],
                    ));
                }
            }
            if ($complete) break;
        }
        return $flattenedTrace;
    }

    /**
     * Returns array of decoded flows from fixture records
     * @param X2FlowTestBase $context A test case for which to obtain data
     * @param string $fixtureName The name of the fixture to pull from
     * @return <array of arrays> decoded flow JSON strings
     */
    public function getFlows ($context,$fixtureName = 'x2flow') {
         return array_map (function ($a) { return CJSON::decode ($a['flow']); }, $context->{$fixtureName});
    }

    /**
     * Executes a specified flow, ensuring that flows won't get triggered recursively
     * @param object $flow An X2Flow model
     */
    public function executeFlow ($flow, $params) {
        $X2Flow = new ReflectionClass ('X2Flow');
        $_triggerDepth = $X2Flow->getProperty ('_triggerDepth');
        $_triggerDepth->setAccessible (TRUE);
        $_triggerDepth->setValue (1);
        $fn = TestingAuxLib::setPublic ('X2Flow', 'executeFlow');
        $returnVal = $fn (array (&$flow, &$params));
        $_triggerDepth->setValue (0);
        return $returnVal;
    }

    public function assertGetInstances ($context, $subClass,$ignoreClassFiles) {
        $items = call_user_func("X2Flow{$subClass}::get{$subClass}Instances");
        $allFiles = scandir(
            $actionsPath = 
                Yii::getPathOfAlias('application.components.x2flow.'.strtolower($subClass).'s'));

        $classFiles = array();
        foreach($allFiles as $file) {
            $classPath = $actionsPath.DIRECTORY_SEPARATOR.$file;
            if(preg_match ("/\.php$/", $file) && is_file($classPath) && !is_dir($classPath)) {
                $classFiles[] = substr($file,0,-4);
            }
        }
        $classesLoaded = array();
        foreach($items as $itemObject) {
            $classesLoaded[] = get_class($itemObject);
        }
        $classesNotLoaded = array_diff($classFiles,$classesLoaded);
        $classesShouldBeLoaded = array_diff($classesNotLoaded,$ignoreClassFiles);
        $context->assertEquals(
            array(),$classesShouldBeLoaded,'Some classes were not instantiated.');
    }

}

?>
