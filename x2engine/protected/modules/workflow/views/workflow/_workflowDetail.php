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


Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');

$users = User::getNames();
if($editable) {
	$form = $this->beginWidget('CActiveForm', array(
		'id'=>'workflowDetailsForm',
		'action'=>array('/workflow/workflow/updateStageDetails','id'=>$model->id),
	));
}
?>
<table class="details" style="margin-bottom:0">
	<tr>
		<td class="label" width="30%">
			<?php echo CHtml::label(Yii::t('workflow','Started'),'startDate'); ?>
		</td>
		<td class="text-field">
			<?php
			$model->createDate = Formatter::formatDate($model->createDate);
			echo CHtml::tag('span',array(),$model->createDate);
			if($editable) {
				$this->widget('CJuiDateTimePicker',array(
					// 'name'=>'startDate',
					// 'value'=>0,
					'model'=>$model, //Model object
					'attribute'=>'createDate', //attribute name
					'mode'=>'date', //use "time","date" or "datetime" (default)
					'options'=>array(
						'dateFormat'=>Formatter::formatDatePicker(),
						'changeMonth'=>true,
						'changeYear'=>true,
						'minDate'=>$minDate,
						'maxDate'=>'0'

					), // jquery plugin options
					'htmlOptions'=>array(
						// 'id'=>'workflowDetails_createDate',
						'title'=>Yii::t('actions','Complete Date'),
					),
					'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
				));
			}
			?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo CHtml::label(Yii::t('workflow','Completed'),'completeDate'); ?></td>
		<td class="text-field">
			<span><?php echo $model->completeDate; ?></span>
			<?php
			$model->completeDate = Formatter::formatDate($model->completeDate);
			if($editable) {
				$this->widget('CJuiDateTimePicker',array(
					// 'name'=>'completeDate',
					// 'value'=>0,
					'model'=>$model, //Model object
					'attribute'=>'completeDate', //attribute name
					'mode'=>'date', //use "time","date" or "datetime" (default)
					'options'=>array(
						'dateFormat'=>Formatter::formatDatePicker(),
						'changeMonth'=>true,
						'changeYear'=>true,
						'minDate'=>$minDate,
						'maxDate'=>'0'

					), // jquery plugin options
					'htmlOptions'=>array(
						'title'=>Yii::t('actions','Complete Date'),
						// 'id'=>'workflowDetails_completeDate',
					),
					'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
				));
			}
			?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo CHtml::label(Yii::t('workflow','Completed By'),'completedBy'); ?></td>
		<td class="text-field">
			<span><?php if(!empty($model->completedBy) && $model->complete=='Yes' && isset($users[$model->completedBy])) echo $users[$model->completedBy]; ?></span>
			<?php if($editable) echo $form->dropDownList($model, 'completedBy', User::getNames(),array('disabled'=>$allowReassignment?null:'disabled')); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo CHtml::label(Yii::t('workflow','Comments'),'completeDate'); ?></td>
		<td class="text-field" style="height:100px;overflow-y:auto;">
			<span><?php echo $model->actionDescription; ?></span>
			<div style="margin:0 2px;">
			<?php if($editable) echo $form->textArea($model,'actionDescription',array('style'=>'width:100%;margin:0 -2px;height:85px;')) ?>
			</div>
		</td>
	</tr>
</table>
<?php
if($editable)
$this->endWidget();
