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

$themeUrl = Yii::app()->theme->getBaseUrl();

$menuItems = array(
	array('label'=>Yii::t('products','Product List'), 'url'=>array('index')),
	array('label'=>Yii::t('products','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('products','View')),
	array('label'=>Yii::t('products','Update'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('products','Delete'), 'url'=>'#', 
		'linkOptions'=>array(
			'submit'=>array('delete','id'=>$model->id),
			'confirm'=>Yii::t('app','Are you sure you want to delete this item?')
		)
	),
);

$menuItems[] = array(
	'label' => Yii::t('app', 'Print Record'), 
	'url' => '#',
	'linkOptions' => array (
		'onClick'=>"window.open('".
			Yii::app()->createUrl('/site/printRecord', array (
				'modelClass' => 'Product', 
				'id' => $model->id, 
				'pageTitle' => Yii::t('app', 'Product').': '.$model->name
			))."');"
	)
);

$this->actionMenu = $this->formatMenu($menuItems);

$modelType = json_encode("Products");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
?>
<div class="page-title icon products">
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo Yii::t('products','Product:'); ?></span> <?php echo $model->name; ?></h2>
	<a class="x2-button icon edit right" href="update/<?php echo $model->id;?>"><span></span></a>
</div>
<div id="main-column" class="half-width">
<?php $this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Product')); ?>

<?php 
	$this->widget('X2WidgetList', array(
		'block'=>'center', 
		'model'=>$model, 
		'modelType'=>'products'
	)); 
?>

<?php $this->widget('Attachments',array('associationType'=>'products','associationId'=>$model->id)); ?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'products',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'products','associationId'=>$model->id));
?>
</div>
