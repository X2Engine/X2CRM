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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('media', 'All Media'), 'url'=>array('index')),
	array('label'=>Yii::t('media', 'Upload'), 'url'=>array('upload')),
	array('label'=>Yii::t('media', 'View'), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('media', 'Update')),
	array('label'=>Yii::t('media', 'Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('media','Are you sure you want to delete this item?'))),
));
?>
<div class="page-title icon media"><h2><span class="no-bold"><?php echo Yii::t('media','Update File: '); ?></span> <?php echo $model->fileName; ?></h2></div>

<?php $form=$this->beginWidget('CActiveForm', array(
   'id'=>'media-form',
   'enableAjaxValidation'=>false,
)); ?>

<?php 

$parts = explode('.',$model->fileName);			// split filename on '.'

$file = Yii::app()->file->set('uploads/'.$model->fileName);

$file_ext = strtolower($file->getExtension());	// extension is the last part

$legal_extensions = array('jpg','gif','png','bmp','jpeg','jpe');

$fileView = '';

if(file_exists("uploads/media/{$model->uploadedBy}/{$model->fileName}")) {
	$file = Yii::app()->file->set("uploads/media/{$model->uploadedBy}/{$model->fileName}");
	$file_ext = strtolower($file->getExtension());	// extension is the last part
	$fileURL = Yii::app()->request->baseUrl.'/uploads/media/'. $model->uploadedBy . '/'.urlencode($model->fileName);
	if(in_array($file_ext,$legal_extensions))
		$fileView .= CHtml::link(CHtml::image($fileURL,'',array('class'=>'attachment-img', 'style'=>'height: 100%; display: block; margin-left: auto; margin-right: auto; padding: 5px')),$fileURL);

} else if (file_exists("uploads/{$model->fileName}")) {
	$fileURL = Yii::app()->request->baseUrl.'/uploads/'.urlencode($model->fileName);
	if(in_array($file_ext,$legal_extensions))
		$fileView .= CHtml::link(CHtml::image($fileURL,'',array('class'=>'attachment-img', 'style'=>'height: 100%; display: block; margin-left: auto; margin-right: auto; padding: 5px')),$fileURL);
}
?>

<table style="width: 900px;">
	<tr>
		<?php if(!empty($fileView)) { ?>
			<td style="width: 32%;">
				<div class="formItem" style="height: 200px; border: 1px solid #CCC; background: #FAFAFA; display: table-cell; -moz-border-radius: 4px; -o-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px;">
					<?php echo $fileView; ?>
				</div>
			</td>
		<?php } ?>

		
		<td style="vertical-align: top;">
		
			<div class="x2-layout form-view" style="margin-bottom: 0;">
			
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

		
		</td>
	
	</tr>
</table>

<?php
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton(Yii::t('media','Update'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";
$this->endWidget();
?>
