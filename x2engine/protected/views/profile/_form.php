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
			<?php echo $form->labelEx($model,'extension'); ?>
			<?php echo $form->textField($model,'extension',array('size'=>20,'maxlength'=>20)); ?>
			<?php echo $form->error($model,'extension'); ?>
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
			$userChoice = (Yii::app()->settings->emailUseSignature == 'user'); 
			if(!$userChoice)
				$model->emailUseSignature = Yii::app()->settings->emailUseSignature;
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
			<?php echo $form->textField($model,'googleId',array('size'=>40,'maxlength'=>250)).' <span class="x2-hint" title="This field should contain a Google Account (i.e. yourname@gmail.com) which you can use to log in to X2Engine with if Google Integration has been enabled.">[?]</span>'; ?>
			<?php echo $form->error($model,'googleId'); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="cell">
			<?php 
            echo $form->labelEx($model,'leadRoutingAvailability'); 
			echo $form->checkBox($model, 'leadRoutingAvailability');
            echo X2Html::hint (
                Yii::t('profile', 'Uncheck this box if you do no want to be automatically '.
                    'assigned new leads through lead routing.'),
                false, null, true);
            ?>
		</div>
	</div>

	<div class="row">
		<?php
		Yii::app()->clientScript->registerPackage ('ckeditor');
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
