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
$users = UserChild::getNames();
$form=$this->beginWidget('CActiveForm', array(
    'enableAjaxValidation'=>false,
));
?>

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
		<?php echo $form->textArea($model,'actionDescription',array('rows'=>3, 'cols'=>40)); ?>
	</div>
</div>

<div class="row">
	<div class="cell dialog-cell">
		<?php echo $form->label($model,($isEvent?'startDate':'dueDate'), array('class'=>'dialog-label'));
		$model->dueDate = $this->formatDateTime($model->dueDate);	//format date from DATETIME

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$model, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=> $this->formatDatePicker('medium'),
				'defaultDate'=>substr($model->dueDate, 0, strlen($model->dueDate) - 6),
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			'htmlOptions'=>array(
				'onClick'=>"$('#ui-datepicker-div').css('z-index', '10020');",
				'id'=>'dialog-Actions_dueDate',
				'readonly'=>'readonly',
				'onChange'=>'giveSaveButtonFocus();',
			), // fix datepicker so it's always on top
		));
		
		if($isEvent) {
			echo $form->label($model, 'endDate', array('class'=>'dialog-label'));
			$model->completeDate = $this->formatDateTime($model->completeDate);	//format date from DATETIME
			$this->widget('CJuiDateTimePicker',array(
				'model'=>$model, //Model object
				'attribute'=>'completeDate', //attribute name
				'mode'=>'datetime', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=> $this->formatDatePicker('medium'),
					'defaultDate'=>($model->completeDate? substr($model->completeDate, 0, strlen($model->completeDate) - 6) : ''),
				), // jquery plugin options
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
				'htmlOptions'=>array(
					'onClick'=>"$('#ui-datepicker-div').css('z-index', '10020');",
					'id'=>'dialog-Actions_startDate',
					'readonly'=>'readonly',
					'onChange'=>'giveSaveButtonFocus();',
				), // fix datepicker so it's always on top
			));
		}

		?>
		

		<?php echo $form->label($model, 'allDay', array('class'=>'dialog-label')); ?>
		<?php echo $form->checkBox($model, 'allDay', array('onChange'=>'giveSaveButtonFocus();')); ?>
	</div>
	
	<div class="cell dialog-cell">
		<?php echo $form->label($model,'priority', array('class'=>'dialog-label')); ?>
		<?php echo $form->dropDownList($model,'priority',
			array(
				'Low'=>Yii::t('actions','Low'),
				'Medium'=>Yii::t('actions','Medium'),
				'High'=>Yii::t('actions','High')
			),
			array('onChange'=>'giveSaveButtonFocus();'));
		?>
		<?php echo $form->label($model, 'color', array('class'=>'dialog-label')); ?>
		<?php echo $form->dropDownList($model, 'color', Actions::getColors(), array('onChange'=>'giveSaveButtonFocus();')); ?>
	</div>
	
	<div class="cell dialog-cell">
		<?php
		if($model->assignedTo == null && is_numeric($model->calendarId)) { // assigned to calendar instead of user?
		    $model->assignedTo = $model->calendarId;
		}
		?>
		<?php echo $form->label($model,'assignedTo', array('class'=>'dialog-label')); ?>
		<?php echo $form->dropDownList($model,'assignedTo',$users + X2Calendar::getEditableCalendarNames(),array('id'=>'dialog_actionsAssignedToDropdown', 'onChange'=>'giveSaveButtonFocus();')); ?>
		<?php /* x2temp */
		echo "<br />";
		$url=$this->createUrl('groups/getGroups');
		echo "<label class=\"dialog-label\">Group?</label>";
		echo CHtml::checkBox('group','',array(
		    'id'=>'dialog_groupCheckbox',
		    'onChange'=>'giveSaveButtonFocus();',
		    'ajax'=>array(
		        'type'=>'POST', //request type
		            'url'=>$url, //url to call.
		            //Style: CController::createUrl('currentController/methodToCall')
		            'update'=>'#dialog_actionsAssignedToDropdown', //selector to update
		            'complete'=>'function(){
		                if($("#dialog_groupCheckbox").attr("checked")!="checked"){
		                    $("#dialog_groupCheckbox").attr("checked","checked");
		                    $("#dialog_Actions_visibility option[value=\'2\']").remove();
		                }else{
		                    $("#dialog_groupCheckbox").removeAttr("checked");
		                    $("#dialog_Actions_visibility").append(
		                        $("<option></option>").val("2").html("User\'s Groups")
		                    );
		                }
		            }'
		    ),
		));
		/* end x2temp */ ?>
	</div>
	
	<div class="cell dialog-cell">
		<?php echo $form->label($model,'visibility', array('class'=>'dialog-label')); ?>
 		<?php
 		$visibility=array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private'));
 		/* x2temp */
 		$visibility[2]='User\'s Groups';
 		/* end x2temp */
 		?>
		<?php echo $form->dropDownList($model,'visibility',$visibility, array('id'=>'dialog_Actions_visibility', 'onChange'=>'giveSaveButtonFocus();')); ?> 
	</div>

	<div class="cell dialog-cell">
		<?php echo $form->label($model,'reminder', array('class'=>'dialog-label')); ?>
		<?php echo $form->dropDownList($model,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes')), array('onChange'=>'giveSaveButtonFocus();')); ?> 
	</div>

</div>

<?php $this->endWidget(); ?>

























