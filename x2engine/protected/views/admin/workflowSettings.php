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
	300=>Yii::t('app','{n} min',5),
	900=>Yii::t('app','{n} min',15),
	1800=>Yii::t('app','{n} min',30),
	3600=>Yii::t('app','{n} hour',1),
	7200=>Yii::t('app','{n} hours',2),
	28800=>Yii::t('app','{n} hours',8),
	86400=>Yii::t('app','{n} day',1),
	172800=>Yii::t('app','{n} days',2),
	432000=>Yii::t('app','{n} days',5),
	604800=>Yii::t('app','{n} days',7),
	1209600=>Yii::t('app','{n} days',14),
	2592000=>Yii::t('app','{n} month',1),
	7776000=>Yii::t('app','{n} months',3),
	15552000=>Yii::t('app','{n} months',6),
	31536000=>Yii::t('app','{n} year',1),
	-1=>Yii::t('app','Unlimited'),
);
$dateLengths = array(
	1=>Yii::t('app','{n} day',1),
	2=>Yii::t('app','{n} days',2),
	3=>Yii::t('app','{n} days',3),
	4=>Yii::t('app','{n} days',4),
	5=>Yii::t('app','{n} days',5),
	7=>Yii::t('app','{n} days',7),
	14=>Yii::t('app','{n} days',14),
	30=>Yii::t('app','{n} month',1),
	90=>Yii::t('app','{n} months',3),
	182=>Yii::t('app','{n} months',6),
	365=>Yii::t('app','{n} year',1),
	-1=>Yii::t('app','Unlimited'),
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
<div class="page-title"><h2><?php echo Yii::t('admin','Workflow Settings'); ?></h2></div>
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
	<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','style'=>'margin-left:0;'))."\n";?>
	</div>




	<?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n";?>
<?php $this->endWidget();?>
</div>