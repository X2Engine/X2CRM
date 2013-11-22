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

/**
 * Credentials view file for the list view that includes controls for setting defaults.
 *
 * @var Credentials $data The credentials record being displayed.
 */
?>


<div class="credentials-view" id="credentials-<?php echo $data->id?>">
	<?php
	$webUser = Yii::app()->user;
	$canDelete = $webUser->checkAccess('CredentialsDelete',array('model'=>$data));
	$deleteImg = CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/delete.png',Yii::t('app','Delete'),$canDelete ? array() : array('style'=>'opacity:0.5','title'=>Yii::t('app','Cannot delete. The item is in use by the system, or you do not have permission.')));
	$delete = $canDelete ? CHtml::link($deleteImg,array('/profile/deleteCredentials','id'=>$data->id),array('class'=>'delete','confirm'=>Yii::t('app','Are you sure you want to delete this item?'))) : $deleteImg;
	echo CHtml::tag('div',array('class'=>'credentials-delete'),$delete);
	?>
	<div class="info-display">
	<?php
	$canAccess = $webUser->checkAccess('CredentialsCreateUpdate',array('model' => $data));
	$editImg = CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/update.png',Yii::t('app','Edit'),$canAccess?array():array('style'=>'opacity:0.5'));
	?>
	<?php
	echo $canAccess ? CHtml::link($editImg,array('/profile/createUpdateCredentials','id'=>$data->id),array('class'=>'update')) : $editImg;
	$encryptedState = $data->isEncrypted ? 'encrypted' : 'unencrypted';
	$privateState = $data->private ? 'private' : 'nonprivate';
	$message = $data->isEncrypted ? Yii::t('app','Encrypted storage enabled') : Yii::t('app','Credentials stored in plain text!');
	?>
	
	<?php
	echo CHtml::image(Yii::app()->theme->baseUrl."/images/credentials/$encryptedState-$privateState.png",$message,array('title'=>$message));


	?>

	<span class="credentials-name" style="font-weight:bold;">
		<?php 
		$nameSuffix = '';
		if($data->userId != $webUser->id){
			$otherUser = User::model()->findByPk($data->userId);
			if($otherUser)
				$nameSuffix = ' ('.Yii::t('app','Owned by {user}',array('{user}'=>$otherUser->profile->fullName)).')';
		}
		?>
		<?php echo $data->name.$nameSuffix; ?></span> <span class="credentials-service-type">&nbsp;&bull;&nbsp;<?php echo $data->serviceLabel . ($data->userId==Credentials::SYS_ID ? '&nbsp;('.Yii::t('app','shared system-wide').')':''); ?></span>
	<?php
	echo '&nbsp;&bull;&nbsp;';
	$data->auth->detailView();
	?>
	</div>
	<div class="default-display">
	<?php
	$defaultOf = array_map(function($d)use($data){return $data->serviceLabels[$d];},$data->isDefaultOf($webUser->id));
	$defaultOfSys = array();
	foreach(Credentials::$sysUseId as $alias => $id) {
		if($data->isDefaultOf($id)) {
			$defaultOfSys[] = $data->sysUseLabel[$id];
		}
	}
	$subsLabels = $data->substituteLabels;
	$sysUseLabels = $data->sysUseLabel;
	$setSys = $data->userId == Credentials::SYS_ID ? $webUser->checkAccess('CredentialsSetDefaultSystemwide',array('model'=>$data,'userId'=>$data->userId)) : false;
	$nDefaultForMe = count($defaultOf);
	$defaultForMe = $nDefaultForMe == count($subsLabels);
	$nDefault = $setSys ? $nDefaultForMe + count($defaultOfSys) : $nDefaultForMe;
	$nLabels = $setSys ? count($subsLabels) + count($sysUseLabels) : count($subsLabels);
	$defaultOf = array_merge($defaultOf,$defaultOfSys);
	
	
	if($webUser->checkAccess('CredentialsSetDefault',array('model'=>$data,'userId'=>$webUser->id)) && ($nDefault < $nLabels)){
		echo '&nbsp;<div class="default-state">';
		echo CHtml::beginForm(array('setDefaultCredentials','id'=>$data->id), 'post');
		echo ($setSys ? Yii::t('app', 'Set as default') : Yii::t('app','Set as my default')).'&nbsp;';
		if(count($subsLabels) > 1){
			$options = array();
			if(array_key_exists($user->id, $data->defaultCredentials)){
				$defaults = $data->defaultCredentials[$user->id];
				foreach($subsLabels as $sub => $label){
					if(isset($defaults[$sub])){
						if($defaults[$sub] == $data->id){
							$options[$sub] = array('selected' => true);
						}
					}
				}
			}
			
			echo CHtml::dropDownList('default', array_keys($options), $subsLabels, array('multiple' => 'multiple', 'options' => $options),array('class'=>'set-default'));
		}else{
			echo CHtml::checkBox('default', false,array('class'=>'set-default'));
		}

		$setFor = $defaultForMe ? array() : array($webUser->id=>Yii::t('app','You'));
		if($setSys) {
			foreach($data->sysUseLabel as $id => $label)
				if(in_array(Credentials::$sysUseTypes[$id],$data->defaultSubstitutesInv[$data->modelClass]))
					// Display only credentials of the appropriate type for each specific system usage
					if(!in_array($label,$defaultOf))
						// Display only credentials 
						$setFor[$id] = $label;

		}
		
		echo '<div class="default-apply">';
		if(count($setFor) > 1 || $setSys) {
			echo '&nbsp;'.Yii::t('app','for').'&nbsp;';
			echo CHtml::dropDownList('userId',$webUser->id,$setFor);
		} else {
			echo CHtml::hiddenField('userId',$webUser->id);
		}

		echo '&nbsp;'.CHtml::submitButton(Yii::t('app', 'Apply'));
		echo '</div>';
		echo CHtml::endForm();
		echo '</div>&nbsp;';
	}
	if($nDefault) {
		echo '<strong>'.Yii::t('app','Default').'</strong>&nbsp;'.implode('&nbsp;',array_map(function($l){return '<div class="default-state default-state-set">'.$l.'</div>';},$defaultOf));
	}
	?>
	</div>
</div>
