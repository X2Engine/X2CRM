<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

class X2FlowTestingAuxLib {

    /**
     * Checks each entry in triggerLog looking for errors
     * @param array $trace One of the return value of executeFlow ()
     * @return bool false if an error was found in the log, true otherwise
     */
    public static function checkTrace ($trace) {
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
     * Returns trace of log for specified flow 
     * @return null|array
     */
    public static function getTraceByFlowId ($flowId) {
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
