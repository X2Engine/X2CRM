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

Yii::app()->clientScript->registerScript('avatarUploader',"function showAttach() {
	e=document.getElementById('attachments');
	if(e.style.display=='none')
		e.style.display='block';
	else
		e.style.display='none';
}
var ar_ext = ['png', 'gif', 'jpg', 'jpeg'];        // array with allowed extensions

function checkPictureExt(el, sbm) {
// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = el.value;
	var ar_name = name.split('.');

	// check the file extension
	var re = 0;
	for(var i=0; i<ar_ext.length; i++) {
		if(ar_ext[i] == ar_name[1]) {
			re = 1;
			break;
		}
	}
	// if re is 1, the extension is in the allowed list
	if(re==1) {
		// enable submit
		document.getElementById(sbm).disabled = false;
	}
	else {
		// delete the file name, disable Submit, Alert message
		el.value = '';
		document.getElementById(sbm).disabled = true;
		alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
	}
}",CClientScript::POS_HEAD);

$attributeLabels = ProfileChild::attributeLabels();
?>
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $attributeLabels['fullName']; ?></td>
		<td><b><?php echo CHtml::encode($model->fullName); ?></b></td>
		<td rowspan="6" width="25%">
			<?php
			if(isset($model->avatar) && $model->avatar!='')
				echo '<img height="180" width="180" src="'.Yii::app()->request->baseUrl.'/'.$model->avatar.'" />'; 
			else
				echo '<img height="180" width="180" src='.Yii::app()->request->baseUrl."/uploads/default.jpg".'>';
			?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['tagLine']; ?></td>
		<td><?php echo CHtml::encode($model->tagLine); ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['username']; ?></td>
		<td><b><?php echo CHtml::encode($model->username); ?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['officePhone']; ?></td>
		<td><b><?php echo CHtml::encode($model->officePhone); ?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['cellPhone']; ?></td>
		<td><b><?php echo CHtml::encode($model->cellPhone); ?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['emailAddress']; ?></td>
		<td><b><?php echo CHtml::mailto($model->emailAddress); ?></b></td>
	</tr>
	<tr>
		<td class="label"></td>
		<td></td>
		<td>
			<?php echo CHtml::form('uploadPhoto/'.$model->id,'post',array('enctype'=>'multipart/form-data')); ?>
			<?php echo CHtml::fileField('photo','',array('id'=>'photo','onchange'=>"checkPictureExt(this, 'submit')"));?><br />
			<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'submit','disabled'=>'disabled'),array('class'=>'x2-button'));?>
			<?php echo CHtml::endForm();?>
		</td>
	</tr>
</table>







