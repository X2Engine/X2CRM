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

Yii::app()->clientScript->registerScript('avatarUploader',"
function showAttach() {
	e=document.getElementById('attachments');
	if(e.style.display=='none')
		e.style.display='block';
	else
		e.style.display='none';
}
var legal_extensions = ['png','gif','jpg','jpe','jpeg'];        // array with allowed extensions

function checkPictureExt(el, sbm) {
// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = el.value;
	var ar_name = name.split('.');
	ar_ext = ar_name[ar_name.length - 1].toLowerCase();
	// alert(ar_ext);
	// check the file extension
	var re = 0;
	for(i in legal_extensions) {
		if(legal_extensions[i] == ar_ext) {
			re = 1;
			break;
		}
	}
	// if re is 1, the extension is in the allowed list
	if(re==1) {
		// enable submit
		$(sbm).removeAttr('disabled');
		$('#photo-form').submit();
	}
	else {
		// delete the file name, disable Submit, Alert message
		el.value = '';
		$(sbm).attr('disabled','disabled');
		alert('\".'+ ar_ext+ '\" is not an file type allowed for upload');
	}


}

$(function() {
	$('.file-wrapper').qtip({
		content: 'Choose Profile picture.'
	});
});

",CClientScript::POS_HEAD);

$attributeLabels = $model->attributeLabels();
?>
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $attributeLabels['fullName']; ?></td>
		<td><b><?php echo CHtml::encode($model->fullName); ?></b></td>
		<td rowspan="8" width="15%" style="text-align:center;">
			<span class="file-wrap3per">
			<?php
			// getimagesize()

			if(isset($model->avatar) && $model->avatar!='' && file_exists($model->avatar)) {

				$imgSize = @getimagesize($model->avatar);
				// die( var_dump($imgSize));
				if(!$imgSize)
					$imgSize = array(45,45);

				$maxDimension = max($imgSize[0],$imgSize[1]);

				$scaleFactor = 1;
				if($maxDimension > 250)
					$scaleFactor = 250 / $maxDimension;

				$imgSize[0] = round($imgSize[0] * $scaleFactor);
				$imgSize[1] = round($imgSize[1] * $scaleFactor);
				// echo var_dump($imgSize);

				echo '<img width="'.$imgSize[0].'" height="'.$imgSize[1].'" src="'.Yii::app()->request->baseUrl.'/'.$model->avatar.'" />';
			} else
				echo '<img width="45" height="45" src='.Yii::app()->request->baseUrl."/uploads/default.png".'>';
			?>
			<?php if($model->username == Yii::app()->user->getName()) {
				echo CHtml::form('uploadPhoto/'.$model->id,'post',array('enctype'=>'multipart/form-data', 'id'=>'photo-form'));
				echo CHtml::fileField('photo','',array('id'=>'photo','onchange'=>"checkPictureExt(this, '#avatarSubmit')")).'<br />';
				// echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'avatarSubmit','disabled'=>'disabled'),array('class'=>'x2-button'));
				echo CHtml::endForm();
			} ?>
			<?php /*<button type="submit" class="x2-button" name="photo-submit"><?php echo Yii::t('profile', 'Choose Picture'); ?></button> */ ?>
			</span>
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
		<td class="label"><?php echo $attributeLabels['googleId']; ?></td>
		<td><b><?php echo CHtml::mailto($model->googleId); ?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo Yii::t('profile','Signature'); ?></td>
		<td><div style="height:50px;width:0px;float:left;"></div><?php echo $model->getSignature(true); ?></td>
	</tr>
</table>







