<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/
 
$attributeLabels = MediaChild::attributeLabels();

$parts = explode('.',$model->fileName);			// split filename on '.'
$file_ext = strtolower($parts[count($parts)-1]);	// extension is the last part

$legal_extensions = array('jpg','gif','png','bmp','jpeg','jpe');

$fileView = '';
if(in_array($file_ext,$legal_extensions))
	$fileView .= CHtml::image(Yii::app()->request->baseUrl.'/uploads/'.urlencode($model->fileName),'',array('class'=>'attachment-img'));
?>
<div class="form no-border" style="margin:0;">
<table class="details">
	<tr>
		<td class="label">
			<label><?php echo $attributeLabels['fileName']; ?></label>
		</td>
		<td colspan="3">
			<?php echo $model->fileName; ?>
		</td>
	</tr><?php if(!empty($fileView)) { ?>
	<tr>
		<td class="label"></td>
		<td colspan="3">
			<?php echo $fileView; ?>
		</td>
	</tr><?php } ?>
	<tr>
		<td class="label"><label><?php echo ucwords($model->associationType); ?></label></td>
		<td colspan="3"><?php 
			if($model->associationType!='feed')
				echo CHtml::link($association->name,array($model->associationType.'/view','id'=>$model->associationId));
			else
				echo CHtml::link(Yii::t('social','Feed Post'),array('profile/'.$model->associationId));
		//echo ucwords($model->associationType); ?></td>
	</tr>
	<tr>
		<td class="label" width="20%"><label><?php echo $attributeLabels['uploadedBy']; ?></label></td>
		<td width="25%"><?php echo $model->uploadedBy; ?></td>
		<td class="label" width="15%"><label><?php echo $attributeLabels['createDate']; ?></label></td>
		<td><b><?php echo date('Y-m-d',$model->createDate); ?></b> <?php echo date('g:ia',$model->createDate); ?></td>
	</tr>
</table>
</div>
<?php echo CHtml::link('Download file',array('download','id'=>$model->id),array('class'=>'x2-button')); ?>
<?php if($file_ext == 'pdf') { ?>
<div class="form">
	<iframe width="565" height="550" src="<?php echo Yii::app()->request->baseUrl.'/uploads/'.urlencode($model->fileName); ?>">
</div>
<?php } ?>