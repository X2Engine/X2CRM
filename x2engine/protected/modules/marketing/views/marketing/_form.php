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

