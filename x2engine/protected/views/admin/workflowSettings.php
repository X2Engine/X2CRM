<?php
/*********************************************************************************
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


$('#backdateRange').change(function() {
	// console.debug($(this).prop('selectedIndex'));
	$('#backdateRangeSlider').slider('value',$(this).prop('selectedIndex')+1);
});
$('#backdateWindow').change(function() {
	// console.debug($(this).prop('selectedIndex'));
	$('#backdateWindowSlider').slider('value',$(this).prop('selectedIndex')+1);
});



",CClientScript::POS_READY);


$timeLengths = array(
	0=>Yii::t('app','Never'),
	30=>Yii::t('app','{n} sec',30),
	60=>Yii::t('app','{n} min',1),
	120=>Yii::t('app','{n} min',5),
	900=>Yii::t('app','{n} min',15),
	1800=>Yii::t('app','{n} min',30),
	3600=>Yii::t('app','{n} hour|{n} hours',1),
	7200=>Yii::t('app','{n} hour|{n} hours',2),
	28800=>Yii::t('app','{n} hour|{n} hours',8),
	86400=>Yii::t('app','{n} day|{n} days',1),
	172800=>Yii::t('app','{n} day|{n} days',2),
	432000=>Yii::t('app','{n} day|{n} days',5),
	604800=>Yii::t('app','{n} day|{n} days',7),
	1209600=>Yii::t('app','{n} day|{n} days',14),
	2592000=>Yii::t('app','{n} month|{n} months',1),
	7776000=>Yii::t('app','{n} month|{n} months',3),
	15552000=>Yii::t('app','{n} month|{n} months',6),
	31536000=>Yii::t('app','{n} year|{n} years',1),
	-1=>Yii::t('admin','Unlimited'),
);
$dateLengths = array(
	1=>Yii::t('app','{n} day|{n} days',1),
	2=>Yii::t('app','{n} day|{n} days',2),
	3=>Yii::t('app','{n} day|{n} days',3),
	4=>Yii::t('app','{n} day|{n} days',4),
	5=>Yii::t('app','{n} day|{n} days',5),
	7=>Yii::t('app','{n} day|{n} days',7),
	14=>Yii::t('app','{n} day|{n} days',14),
	30=>Yii::t('app','{n} month|{n} months',1),
	90=>Yii::t('app','{n} month|{n} months',3),
	182=>Yii::t('app','{n} month|{n} months',6),
	365=>Yii::t('app','{n} year|{n} years',1),
	-1=>Yii::t('admin','Unlimited'),
);



// 1-based indeces
$backdateWindowIndex = array_search($model->workflowBackdateWindow,array_keys($timeLengths));
if($backdateWindowIndex === false)
	$backdateWindowIndex = count($timeLengths);	// default to last value (unlimited)
else
	$backdateWindowIndex++;
	
$backdateRangeIndex = array_search($model->workflowBackdateRange,array_keys($dateLengths));
if($backdateRangeIndex === false)
	$backdateRangeIndex = count($timeLengths);
else
	$backdateRangeIndex++;

?>
<div class="span-16">
<h2><?php echo Yii::t('admin','Workflow Settings'); ?></h2>
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'settings-form',
	'enableAjaxValidation'=>false,
));
?>
	<div class="form">
		<?php echo $form->labelEx($model,'workflowBackdateWindow'); 
		$this->widget('zii.widgets.jui.CJuiSlider', array(
			'value'=>$backdateWindowIndex,
			// additional javascript options for the slider plugin
			'options'=>array(
				'min'=>1,
				'max'=>count($timeLengths),
				'slide'=>"js:function(event,ui) {
					$('#backdateWindow>option:nth-child('+ui.value+')').attr('selected',true);
				}",
				'animate'=>300,
			),
			'htmlOptions'=>array(
				'id'=>'backdateWindowSlider',
				'style'=>'width:340px;margin:10px 0;',
			),
		));
		?>
		<?php echo $form->dropDownList($model,'workflowBackdateWindow',$timeLengths,array('id'=>'backdateWindow')); ?><br>
		<?php echo Yii::t('admin','How long users have to backdate a workflow date.'); ?>
		<p>
		<hr>
		<?php echo $form->labelEx($model,'workflowBackdateRange'); ?>
		<?php $this->widget('zii.widgets.jui.CJuiSlider', array(
			'value'=>$backdateRangeIndex,
			// additional javascript options for the slider plugin
			'options'=>array(
				'min'=>1,
				'max'=>count($dateLengths),
				'slide'=>"js:function(event,ui) {
					$('#backdateRange>option:nth-child('+ui.value+')').attr('selected',true);
				}",
				'animate'=>300,
			),
			'htmlOptions'=>array(
				'id'=>'backdateRangeSlider',
				'style'=>'width:340px;margin:10px 0;',
			),
		));
		?>
		<?php echo $form->dropDownList($model,'workflowBackdateRange',$dateLengths,array('id'=>'backdateRange')); ?><br>
		<?php echo Yii::t('admin','How far back users can backdate a workflow stage.'); ?>
		<p>
		<hr>
		<?php echo $form->checkBox($model, 'workflowBackdateReassignment',array('id'=>'backdateReassignment')); ?>
		<label for="backdateReassignment" style="display:inline"><?php echo Yii::t('admin', 'Backdate reassignment'); ?></label><br>
		<?php echo Yii::t('admin','Users can change who a workflow stage was completed by.'); ?>
		</p>
	</div>


	
	
	<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','style'=>'margin-left:0;'))."\n";?>
	<?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?>
</div>