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
?><h3><?php echo Yii::t('admin','Customize Fields'); ?></h3>
<?php echo Yii::t('admin','This form will allow you to rename or show/hide any field on the four major models (Contacts, Actions, Sales and Accounts).'); ?><br><br>
<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'criteria-form',
		'enableAjaxValidation'=>false,
		'action'=>'customizeFields',
	)); ?>
	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br>
	<div class="row">
		<?php echo $form->labelEx($model,'modelName'); ?>
		<?php echo $form->dropDownList($model,'modelName',array('Actions'=>'Actions','Contacts'=>'Contacts','Sales'=>'Sales','Accounts'=>'Accounts'),
			array(
			'empty'=>'Select a model',
			'ajax' => array(
			'type'=>'POST', //request type
			'url'=>CController::createUrl('admin/getAttributes'), //url to call.
			//Style: CController::createUrl('currentController/methodToCall')
			'update'=>'#dynamicFields', //selector to update
			//'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
			//leave out the data key to pass all form values through
			))); ?>
		<?php echo $form->error($model,'modelName'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'fieldName'); ?>
		<?php echo $form->dropDownList($model,'fieldName',array(),array('empty'=>'Select a model first','id'=>'dynamicFields')); ?>
		<?php echo $form->error($model,'fieldName'); ?>
	</div>
	<br>
	<div class="row">
		<div>
		Please enter the new name for your chosen field.<br>
		Leave blank if you don't want to change it.</div><br>
		<?php echo $form->labelEx($model,'attributeLabel'); ?>
		<?php echo $form->textField($model,'attributeLabel'); ?>
		<?php echo $form->error($model,'attributeLabel'); ?>
	</div>
	
	<div class="row">
		<?php //echo $form->labelEx($model,'visible'); ?>
		<?php //echo $form->dropDownList($model,'visible',array('1'=>'Show','0'=>'Hide')); ?>
		<?php //echo $form->error($model,'visible'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'required');?>
		<?php echo $form->checkBox($model,'required');?>
		<?php echo $form->error($model,'required');?>
	</div>
	
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>