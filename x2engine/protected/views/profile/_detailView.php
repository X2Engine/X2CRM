<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::app()->clientScript->registerCss("viewProfile", "

.avatar-upload {
    border: 3px solid #f8f8f8;
    -webkit-border-radius:8px;
	-moz-border-radius:8px;
	-o-border-radius:8px;
	border-radius:8px;
}

");

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

				echo '<img width="'.$imgSize[0].'" height="'.$imgSize[1].'" class="avatar-upload" '.
                    'src="'.Yii::app()->request->baseUrl.'/'.$model->avatar.'" />';
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







