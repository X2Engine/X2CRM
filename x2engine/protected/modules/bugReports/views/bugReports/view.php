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

include("protected/modules/bugReports/bugReportsConfig.php");

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create {X}',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('create')),
	array('label'=>Yii::t('module','View {X}',array('{X}'=>$moduleConfig['recordName']))),
	array('label'=>Yii::t('module','Update {X}',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Delete {X}',array('{X}'=>$moduleConfig['recordName'])), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
    array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
));
?>
<div class="page-title"><h2><?php echo Yii::t('module','View {X}',array('{X}'=>$moduleConfig['recordName'])); ?>: <?php echo $model->name; ?></h2></div>
<div id="main-column" class="half-width">
<?php $this->renderPartial('application.components.views._detailView',array('model'=>$model, 'modelName'=>'BugReports')); ?>

<?php $this->widget('Attachments',array('associationType'=>'bugReports','associationId'=>$model->id,'startHidden'=>true)); ?>

<?php
$this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'BugReports'));

?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'bugReports',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);
$this->widget('History',array('associationType'=>'BugReports','associationId'=>$model->id));
?>
</div>