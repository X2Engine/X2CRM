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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');

Yii::app()->clientScript->registerScript('highlightSaveContact',"
$(function(){
	$('#contacts-form input, #contacts-form select, #contacts-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').addClass('highlight'); //css('background','yellow');
	}
	);
}
);");



$itemFields = array();
$itemFieldLinks = array();
foreach($itemModel->getFields() as $field) {
	$itemFields[$field->fieldName] = $field->type;
	if(!empty($field->linkType))
		$itemFields[$field->fieldName] = $field->linkType;
	
}

Yii::app()->clientScript->registerScript('listCriteriaJs', "

var fieldTypes = ".json_encode($itemFields,false).";
var fieldLinkTypes = ".json_encode($itemFieldLinks,false).";

function deleteCriterion(object) {
	$(object).closest('li').animate({
		opacity: 0,
		height: 0
	}, 200,function() { $(this).remove(); });
	
	if($('#list-criteria li').length < 3)	// prevent people from deleting the last criterion
		$('#list-criteria a.del').fadeOut(300);
}

function addCriterion() {

	$('#list-criteria ol').append($('#list-criteria li:first').clone().hide());
	$('#list-criteria a.del').fadeIn(300);
	$('#list-criteria li:last-child').find(':input').val('');
	$('#list-criteria li:last-child').slideDown(300);
}

$(function() {
	$('#list-criteria ol').sortable({
		// tolerance:'intersect',
		// items:'.formSection',
		// placeholder:'formSectionPlaceholder',
		handle:'.handle',
		// opacity:0.5,
		axis:'y',
		distance:10,
	});
	if($('#list-criteria li').length < 2)	// prevent people from deleting the last criterion
		$('#list-criteria a.del').hide();

		
	$('#listType').change(function() {
		if($(this).val() == 'static')
			$('#list-criteria').fadeOut(300);
		else if($('#list-criteria').length)
			$('#list-criteria').fadeIn(300);
		else
			window.location.reload();
	
	});
});
",CClientScript::POS_HEAD);
?>
<div class="form">
	<?php
	$form=$this->beginWidget('CActiveForm', array(
		'id'=>'contacts-form',
		'enableAjaxValidation'=>false,
	));
	?>
	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em>
<?php
echo $form->errorSummary($model);
?>
<div class="row">
	<?php //echo $form->labelEx($model,'campaignId'); ?>
	<?php //echo $form->textField($model,'campaignId',array('size'=>10,'maxlength'=>10)); ?>
	<?php //echo $form->error($model,'campaignId'); ?>
</div>
<div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>30,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
	<?php if($model->isNewRecord) { ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'type'); ?>
		<?php echo $form->dropDownList($model,'type',$listTypes,array('id'=>'listType')); ?>
		<?php echo $form->error($model,'type'); ?>
	</div>
	<?php } ?>
	<div class="cell">
		<?php echo $form->labelEx($model,'assignedTo'); ?>
		<?php
			if(empty($model->assignedTo))
				$model->assignedTo = Yii::app()->user->getName();
			echo $form->dropDownList($model,'assignedTo',$users,array('tabindex'=>null)); ?>
		<?php echo $form->error($model,'assignedTo'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'visibility'); ?>
		<?php
			echo $form->dropDownList($model,'visibility',array(
				1=>Yii::t('contacts','Public'),
				0=>Yii::t('contacts','Private')
			),array('tabindex'=>null));
		?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'logicType'); ?>
		<?php
			echo $form->dropDownList($model,'logicType',array(
				'AND'=>Yii::t('contacts','AND'),
				'OR'=>Yii::t('contacts','OR')
			),array('tabindex'=>null));
		?>
	</div>
</div>

<div class="row">
	<?php //echo $form->labelEx($model,'description'); ?>
	<?php //echo $form->textArea($model,'description',array('style'=>'width:440px;height:60px;')); ?>
	<?php //echo $form->error($model,'description'); ?>
</div>
<?php
if($model->type == 'dynamic') {
	$attributeLabels = $model->itemAttributeLabels;
	$attributeLabels['tags'] = Yii::t('contacts','Tags');
	natcasesort($attributeLabels);
	
	?>
	<div class="x2-sortlist" id="list-criteria">
	<ol>
	<?php foreach($criteriaModels as &$criterion) { ?>
	<li>
		<div class="handle"></div>
		<div class="content">
			<div class="cell">
				<?php echo CHtml::label($criterion->getAttributeLabel('attribute'),'X2List[attribute][]'); ?>
				<?php echo CHtml::dropDownList('X2List[attribute][]',$criterion->attribute,$attributeLabels); ?>
			</div>
			<div class="cell">
				<?php echo CHtml::label($criterion->getAttributeLabel('comparison'),'X2List[comparison][]'); ?>
				<?php echo CHtml::dropDownList('X2List[comparison][]',$criterion->comparison,$comparisonList,array('encode'=>false)); ?>
			</div>
			<div class="cell">
				<?php echo CHtml::label($criterion->getAttributeLabel('value'),'X2List[value][]'); ?>
				<?php echo CHtml::textField('X2List[value][]',$criterion->value,array('size'=>'30')); ?>
			</div>
			<a href="javascript:void(0)" onclick="deleteCriterion(this);" title="<?php echo Yii::t('app','Del'); ?>" class="del"></a>
		</div>
		</li>
	<?php } ?>
	</ol>
	
<a href="javascript:void(0)" onclick="addCriterion()" class="x2-sortlist-add">[<?php echo Yii::t('app','Add'); ?>]</a>
	</div>
<?php } ?>
<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>
<?php
$this->endWidget();
?>
</div>