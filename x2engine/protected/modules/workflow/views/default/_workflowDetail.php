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

 
Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');

$users = User::getNames();

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'workflowDetailsForm',
	'action'=>array('/workflow/updateStageDetails/'.$model->id),
));
?>
<table class="details" style="margin-bottom:0">
	<tr>
		<td class="label" width="30%">
			<?php echo CHtml::label(Yii::t('workflow','Started'),'startDate'); ?>
		</td>
		<td class="text-field">
			<?php
			$model->createDate = $this->formatDate($model->createDate);
			echo CHtml::tag('span',array(),$model->createDate);
			
			$this->widget('CJuiDateTimePicker',array(
				// 'name'=>'startDate',
				// 'value'=>0,
				'model'=>$model, //Model object
				'attribute'=>'createDate', //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,
					'maxDate'=>'0'

				), // jquery plugin options
				'htmlOptions'=>array(
					// 'id'=>'workflowDetails_createDate',
					'title'=>Yii::t('actions','Complete Date'),
				),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo CHtml::label(Yii::t('workflow','Completed'),'completeDate'); ?></td>
		<td class="text-field">
			<span><?php echo $model->completeDate; ?></span>
			<?php
			$model->completeDate = $this->formatDate($model->completeDate);
			$this->widget('CJuiDateTimePicker',array(
				// 'name'=>'completeDate',
				// 'value'=>0,
				'model'=>$model, //Model object
				'attribute'=>'completeDate', //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,
					'maxDate'=>'0'

				), // jquery plugin options
				'htmlOptions'=>array(
					'title'=>Yii::t('actions','Complete Date'),
					// 'id'=>'workflowDetails_completeDate',
				),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo CHtml::label(Yii::t('workflow','Completed By'),'completedBy'); ?></td>
		<td class="text-field">
			<span><?php if(!empty($model->completedBy) && isset($users[$model->completedBy])) echo $users[$model->completedBy]; ?></span>
			<?php echo $form->dropDownList($model, 'completedBy', User::getNames()); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo CHtml::label(Yii::t('workflow','Comments'),'completeDate'); ?></td>
		<td class="text-field" style="height:100px;overflow-y:auto;">
			<span><?php echo $model->actionDescription; ?></span>
			<div style="margin:0 2px;">
			<?php echo $form->textArea($model,'actionDescription',array('style'=>'width:100%;margin:0 -2px;height:85px;')) ?>
			</div>
		</td>
	</tr>
</table>
<?php
// echo CHtml::htmlButton(Yii::t('app','Edit'),array('class'=>'x2-button right editButton','onclick'=>'$("#workflowStageDetails").dialog("editMode");'));

// echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button right','onclick'=>'$("#workflowStageDetails").dialog("editMode");'));
// echo CHtml::ajaxSubmitButton(Yii::t('app','Save'),$form->action,array(),array('class'=>'x2-button highlight right'));
?>
<?php
$this->endWidget();

?>