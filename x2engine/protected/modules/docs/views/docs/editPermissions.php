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

$pieces = $model->editPermissions;
$user = Yii::app()->user->getName();

$authParams['assignedTo']=$model->createdBy;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('index')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('createQuote')),
	array('label'=>Yii::t('docs','View'), 'url'=>array('view','id'=>$model->id)),
    array('label'=>Yii::t('docs','Edit Doc'), 'url'=>array('update', 'id'=>$model->id)),
    array('label'=>Yii::t('docs','Delete Doc'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('docs','Are you sure you want to delete this item?')))
),$authParams);


if(array_search($user,$pieces)!=false || $user==$model->editPermissions || $user=='admin' || $user==$model->createdBy)
	$this->actionMenu[]=array('label'=>Yii::t('docs','Edit Doc Permissions'));
	
$this->actionMenu[] = array('label'=>Yii::t('docs','Export Doc'),'url'=>array('exportToHtml','id'=>$model->id));
	
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'docs-form',
	'enableAjaxValidation'=>false,
));?>
<div class="page-title icon docs"><h2><?php echo Yii::t('docs','Edit Doc Permissions');?></h2></div>
<div class="form">
	<div class="row" style="width:500px;">
		<?php echo Yii::t('docs','Please select which users are allowed to edit the document.  Use Control + Click to select or deselect individual users.'); ?>
	</div><br>
	<div class="row"><?php
		echo $form->label($model,'editPermissions');
		echo $form->dropDownList($model,'editPermissions',$users,array('multiple'=>'multiple','size'=>'5'));
		echo $form->error($model,'editPermissions'); ?>
	</div>
	<div class="row">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
</div>
<?php $this->endWidget();