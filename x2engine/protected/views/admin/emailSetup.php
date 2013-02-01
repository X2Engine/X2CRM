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

Yii::app()->clientScript->registerScript('toggleAuthInfo',"
	$('#Admin_emailUseAuth').change(function() {
		if(($(this).val() == 'admin' && $('#auth-info').is(':hidden'))
			|| ($(this).val() != 'admin' && $('#auth-info').is(':visible'))) {
			
			$('#auth-info').animate({
				opacity: 'toggle',
				height: 'toggle'
			}, 400);
		}
	});
	
	$('#Admin_emailType').change(function() {
		if(($(this).val() == 'smtp' && $('#server-info').is(':hidden'))
			|| ($(this).val() != 'smtp' && $('#server-info').is(':visible'))) {
			
			$('#server-info').animate({
				opacity: 'toggle',
				height: 'toggle'
			}, 400);
		}
	});
	$('#Admin_emailUseSignature').change(function() {
		if(($(this).val() == 'admin' && $('#signature-box').is(':hidden'))
			|| ($(this).val() != 'admin' && $('#signature-box').is(':visible'))) {
			
			$('#signature-box').animate({
				opacity: 'toggle',
				height: 'toggle'
			}, 400);
		}
	});
	
	$('#email-setup input, #email-setup select, #email-setup textarea').change(function(){
		$('#save-button').addClass('highlight'); //css('background','yellow');
	});
	
",CClientScript::POS_READY);
?>
<div class="span-16">
<div class="page-title"><h2><?php echo Yii::t('admin','Email Server Configuration'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','Ready to send email? We need some information about your mail server.'); ?>
<br><br>
<?php
$form=$this->beginWidget('CActiveForm', array(
		'id'=>'email-setup',
		'enableAjaxValidation'=>false,
	));
?>
	<h4><?php echo Yii::t('admin','Outbound Email Server'); ?></h4>
	<div class="row">
		<div class="cell" style="width:310px;">
			<?php echo $form->labelEx($model,'emailType'); ?>
			<?php echo $form->dropDownList($model,'emailType',array(
				'mail'=>Yii::t('admin','PHP Mail'),
				'sendmail'=>Yii::t('admin','Sendmail'),
				'qmail'=>Yii::t('admin','Qmail'),
				'smtp'=>Yii::t('admin','SMTP'),
			));
			//echo $form->error($actionModel,'priority'); ?>
		</div>
		<div class="cell">
			<?php //echo CHtml::button(Yii::t('app','Send test email'),array('class'=>'x2-button','style'=>'margin-top:16px;'))."\n";?>
		</div>
	</div>
	<div id="server-info"<?php if($model->emailType != 'smtp') echo ' style="display:none;"'; ?>>
		<div class="row">
			<div class="cell">
				<?php echo $form->labelEx($model,'emailHost'); ?>
				<?php echo $form->textField($model,'emailHost',array('size'=>30)); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($model,'emailPort'); ?>
				<?php echo $form->textField($model,'emailPort',array('style'=>'width:40px;','maxlength'=>5)); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($model,'emailSecurity'); ?>
				<?php echo $form->dropDownList($model,'emailSecurity',array(
					''=>Yii::t('admin','None'),
					'tls'=>Yii::t('admin','TLS'),
					'ssl'=>Yii::t('admin','SSL'),
				)); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($model,'emailUseAuth'); ?>
				<?php echo $form->dropDownList($model,'emailUseAuth',array(
						'none'=>Yii::t('admin','None'),
						// 'user'=>Yii::t('admin','User account'),
						'admin'=>Yii::t('admin','Global account'),
					)); ?>
			</div>
		</div>
		<div class="row" id="auth-info"<?php if($model->emailUseAuth != 'admin') echo ' style="display:none;"'; ?>>
			<div class="cell">
				<?php echo $form->labelEx($model,'emailUser'); ?>
				<?php echo $form->textField($model,'emailUser'); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($model,'emailPass'); ?>
				<?php echo $form->passwordField($model,'emailPass'); ?>
			</div>
		</div>
	</div>
	<br>
	<h4><?php echo Yii::t('admin','Bulk Email Settings'); ?></h4>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'emailFromName'); ?>
			<?php echo $form->textField($model,'emailFromName',array('size'=>30)); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'emailFromAddr'); ?>
			<?php echo $form->textField($model,'emailFromAddr',array('size'=>40)); ?>
		</div>
	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'emailBatchSize'); ?>
			<?php echo $form->textField($model,'emailBatchSize',array('size'=>10)); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'emailInterval'); ?>
			<?php echo $form->textField($model,'emailInterval',array('size'=>10)); ?>
		</div>
	</div>
	<br>
	<div class="row">
		<?php echo $form->labelEx($model,'emailUseSignature'); ?>
		<?php echo $form->dropDownList($model,'emailUseSignature',array(
				''=>Yii::t('admin','None'),
				'user'=>Yii::t('admin','User\'s Choice'),
				// 'group'=>Yii::t('admin','Group signature'),
				'admin'=>Yii::t('admin','Default Signature'),
			)); ?>
	</div>
	<div class="row" id="signature-box"<?php if($model->emailUseSignature != 'admin') echo ' style="display:none;"'; ?>>
		<?php echo $form->labelEx($model,'emailSignature'); ?>
		<?php echo $form->textArea($model,'emailSignature',array('style'=>'width:490px;height:80px;')); ?>
		<br>
		<?php echo Yii::t('admin','You can use the following variables in this template: {first}, {last}, {phone} and {email}.'); ?>
	</div>

	<br>
	<h4><?php echo Yii::t('admin','Service Case Email Settings'); ?></h4>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'serviceCaseFromEmailName'); ?>
			<?php echo $form->textField($model,'serviceCaseFromEmailName',array('size'=>30)); ?>
		</div>
		<div class="cell">
			<?php echo $form->labelEx($model,'serviceCaseFromEmailAddress'); ?>
			<?php echo $form->textField($model,'serviceCaseFromEmailAddress',array('size'=>40)); ?>
		</div>
	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'serviceCaseEmailSubject'); ?>
			<?php echo $form->textField($model,'serviceCaseEmailSubject',array('size'=>30)); ?>
		</div>
	</div>
	<div class="row">
		<div class="cell">
			<?php echo $form->labelEx($model,'serviceCaseEmailMessage'); ?>
			<?php echo $form->textArea($model,'serviceCaseEmailMessage',array('style'=>'width:490px;height:80px;')); ?>
			<br>
			<?php echo Yii::t('admin','You can use the following variables in this template: {first}, {last}, {phone}, {email}, {description}, and {case}.'); ?>
		</div>
	</div>
	<br>
	
<?php //echo $form->labelEx($admin,'chatPollTime'); ?>
<?php


// echo $form->textField($admin,'chatPollTime',array('id'=>'chatPollTime')); ?>

<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button'))."\n";?>
<?php $this->endWidget();?></div>
</div>
