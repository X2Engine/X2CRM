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
 * Copyright ? 2011-2012 by X2Engine Inc. www.X2Engine.com
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


$canEdit = $model->id==Yii::app()->user->getId() || Yii::app()->user->checkAccess('AdminIndex');

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile'), 'url'=>array('view','id'=>$model->id)),
	array('label'=>Yii::t('profile','Update Profile'), 'url'=>array('update','id'=>$model->id),'visible'=>$canEdit),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Change Password'),'visible'=>($model->id==Yii::app()->user->getId())),
);

echo CHtml::form();
?>
<div class="form">
	<h2><?php echo Yii::t('app','Change Password Form'); ?></h2>
	<div class="row" style="margin-bottom:10px;">
		<div class="cell">
			<label><?php echo Yii::t('app','Old Password'); ?></label>
			<?php echo CHtml::passwordField('oldPassword');?> 
		</div>
	</div>
	<div class="row">
		<div class="cell">
		<label><?php echo Yii::t('app','New Password'); ?></label>
			<?php echo CHtml::passwordField('newPassword','',array('id'=>'newPassword'));?> 
		</div>
	</div>
	<div class="row">
		<div class="cell">
		<label><?php echo Yii::t('app','Confirm New Password'); ?></label>
			<?php echo CHtml::passwordField('newPassword2','',array('id'=>'newPassword2'));?> 
		</div>
	</div>
	<br>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('id'=>'save-changes','class'=>'x2-button')); ?>
	</div>
</div>

<script>
	$('form').submit(function() {
		var newPass=$('#newPassword').val();
		var newPass2=$('#newPassword2').val();
		if(newPass!=newPass2){
			alert('New passwords do not match.');
			return false;
		}
	});
</script>









