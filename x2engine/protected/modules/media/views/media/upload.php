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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('media', 'All Media'), 'url'=>array('index')),
	array('label'=>Yii::t('media', 'Upload')),
));
?>
<div class="page-title icon media">
<h2><?php echo Yii::t('media','Upload Media File'); ?></h2>
</div>

<?php $form=$this->beginWidget('CActiveForm', array(
   'id'=>'media-form',
   'enableAjaxValidation'=>false,
)); ?>

<div class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('media', 'Select File'); ?></span>
		</div>
		<div class="tableWrapper">
			<table>
				<tbody>
					<tr class="formSectionRow">
						<td style="background: #FAFAFA;">
							<div class="x2-file-wrapper">
							    <input type="file" class="x2-file-input" name="upload" onChange="var validName = mediaCheckName(this); if(validName) {mediaFileUpload(this.form, $(this), '<?php echo Yii::app()->createUrl('/site/tmpUpload'); ?>', '<?php echo Yii::app()->createUrl('/site/removeTmpUpload'); ?>'); }">
							    <input type="button" class="x2-button" value="<?php echo Yii::t('media', 'Choose File'); ?>">
							    <?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/loading.gif',Yii::t('app','Loading'),array('id'=>'choose-file-saving-icon', 'style'=>'position: absolute; width: 14px; height: 14px; filter: alpha(opacity=0); -moz-opacity: 0.00; opacity: 0.00;')); ?>
							    <span class="filename"></span>
							    <input type="hidden" class="temp-file-id" name="TempFileId" value="">
							</div>
							<div style="padding: 5px;">
								<span style="vertical-align: middle">
									<?php echo Yii::t('media', 'Max') .' '. Media::getServerMaxUploadSize(); ?> MB
								</span>
								<span style="vertical-align: middle; padding-left: 20px;">
									<?php echo Yii::t('media', 'Forbidden File Extensions:') . ' ' . Media::forbiddenFileTypes(); ?>
								</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('media', 'Association'); ?></span>
		</div>
		<div class="tableWrapper">
			<table>
				<tbody>
					<tr class="formSectionRow">
						<td style="width: 300px">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Association Type'); ?></label>
								<div class="formInputBox" style="width: 200px; height: auto;">
									<?php echo $form->dropDownList($model,'associationType',
										array(
											'none'=>Yii::t('actions','None'),
											'contacts'=>Yii::t('actions','Contact'),
											'opportunities'=>Yii::t('actions','Opportunity'),
											'accounts'=>Yii::t('actions','Account'),
											'bg'=>Yii::t('media', 'Background'),
										), array('onChange'=>'showAssociationAutoComplete(this)')); ?>
								</div>
							</div>
							
						</td>
					</tr>
					
					<tr class="formSectionRow">
						<td style="width: 300px">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Association Name'); ?></label>
								<div class="formInputBox" style="width: 200px; height: auto;">
									<?php
									
										// contacts association auto-complete
										$linkSource = $this->createUrl(X2Model::model('Contacts')->autoCompleteSource);
										$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
											'name'=>'auto_select',
											'source' => $linkSource,
											'options'=>array(
												'minLength'=>'2',
												'select'=>'js:function( event, ui ) {
													$("#association-id").val(ui.item.id);
													$(this).val(ui.item.value);
													return false;
												}',
											),
											'htmlOptions'=>array(
												'style'=>'display:none;',
												'id'=>'contacts-auto-select',
											),
										));
										
										// accounts association auto-complete
										$linkSource = $this->createUrl(X2Model::model('Accounts')->autoCompleteSource);
										$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
											'name'=>'auto_select',
											'source' => $linkSource,
											'options'=>array(
												'minLength'=>'2',
												'select'=>'js:function( event, ui ) {
													$("#association-id").val(ui.item.id);
													$(this).val(ui.item.value);
													return false;
												}',
											),
											'htmlOptions'=>array(
												'style'=>'display:none;',
												'id'=>'accounts-auto-select',
											),
										));
										
										// opportunities association auto-complete
										$linkSource = $this->createUrl(X2Model::model('Opportunity')->autoCompleteSource);
										$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
											'name'=>'auto_select',
											'source' => $linkSource,
											'options'=>array(
												'minLength'=>'2',
												'select'=>'js:function( event, ui ) {
													$("#association-id").val(ui.item.id);
													$(this).val(ui.item.value);
													return false;
												}',
											),
											'htmlOptions'=>array(
												'style'=>'display:none;',
												'id'=>'opportunities-auto-select',
											),
										));

										
										echo $form->hiddenField($model, 'associationId', array('id'=>'association-id'));
									?>
								</div>
							</div>
							
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('media', 'Permission'); ?></span>
		</div>
		<div class="tableWrapper">
			<table>
				<tbody>
					<tr class="formSectionRow">
						<td style="width: 300px">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Private'); ?></label>
								<div class="formInputBox" style="width: 200px; height: auto;">
									<?php echo $form->checkbox($model,'private'); ?>
								</div>
							</div>
							
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
				
	<div class="formSection showSection">
	    <div class="formSectionHeader">
	    	<span class="sectionTitle"><?php echo Yii::t('media', 'Description'); ?></span>
	    </div>
	    <div class="tableWrapper">
	    	<table>
	    		<tbody>
	    			<tr class="formSectionRow">
	    				<td style="width: 300px">
	    					<div class="formItem leftLabel">
	    						<div class="formInputBox" style="width: 550px; height: auto;">
	    							<?php echo $form->textarea($model, 'description', array('rows'=>5)); ?>
	    						</div>
	    					</div>
	    					
	    				</td>
	    			</tr>
	    		</tbody>
	    	</table>
	    </div>
	</div>
	
</div>

<?php
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton(Yii::t('media','Upload'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";
$this->endWidget();
?>

<?php
// place the saving icon over the 'Choose File' button (which starts invisible)
Yii::app()->clientScript->registerScript('savingIcon',"
$(function() {	
	initX2FileInput();
});");
?>
