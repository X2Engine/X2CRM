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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
		<?php //echo CHtml::label(Yii::t('Actions', 'Start Date'), 'dialog-Actions_dueDate'); ?>
		<?php echo $form->label($model,('startDate'), array('class'=>'dialog-label'));
		$defaultDate = $this->formatDate($model->dueDate, 'medium');
		$model->dueDate = $this->formatDateTime($model->dueDate);	//format date from DATETIME
//		$event->start = $this->formatDateTime($event->start);
		
		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$model, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=> $this->formatDatePicker('medium'),
				'timeFormat'=> $this->formatTimePicker(),
				'defaultDate'=>$defaultDate,
				'ampm'=>$this->formatAMPM(),
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
		$defaultDate = $this->formatDate($model->completeDate, 'medium');
		$model->completeDate = $this->formatDateTime($model->completeDate);	//format date from DATETIME
		$this->widget('CJuiDateTimePicker',array(
		    'model'=>$model, //Model object
		    'attribute'=>'completeDate', //attribute name
		    'mode'=>'datetime', //use "time","date" or "datetime" (default)
		    'options'=>array(
		    	'dateFormat'=> $this->formatDatePicker('medium'),
				'timeFormat'=> $this->formatTimePicker(),
		    	'defaultDate'=>$defaultDate,
		    	'ampm'=>$this->formatAMPM(),
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