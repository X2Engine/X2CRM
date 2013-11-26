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
 * Credentials form.
 *
 * @var Credentials $model The ID of the credentials being modified
 * @var bool $includeTitle Whether to print the title inside of the
 * @var User $user The system user
 */

echo '<div class="form">';

$action = null;
if($model->isNewRecord)
	$action = array('/profile/createUpdateCredentials','class'=>$model->modelClass);
else
	$action = array('/profile/createUpdateCredentials','id'=>$model->id);

echo CHtml::beginForm($action);
?>

<!-- Credentials metadata -->
<?php
echo $includeTitle ? $model->pageTitle.'<hr />' : '';
// Model class hidden field, so that it saves properly:
echo CHtml::activeHiddenField($model,'modelClass');

echo CHtml::activeLabel($model, 'name');
echo CHtml::error($model, 'name');
echo CHtml::activeTextField($model, 'name');

echo CHtml::activeLabel($model, 'private');
echo CHtml::activeCheckbox($model, 'private',array('value'=>1));
echo CHtml::tag('span', array('class' => 'x2-hint', 'style'=>'display:inline-block; margin-left:5px;', 'title' => Yii::t('app', 'If you disable this option, administrators and users granted privilege to do so will be able to use these credentials on your behalf.')),'[?]');

if($model->isNewRecord){
	if(Yii::app()->user->checkAccess('CredentialsAdmin')){
		$users = array($user->id => Yii::t('app', 'You'));
		$users[Credentials::SYS_ID] = 'System';
		echo CHtml::activeLabel($model, 'userId');
		echo CHtml::activeDropDownList($model, 'userId', $users, array('selected' => Credentials::SYS_ID));
	}else{
		echo CHtml::activeHiddenField($model, 'userId', array('value' => $user->id));
	}
}

?>
	
<!-- Credentials details (embedded model) -->
<?php
$this->widget('EmbeddedModelForm', array(
	'model' => $model,
	'attribute' => 'auth'
));
?>
</div>

<div class="credentials-buttons">
<?php
echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button credentials-save','style'=>'display:inline-block;margin-top:0;'));
echo CHtml::link(Yii::t('app','Cancel'),array('/profile/manageCredentials'),array('class'=>'x2-button credentials-cancel'));
?></div><?php
echo CHtml::endForm();
?>

