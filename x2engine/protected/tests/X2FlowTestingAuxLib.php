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




class X2FlowTestingAuxLib {

    private static function _checkTrace ($tree) {
        while (true) {
            $complete = true;
            foreach ($tree as $action) {
                if ($action[0] === 'X2FlowSwitch') {
                    $tree = $action[2];
                    $complete = false;
                    break;
                } elseif ($action[0] === 'X2FlowSplitter') {
                    $success = self::_checkTrace ($action[2]);
                    if (!$success) return false;
                } elseif (!$action[1][0]) {
                    return false;
                }
            }
            if ($complete) break;
        }
        return true;
    }

    /**
     * Checks each entry in triggerLog looking for errors
     * @param array $trace One of the return value of executeFlow ()
     * @return bool false if an error was found in the log, true otherwise
     */
    public static function checkTrace ($trace) {
        // account for alternate trace format produced by TriggerLog::appendTriggerLog
        if (!is_array ($trace[0])) $trace = array ($trace);
        foreach ($trace as $tree) {
            if (!$tree[0]) {
                return false;
            }
            $tree = $tree[1];
            $success = self::_checkTrace ($tree);
            if (!$success) return false;
        }
        return true;
    }


    /**
     * Find flow action in flow and return it
     * @param string|int $id identifier for flow action
     * @return array|false
     */
    public static function findFlowItem (array $flow, $id) {
        return self::_findFlowItem ($flow['items'], $id);
    }

    private static function _findFlowItem ($items, $id) {
        foreach ($items as $item) {
            if ($item['id'] === $id || $item['type'] === $id) {
                return $item;
            } elseif ($item['type'] === 'X2FlowSwitch') {
                if (isset ($item['trueBranch'])) {
                    $ret = self::_findFlowItem ($item['trueBranch'], $id); 
                    if ($ret) return $ret;
                }
                if (isset ($item['falseBranch'])) {
                    $ret = self::_findFlowItem ($item['falseBranch'], $id); 
                    if ($ret) return $ret;
                }
            }
        }
        return false;
    }

    /**
     * Returns trace of log for specified flow 
     * @return null|array
     */
    public static function getTraceByFlowId ($flowId) {
        $log = TriggerLog::model()->findByAttributes (array (
            'flowId' => $flowId,
        ));
        if ($log) {
            $decodedLog = CJSON::decode ($log->triggerLog);
            $log = array_slice ($decodedLog, 1);
            return $log;
        } else {
            return $log;
        }
    }

    /**
     * Clears all trigger logs
     */
    public static function clearLogs (X2DbTestCase $that=null) {
        Yii::app()->db->createCommand ('delete from x2_trigger_logs where 1=1')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_trigger_logs
             where 1=1')
             ->queryScalar ();
        if ($that) $that->assertTrue ($count === '0');
    }
}

?>
