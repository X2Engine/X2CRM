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






/**
 * Description of ActionTimerControl
 *
 * @author Raymond Colebaugh <raymond@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 */
class ActionTimerControl extends X2Widget{
    private $_timer;
    public $model;

    public $associationType;

    private $_hideForm = false;
    
    public function init() {
        $seconds = time() - $this->timer->timestamp;
        $totalSeconds = ActionTimer::getTimeSpent($this->model->id,get_class($this->model));
        Yii::app()->clientScript->registerScript('actionTimerVars', '
                if (typeof x2.actionTimer == "undefined")
                    x2.actionTimer = {};
                x2.actionTimer.actionUrl = '. json_encode(Yii::app()->controller->createUrl('/actions/actions/timerControl')) .';
                var seconds = '. ($this->getTimer()->isNewRecord ? 0 : $seconds) .';
                var totalSeconds = '.$totalSeconds.';
                x2.actionTimer.elapsed = {hours: 0, minutes: 0, seconds: seconds};
                x2.actionTimer.totalElapsed = {hours:0,minutes:0,seconds:totalSeconds+seconds};
                x2.actionTimer.initialElapsed = {hours:0,minutes:0,seconds:totalSeconds+seconds};
                x2.actionTimer.normalizeTime();
                x2.actionTimer.oldTitle = document.title;
                x2.actionTimer.displayInTitle = '.json_encode((integer) !Yii::app()->params->profile->disableTimeInTitle).';
                x2.actionTimer.text = '.json_encode(array_merge(array(
                    'Hours' => Yii::t('app','Hours'),
                    'Minutes' => Yii::t('app','Minutes'),
                    'Pause' => Yii::t('app','Pause'),
                    'Start' => Yii::t('app','Start'),
                    'Stop' => Yii::t('app','Stop'),
                ),Dropdowns::getItems(120))).';
                // True if started, false if not:
                x2.actionTimer.getElement("#actionTimerStartButton").data("status", '. (!$this->timer->isNewRecord ? "true" : "false") .');
                x2.actionTimer.getElement("#actionTimerControl-total").text(x2.actionTimer.formatTotal());
                x2.actionTimer.publisherAction = '.json_encode(Yii::app()->controller->createUrl('/actions/actions/publisherCreate')).';
                // Finally, now that everything is declared, start (if timer already started)
                if(x2.actionTimer.getElement("#actionTimerStartButton").data("status") == true) {
                    x2.actionTimer.start();
                }
                ',CClientScript::POS_READY);
        if($totalSeconds + $seconds == 0) {
            $this->_hideForm = true;
            //Yii::app()->clientScript->registerCss('actionTimer-hidden','#actionTimerLog-form {display: none;}');
        }
        Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl . '/js/actionTimer.js');
        //Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/actionTimer.css');
        parent::init();
    }
    
    public function getTimer() {
        if (!isset($this->_timer)) {
            $this->_timer = ActionTimer::setup(false, array(
                        'associationId' => $this->model->id,
                        'associationType' => get_class($this->model),
                        'userId' => Yii::app()->getSuId()
            ));
        }
        return $this->_timer;
    }
    
    public function setTimer(ActionTimer $value) {
        $this->_timer = $value;
    }
    
    public function run() {
        $this->render('actionTimerControl', array(
            'model' => $this->model,
            'timer'=>$this->timer,
            'started'=>!$this->timer->isNewRecord,
            'associationType' => $this->model->module,
            'hideForm' => $this->_hideForm,
        ));
    }
}
