<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'profile-form',
	'enableAjaxValidation'=>false,
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br />

	<?php echo $form->errorSummary($model); ?>

	<div class="top row">
		<?php echo $form->labelEx($model,'tagLine'); ?>
		<?php echo $form->textField($model,'tagLine',array('size'=>50,'maxlength'=>250)); ?>
		<?php echo $form->error($model,'tagLine'); ?>
	</div>

	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'officePhone'); ?>
			<?php echo $form->textField($model,'officePhone',array('size'=>20,'maxlength'=>20)); ?>
			<?php echo $form->error($model,'officePhone'); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'cellPhone'); ?>
			<?php echo $form->textField($model,'cellPhone',array('size'=>20,'maxlength'=>20)); ?>
			<?php echo $form->error($model,'cellPhone'); ?>
		</div>
	</div>
	<div class="row">

		<div class="cell">
			<?php echo $form->labelEx($model,'emailAddress'); ?>
			<?php echo $form->textField($model,'emailAddress',array('size'=>40,'maxlength'=>40)); ?>
			<?php echo $form->error($model,'emailAddress'); ?>
		</div>
		<div class="cell">
			<?php
			$userChoice = (Yii::app()->params->admin->emailUseSignature == 'user'); 
			if(!$userChoice)
				$model->emailUseSignature = Yii::app()->params->admin->emailUseSignature;
			?>
			<?php echo $form->labelEx($model,'emailUseSignature'); ?>
			<?php echo $form->dropDownList($model,'emailUseSignature',array(
				'none'=>Yii::t('admin','None'),
				'user'=>Yii::t('admin','Use my signature'),
				// 'group'=>Yii::t('admin','Use group signature'),
				'admin'=>Yii::t('admin','Use default'),
			),array('disabled'=>($userChoice? null : 'disabled'))); ?>
			<?php echo $form->error($model,'emailUseSignature'); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'googleId'); ?>
			<?php echo $form->textField($model,'googleId',array('size'=>40,'maxlength'=>250)); ?>
			<?php echo $form->error($model,'googleId'); ?>
		</div>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'emailSignature'); ?>
		<?php echo $form->textArea($model,'emailSignature',array('rows'=>5, 'cols'=>50)); ?>
		<?php echo $form->error($model,'emailSignature'); ?>
	</div>


	<div class="row">
		<?php echo $form->labelEx($model,'notes'); ?>
		<?php echo $form->textArea($model,'notes',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'notes'); ?>
	</div>
    
    <div class="row">
		<?php echo $form->labelEx($model,'address'); ?>
		<?php echo $form->textArea($model,'address',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'address'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->