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

