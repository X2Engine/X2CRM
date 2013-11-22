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
		array('label'=>Yii::t('calendar','Calendar')),
		array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
		array('label'=>Yii::t('calendar','View')),
		array('label'=>Yii::t('calendar','Update'), 'url'=>array('update', 'id'=>$model->id)),
		array('label'=>Yii::t('calendar','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
		array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
	);
} else {
	$menuItems = array(
		array('label'=>Yii::t('calendar','Calendar')),
		array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
		array('label'=>Yii::t('calendar','View')),
		array('label'=>Yii::t('calendar','Update'), 'url'=>array('update', 'id'=>$model->id)),
		array('label'=>Yii::t('calendar','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	);
}
$this->actionMenu = $this->formatMenu($menuItems);
?>

<h2><?php echo Yii::t('calendar','Shared Calendar:'); ?> <b><?php echo $model->name; ?></b> <a class="x2-button" href="<?php echo $this->createUrl('update', array('id'=>$model->id));?>">Edit</a></h2>

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quotes-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'calendar'));
?>
</div>
<?php $this->endWidget(); ?>

<?php /*
<a class="x2-button" href="#" onClick="toggleForm('#attachment-form',200);return false;"><span><?php echo Yii::t('app','Attach A File/Photo'); ?></span></a>
<br /><br />

<div id="attachment-form" style="display:none;">
	<?php $this->widget('Attachments',array('type'=>'quotes','associationId'=>$model->id)); ?>
</div>
<?php

$this->widget('InlineActionForm',
	array(
		'associationType'=>'calendar',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'users'=>$users,
		'startHidden'=>false
	)
);

if(isset($_GET['history']))
    $history=$_GET['history'];
else
    $history="all";

$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$actionHistory,
	'itemView'=>'../actions/_view',
	'htmlOptions'=>array('class'=>'action list-view'),
	'template'=> 
            ($history=='all'?'<h3>'.Yii::t('app','History')."</h3>":CHtml::link(Yii::t('app','History'),"?history=all")).
            " | ".($history=='actions'?'<h3>'.Yii::t('app','Actions')."</h3>":CHtml::link(Yii::t('app','Actions'),"?history=actions")).
            " | ".($history=='comments'?'<h3>'.Yii::t('app','Comments')."</h3>":CHtml::link(Yii::t('app','Comments'),"?history=comments")).
            " | ".($history=='attachments'?'<h3>'.Yii::t('app','Attachments')."</h3>":CHtml::link(Yii::t('app','Attachments'),"?history=attachments")).
            '</h3>{summary}{sorter}{items}{pager}',
)); */
?>