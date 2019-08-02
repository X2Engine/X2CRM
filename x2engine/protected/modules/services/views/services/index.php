<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




$menuOptions = array(
    'index', 'create', 'createWebForm', 'import', 'export', 'lists',
);
$this->insertMenu($menuOptions);

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

$this->widget('X2GridView', array(
	'id'=>'services-grid',
	'title'=>Yii::t('services','Service Cases'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon services x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        '{massActionButtons}'.
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
			'value'=>'isset ($data->contactIdModel) ? ($data->contactIdModel ? (isset($data->contactIdModel->companyModel) ? $data->contactIdModel->companyModel->getLink() : "") : "") : ""'
		),
        'status'=>array(
			'name'=>'status',
			'type'=>'raw',
			'value'=>'Yii::t("services",$data->renderAttribute("status"))',
		),
        'impact'=>array(
			'name'=>'impact',
			'type'=>'raw',
			'value'=>'Yii::t("services",$data->renderAttribute("impact"))',
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
