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
	array('label'=>Yii::t('media', 'Upload'), 'url'=>array('upload')),
	array('label'=>Yii::t('media', 'View')),
	array('label'=>Yii::t('media', 'Update'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('media', 'Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('media','Are you sure you want to delete this item?'))),
));

?>

<h2><?php echo Yii::t('media','File: '); ?><b><?php echo $model->fileName; ?></b></h2>
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
												<?php if($model->associationType) { ?>
													<?php echo ($model->associationType == 'bg'? Yii::t('media', 'Background') : ucfirst($model->associationType)); ?>
												<?php } ?>
											</div>
										</div>
										
									</td>
								</tr>
								
								<tr class="formSectionRow">
									<td style="width: 300px">
										<div class="formItem leftLabel">
											<label><?php echo Yii::t('media', 'Association Name'); ?></label>
											<div class="formInputBox" style="width: 200px; height: auto;">
												<?php if($model->associationType && $model->associationType != 'bg') { ?>
													<?php 
														if(!empty($model->associationId) && is_numeric($model->associationId)) {
															$className = ucfirst($model->associationType);
															if(class_exists($className, false))
																$linkModel = CActiveRecord::model($className)->findByPk($model->associationId);
															if(isset($linkModel))
																echo $linkModel->name;
															else
																echo '';
														} else {
															echo '';
														}
													?>
												<?php } ?>
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
												<?php echo CHtml::checkbox('private', $model->private, array( 'onclick'=>"return false", 'onkeydown'=>"return false")); ?>
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
												<?php echo $model->description; ?>
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

<?php echo CHtml::link(Yii::t('media', 'Download File'),array('download','id'=>$model->id),array('class'=>'x2-button')); ?>

<br />
<br />
<?php $this->widget('Publisher',
	array(
		'associationType'=>'media',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName()
	)
); ?>

<?php
if(isset($_GET['history']))
	$history=$_GET['history'];
else
	$history='all';
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$this->getHistory($model, 'media'),
	'itemView'=>'application.modules.actions.views.actions._view',
	'id'=>'media-history',
	'htmlOptions'=>array('class'=>'action list-view'),
	'template'=> 
	($history=='all'?'<h3>'.Yii::t('app','History').'</h3>':CHtml::link(Yii::t('app','History'),'javascript:$.fn.yiiListView.update("media-history", {data: "history=all"})')).
	' | '.($history=='actions'?'<h3>'.Yii::t('app','Actions').'</h3>':CHtml::link(Yii::t('app','Actions'),'javascript:$.fn.yiiListView.update("media-history", {data: "history=actions"})')).
	' | '.($history=='comments'?'<h3>'.Yii::t('app','Comments').'</h3>':CHtml::link(Yii::t('app','Comments'),'javascript:$.fn.yiiListView.update("media-history", {data: "history=comments"})')).
	' | '.($history=='attachments'?'<h3>'.Yii::t('app','Attachments').'</h3>':CHtml::link(Yii::t('app','Attachments'),'javascript:$.fn.yiiListView.update("media-history", {data: "history=attachments"})')).
	'</h3>{summary}{sorter}{items}{pager}',
));

?>