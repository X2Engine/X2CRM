<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/
 ?>

<?php
$users = User::getNames();
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
		<?php echo $form->textArea($model,'actionDescription',array('rows'=>3, 'cols'=>40, 'onChange'=>'giveSaveButtonFocus();')); ?>
	</div>
</div>

<div class="row">
	<div class="cell dialog-cell">
		<?php echo $form->label($model,($isEvent?'startDate':'dueDate'), array('class'=>'dialog-label'));
		$defaultDate = Formatter::formatDate($model->dueDate, 'medium');
		$model->dueDate = Formatter::formatDateTime($model->dueDate);	//format date from DATETIME

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$model, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=> Formatter::formatDatePicker('medium'),
				'timeFormat'=>Formatter::formatTimePicker(),
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
		
		if($isEvent) {
			echo $form->label($model, 'endDate', array('class'=>'dialog-label'));
			$defaultDate = Formatter::formatDate($model->completeDate, 'medium');
			$model->completeDate = Formatter::formatDateTime($model->completeDate);	//format date from DATETIME
			$this->widget('CJuiDateTimePicker',array(
				'model'=>$model, //Model object
				'attribute'=>'completeDate', //attribute name
				'mode'=>'datetime', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=> Formatter::formatDatePicker('medium'),
					'timeFormat'=>Formatter::formatTimePicker(),
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
		<?php if(is_numeric($model->assignedTo)) { // action assigned to group ?>
		<?php 	$assignedToValues = Groups::getNames(); ?>
		<?php } else { ?>
		<?php 	$assignedToValues = $users; ?>
		<?php } ?>
		<?php echo $form->dropDownList($model,'assignedTo',$assignedToValues,array('id'=>'dialog_actionsAssignedToDropdown', 'onChange'=>'giveSaveButtonFocus();')); ?>
		<?php /* x2temp */
		echo "<br />";
		$url=$this->createUrl('/groups/getGroups');
		echo "<label class=\"dialog-label\">Group?</label>";
		echo CHtml::checkBox('group', is_numeric($model->assignedTo),array(
		    'id'=>'dialog_groupCheckbox',
		    'onChange'=>'giveSaveButtonFocus();',
		    'ajax'=>array(
		        'type'=>'POST', //request type
		            'url'=>$url, //url to call.
		            //Style: CController::createUrl('currentController/methodToCall')
		            'update'=>'#dialog_actionsAssignedToDropdown', //selector to update
		            'data'=>'js:{checked: $(this).attr("checked")=="checked"}',
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

























