<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




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
