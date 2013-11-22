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

$this->breadcrumbs=array(
	'Docs',
);
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('docs','List Docs')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('createQuote')),
));

Yii::app()->clientScript->registerCss('docsIndexCss', "
    #attachments-grid {
        margin-top: 17px;
    }
");
Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php
//	$this->widget('zii.widgets.grid.CGridView', array(
//	'id'=>'docs-grid',
//	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
//	'template'=> '<div class="page-title"><h2>'.Yii::t('docs','Documents').'</h2><div class="title-bar">'
//		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
//		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1))
//		.'{summary}</div></div>{items}{pager}',
//	'dataProvider'=>$model->search(),
//	'filter'=>$model,
//	'columns'=>array(
//		array(
//			'name'=>'name',
//			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
//			'type'=>'raw',
//			'htmlOptions'=>array('width'=>'30%'),
//		),
//		array(
//			'name'=>'createdBy',
//			'value'=>'User::getUserLinks($data->createdBy)',
//			'type'=>'raw',
//		),
//		array(
//			'name'=>'updatedBy',
//			'value'=>'User::getUserLinks($data->updatedBy)',
//			'type'=>'raw',
//		),
//		array(
//			'name'=>'createDate',
//			'type'=>'raw',
//			'value'=>'Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"), $data->createDate)',
//		),
//		array(
//			'name'=>'lastUpdated',
//			'type'=>'raw',
//			'value'=>'Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"), $data->lastUpdated)',
//		),
//	),
//));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').unbind('click').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('docs-grid', {
		data: $(this).serialize()
	});
	return false;
});

",CClientScript::POS_READY);

$this->widget('application.components.X2GridView', array(
	'id'=>'docs-grid',
	'title'=>Yii::t('docs','Docs'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> '<div class="page-title icon docs">{title}{buttons}{filterHint}{summary}</div>{items}{pager}',
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'modelName'=>'Docs',
	'viewName'=>'docs',
	'defaultGvSettings'=>array(
		'name' => 253,
		'createdBy' => 76,
		'createDate' => 111,
		'lastUpdated' => 115,
	),
	'specialColumns'=>array(
		'name' => array(
			'header'=>Yii::t('docs','Title'),
			'name'=>'name',
			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'type' => array(
			'header'=>Yii::t('docs','Type'),
			'name'=>'type',
			'value'=>'$data->parseType()',
			'type'=>'raw',
		),
		'createdBy' => array(
			'header'=>Yii::t('docs','Created By'),
			'name'=>'createdBy',
			'value'=>'User::getUserLinks($data->createdBy,true,true)',
			'type'=>'raw',
		),
		'updatedBy' => array(
			'header'=>Yii::t('docs','Updated By'),
			'name'=>'updatedBy',
			'value'=>'User::getUserLinks($data->updatedBy,true,true)',
			'type'=>'raw',
		),
	),
	'excludedColumns' => array(
		'text',
		'type',
		'editPermissions',
	),
	'enableControls'=>false,
	'fullscreen'=>true,
));
?>
<br />
<div class='flush-grid-view'>
<?php
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'attachments-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title rounded-top icon docs"><h2>'.Yii::t('docs','Uploaded Docs').'</h2>{summary}</div>{items}{pager}',
	'dataProvider'=>$attachments,
	'columns'=>array(
		array(
			'name'=>'fileName',
			'value'=>'$data->getMediaLink()',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'30%'),
		),
		array(
			'name'=>'uploadedBy',
			'value'=>'User::getUserLinks($data->uploadedBy)',
			'type'=>'raw',
		),
		array(
			'name'=>'createDate',
			'type'=>'raw',
			'value'=>'Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"), $data->createDate)',
		),
	),
));
	?>
</div>
<br/>
<?php
$this->widget('Attachments',array('associationType'=>'docs','associationId'=>$model->id)); ?>
