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


if(empty($model->stages))
	$model->stages = array(new WorkflowStage);	// start with at least 1 blank row

// look up all the available roles
$roles = array(''=>Yii::t('app','Anyone'));
$roleIds = Yii::app()->db->createCommand()->select('id')->from('x2_roles')->queryColumn();
$roleNames = Yii::app()->db->createCommand()->select('name')->from('x2_roles')->queryColumn();

if(!empty($roleIds) && !empty($roleNames) && count($roleIds) == count($roleNames))
	$roles += array_combine($roleIds,$roleNames);
unset($roleIds,$roleNames);		// cleanup temp vars

Yii::app()->clientScript->registerScript('addWorkflowStage', "
function deleteStage(object) {
	$(object).closest('li').animate({
		opacity: 0,
		height: 0
	}, 200,function() { $(this).remove(); updateStageNumbers(); });

	var stageCount = $('#workflow-stages li').length;
	$('#workflow-stages li select.workflow_requirePrevious').find('option:last').remove();
}

function addStage() {

	var stageCount = $('#workflow-stages li').length;

	$('#workflow-stages ol').append('\
	<li style=\"display:none;\">\
	<div class=\"handle\"></div>\
	<div class=\"content\">\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->stages[0]->getAttributeLabel('name'),null)
			.CHtml::textField('WorkflowStages[][name]','',array('class'=>'workflow_name','style'=>'width:140px','maxlength'=>40)))
			// .CHtml::error('WorkflowStages_name'))
		." \
		</div> \
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->stages[0]->getAttributeLabel('requirePrevious'),null)
			.' '.preg_replace('/[\r\n]+/u','',CHtml::dropdownList('WorkflowStages[][requirePrevious]',0,array('0'=>Yii::t('app','None'),'1'=>Yii::t('app','All')),array('class'=>'workflow_requirePrevious','style'=>'width:100px;'))))
		."</div>\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->stages[0]->getAttributeLabel('roles'),null)
			.' '.preg_replace('/[\r\n]+/u','',CHtml::dropdownList('WorkflowStages[][roles][]','',$roles,array('multiple'=>'multiple','class'=>'workflow_roles','style'=>'width:100px;'))))
		."</div>\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->stages[0]->getAttributeLabel('requireComment'),null)
			.' '.preg_replace('/[\r\n]+/u','',CHtml::dropdownList('WorkflowStages[][requireComment]',0,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('class'=>'workflow_requireComment','style'=>'width:80px;'))))
		."</div>\
		<div class=\"cell\">\
			<a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\" title=\"".Yii::t('workflow','Delete')."\" class=\"del\"></a>\
		</div>\
	</div>\
	</li>');
	stageCount++;

	for(i=1;i<stageCount;i++)
		$('#workflow-stages li:last-child select.workflow_requirePrevious').append('<option value=\"-'+i+'\">".addslashes(Yii::t('workflow','Stage'))." '+i+'</option>');
	$('#workflow-stages li select.workflow_requirePrevious').append('<option value=\"'+stageCount+'\">".addslashes(Yii::t('workflow','Stage'))." '+stageCount+'</option>');
	$('#workflow-stages li:last-child').slideDown(300);
	updateStageNumbers();
}

function updateStageNumbers() {
	$('#workflow-stages li').each(function(i,element) {
		$(this).find('.handle').html(i+1);
		$(this).find('input.workflow_name').attr('name','WorkflowStages['+(i+1)+'][name]');
		$(this).find('select.workflow_requirePrevious').attr('name','WorkflowStages['+(i+1)+'][requirePrevious]');
		$(this).find('select.workflow_roles').attr('name','WorkflowStages['+(i+1)+'][roles][]');
		$(this).find('select.workflow_requireComment').attr('name','WorkflowStages['+(i+1)+'][requireComment]');
	});
}

$(function() {
	$('#workflow-stages ol').sortable({
		// tolerance:'intersect',
		// items:'.formSection',
		// placeholder:'formSectionPlaceholder',
		handle:'.handle',
		// opacity:0.5,
		axis:'y',
		distance:10,
		stop:updateStageNumbers
		// change:function() { window.layoutChanged = true; }
	});
});
",CClientScript::POS_HEAD);
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'workflow-form',
	'enableAjaxValidation'=>false,
)); ?>
	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'name'); ?>
			<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>250)); ?>
			<?php echo $form->error($model,'name'); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'isDefault'); ?>
			<?php echo $form->checkbox($model,'isDefault'); ?>
		</div>
	</div>
	<div id="workflow-stages" class="x2-sortlist">
	<ol><?php

	$stageRequirements = array(
		'0'=>Yii::t('workflow','None'),
		'1'=>Yii::t('workflow','All')
	);
	for($i=1;$i<=count($model->stages);$i++)
		$stageRequirements['-'.$i] = Yii::t('workflow','Stage').' '.$i;

	// $model->stages = array_reverse($model->stages);

	for($i=0; $i<count($model->stages); $i++) {
		$stage = $model->stages[$i];

		?><li>
		<div class="handle"><?php echo $i+1; ?></div>
		<div class="content">
			<div class="cell">
				<?php echo $form->labelEx($stage,'name'); ?>
				<?php echo CHtml::textField('WorkflowStages['.($i+1).'][name]',$stage->name,array('class'=>'workflow_name','style'=>'width:140px','maxlength'=>40)); ?>
				<?php echo CHtml::error($stage,'name'); ?>
			</div>

			<div class="cell">
				<?php echo $form->labelEx($stage,'requirePrevious'); ?>
				<?php
				if(empty($stage->roles))
					$stage->roles = array('');
				echo CHtml::dropdownList('WorkflowStages['.($i+1).'][requirePrevious]',$stage->requirePrevious,$stageRequirements,array('class'=>'workflow_requirePrevious','style'=>'width:100px;')); ?>
			</div>
			<div class="cell">
				<?php echo $form->label($stage,'roles'); ?>
				<?php echo CHtml::dropdownList('WorkflowStages['.($i+1).'][roles][]',$stage->roles,$roles,array('multiple'=>'multiple','class'=>'workflow_roles','style'=>'width:100px;')); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($stage,'requireComment'); ?>
				<?php echo CHtml::dropdownList('WorkflowStages['.($i+1).'][requireComment]',$stage->requireComment,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('class'=>'workflow_requireComment','style'=>'width:80px;')); ?>
			</div>
			<a href="javascript:void(0)" onclick="deleteStage(this);" title="<?php echo Yii::t('workflow','Del'); ?>" class="del"></a>
		</div>
		</li>
		<?php
	}
	?>
	</ol>
	</div>
	<a href="javascript:void(0)" onclick="addStage()" class="x2-sortlist-add">[<?php echo Yii::t('workflow','Add'); ?>]</a>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->