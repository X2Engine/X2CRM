<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/
 
Yii::app()->clientScript->registerScript('addWorkflowStage', "
function deleteStage(object) {
	$(object).closest('li').remove();
}

function moveStageUp(object) {
	var prev = $(object).closest('li').prev();
	if(prev.length>0) {
		prev.before('<li>'+$(object).closest('li').html()+'</li>');
		$(object).closest('li').remove();
	}
}
function moveStageDown(object) {
	var next = $(object).closest('li').next();
	if(next.length>0) {
		next.after('<li>'+$(object).closest('li').html()+'</li>');
		$(object).closest('li').remove();
	}
}

function addStage() {
	$('#workflow-stages ol').append(' \
	<li>\
	<div class=\"row\">\
		<div class=\"cell\">\
			".addslashes(CHtml::label(Yii::t('workflow','Stage Name'),null)
			.CHtml::textField('WorkflowStages[name][]','',array('size'=>40,'maxlength'=>40)))
			// .CHtml::error('WorkflowStages_name'))
		." \
		</div> \
		<div class=\"cell\">\
			".addslashes(CHtml::label(Yii::t('workflow','Conversion Rate'),null)
			.CHtml::textField('WorkflowStages[conversionRate][]','',array('size'=>10,'maxlength'=>20)))
			// .CHtml::error('WorkflowStages_conversionRate'))
		." \
		</div> \
		<div class=\"cell\">\
			".addslashes(CHtml::label(Yii::t('workflow','Value'),null)
			.CHtml::textField('WorkflowStages[value][]','',array('size'=>10,'maxlength'=>20)))
			// .CHtml::error('WorkflowStages_value'))
		." \
		</div>\
		<div class=\"cell\">\
			<a href=\"javascript:void(0)\" onclick=\"moveStageUp(this);\">[".Yii::t('workflow','Up')."]</a>\
			<a href=\"javascript:void(0)\" onclick=\"moveStageDown(this);\">[".Yii::t('workflow','Down')."]</a>\
			<a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\">[".Yii::t('workflow','Del')."]</a>\
		</div>\
	</div>\
	</li>');
}

",CClientScript::POS_HEAD);
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'workflow-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>250)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
	<div id="workflow-stages">
	<?php
	if(empty($stages))
		$stages[] = new WorkflowStage;	// start with at least 1 blank row
	?><ol><?php
	foreach($stages as &$stage) { ?>
	<li>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($stage,'name'); ?>
			<?php echo CHtml::textField('WorkflowStages[name][]',$stage->name,array('size'=>40,'maxlength'=>40)); ?>
			<?php echo CHtml::error($stage,'name'); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($stage,'conversionRate'); ?>
			<?php echo CHtml::textField('WorkflowStages[conversionRate][]',$stage->conversionRate,array('size'=>10,'maxlength'=>20)); ?>
			<?php echo CHtml::error($stage,'conversionRate'); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($stage,'value'); ?>
			<?php echo CHtml::textField('WorkflowStages[value][]',$stage->value,array('size'=>10,'maxlength'=>20)); ?>
			<?php echo $form->error($stage,'value'); ?>
		</div>
		<div class="cell">
			<a href="javascript:void(0)" onclick="moveStageUp(this);">[<?php echo Yii::t('workflow','Up'); ?>]</a>
			<a href="javascript:void(0)" onclick="moveStageDown(this);">[<?php echo Yii::t('workflow','Down'); ?>]</a>
			<a href="javascript:void(0)" onclick="deleteStage(this);">[<?php echo Yii::t('workflow','Del'); ?>]</a>
		</div>
	</div>
	</li>
	<?php 
	}
	?>
	</ol>
	</div>
	<a href="javascript:void(0)" onclick="addStage()" class="add-workflow-stage">[<?php echo Yii::t('workflow','Add'); ?>]</a>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'x2-button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->