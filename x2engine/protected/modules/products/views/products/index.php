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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('products','Product List')),
	array('label'=>Yii::t('products','Create'), 'url'=>array('create')),
));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('opportunities-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

function trimText($text) {
	if(mb_strlen($text,'UTF-8')>150)
		return mb_substr($text,0,147,'UTF-8').'...';
	else
		return $text;
}
?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<div class='flush-grid-view'>
<?php
	$canDelete = Yii::app()->params->isAdmin;
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'product-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title icon products"><h2>'.Yii::t('products','Products').
        '</h2><div class="x2-button-group">'
		.CHtml::link(
            '<span></span>','#',array('title'=>Yii::t('app','Advanced Search'),
            'class'=>'x2-button search-button'))
		.CHtml::link(
            '<span></span>',array(Yii::app()->controller->action->id,'clearFilters'=>1),
            array('title'=>Yii::t('app','Clear Filters'),'class'=>'x2-button filter-button')).'</div> '
		.X2GridView::getFilterHint()
		.'{summary}</div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'name',
			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		array(
			'name'=>'type',
			'header'=>Yii::t('products', 'Type'),
			'value'=>'trimText($data->type)',
			'type'=>'raw',
		),
		array(
			'name'=>'description',
			'header'=>Yii::t('products','Description'),
			'value'=>'trimText($data->description)',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
		array(
			'name'=>'createDate',
			'value'=>'Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"), '.
                '$data->createDate)',
			'type'=>'raw',
		),
		array(
			'header'=>Yii::t('products', 'Tools'),
			'class'=>'CButtonColumn',
			'buttons' => array(
				'delete' => array(
					'visible' => ($canDelete?'true':'false'),
				),
			),
		),
	),
)); ?>
</div>
