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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 
$attributeLabels = $model->attributeLabels();

$parts = explode('.',$model->fileName);			// split filename on '.'

$file = Yii::app()->file->set('uploads/'.$model->fileName);

$file_ext = strtolower($file->getExtension());	// extension is the last part

$legal_extensions = array('jpg','gif','png','bmp','jpeg','jpe');

$fileView = '';
$fileURL = Yii::app()->request->baseUrl.'/uploads/'.urlencode($model->fileName);
if(in_array($file_ext,$legal_extensions))
	$fileView .= CHtml::link(CHtml::image($fileURL,'',array('class'=>'attachment-img')),$fileURL);
?>
<div class="form no-border" style="margin:0;">
<table class="details">
	<?php if(!empty($fileView)) { ?>
		<tr>
			<td colspan="4">
				<?php echo $fileView; ?>
			</td>
		</tr>
	<?php } ?>
	<tr>
		<td class="label"><label><?php echo $attributeLabels['associationType']; ?></label></td>
		<td colspan="3">
			<?php 
				if(isset($model->associationId) && $model->associationId != null) {
					$association = $this->getAssociationModel($model->associationType,$model->associationId);
					echo CHtml::link($association->name,array($model->associationType.'/view','id'=>$model->associationId));
				}
			?>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><label><?php echo $attributeLabels['uploadedBy']; ?></label></td>
		<td width="25%"><?php echo User::getUserLinks($model->uploadedBy); ?></td>
		<td class="label" width="15%"><label><?php echo $attributeLabels['private']; ?></label></td>
		<td><b><?php echo CHtml::checkBox('private', $model->private, array('onclick'=>'return false;', 'onkeydown'=>'return false;')); ?></td>
	</tr>
	<tr>
		<td class="label" width="15%"><label><?php echo $attributeLabels['createDate']; ?></label></td>
		<td><b><?php echo $this->formatLongDateTime($model->createDate); ?></td>
		<td class="label" width="15%"><label><?php echo $attributeLabels['lastUpdated']; ?></label></td>
		<td><b><?php echo $this->formatLongDateTime($model->lastUpdated); ?></td>
	</tr>
</table>
</div>
<?php echo CHtml::link('Download file',array('download','id'=>$model->id),array('class'=>'x2-button')); ?>
<?php /* if($file_ext == 'pdf') { ?>
<div class="form">
	<iframe width="565" height="550" src="<?php echo Yii::app()->request->baseUrl.'/uploads/'.urlencode($model->fileName); ?>">
</div>
<?php }  */
 ?>