<?php

/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




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
	$deleteImg = X2Html::fa('fa-times fa-lg', array(
		'class'=>'x2-delete-icon',
		'title'=> $canDelete ? Yii::t('app','Delete') : Yii::t('app', 'Cannot delete. The item is in use by the system, or you do not have permission.')
	));
	// $deleteImg = CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/delete.png',Yii::t('app','Delete'),$canDelete ? array() : array('style'=>'opacity:0.5','title'=>Yii::t('app','Cannot delete. The item is in use by the system, or you do not have permission.')));
	$delete = $canDelete ? CHtml::link($deleteImg,array('/profile/deleteCredentials','id'=>$data->id),array('class'=>'delete','confirm'=>Yii::t('app','Are you sure you want to delete this item?'))) : $deleteImg;
	echo CHtml::tag('div',array('class'=>'credentials-delete'),$delete);
	?>
	<div class="info-display">
	<?php
	$canAccess = $webUser->checkAccess('CredentialsCreateUpdate',array('model' => $data));
	// $editImg = CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/update.png',Yii::t('app','Edit'),$canAccess?array():array('style'=>'opacity:0.5'));
	$editImg = X2Html::fa('fa-edit', array(
		'title'=>Yii::t('app','Edit')
	));
	?>
	<?php
	echo $canAccess ? CHtml::link($editImg,array('/profile/createUpdateCredentials','id'=>$data->id),array('class'=>'update')) : $editImg;
	$encryptedState = $data->isEncrypted ? 'encrypted' : 'unencrypted';
	$privateState = $data->private ? 'private' : 'nonprivate';
	$message = $data->isEncrypted ? Yii::t('app','Encrypted storage enabled') : Yii::t('app','Credentials stored in plain text!');
	?>
	
	<?php
	echo X2Html::fa('fa-lock fa-lg', array(
		'class' => $encryptedState,
		'title' => $message
	));
	// echo CHtml::image(Yii::app()->theme->baseUrl."/images/credentials/$encryptedState-$privateState.png",$message,array('title'=>$message));


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
	$setSys = $data->userId == Credentials::SYS_ID 
            ? $webUser->checkAccess(
                'CredentialsSetDefaultSystemwide',array('model'=>$data,'userId'=>$webUser->id))
            : false;
	$nDefaultForMe = count($defaultOf);
	$defaultForMe = $nDefaultForMe == count($subsLabels);
	$nDefault = $setSys ? $nDefaultForMe + count($defaultOfSys) : $nDefaultForMe;
	$nLabels = $setSys ? count($subsLabels) + count($sysUseLabels) : count($subsLabels);
	$defaultOf = array_merge($defaultOf,$defaultOfSys);
	
	
	if(!$data->isBounceAccount && $webUser->checkAccess('CredentialsSetDefault',array('model'=>$data,'userId'=>$webUser->id)) && ($nDefault < $nLabels)){
        if ($data->modelClass !== 'TwitterApp') {
		    echo '&nbsp;<div class="default-state">';
            echo CHtml::beginForm(array('setDefaultCredentials','id'=>$data->id), 'post');
            echo ($setSys ? 
                Yii::t('app', 'Set as default') : Yii::t('app','Set as my default')).'&nbsp;';
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
                
                echo CHtml::dropDownList(
                    'default',
                    array_keys($options),
                    $subsLabels,
                    array(
                        'multiple' => 'multiple',
                        'options' => $options
                    ),array('class'=>'set-default'));
            }else{
                echo CHtml::checkBox('default', false,array('class'=>'set-default'));
            }

            $setFor = $defaultForMe ? array() : array($webUser->id=>Yii::t('app','You'));
            if($setSys) {
                foreach($data->sysUseLabel as $id => $label)
                    if(in_array(
                        Credentials::$sysUseTypes[$id],
                        $data->defaultSubstitutesInv[$data->modelClass])) {
                        // Display only credentials of the appropriate type for each specific 
                        // system usage
                        if(!in_array($label,$defaultOf))
                            // Display only credentials 
                            $setFor[$id] = $label;
                    }
            }
            
            echo '<div class="default-apply">';
            if(count($setFor) > 1 || $setSys) {
                echo '&nbsp;'.Yii::t('app','for').'&nbsp;';
                echo CHtml::dropDownList('userId',$webUser->id,$setFor,array(
                    'class' => 'x2-select'
                ));
            } else {
                echo CHtml::hiddenField('userId',$webUser->id);
            }

            echo '&nbsp;'.CHtml::submitButton(Yii::t('app', 'Apply'),array(
                'class' => 'x2-button',
            ));
            echo '</div>';
            echo CHtml::endForm();
        }
		echo '</div>&nbsp;';
	}
	if($nDefault) {
		echo '<strong>'.Yii::t('app','Default').'</strong>&nbsp;'.implode('&nbsp;',array_map(function($l){return '<div class="default-state default-state-set">'.$l.'</div>';},$defaultOf));
	}
    if($data->isBounceAccount) {
        echo '<div class="default-state default-state-set">Bounce Handling Account</div>';
    }
	?>
	</div>
</div>
