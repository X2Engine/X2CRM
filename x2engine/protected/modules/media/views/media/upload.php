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




$menuOptions = array(
    'index', 'upload',
);
$this->insertMenu($menuOptions);

Yii::app()->clientScript->registerCssFile(
    Yii::app()->controller->module->assetsUrl.'/css/media.css');

?>
<div class="page-title icon media">
<h2><?php echo Yii::t('media','Upload Media File'); ?></h2>
</div>

<?php $form=$this->beginWidget('CActiveForm', array(
   'id'=>'media-form',
   'enableAjaxValidation'=>false,
)); ?>

<div id='media-form' class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('media', 'Select File'); ?></span>
		</div>
		<div class="tableWrapper">
			<table>
				<tbody>
					<tr class="formSectionRow">
						<td>
							<div class="x2-file-wrapper">
							    <input type="file" class="x2-file-input" name="upload" onChange="x2.uploadMedia(this)">
							    <input type="button" class="x2-button" value="<?php echo Yii::t('media', 'Choose File'); ?>">
                                <span class="error"></span>
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
                            <?php if ($model->hasErrors('path')) { ?>
                            <div>
                                <?php echo X2Html::fa('warning') .$form->error($model,'path', array('style'=>'display:inline-block')); ?>
                            </div>
                            <?php } ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('media', 'Title'); ?></span>
		</div>
		<div class="tableWrapper">
			<table>
				<tbody>
					<tr class="formSectionRow">
						<td style="width: 300px">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Title'); ?></label>
								<div class="formInputBox" style="width: 200px; height: auto;">
									<?php echo $form->textField($model,'name'); ?>
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
			<span class="sectionTitle"><?php echo Yii::t('media', 'Association'); ?></span>
		</div>
		<div class="tableWrapper">
			<table>
				<tbody>
					<tr class="formSectionRow">
						<td style="">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Association Type'); ?></label>
								<div class="formInputBox" style="height: auto;">
									<?php 
                                    $linkableModels = 
                                        X2Model::getModelTypesWhichSupportRelationships(true);
                                    $this->widget ('MultiTypeAutocomplete', array (
                                        'selectName' => 'Media[associationType]',
                                        'hiddenInputName' => 'Media[associationId]',
                                        'selectValue' => '',
                                        'options' => array_merge (
                                            array ('' => Yii::t('app', 'None')),
                                            array_diff_key ($linkableModels, array_flip (array (
                                                'Media',
                                                'Groups',
                                                'X2List',
                                                'Actions',
                                                'Reports',
                                            ))),
                                            array ('bg' => Yii::t('app', 'Background'))
                                        ),
                                        'staticOptions' => array (
                                            '', 'bg'
                                        ), 
                                        'htmlOptions' => array (
                                            'class' => 'media-association-type',
                                        ),
                                    ));
//                                    echo $form->dropDownList($model,'associationType',
//										array(
//											'none'=>Yii::t('actions','None'),
//											'contacts'=>Yii::t('actions','Contact'),
//											'opportunities'=>Yii::t('actions','Opportunity'),
//											'accounts'=>Yii::t('actions','Account'),
//											'bg'=>Yii::t('media', 'Background'),
//										), array('onChange'=>'showAssociationAutoComplete(this)')); ?>
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
echo '		'.CHtml::submitButton(Yii::t('media','Upload'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24, 'style' =>'display: inline-block'))."\n";
echo "	</div>\n";
$this->endWidget();
?>

<?php
// place the saving icon over the 'Choose File' button (which starts invisible)
Yii::app()->clientScript->registerScript('savingIcon',"

    x2.uploadMedia = function(elem) {
        $('#choose-file-saving-icon').css({opacity: 1.0});
        var validName = mediaCheckName(elem);
        var tmpUploadUrl = '". Yii::app()->createUrl('/site/tmpUpload') ."';
        var rmTmpUploadUrl = '". Yii::app()->createUrl('/site/removeTmpUpload') ."';
        if (validName) {
            var status = mediaFileUpload(
                elem.form, $(elem),
                tmpUploadUrl,
                rmTmpUploadUrl
            );
        }
    };

    $(function() {
	    x2.forms.initX2FileInput();
    });
");
?>
