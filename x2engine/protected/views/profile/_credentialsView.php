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

/**
 * Credentials file for the list view
 */
?>


<div class="credentials-view" id="credentials-<?php echo $data->id?>" style="border-radius: 4px;border: 1px solid #999;padding:10px;margin-bottom:10px;">

	<?php echo CHtml::link(CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/update.png',Yii::t('app','Edit')),array('profile/createUpdateCredentials','id'=>$data->id),array('class'=>'update')); ?>

	<span class="credentials-name" style="font-weight:bold;">
		<?php echo $data->name; ?></span> <span class="credentials-service-type">(<?php echo $data->serviceLabel; ?>)</span>
	<?php echo $data->auth->detailView(); ?>
	<?php
	$defaultOf = array_map(function($d)use($data){return $data->serviceLabels[$d];},$data->isDefaultOf(Yii::app()->user->id));
	echo count($defaultOf) ? ('&bull;&nbsp;<strong>'.Yii::t('app','Default').':</strong> '.implode(', ',array_map(function($l){return '<span style="display:inline-block; font-size:12px; padding:4px; -moz-border-radius:7px; -webkit-border-radius:8px; border-radius:8px; background-color: #EEEEEE;border:1px solid #999999">'.$l.'</span>';},$defaultOf))) : '' ;
	?>

	<?php
	$encryptedState = $data->isEncrypted ? 'encrypted' : 'unencrypted';
	$privateState = $data->private ? 'private' : 'nonprivate';
	$message = $data->isEncrypted ? Yii::t('app','Encrypted storage enabled') : Yii::t('app','Credentials stored in plain text!');
	echo CHtml::image(Yii::app()->theme->baseUrl."/images/credentials/$encryptedState-$privateState.png",$message,array('title'=>$message));
	?>

	<?php echo CHtml::link(CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/delete.png'),array('profile/deleteCredentials','id'=>$data->id),array('class'=>'delete','style'=>'float:right;','confirm'=>Yii::t('app','Are you sure you want to delete this item?')));?>
</div>