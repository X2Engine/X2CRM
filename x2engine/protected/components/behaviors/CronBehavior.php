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






Yii::import('application.modules.actions.models.*');
Yii::import('application.models.CronEvent');
Yii::import('application.models.embedded.CronSchedule');

/**
 * Run scheduled tasks, including emailing.
 * 
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CronBehavior extends CBehavior {

    public $isConsole = true;

    public function log($message, $level = 'trace', $category = 'application.automation.cron') {
        Yii::log($message, $level, $category);
    }

    public function runCron() {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->log('Cron request made from ' . $_SERVER['REMOTE_ADDR']);
        } else {
            $this->log(sprintf(
                'Cron command executing with uid/gid %d:%d', posix_geteuid(), posix_getegid()));
        }

        $t0 = time(); // monitor how long this is taking

        $events = CronEvent::model ()->getPastDueCronEvents ();
        $timeout = Yii::app()->settings->batchTimeout;

        $n_events = 0;
        foreach ($events as &$event) {
            if (time() - $t0 > $timeout) { // stop after X seconds, we don't want to time out
                $this->log("Time limit of $timeout seconds reached after processing $n_events " .
                        "X2Flow events.", 'error');
                return;
            }
            $n_events++;
            $data = CJSON::decode($event->data); // attempt to decode JSON data,
            if ($data === false) { // delete and skip event if it's corrupt
                $this->log("Encountered a corrupt event record that will be deleted (invalid " .
                        "JSON): id={$event->id}, data={$event->data}", 'error');
                $event->delete();
                continue;
            }

            if ($event->type === 'recurringSchedule') {
                $params = isset ($data['params']) ? $data['params'] : array ();
                X2Flow::trigger ('PeriodicTrigger', $params);
            } elseif ($event->type === 'x2flow') { // wait actions
                if (isset($data['flowId'], $data['flowPath'], $data['params'])) {
                    $flow = CActiveRecord::model('X2Flow')->findByPk($data['flowId']);
                    if ($flow !== null) {

                        // reload the model into the params array
                        if (isset($data['modelId'], $data['modelClass'])) {
                            $data['params']['model'] = CActiveRecord::model($data['modelClass'])->
                                findByPk($data['modelId']);
                            if (is_null($data['params']['model'])) {
                                $event->delete();
                                continue;
                            }
                        }

                        $triggerLogId = null;
                        if (isset($data['triggerLogId'])) {
                            $triggerLogId = $data['triggerLogId'];
                            $flowRetArr = X2Flow::resumeFlowExecution(
                                $flow, $data['params'], $data['flowPath'], $triggerLogId);
                            $flowTrace = $flowRetArr['trace'];
                            TriggerLog::appendTriggerLog($triggerLogId, array($flowTrace));
                        } else {
                            $flowTrace = X2Flow::resumeFlowExecution(
                                $flow, $data['params'], $data['flowPath']);
                        }

                        if (!$event->recurring) {
                            $event->delete();    // it was a one-time thing, we're done
                        } else {
                            $event->update(array(
                                'lastExecution' => time(),
                                'time' => $event->time + $event->interval,
                            ));
                        }
                    } else {
                        $event->delete();    // flow has been deleted or something
                    }
                } else {
                    $event->delete();    // event is missing parameters
                }
            } else if ($event->type == 'activity_report') {
                $filters = json_decode($data['filters'], true);
                $userId = $data['userId'];
                $limit = $data['limit'];
                $range = $data['range'];
                $deleteKey = $data['deleteKey'];
                $message = Events::generateFeedEmail(
                    $filters, $userId, $range, $limit, $event->id, $deleteKey);
                $eml = new InlineEmail;
                $emailFrom = Credentials::model()->getDefaultUserAccount(
                    Credentials::$sysUseId['systemNotificationEmail'], 'email');
                if ($emailFrom == Credentials::LEGACY_ID) {
                    $eml->from = array(
                        'name' => 'X2Engine Email Capture',
                        'address' => Yii::app()->settings->emailFromAddr,
                    );
                } else {
                    $eml->credId = $emailFrom;
                }

                $mail = $eml->mailer;
                $mail->FromName = 'X2Engine';
                $mail->Subject = 'X2Engine Activity Feed Report';
                $mail->MsgHTML($message);
                $profRecord = Profile::model()->findByPk($userId);
                if (isset($profRecord)) {
                    $mail->addAddress($profRecord->emailAddress);
                    try{
                        $mail->send();
                    } catch (Exception $e){
                        $this->log("Failed to send Activity Feed Report email for user: {$profRecord->username}");
                    }
                    $event->recur();
                } else {
                    // Corrupt event
                    $event->delete();
                }
            }
        }
        $t1 = time();
        $t_events = $t1 - $t0;
        if ($n_events > 0)
            $this->log("Processed $n_events cron events.");

        $criteria = new CDbCriteria();
        // $criteria->addInCondition(array(
        $actionOverdueFlows = CActiveRecord::model('X2Flow')->
            findAllByAttributes(array('active' => true, 'triggerType' => 'ActionOverdueTrigger'));

        $n_overdue = 0;
        foreach ($actionOverdueFlows as &$flow) {
            $t1_0 = time();
            if ($t1_0 - $t0 > $timeout) {
                $this->log(
                    "Time limit of $timeout seconds reached after processing $n_events cron " .
                    "events ($t_events seconds) and $n_overdue overdue action X2Flow triggers (" .
                    ($t1_0 - $t1) . " seconds)");
                return;
            }
            $flow = CJSON::decode($flow->flow);
            if ($flow === false || !isset($flow['trigger']['type'], $flow['trigger']['options']))
                continue;
            $options = &$flow['trigger']['options'];

            if (!isset($options['duration']) || !isset($options['duration']['value']))
                continue;

            $time = X2FlowItem::calculateTimeOffset((float) $options['duration']['value'], 'secs');

            if ($time === false)
                continue;

            $n_overdue++;
            $time = time() - $time;

            $criteria = new CDbCriteria;
            $criteria->addCondition('flowTriggered=0 AND complete != "Yes" AND dueDate < ' . $time);
            $criteria->limit = 100;

            $actions = CActiveRecord::model('Actions')->findAll($criteria);

            foreach ($actions as &$action) {
                if (time() - $t0 > $timeout)    // stop; we don't want to time out
                    return;
                $action->flowTriggered = 1;
                $action->update(array('flowTriggered'));
                X2Flow::trigger('ActionOverdueTrigger', array(
                    'model' => $action,
                    'duration' => time()-$action->dueDate,
                ));
            }
        }
        if ($n_overdue > 0) {
            $this->log("Processed $n_overdue overdue action X2Flow triggers in " . (time() - $t1) .
                    " seconds.");
        }
        // Finally, send unset campaign email using any remaining time:
        if (!$this->isConsole) {
            Yii::import('application.modules.marketing.components.CampaignMailingBehavior');
            $results = CampaignMailingBehavior::sendMail(null, $t0);
            if (isset($results['messages'])) {
                if (is_array($results['messages'])) {
                    $this->log(
                        "Ran marketing batch emailer. " . implode("\n", $results['messages']));
                }
            }
        }
    }

}

?>
