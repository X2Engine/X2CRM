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

$title = ($model->type=='email')? Yii::t('docs','Edit Template:') : Yii::t('docs','Edit Document:');
 
$pieces = explode(", ",$model->editPermissions);
$user = Yii::app()->user->getName();

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('index')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('createQuote')),
	array('label'=>Yii::t('docs','View'), 'url'=>array('view','id'=>$model->id)),
));

if(array_search($user,$pieces)!==false || $user==$model->editPermissions || $user=='admin' || $user==$model->createdBy)
	$this->actionMenu[] = array('label'=>Yii::t('docs','Edit Doc'));
if(Yii::app()->params->isAdmin || $user==$model->createdBy)
	$this->actionMenu[] = array('label'=>Yii::t('docs','Delete Doc'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('docs','Are you sure you want to delete this item?')));
if(Yii::app()->params->isAdmin || $user==$model->createdBy)
	$this->actionMenu[]=array('label'=>Yii::t('docs','Edit Doc Permissions'), 'url'=>array('changePermissions', 'id'=>$model->id));
	
$this->actionMenu[] = array('label'=>Yii::t('docs','Export Doc'),'url'=>array('exportToHtml','id'=>$model->id));
?>
<div class="page-title icon docs"><h2><span class="no-bold"><?php echo $title; ?></span> <?php echo CHtml::encode($model->name); ?></h2></div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>