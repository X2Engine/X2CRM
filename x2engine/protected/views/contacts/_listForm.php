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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');

Yii::app()->clientScript->registerScript('highlightSaveContact',"
$(function(){
	$('#contacts-form input, #contacts-form select, #contacts-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').css('background','yellow');
	}
	);
}
);");
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
	<?php echo $form->labelEx($model,'name'); ?>
	<?php echo $form->textField($model,'name',array('size'=>40,'maxlength'=>100)); ?>
	<?php echo $form->error($model,'name'); ?>
</div>

<div class="row">
	<?php //echo $form->labelEx($model,'description'); ?>
	<?php //echo $form->textArea($model,'description',array('style'=>'width:440px;height:60px;')); ?>
	<?php //echo $form->error($model,'description'); ?>
</div>
<?php
foreach($criteriaModels as $criterion) {

	echo $this->renderPartial('application.views.contacts._listCriterion', //'//contacts/_form',
	array(
		'model'=>$criterion,
		'form'=>$form,
		'attributeList'=>$attributeList,
		'comparisonList'=>$comparisonList,
	));
}
?>
<div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'type'); ?>
		<?php echo $form->dropDownList($model,'type',$listTypes); ?>
		<?php echo $form->error($model,'type'); ?>
	</div>
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
		<?php echo $form->error($model,'visibility'); ?>
	</div>
</div>
<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>
<?php
$this->endWidget();
?>
</div>