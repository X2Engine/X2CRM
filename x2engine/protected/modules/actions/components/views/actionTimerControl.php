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




    

?>
<div id="actionTimer">
    <?php
$form = $this->beginWidget('CActiveForm', array(
    'enableAjaxValidation' => false,
    'id' => 'actionTimerControl-form',
    'method' => 'post',
        ));
echo $form->dropDownList($timer, "type", Dropdowns::getItems(120), array('style' => 'width: 100%; margin-bottom: 5px;','class'=>'x2-minimal-select'));
echo '<span id="actionTimerDisplay">00:00:00</span>';
echo $form->hiddenField($timer, 'associationId', array('value' => $model->id));
echo $form->hiddenField($timer, 'associationType', array('value' => get_class($model)));
echo $form->hiddenField($timer, 'userId', array('value' => Yii::app()->user->id));
echo CHtml::link(( $started ? Yii::t('app', 'Stop') : Yii::t('app', 'Start')),'javascript:void(0);', array('class' => 'x2-minimal-button', 'id' => 'actionTimerStartButton'));
echo CHtml::link(Yii::t('app', 'Reset'),'javascript:void(0);', array('class' => 'x2-minimal-button', 'id' => 'timerReset'));
$this->endWidget();
?>
<div id="actionTimerControl-summary"><?php
echo Yii::t('app', 'Total time elapsed: ').'<br /><span id="actionTimerControl-total"><br /></span>';
?></div>
<?php
$action = new TimeFormModel;
$form = $this->beginWidget ('TimeActiveForm', array (
    'id' => 'actionTimerLog-form',
    'formModel' => $action,
    'htmlOptions' => array ('style' => ($hideForm ? 'display: none;' : '')),
));
echo $form->hiddenField(
    $action, 'timers', array('id' => 'timetrack-timers')); // Timers to be applied
echo $form->hiddenField($action,'timeSpent', array('id' => 'timetrack-timespent'));
echo $form->hiddenField($action,'dueDate',array('id'=>'timetrack-start'));
echo $form->hiddenField($action,'completeDate',array('id'=>'timetrack-end'));
echo $form->hiddenField($action,'associationType',array('value'=>$associationType));
echo $form->hiddenField($action,'associationId',array('value'=>$model->id));
echo $form->hiddenField($action,'assignedTo',array ('value' => Yii::app()->user->getName ()));
echo $form->textArea($action,'actionDescription',array('id'=>'timetrack-log-description'));

echo CHtml::submitButton(Yii::t('app','Submit'),array(
        'class'=>'x2-button highlight',
        'id' => 'actionTimerLog-submit'
    )
);
$this->endWidget();
?>
</div>
