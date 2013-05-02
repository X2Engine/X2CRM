<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
		<?php
		// Determine the available mail sending methods:
		$can = array(
			'sendmail' => @is_executable('/usr/sbin/sendmail'),
			'qmail' => @is_executable('/var/qmail/bin/sendmail')
		);
		$mailMethods = array();
		if((bool) @ini_get('sendmail_path'))
			if(@is_executable(@ini_get('sendmail_path')))
				$mailMethods['mail'] = Yii::t('admin','PHP Mail');
		if($can['sendmail'])
			$mailMethods['sendmail'] = Yii::t('admin','Sendmail');
		if($can['qmail'])
			$mailMethods['qmail'] = Yii::t('admin','Qmail');
		$mailMethods['smtp'] = Yii::t('admin','SMTP');
		?>
		<div class="cell" style="width:310px;">
			<?php echo $form->labelEx($model,'emailType'); ?>
			<?php echo $form->dropDownList($model,'emailType',$mailMethods);
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
