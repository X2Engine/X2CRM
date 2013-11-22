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
?>

<?php
if(Yii::app()->params->admin->googleIntegration) { // menu if google integration is enables has additional options
	$menuItems = array(
		array('label'=>Yii::t('calendar','Calendar'), 'url'=>array('index')),
		array('label'=>Yii::t('calendar', 'My Calendar Permissions')),
//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
		array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
	);
} else {
	$menuItems = array(
		array('label'=>Yii::t('calendar','Calendar'), 'url'=>array('index')),
		array('label'=>Yii::t('calendar', 'My Calendar Permissions')),
//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
	);
}
$this->actionMenu = $this->formatMenu($menuItems);
?>



<?php

$users = User::model()->findAll(array('select'=>'id, username, firstName, lastName', 'index'=>'id'));

$this->beginWidget('CActiveForm', array(
    'id'=>'user-permission-form',
    'enableAjaxValidation'=>false,
));

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
Yii::app()->clientScript->registerCss('userPermissionCss',"
.user-permission {
    width: 460px;
    height: 200px;
}
#switcher {
    margin-top: 20px;
}
",'screen, projection');
Yii::app()->clientScript->registerScript('userCalendarPermission', "
$(function() {
    $('.user-permission').multiselect();
});
",CClientScript::POS_HEAD);

$names = array();
foreach($users as $user)
    if($user->username != 'admin' && $user->id != Yii::app()->user->id)
    	$names[$user->id] = $user->firstName . ' ' . $user->lastName;
    	
$viewPermission = X2CalendarPermissions::getUserIdsWithViewPermission(Yii::app()->user->id);
$editPermission = X2CalendarPermissions::getUserIdsWithEditPermission(Yii::app()->user->id);
?>
<div class="page-title"><h2><?php echo Yii::t('calendar', 'View Permission'); ?></h2></div>
<div class="form">
	<?php echo Yii::t('calendar', 'These users can view your calendar.'); ?>
	<?php
	echo CHtml::listBox('view-permission', $viewPermission, $names, array(
		'class'=>'user-permission',
		'multiple'=>'multiple',
		'onChange'=>'giveSaveButtonFocus();',
	));
	?>
	<br>
</div>
<div class="page-title rounded-top"><h2><?php echo Yii::t('calendar', 'Edit Permission'); ?></h2></div>
<div class="form">
	<?php echo Yii::t('calendar', 'These users can edit your calendar.'); ?>
	<?php
	echo CHtml::listBox('edit-permission', $editPermission, $names, array(
		'class'=>'user-permission',
		'multiple'=>'multiple',
		'onChange'=>'giveSaveButtonFocus();',
	));
	?>
	<br>
	<div class="row buttons">
		<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button', 'name'=>'save-button', 'tabindex'=>24)); ?>
	</div>
</div>
<?php
$this->endWidget();
