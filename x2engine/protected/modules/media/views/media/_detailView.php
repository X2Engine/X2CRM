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
					$association = X2Model::getAssociationModel($model->associationType,$model->associationId);
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
		<td><b><?php echo Formatter::formatLongDateTime($model->createDate); ?></td>
		<td class="label" width="15%"><label><?php echo $attributeLabels['lastUpdated']; ?></label></td>
		<td><b><?php echo Formatter::formatLongDateTime($model->lastUpdated); ?></td>
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