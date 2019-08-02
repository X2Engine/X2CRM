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
    'index', 'upload', 'view', 'edit', 'delete',
);
$this->insertMenu($menuOptions, $model);

?>
<div class="page-title icon media"><h2><span class="no-bold"><?php echo Yii::t('media','Update File: '); ?></span> <?php echo $model->renderAttribute (($model->drive || !empty($model->name))? "name" : "fileName"); ?></h2></div>

<?php $form=$this->beginWidget('CActiveForm', array(
   'id'=>'media-form',
   'enableAjaxValidation'=>false,
)); ?>

<?php
Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/view.css');
Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/update.css');
$fileAssoc = $model->associationType;
?>
			<div class="x2-layout form-view media-tray" style="margin-bottom: 0;">
                <?php if($model->fileExists() && $model->isImage()){ ?>
                <div class='column'>
                <?php
                }
                ?>
                <div class='x2-layout-island'>
				<?php if (! $model->drive) { ?>
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
												<?php echo $form->textField($model, 'name'); ?>
											</div>
										</div>

									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php } ?>

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
												<?php 

													$displayArray=array(
														'none'=>Yii::t('actions','None'),
														'contacts'=>Yii::t('actions','Contact'),
														'opportunities'=>Yii::t('actions','Opportunity'),
														'accounts'=>Yii::t('actions','Account'),
														'bg'=>Yii::t('media', 'Background'),
														// 'products'=>Yii::t('media', 'Product'),
														'docs'=>Yii::t('media','Doc'),
														'theme'=>Yii::t('media','Theme'));

													if(!isset($displayArray[$fileAssoc])){
														$selected = $displayArray['none'];
													}
													else {
														$selected = $displayArray[$fileAssoc];
													}

													echo $form->dropDownList($model,'associationType',
														$displayArray,
													 array('onChange'=>'showAssociationAutoComplete(this)',
															'options' => 
														array($selected=>array('selected'=>true))
													)); ?>
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
												<?php echo $form->checkbox($model, 'private'); ?>
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
											<div class="formInputBox" style="width: 100%; height: auto;">
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
                <?php if($model->fileExists() && $model->isImage()){ ?>
                </div>
                <?php
                }
                ?>
                <?php
                if ($model->isImage () && $model->fileExists ()) {
                    ?>
                    <div class='column'>
                    <?php
                    echo $model->renderAttribute ('image'); 
                    ?>
                    </div>
                    <?php
                }
                ?>
			</div>



<?php
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton(Yii::t('media','Update'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";
$this->endWidget();

Yii::app()->clientscript->registerScript("AutoComplete", "
	$(function(){
		$('#Media_associationType').change();
	});
");
?>
