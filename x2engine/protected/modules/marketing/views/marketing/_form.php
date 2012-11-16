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



Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');

$insertableAttributes = array();
foreach(CActiveRecord::model('Contacts')->attributeLabels() as $fieldName => $label)
	$insertableAttributes[$label] = '{'.$fieldName.'}';

Yii::app()->clientScript->registerScript('editorSetup','

x2.insertableAttributes = '.CJSON::encode(array(Yii::t('contacts','Contact Attributes')=>$insertableAttributes)).';

$("#Campaign_content").parent()
	.css({width:"",height:""})
	.removeClass("formInputBox")
	.closest(".formItem")
	.removeClass("formItem")
	.css("clear","both")
	.find("label").remove();

if(window.emailEditor)
	window.emailEditor.destroy(true);
window.emailEditor = createCKEditor("Campaign_content",{tabIndex:5,insertableAttributes:x2.insertableAttributes});
	
setupEmailAttachments("campaign-attachments");

$("#campaign-attachments-wrapper").qtip({content: "Drag files from the Media Widget here."});

',CClientScript::POS_READY);

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'campaign-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('onsubmit'=>'editor.post();')
));

$this->renderPartial('application.components.views._form', array('model'=>$model,'users'=>User::getNames(),'form'=>$form, 'modelName'=>'Campaign'));
?>

<h2><?php echo Yii::t('app','Attachments'); ?></h2>

<div id="campaign-attachments-wrapper" class="x2-layout form-view">
<div class="formSection showSection">
	<div class="formSectionHeader">
		<span class="sectionTitle"><?php echo Yii::t('app','Attachments'); ?></span>
	</div>
	<div id="campaign-attachments" class="tableWrapper" style="min-height: 100px; padding: 5px;">
		<?php $attachments = $model->attachments; ?>
		<?php if($attachments) { ?>
			<?php foreach($attachments as $attachment) { ?>
				<?php $media = $attachment->mediaFile; ?>
				<?php if($media && $media->fileName) { ?>
					<div style="font-weight: bold;">
						<span class="filename"><?php echo $media->fileName; ?></span>
						<input type="hidden" value="<?php echo $media->id; ?>" name="AttachmentFiles[id][]" class="AttachmentFiles">
						<span class="remove"><a href="#">[x]</a></span>
					</div>
				<?php } ?>
			<?php } ?>
		<?php } ?>
		<div class="next-attachment" style="font-weight: bold;">
			<span class="filename"></span>
			<span class="remove"></span>
		</div>
	</div>
</div>
</div>

<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>

<?php $this->endWidget(); ?>
