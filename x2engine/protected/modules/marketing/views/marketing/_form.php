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



Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');

$insertableAttributes = array();
foreach(X2Model::model('Contacts')->attributeLabels() as $fieldName => $label)
	$insertableAttributes[$label] = '{'.$fieldName.'}';

Yii::app()->clientScript->registerScript('editorSetup','

/*
Instantiate CKEditor for campaign text field/email template.
Parameters:
	suppressInsertableAttrs - if true, instantiate editor without insertable attributes
*/
function setUpTextEditor (suppressInsertableAttrs) {
	if(window.emailEditor) {
		window.emailEditor.updateElement ();
		window.emailEditor.destroy(true);
	}

	if (suppressInsertableAttrs) {
		window.emailEditor = createCKEditor("Campaign_content",{
			tabIndex:5,
			fullPage:true
		},function(){
			window.emailEditor.document.on("keyup",function(){ 
                $("#Campaign_templateDropdown").val("0"); 
            });
		});
	} else {
		window.emailEditor = createCKEditor("Campaign_content",{
			tabIndex:5,
			insertableAttributes:x2.insertableAttributes,
			fullPage:true
		},function(){
			window.emailEditor.document.on("keyup",function(){ 
                $("#Campaign_templateDropdown").val("0"); 
            });
		});
	}

}

(function campaignsMain () {
	$("#Campaign_content").parent()
		.css({width:"",height:""})
		.removeClass("formInputBox")
		.closest(".formItem")
		.removeClass("formItem")
		.css("clear","both")
		.find("label").remove();

	x2.insertableAttributes = '.CJSON::encode(array(Yii::t('contacts','Contact Attributes')=>$insertableAttributes)).';

	setupEmailAttachments("campaign-attachments");

	$("#Campaign_templateDropdown").change(function() {
		var template = $(this).val();
		if(template != "0") {
			
			$.ajax({
				url:yii.baseUrl+"/index.php/docs/fullView/"+template+"?json=1",
				type:"GET",
				dataType:"json"
			}).done(function(data) {
				window.emailEditor.setData(data.body);
				$(\'input[name="Campaign[subject]"]\').val(data.subject);
				window.emailEditor.document.on("keyup",function(){ $("#Campaign_templateDropdown").val("0"); });
			});
		}
	});
	
	var currCampaignType = "";
	$("#Campaign_type").change(function(){
	
		if($(this).val() == "Email") {
			$("#Campaign_sendAs").parents(".formItem").fadeIn();
			$("#Campaign_subject").parents(".formItem").fadeIn();
			$("#Campaign_templateDropdown").parents(".formItem").fadeIn();
			$("#attachments-container").show ();
		} else {
			$("#Campaign_sendAs").parents(".formItem").fadeOut();
			$("#Campaign_subject").parents(".formItem").fadeOut();
			$("#Campaign_templateDropdown").parents(".formItem").fadeOut();
			$("#attachments-container").hide ();
		}
	
		// give x2layout section an appropriate title, hide/show insertable attributes
		var campaignType = $("#Campaign_type").val ();	
		switch (campaignType) {
			case "Email":
				var campaignTypeChanged = "Email" !== currCampaignType;
				currCampaignType = campaignType;
				if (campaignTypeChanged) setUpTextEditor (false);
				break;
			case "Call List":
			case "Physical Mail":
				var templateTypeChanged = currCampaignType !== "Email" && campaignType === "Email";
				currCampaignType = campaignType;
				if (campaignTypeChanged) setUpTextEditor (false);
				break;
		}
	
	});
	
	$("#Campaign_type").each(function(){
		if($(this).val() != "Email")
			$("#Campaign_sendAs").parents(".formItem").hide();
	});
	
	$("#Campaign_type").change ();

}) ();

',CClientScript::POS_READY);

$this->renderPartial('application.components.views._form', array(
	'model'=>$model,
	'users'=>User::getNames(),
	'form'=>$form,
	'modelName'=>'Campaign',
	'specialFields'=>array(
		'template'=>CHtml::activeDropDownList(
			$model,'template',array('0'=>Yii::t('docs','Custom Message')) + Docs::getEmailTemplates(),
			array(
				'title'=>$model->getAttributeLabel('template'),
				'id'=>'Campaign_templateDropdown'
			)
		)
	)
));
?>


<div id="attachments-container">
	<h2><?php echo Yii::t('app','Attachments'); ?></h2>
	
	<div id="campaign-attachments-wrapper" class="x2-layout form-view x2-hint"
	 title="<?php echo addslashes (Yii::t('app', 'Drag files from the Files Widget here.')); ?>">

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
								<input type="hidden" value="<?php echo $media->id; ?>" 
								 name="AttachmentFiles[id][]" class="AttachmentFiles">
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
</div>

<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>

