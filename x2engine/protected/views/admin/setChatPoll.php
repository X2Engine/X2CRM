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
$(function() {
	$('#chatPollTime').change(function() {
		$('#chatPollSlider').slider('value',$(this).val());
	});
});
",CClientScript::POS_HEAD);
?>
<div class="span-12">
<h2><?php echo Yii::t('admin','Set Chat Poll Rate'); ?></h2>
<?php echo Yii::t('admin','Set the duration between chat update requests in miliseconds.'); ?>
<br /><br />
<?php echo Yii::t('admin','Decreasing this number allows for more instantaneous chatting, but generates more server requests, so adjust it to taste. The default value is 2000 (2 seconds).'); ?>
<br /><br />
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
		'id'=>'timeout-form',
		'enableAjaxValidation'=>false,
	));
?>

<?php echo $form->labelEx($admin,'chatPollTime'); ?>
<?php

$this->widget('zii.widgets.jui.CJuiSlider', array(
	'value'=>$admin->chatPollTime,
	// additional javascript options for the slider plugin
	'options'=>array(
		'min'=>100,
		'max'=>10000,
		'step'=>100,
		'change'=>"js:function(event,ui) {
			$('#chatPollTime').val(ui.value);
		}",
		'slide'=>"js:function(event,ui) {
			$('#chatPollTime').val(ui.value);
		}",
	),
	'htmlOptions'=>array(
		'style'=>'width:440px;margin:10px 0;',
		'id'=>'chatPollSlider'
	),
));

echo $form->textField($admin,'chatPollTime',array('id'=>'chatPollTime')); ?>

<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?></div>
</div>