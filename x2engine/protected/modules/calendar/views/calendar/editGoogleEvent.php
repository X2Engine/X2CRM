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
 ?>

<?php
$users = User::getNames();
$form=$this->beginWidget('CActiveForm', array(
    'enableAjaxValidation'=>false,
));
?>

<input type="hidden" name="EventId" value="<?php echo $eventId; ?>">
<style type="text/css">

.dialog-label {
	font-weight: bold;
	display: block;
}

.cell {
	float: left;
}

.dialog-cell {
	padding: 5px;
}

</style>

<div class="row">
	<div class="text-area-wrapper">
		<?php echo $form->textArea($model,'actionDescription',array('rows'=>3, 'cols'=>40, 'onChange'=>'giveSaveButtonFocus();')); ?>
	</div>
</div>

<div class="row">
	<div class="cell dialog-cell">
		<?php echo $form->label($model,('startDate'), array('class'=>'dialog-label'));
		$defaultDate = Formatter::formatDate($model->dueDate, 'medium');
		$model->dueDate = Formatter::formatDateTime($model->dueDate);	//format date from DATETIME
//		$event->start = Formatter::formatDateTime($event->start);

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$model, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=> Formatter::formatDatePicker('medium'),
				'timeFormat'=> Formatter::formatTimePicker(),
				'defaultDate'=>$defaultDate,
				'ampm'=>Formatter::formatAMPM(),
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			'htmlOptions'=>array(
				'onClick'=>"$('#ui-datepicker-div').css('z-index', '10020');", // fix datepicker so it's always on top
				'id'=>'dialog-Actions_dueDate',
				'readonly'=>'readonly',
				'onChange'=>'giveSaveButtonFocus();',
			),
		));

		echo $form->label($model, 'endDate', array('class'=>'dialog-label'));
		$defaultDate = Formatter::formatDate($model->completeDate, 'medium');
		$model->completeDate = Formatter::formatDateTime($model->completeDate);	//format date from DATETIME
		$this->widget('CJuiDateTimePicker',array(
		    'model'=>$model, //Model object
		    'attribute'=>'completeDate', //attribute name
		    'mode'=>'datetime', //use "time","date" or "datetime" (default)
		    'options'=>array(
		    	'dateFormat'=> Formatter::formatDatePicker('medium'),
				'timeFormat'=> Formatter::formatTimePicker(),
		    	'defaultDate'=>$defaultDate,
		    	'ampm'=>Formatter::formatAMPM(),
		    ), // jquery plugin options
		    'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
		    'htmlOptions'=>array(
		    	'onClick'=>"$('#ui-datepicker-div').css('z-index', '10020');", // fix datepicker so it's always on top
		    	'id'=>'dialog-Actions_startDate',
		    	'readonly'=>'readonly',
		    	'onChange'=>'giveSaveButtonFocus();',
		    ),
		));

		?>

		<?php echo $form->label($model, 'allDay', array('class'=>'dialog-label')); ?>
		<?php echo $form->checkBox($model, 'allDay', array('onChange'=>'giveSaveButtonFocus();')); ?>
	</div>

	<div class="cell dialog-cell">
		<?php echo $form->label($model, 'color', array('class'=>'dialog-label')); ?>
		<?php echo $form->dropDownList($model, 'color', Actions::getColors(), array('onChange'=>'giveSaveButtonFocus();')); ?>
	</div>
</div>

<?php $this->endWidget(); ?>
