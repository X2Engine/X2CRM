<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$menuItems = array(
	array('label'=>Yii::t('services','All Cases')),
	array('label'=>Yii::t('services','Create Case'), 'url'=>array('create')),
	array('label'=>Yii::t('services','Create Web Form'), 'url'=>array('createWebForm')),

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
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('services','Service Cases').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link')) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$model->searchWithStatusFilter(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Services',
	'viewName'=>'services',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'id'=>100,
		'status'=>150,
		'impact'=>150,
		'lastUpdated'=>200,
		'updatedBy'=>200,
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
/*		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('services','Name'),
			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
			'type'=>'raw',
		), */
	),
	'enableControls'=>true,
));
?>
