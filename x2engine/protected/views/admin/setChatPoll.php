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