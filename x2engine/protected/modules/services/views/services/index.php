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

$menuItems = array(
	array('label'=>Yii::t('services','All Cases')),
	array('label'=>Yii::t('services','Create Case'), 'url'=>array('create')),
	array('label'=>Yii::t('services','Create Web Form'), 'url'=>array('createWebForm')),
    array('label'=>Yii::t('services','Case Report'), 'url'=>array('servicesReport')),

);

$this->actionMenu = $this->formatMenu($menuItems);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('services-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
/*
?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php
*/

$field = Fields::model()->findByAttributes(array('modelName'=>'Services', 'fieldName'=>'status', 'type'=>'dropdown'));
if($field) {
	$statuses = Dropdowns::getItems($field->linkType);
	if($statuses) {
//		var_dump(json_decode($dropdown->options));
		$this->serviceCaseStatuses = $statuses;
	}
}

$this->widget('application.components.X2GridView', array(
	'id'=>'services-grid',
	'title'=>Yii::t('services','Service Cases'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon services x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        /* x2prostart */'{massActionButtons}'./* x2proend */
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->searchWithStatusFilter(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	// 'columns'=>$columns,
	'modelName'=>'Services',
	'viewName'=>'services',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
		'id' => 43,
		'impact' => 80,
		'status' => 233,
		'assignedTo' => 112,
		'lastUpdated' => 79,
		'updatedBy' => 111,
//		'name'=>234,
//		'type'=>108,
//		'annualRevenue'=>128,
//		'phone'=>115,
	),
	'specialColumns'=>array(
		'id'=>array(
			'name'=>'id',
			'type'=>'raw',
			'value'=>'CHtml::link($data->id, array("view","id"=>$data->id))',
		),
		'account'=>array(
			'name'=>'account',
			'header'=>Yii::t('contacts', 'Account'),
			'type'=>'raw',
			'value'=>'$data->contactIdModel? (isset($data->contactIdModel->companyModel) ? $data->contactIdModel->companyModel->getLink() : "") : ""'
		),
        'status'=>array(
			'name'=>'status',
			'type'=>'raw',
			'value'=>'Yii::t("services",$data->status)',
		),
        'impact'=>array(
			'name'=>'impact',
			'type'=>'raw',
			'value'=>'Yii::t("services",$data->impact)',
		),
/*		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('services','Name'),
			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
			'type'=>'raw',
		), */
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));
?>
