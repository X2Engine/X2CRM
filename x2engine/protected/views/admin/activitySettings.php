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




Yii::app()->clientScript->registerScript('updateChatPollSlider', "

$('#settings-form input, #settings-form select, #settings-form textarea').change(function() {
	$('#save-button').addClass('highlight'); //css('background','yellow');
});

$('#chatPollTime').change(function() {
	$('#chatPollSlider').slider('value',$(this).val());
});
$('#timeout').change(function() {
	$('#timeoutSlider').slider('value',$(this).val());
});
", CClientScript::POS_READY);

?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Activity Feed Settings'); ?></h2></div>
<div class='admin-form-container'>
	<?php
	$form = $this->beginWidget('CActiveForm', array(
	'id' => 'settings-form',
	'enableAjaxValidation' => false,
	    ));
	?>

	<div class="form">
	<?php
	echo $form->labelEx($model, 'eventDeletionTime')."<br /><br />";
	echo $form->dropDownList($model,'eventDeletionTime',array(
        1=>Yii::t('app','{n} day',array('{n}'=>1)),
        7=>Yii::t('app','{n} days',array('{n}'=>7)),
        30=>Yii::t('app','{n} days',array('{n}'=>30)),
        0=>Yii::t('app','Do not delete')
    ));
	?><br>
	<?php echo Yii::t('admin', 'Set how long activity feed events should last before deletion.'); ?>
	<br><br>
	<?php echo Yii::t('admin', 'Events build up quickly as they are triggered very often and it is highly recommended that some form of clean up is enabled.  Default is 7 days.'); ?>
    </div>
    <div class="form">
	<?php
	echo $form->labelEx($model, 'eventDeletionTypes')."<br /><br />";
	echo $form->checkBoxList($model,'eventDeletionTypes',array(
        'feed'=>Events::model()->parseType('feed'),
        'comment'=>Events::model()->parseType('comment'),
        'record_create'=>Events::model()->parseType('record_create'),
        'record_deleted'=>Events::model()->parseType('record_deleted'),
        'action_reminder'=>Events::model()->parseType('action_reminder'),
        'action_complete'=>Events::model()->parseType('action_complete'),
        'calendar_event'=>Events::model()->parseType('calendar_event'),
        'case_escalated'=>Events::model()->parseType('case_escalated'),
        'email_opened'=>Events::model()->parseType('email_opened'),
        'email_sent'=>Events::model()->parseType('email_sent'),
        'notif'=>Events::model()->parseType('notif'),
        'weblead_create'=>Events::model()->parseType('weblead_create'),
        'web_activity'=>Events::model()->parseType('web_activity'),
        'workflow_complete'=>Events::model()->parseType('workflow_complete'),
        'workflow_revert'=>Events::model()->parseType('workflow_revert'),
        'workflow_start'=>Events::model()->parseType('workflow_start'),
    ));
	?>
	<br><br>
	<?php echo Yii::t('admin', 'Set which types of events will be deleted.  Note that only events will be deleted and not the records themselves, except in the case of Social Posts, which are events.'); ?><br />
	<br />
    <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n"; ?>
    <?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n"; ?>
    <?php $this->endWidget(); ?>
</div>
</div>
<style>
    div.form label{
        display:inline;
    }
</style>
