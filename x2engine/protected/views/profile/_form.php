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
				'user'=>Yii::t('profile','Use my signature'),
				// 'group'=>Yii::t('admin','Use group signature'),
				'admin'=>Yii::t('profile','Use default'),
			),array('disabled'=>($userChoice? null : 'disabled'))); ?>
			<?php echo $form->error($model,'emailUseSignature'); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'googleId'); ?>
			<?php echo $form->textField($model,'googleId',array('size'=>40,'maxlength'=>250)).' <span class="x2-hint" title="This field should contain a Google Account (i.e. yourname@gmail.com) which you can use to log in to X2CRM with if Google Integration has been enabled.">[?]</span>'; ?>
			<?php echo $form->error($model,'googleId'); ?>
		</div>
	</div>
	
	<div class="row">
		<?php
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
		$notNullAttributes = array_filter($model->attributes,function($a){return !empty($a);});
		$insertableAttributes = array();
		foreach($notNullAttributes as $attr=>$value) {
			$insertableAttributes[$model->getAttributeLabel($attr)] = $value;
		}
		$insertableAttributes = array(Yii::t('app', 'Profile') => $insertableAttributes);
		Yii::app()->clientScript->registerScript('setInsertableAttributes', 'x2.insertableAttributes = '.CJSON::encode($insertableAttributes).';', CClientScript::POS_HEAD);
		Yii::app()->clientScript->registerScript('setupEmailSignatureForm', '
			CKEDITOR.replace("email-signature",{"height":125,"width":725});');
		?>
		<?php echo $form->labelEx($model,'emailSignature'); ?>
		<?php echo $form->textArea($model,'emailSignature',array('id'=>'email-signature','style'=>'max-width:600px; max-height:400px;')); ?>
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