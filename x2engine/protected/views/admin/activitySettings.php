<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */

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
<div class="span-16">
    <h2><?php echo Yii::t('admin', 'Activity Feed Settings'); ?></h2>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
	'id' => 'settings-form',
	'enableAjaxValidation' => false,
	    ));
    ?>
    
    <div class="form">
	<?php
	echo $form->labelEx($model, 'eventDeletionTime')."<br /><br />";
	echo $form->dropDownList($model,'eventDeletionTime',array(1=>'1 day',7=>'7 days',30=>'30 days', 0=>'Do not delete'));
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
<style>
    div.form label{
        display:inline;
    }
</style>