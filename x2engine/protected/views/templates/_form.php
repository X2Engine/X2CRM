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
?>
<div class="form">

<?php  include("protected/config/templatesConfig.php");
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'templates-form',
	'enableAjaxValidation'=>false,
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />

	<?php echo $form->errorSummary($model); ?>
<?php if($moduleConfig['assignedToDisplay']=='1'){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'assignedTo'); ?>
		<?php echo $form->dropDownList($model,'assignedTo',$users); ?>
		<?php echo $form->error($model,'assignedTo'); ?>
	</div>
<?php } ?>
	<div class="row">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
<?php if($moduleConfig['descriptionDisplay']=='1'){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>
<?php } ?>
	<?php if($moduleConfig['displayOne']=='1'){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'fieldOne'); ?>
		<?php echo $form->textField($model,'fieldOne',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'fieldOne'); ?>
	</div>
<?php } ?>
	<?php if($moduleConfig['displayTwo']=='1'){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'fieldTwo'); ?>
		<?php echo $form->textField($model,'fieldTwo',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'fieldTwo'); ?>
	</div>
<?php } ?>
	<?php if($moduleConfig['displayThree']=='1'){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'fieldThree'); ?>
		<?php echo $form->textField($model,'fieldThree',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'fieldThree'); ?>
	</div>
<?php } ?>
	<?php if($moduleConfig['displayFour']=='1'){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'fieldFour'); ?>
		<?php echo $form->textField($model,'fieldFour',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'fieldFour'); ?>
	</div>
<?php } ?>
	<?php if($moduleConfig['displayFive']=='1'){ ?>
	<div class="row">
		<?php echo $form->labelEx($model,'fieldFive'); ?>
		<?php echo $form->textField($model,'fieldFive',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'fieldFive'); ?>
	</div>
<?php } ?>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div><!-- form -->