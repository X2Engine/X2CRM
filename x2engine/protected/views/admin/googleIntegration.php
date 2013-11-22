<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
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

		<?php // echo $form->labelEx($model,'googleAPIKey'); ?>
		<?php // echo $form->textField($model,'googleAPIKey', array('size'=>75)); ?>

		<br><br>

		<?php echo Yii::t('admin','Google integration allows users to link their calendars on x2crm with Google Calendars as well as log in with their Google IDs.'); ?>
		<br><br>

		<?php echo Yii::t('admin', 'You will need to create a google app in order to use google integration.'); ?>
		<?php echo Yii::t('admin','You can find your Client ID, Client Secret, and API Key on your '); ?>
		<?php echo CHtml::link(Yii::t('admin', 'google console'), 'http://code.google.com/apis/console'); ?>.
		<?php echo Yii::t('admin', 'Also, the following links need to be added to your app\'s Authorized Redirect URIs:'); ?>
		<br><br>
<textarea style="padding:5px; height:60px;line-height:20px;width:600px;" disabled="disabled">
<?php echo (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('/calendar/calendar/syncActionsToGoogleCalendar'); ?>

<?php echo (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('/site/googleLogin'); ?>

<?php echo (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('/site/upload'); ?>
</textarea>
        <?php echo Yii::t('admin', 'Additionally, the following link needs to be added to your app\'s Authorized Javascript Origins'); ?>
		<br><br>
<textarea style="padding:5px; height:20px;line-height:20px;width:600px;" disabled="disabled">
<?php echo (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];?>
</textarea>
		<br><br>
        <hr />
        <span class="mock-x2-form-label"><?php echo Yii::t('admin','Google Analytics (optional)'); ?></span><br />
            <?php
        foreach(array('public', 'internal') as $type){
            echo $form->labelEx($model, "gaTracking_$type");
            echo $form->textField($model, "gaTracking_$type", array('id' => "gaTracking_$type"));
        }
        echo '<br />';
        echo Yii::t('admin', 'Enter property IDs to enable Google Analytics tracking. The public ID will be used on publicly-accessible web lead and service case forms. The internal one will be used within X2CRM, for tracking the activity of authenticated users.');
        ?>
    <br /><br /><hr />
	<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button'))."\n";?>
	<?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?>
</div>
</div>