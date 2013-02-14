<?php
/*********************************************************************************
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
 ********************************************************************************/

Yii::app()->clientScript->registerScript('updateChatPollSlider',"

$('#settings-form input, #settings-form select, #settings-form textarea').change(function() {
	$('#save-button').addClass('highlight'); //css('background','yellow');
});

$('#chatPollTime').change(function() {
	$('#chatPollSlider').slider('value',$(this).val());
});
$('#timeout').change(function() {
	$('#timeoutSlider').slider('value',$(this).val());
});
",CClientScript::POS_READY);
?>
<div class="page-title"><h2><?php echo Yii::t('admin','Google Integration'); ?></h2></div>
<div class="form">
<div style="width:500px;">
<?php
$form=$this->beginWidget('CActiveForm', array(
		'id'=>'settings-form',
		'enableAjaxValidation'=>false,
	));
?>
		<?php echo $form->checkbox($model, 'googleIntegration'); ?>
		<?php echo $form->labelEx($model,'googleIntegration',array('style'=>'display:inline;')); ?>
		<br><br>
		<?php echo $form->labelEx($model,'googleClientId'); ?>
		<?php echo $form->textField($model,'googleClientId', array('size'=>75)); ?>
		
		<?php echo $form->labelEx($model,'googleClientSecret'); ?>
		<?php echo $form->textField($model,'googleClientSecret', array('size'=>75)); ?>

		<?php echo $form->labelEx($model,'googleAPIKey'); ?>
		<?php echo $form->textField($model,'googleAPIKey', array('size'=>75)); ?>
		
		<br><br>
		
		<?php echo Yii::t('admin','Google integration allows users to link their calendars on x2crm with Google Calendars as well as log in with their Google IDs.'); ?>
		<br><br>

		<?php echo Yii::t('admin', 'You will need to create a google app in order to use google integration.'); ?>
		<?php echo Yii::t('admin','You can find your Client ID, Client Secret, and API Key on your '); ?>
		<?php echo CHtml::link(Yii::t('admin', 'google console'), 'http://code.google.com/apis/console'); ?>.
		<?php echo Yii::t('admin', 'Also, the following link needs to be added to your app\'s Redirect URIs:'); ?>
		<br><br>
<textarea style="padding:5px; height:40px;line-height:20px;width:400px;" disabled="disabled">
<?php echo (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('/calendar/calendar/syncActionsToGoogleCalendar'); ?>

<?php echo (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('/site/googleLogin'); ?>
</textarea>
		<br><br>
	<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button'))."\n";?>
	<?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?>
</div>
</div>