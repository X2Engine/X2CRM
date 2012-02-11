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
 
Yii::app()->clientScript->registerScript('addWorkflowStage', "
function deleteStage(object) {
	$(object).closest('li').animate({
		opacity: 0,
		height: 0
	}, 200,function() { $(this).remove(); });
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
	<li style=\"display:none;\">\
	<div class=\"row\">\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->getAttributeLabel('name'),null)
			.CHtml::textField('WorkflowStages[name][]','',array('size'=>40,'maxlength'=>40)))
			// .CHtml::error('WorkflowStages_name'))
		." \
		</div> \
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->getAttributeLabel('conversionRate'),null)
			.CHtml::textField('WorkflowStages[conversionRate][]','',array('size'=>10,'maxlength'=>20)))
			// .CHtml::error('WorkflowStages_conversionRate'))
		." \
		</div> \
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->getAttributeLabel('value'),null)
			.CHtml::textField('WorkflowStages[value][]','',array('size'=>10,'maxlength'=>20)))
		."</div>\
		<div class=\"cell\">\
			<a href=\"javascript:void(0)\" onclick=\"moveStageUp(this);\" title=\"".Yii::t('workflow','Up')."\" class=\"up\"></a>\
			<a href=\"javascript:void(0)\" onclick=\"moveStageDown(this);\" title=\"".Yii::t('workflow','Down')."\" class=\"down\"></a>\
			<a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\" title=\"".Yii::t('workflow','Delete')."\" class=\"del\"></a>\
		</div>\
	</div>\
	<div class=\"row\" style=\"padding-left:15px;\">\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->getAttributeLabel('requirePrevious'),null)
			.' '.preg_replace('/[\r\n]+/u','',CHtml::dropdownList('WorkflowStages[requirePrevious][]',0,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('style'=>'width:160px;'))))
		."</div>\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->getAttributeLabel('requireComment'),null)
			.' '.preg_replace('/[\r\n]+/u','',CHtml::dropdownList('WorkflowStages[requireComment][]',0,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('style'=>'width:140px;'))))
		."</div>\
	</div>\
	</li>');
	$('#workflow-stages li:last-child').slideDown(300);
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
			<a href="javascript:void(0)" onclick="moveStageUp(this);" title="<?php echo Yii::t('workflow','Up'); ?>" class="up"></a>
			<a href="javascript:void(0)" onclick="moveStageDown(this);" title="<?php echo Yii::t('workflow','Down'); ?>" class="down"></a>
			<a href="javascript:void(0)" onclick="deleteStage(this);" title="<?php echo Yii::t('workflow','Del'); ?>" class="del"></a>
		</div>
	</div>
	<div class="row" style="padding-left:15px;">
		<div class="cell">
			<?php echo $form->labelEx($stage,'requirePrevious'); ?>
			<?php echo CHtml::dropdownList('WorkflowStages[requirePrevious][]',$stage->requirePrevious,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('style'=>'width:160px;')); ?>
			<?php echo $form->error($stage,'requirePrevious'); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($stage,'requireComment'); ?>
			<?php echo CHtml::dropdownList('WorkflowStages[requireComment][]',$stage->requireComment,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('style'=>'width:140px;')); ?>
			<?php echo $form->error($stage,'requireComment'); ?>
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