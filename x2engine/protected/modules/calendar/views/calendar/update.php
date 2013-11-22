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
		array('label'=>Yii::t('calendar','My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
		array('label'=>Yii::t('calendar','List'),'url'=>array('list')),
		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
		array('label'=>Yii::t('calendar','View'), 'url'=>array('view', 'id'=>$model->id)),
		array('label'=>Yii::t('calendar','Update')),
		array('label'=>Yii::t('calendar','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
		array('label'=>Yii::t('calendar','Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
	);
} else {
	$menuItems = array(
		array('label'=>Yii::t('calendar','Calendar'), 'url'=>array('index')),
		array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
		array('label'=>Yii::t('calendar','View'), 'url'=>array('view', 'id'=>$model->id)),
		array('label'=>Yii::t('calendar','Update')),
		array('label'=>Yii::t('calendar','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),

	);
}
$this->actionMenu=$this->formatMenu($menuItems);
?>

<h2 style="margin-bottom:0;"><?php echo Yii::t('quotes','Update Quote: {name}',array('{name}'=>$model->name)); ?> <a class="x2-button" href="javascript:void(0);" onclick="$('#save-button').click();">Save</a></h2>

<?php

$users = User::getNames();
unset($users['Anyone']);
unset($users['admin']);

$form=$this->beginWidget('CActiveForm', array(
   'id'=>'calendar-form',
   'enableAjaxValidation'=>false,
));

echo $this->renderPartial('application.components.views._form', 
	array(
		'model'=>$model,
		'form'=>$form,
		'modelName'=>'calendar',
		'users'=>$users,
		'isQuickCreate'=>true, // let us create the CActiveForm in this file
	)
);
?>

<?php if(!$googleIntegration) { ?>

<div class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('calendar', 'Google'); ?></span>
		</div>
	</div>
</div>

<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#eee;">
	<table frame="border">
		<td>
			<?php echo $form->labelEx($model, 'googleCalendar'); ?>
			<?php echo $form->checkbox($model, 'googleCalendar'); ?>
			<?php echo $form->labelEx($model, 'googleFeed'); ?>
			<?php echo $form->textField($model, 'googleFeed', array('size'=>75)); ?>
		</td>
	</table>
</div>

<?php } ?>
<?php $this->endWidget(); ?>
