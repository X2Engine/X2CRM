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


?>
	<div class="form email-status"  id="inline-email-status" style="display:none"></div>
<div id="inline-email-top"></div>

<div id="inline-email-form">
	<span id="template-change-confirm" style="display:none"><?php echo Yii::t('app','Note: you have entered text into the email that will be lost. Are you sure you want to continue?'); ?></span>
<?php
/* if(isset($preview) && !empty($preview)) { ?>
<div class="form">
	<?php echo $preview; ?>
</div>
<?php
} */


echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/loading.gif',Yii::t('app','Loading'),array('id'=>'email-sending-icon'));
$emailSent = false;

if(!empty($model->status)) {
	$index = array_search('200',$model->status);
	if($index !== false) {
		unset($model->status[$index]);
		$model->message = '';
		$signature = Yii::app()->params->profile->getSignature(true);
		$model->message = '<font face="Arial" size="2">'.(empty($signature)? '' : '<br><br>' . $signature).'</font>';
		$model->subject = '';
		$attachments = array();
		$emailSent = true;
	}
	echo '<div class="form email-status">';
	foreach($model->status as &$status_msg) echo $status_msg." \n";
	echo '</div>';
}
?>


<div id="email-mini-module" class="wide form<?php if($emailSent) echo ' hidden'; ?>">
	<?php $form = $this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'method'=>'post',
	));
	echo $form->hiddenField($model,'modelId');
	echo $form->hiddenField($model,'modelName');
	?>
	<div class="row">
		<div id="inline-email-errors" class="error" style="display:none"></div>
		<?php echo $form->errorSummary($model, Yii::t('app', "Please fix the following errors:"), null, array('style'=>'margin-bottom: 5px;')); ?>
	</div>
	<div class="row">
		<?php //echo $form->error($model,'to'); ?>
		<?php echo $form->label($model,'to', array('class'=>'x2-email-label')); ?>
		<?php echo $form->textField($model,'to',array('id'=>'email-to','style'=>'width:400px;', 'tabindex'=>'1'));?> 
		<a href="javascript:void(0)" id="cc-toggle"<?php if(!empty($model->cc)) echo ' style="display:none;"'; ?>>[cc]</a> 
		<a href="javascript:void(0)" id="bcc-toggle"<?php if(!empty($model->bcc)) echo ' style="display:none;"'; ?>>[bcc]</a>
	</div>
	<div class="row" id="cc-row"<?php if(empty($model->cc)) echo ' style="display:none;"'; ?>>
		<?php //echo $form->error($model,'to'); ?>
		<?php echo $form->label($model,'cc', array('class'=>'x2-email-label')); ?>
		<?php echo $form->textField($model,'cc',array('id'=>'email-cc', 'tabindex'=>'2')); ?>
	</div>
	<div class="row" id="bcc-row"<?php if(empty($model->bcc)) echo ' style="display:none;"'; ?>>
		<?php //echo $form->error($model,'to'); ?>
		<?php echo $form->label($model,'bcc', array('class'=>'x2-email-label')); ?>
		<?php echo $form->textField($model,'bcc',array('id'=>'email-bcc', 'tabindex'=>'3')); ?>
	</div>
	<div class="row">
		<?php echo $form->label($model,'subject', array('class'=>'x2-email-label')); ?>
		<?php echo $form->textField($model,'subject', array('style'=>'width: 265px;', 'tabindex'=>'4')); ?>
		<?php $templateList = Docs::getEmailTemplates($type); ?>
		<?php $templateList = array('0'=>Yii::t('docs','Custom Message')) + $templateList; ?>
		<?php echo $form->label($model,'template', array('class'=>'x2-email-label', 'style'=>'float: none; margin-left: 10px; vertical-align: text-top;')); ?>
		<?php echo $form->dropDownList($model,'template',$templateList,array('id'=>'email-template')); ?>
	</div>
	<div class="row" id="email-message-box">
		<?php echo $form->textArea($model,'message',array('id'=>'email-message','style'=>'margin:0;padding:0;')); ?>
	</div>
	
	<div class="row" id="email-attachments">
		<div class="form" style="text-align:left;background:none;overflow:visible;">
			<b><?php echo Yii::t('app','Attach a File'); ?></b><br />
			<?php if(isset($attachments)) { // is this a refreshed form with previous attachments? ?>
				<?php foreach($attachments as $attachment) { ?>
					<div>
						<span class="filename"><?php echo $attachment['filename']; ?></span>
						<span class="remove"><a href="#">[x]</a></span>
						<span class="error"></span>
						<input type="hidden" name="AttachmentFiles[temp][]" value="<?php echo ($attachment['temp']? "true" : "false"); ?>">
						<input type="hidden" name="AttachmentFiles[id][]" class="AttachmentFiles" value="<?php echo $attachment['id']; ?>">
					</div>
				<?php } ?>
			<?php } ?>
			<div class="next-attachment">
				<?php //echo CHtml::fileField('upload','',array('onchange'=>'checkName(this, "#submitAttach"); if($("#submitAttach").attr("disabled") != "disabled") {fileUpload(this.form, $(this), "'. Yii::app()->createUrl('site/tmpUpload') .'", "'. Yii::app()->createUrl('site/removeTmpUpload') .'"); }')); ?>
				<span class="upload-wrapper">
					<span class="x2-file-wrapper">
					    <input type="file" class="x2-file-input" name="upload" onChange="checkName(this, '#submitAttach'); if($('#submitAttach').attr('disabled') != 'disabled') {fileUpload(this.form, $(this), '<?php echo Yii::app()->createUrl('site/tmpUpload'); ?>', '<?php echo Yii::app()->createUrl('site/removeTmpUpload'); ?>'); }">
					    <input type="button" class="x2-button" value="Choose File">
					    <?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/loading.gif',Yii::t('app','Loading'),array('id'=>'choose-file-saving-icon', 'style'=>'position: absolute; width: 14px; height: 14px; filter: alpha(opacity=0); -moz-opacity: 0.00; opacity: 0.00;')); ?>
					</span>
					<span style="vertical-align: middle">
					    <?php echo Yii::t('media', 'Max') .' '. Media::getServerMaxUploadSize(); ?> MB
					</span>
				</span>
				<span class="filename"></span>
				<span class="remove"></span>
				<span class="error"></span>
			</div>
		</div>
	</div>
	
	<div class="row buttons" style="padding-left:0;">
	<?php

	
	echo CHtml::ajaxSubmitButton(
		Yii::t('app','Send'),
		array('inlineEmail','ajax'=>1),
		array(
			'beforeSend'=>"setInlineEmailFormLoading",
			'dataType' => 'json',
			'success'=>"handleInlineEmailActionResponse",
		),
		array(
			'id'=>'send-email-button',
			'class'=>'x2-button highlight',
			// 'style'=>'margin-left:-20px;',
			'name'=>'InlineEmail[submit]',
			'onclick'=>'window.inlineEmailEditor.updateElement();',
		)
	);
	
	// if(is_file(__DIR__.'/inlineEmailForm_pro.php'))
		// include('inlineEmailForm_pro.php');
		
	echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button right','onclick'=>"toggleEmailForm();return false;")); ?>
	</div>
	<?php $this->endWidget(); ?>
</div>
</div>
