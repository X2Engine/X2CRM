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

$pieces = explode(", ",$model->editPermissions);
$user = Yii::app()->user->getName();

$this->actionMenu=array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('index')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('createQuote')),
	array('label'=>Yii::t('docs','View'), 'url'=>array('view','id'=>$model->id)),
);
$this->actionMenu=$this->formatMenu($this->actionMenu,array());
if($user=='admin' || $user==$model->createdBy)
	$this->actionMenu[] = array('label'=>Yii::t('docs','Edit Doc'), 'url'=>array('update', 'id'=>$model->id));
if($user=='admin')
	$this->actionMenu[] = array('label'=>Yii::t('docs','Delete Doc'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?'));
if(array_search($user,$pieces)!=false || $user==$model->editPermissions || $user=='admin' || $user==$model->createdBy)
	$this->actionMenu[]=array('label'=>Yii::t('docs','Edit Doc Permissions'), 'url'=>array('changePermissions', 'id'=>$model->id));
	
$this->actionMenu[] = array('label'=>Yii::t('docs','Export Doc'));
?>
<div class="page-title icon docs"><h2><?php echo Yii::t('docs','Export Doc');?></h2></div>
<div class="form"><div class="span-10">
<?php echo Yii::t('docs','Please right click the link below and select "Save As" to download the document!  Left clicking opens the document in a printer-friendly mode.');?><br /><br />
<?php echo $link; ?>
</div>
</div>