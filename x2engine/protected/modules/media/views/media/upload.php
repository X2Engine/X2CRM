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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('media', 'All Media'), 'url'=>array('index')),
	array('label'=>Yii::t('media', 'Upload')),
));
?>

<h2><?php echo Yii::t('media','Upload Media File: '); ?><b><?php echo $model->fileName; ?></b></h2>


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
							    <input type="file" class="x2-file-input" name="upload" onChange="var validName = mediaCheckName(this); if(validName) {mediaFileUpload(this.form, $(this), '<?php echo Yii::app()->createUrl('site/tmpUpload'); ?>', '<?php echo Yii::app()->createUrl('site/removeTmpUpload'); ?>'); }">
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
										$linkSource = $this->createUrl(CActiveRecord::model('Contacts')->autoCompleteSource);
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
										$linkSource = $this->createUrl(CActiveRecord::model('Accounts')->autoCompleteSource);
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
										$linkSource = $this->createUrl(CActiveRecord::model('Opportunity')->autoCompleteSource);
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