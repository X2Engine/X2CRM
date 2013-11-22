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


$canEdit = $model->id==Yii::app()->user->getId() || Yii::app()->params->isAdmin;

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile'), 'url'=>array('view','id'=>$model->id)),
	array('label'=>Yii::t('profile','Edit Profile'), 'url'=>array('update','id'=>$model->id),'visible'=>$canEdit),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Change Password'),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Manage Apps'),'url'=>array('manageCredentials'))
);
?>
<div class="page-title icon profile"><h2><?php echo Yii::t('profile','Change Password Form'); ?></h2></div>
<?php echo CHtml::form(); ?>
<div class="form">
	
	<div class="row" style="margin-bottom:10px;">
		<div class="cell">
			<label><?php echo Yii::t('profile','Old Password'); ?></label>
			<?php echo CHtml::passwordField('oldPassword');?> 
		</div>
	</div>
	<div class="row">
		<div class="cell">
		<label><?php echo Yii::t('profile','New Password'); ?></label>
			<?php echo CHtml::passwordField('newPassword','',array('id'=>'newPassword'));?> 
		</div>
	</div>
	<div class="row">
		<div class="cell">
		<label><?php echo Yii::t('profile','Confirm New Password'); ?></label>
			<?php echo CHtml::passwordField('newPassword2','',array('id'=>'newPassword2'));?> 
		</div>
	</div>
	<br>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('id'=>'save-changes','class'=>'x2-button')); ?>
	</div>
</div>
</form>
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









