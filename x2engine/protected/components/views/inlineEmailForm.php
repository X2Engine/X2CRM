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

Yii::app()->clientScript->registerScript('inlineEmailEditor',"
function setupEmailEditor() {
	if($('#email-message').data('editorSetup') != true) {
		new TINY.editor.edit('teditor',{
			id:'email-message',
			// width:560,
			height:200,
			cssclass:'tinyeditor',
			controlclass:'tecontrol',
			rowclass:'teheader',
			dividerclass:'tedivider',
			controls:['bold','italic','underline','strikethrough','|','subscript','superscript','|',
					'orderedlist','unorderedlist','|','outdent','indent','|','leftalign',
					'centeralign','rightalign','blockjustify','|','undo','redo','n',
					'font','size','unformat','|','image','hr','link','unlink','|','print'],
			footer:true,
			fonts:['Verdana','Arial','Georgia','Trebuchet MS'],
			xhtml:false,
			cssfile:'".Yii::app()->theme->getBaseUrl().'/css/tinyeditor.css'."',
			// bodyid:'editor',
			footerclass:'tefooter',
			toggle:{text:'source',activetext:'wysiwyg',cssclass:'tetoggle'},
			resize:{cssclass:'teresize'}
		});
		
		$('#email-message').data('editorSetup',true);
		
		// give send-email module focus when tinyedit clicked		
		$('#email-message-box').find('iframe').contents().find('body').click(function() {
		    if(!$('#inline-email-form').find('.wide.form').hasClass('focus-mini-module')) {
		    	$('.focus-mini-module').removeClass('focus-mini-module');
		    	$('#inline-email-form').find('.wide.form').addClass('focus-mini-module');
		    }
		});
		
		$('#inline-email-form').find('iframe').attr('tabindex', 5);
	}
}
",CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('inlineEmailEditorSetup',"

if(window.hideInlineEmail)
	$('#inline-email-form').hide();
else
	setupEmailEditor();
",CClientScript::POS_READY);
?>
<div id="inline-email-top"></div>
<div id="inline-email-form">
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
		<?php $templateList = DocChild::getEmailTemplates(); ?>
		<?php $templateList = array('0'=>Yii::t('docs','Custom Message')) + $templateList; ?>
		<?php echo $form->label($model,'template', array('class'=>'x2-email-label', 'style'=>'float: none; margin-left: 10px; vertical-align: text-top;')); ?>
		<?php echo $form->dropDownList($model,'template',$templateList,array('id'=>'email-template')); ?>
	</div>
	<div class="row" id="email-message-box">
		<?php echo $form->textArea($model,'message',array('id'=>'email-message','style'=>'margin:0;padding:0;')); ?>
	</div>
	
	<div class="row" id="email-attachments">
		<div class="form" style="text-align:left; background-color: inherit">
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
	
	<div class="row buttons">
	<?php
	echo CHtml::ajaxSubmitButton(
		Yii::t('app','Send'),
		array('inlineEmail','ajax'=>1),
		array(
			'beforeSend'=>"function(a,b) { teditor.post(); $('#email-sending-icon').show(); }",
			'replace'=>'#inline-email-form',
			'complete'=>"function(response) { $('#email-sending-icon').hide(); setupEmailEditor(); updateHistory(); initX2EmailForm(); }",
		),
		array(
			'id'=>'send-email-button',
			'class'=>'x2-button highlight',
			'style'=>'margin-left:-20px;',
			'name'=>'InlineEmail[submit]',
			'onclick'=>'teditor.post();',
		)
	);
	echo CHtml::ajaxSubmitButton(
		Yii::t('app','Preview'),
		array('inlineEmail','ajax'=>1,'preview'=>1),
		array(
			'beforeSend'=>"function(a,b) { teditor.post(); $('#email-sending-icon').show(); }",
			'replace'=>'#inline-email-form',
			'complete'=>"function(response) { $('#email-sending-icon').hide(); setupEmailEditor(); initX2EmailForm(); }",
		),
		array(
			'id'=>'preview-email-button',
			'class'=>'x2-button',
			'name'=>'InlineEmail[submit]',
			'onclick'=>'teditor.post();',
		)
	);
	echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button','onclick'=>"toggleEmailForm();return false;"));
	// echo CHtml::htmlButton(Yii::t('app','Send'),array('type'=>'submit','class'=>'x2-button','id'=>'send-button','style'=>'margin-left:90px;')); ?>
	</div>
	<?php $this->endWidget(); ?>
</div>
</div>
