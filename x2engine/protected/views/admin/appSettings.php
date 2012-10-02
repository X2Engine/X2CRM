<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
    <h2><?php echo Yii::t('admin', 'General Settings'); ?></h2>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
	'id' => 'settings-form',
	'enableAjaxValidation' => false,
	    ));
    ?>
    
    <div class="form">
	<?php
	echo $form->labelEx($model, 'chatPollTime');
	$this->widget('zii.widgets.jui.CJuiSlider', array(
	    'value' => $model->chatPollTime,
	    // additional javascript options for the slider plugin
	    'options' => array(
		'min' => 100,
		'max' => 10000,
		'step' => 100,
		'change' => "js:function(event,ui) {
					$('#chatPollTime').val(ui.value);
					$('#chatPollTime').change();
				}",
		'slide' => "js:function(event,ui) {
					$('#chatPollTime').val(ui.value);
				}",
	    ),
	    'htmlOptions' => array(
		'style' => 'width:340px;margin:10px 0;',
		'id' => 'chatPollSlider'
	    ),
	));

	echo $form->textField($model, 'chatPollTime', array('id' => 'chatPollTime'));
	?><br>
	<?php echo Yii::t('admin', 'Set the duration between chat update requests in milliseconds.'); ?>
	<br><br>
	<?php echo Yii::t('admin', 'Decreasing this number allows for more instantaneous chatting, but generates more server requests, so adjust it to taste. The default value is 2000 (2 seconds).'); ?>
    </div>
    <div class="form">
	<?php
	echo $form->labelEx($model, 'timeout');
	$this->widget('zii.widgets.jui.CJuiSlider', array(
	    'value' => $model->timeout,
	    // additional javascript options for the slider plugin
	    'options' => array(
		'min' => 5,
		'max' => 1440,
		'step' => 5,
		'change' => "js:function(event,ui) {
				$('#timeout').val(ui.value);
				$('#timeout').change();
			}",
		'slide' => "js:function(event,ui) {
				$('#timeout').val(ui.value);
			}",
	    ),
	    'htmlOptions' => array(
		'style' => 'width:340px;margin:10px 0;',
		'id' => 'timeoutSlider'
	    ),
	));

	echo $form->textField($model, 'timeout', array('id' => 'timeout'));
	?>
	<br>
	<?php echo Yii::t('admin', 'Set user session expiration time (in minutes). Default is 60.'); ?><br />
	<br />

	<?php echo Yii::t('admin', 'Enable Strict Lock on Quotes'); ?>
	<?php echo $form->checkBox($model, 'quoteStrictLock'); ?>
    </div><br>

    <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n"; ?>
    <?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n"; ?>
    <?php $this->endWidget(); ?>
</div>