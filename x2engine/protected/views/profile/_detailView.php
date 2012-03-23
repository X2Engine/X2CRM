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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
	}
	else {
		// delete the file name, disable Submit, Alert message
		el.value = '';
		$(sbm).attr('disabled','disabled');
		alert('\".'+ ar_ext+ '\" is not an file type allowed for upload');
	}
}",CClientScript::POS_HEAD);

$attributeLabels = $model->attributeLabels();
?>
<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo $attributeLabels['fullName']; ?></td>
		<td><b><?php echo CHtml::encode($model->fullName); ?></b></td>
		<td rowspan="6" width="25%">
			<?php
			// getimagesize()
			
			if(isset($model->avatar) && $model->avatar!='') {

				$imgSize = @getimagesize($model->avatar);
				// die( var_dump($imgSize));
				if(!$imgSize)
					$imgSize = array(180,180);
				
				$maxDimension = max($imgSize[0],$imgSize[1]);
				
				$scaleFactor = 1;
				if($maxDimension > 180)
					$scaleFactor = 180 / $maxDimension;
					
				$imgSize[0] = round($imgSize[0] * $scaleFactor);
				$imgSize[1] = round($imgSize[1] * $scaleFactor);
				// echo var_dump($imgSize);
				
				echo '<img width="'.$imgSize[0].'" height="'.$imgSize[1].'" src="'.Yii::app()->request->baseUrl.'/'.$model->avatar.'" />';
			} else
				echo '<img width="180" height="180" src='.Yii::app()->request->baseUrl."/uploads/default.jpg".'>';
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
		<td class="label"><?php echo Yii::t('profile','Signature'); ?></td>
		<td><div style="height:50px;width:0px;float:left;"></div><?php echo $model->getSignature(true); ?></td>
		<td><?php if($model->username == Yii::app()->user->getName()) {
				echo CHtml::form('uploadPhoto/'.$model->id,'post',array('enctype'=>'multipart/form-data'));
				echo CHtml::fileField('photo','',array('id'=>'photo','onchange'=>"checkPictureExt(this, '#avatarSubmit')")).'<br />';
				echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'avatarSubmit','disabled'=>'disabled'),array('class'=>'x2-button'));
				echo CHtml::endForm();
			} ?>
		</td>
	</tr>
</table>







