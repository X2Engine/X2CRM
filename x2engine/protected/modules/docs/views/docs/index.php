<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

$this->breadcrumbs=array(
	'Docs',
);
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('docs','List Docs')),
	array('label'=>Yii::t('docs','Create Doc'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('createEmail')),
	array('label'=>Yii::t('docs','Create Quote'), 'url'=>array('createQuote')),
));

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
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title icon docs"><h2>'.Yii::t('docs','Docs').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link')) . ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div></div>{items}{pager}', 
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'modelName'=>'Docs',
	'viewName'=>'docs',
	'defaultGvSettings'=>array(
		'name'=>80,
		'createdBy'=>80,
		'createDate' => 80,
		'lastUpdated'=>80,
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
			'value'=>'User::getUserLinks($data->createdBy,true,false)',
			'type'=>'raw',
		),
		'updatedBy' => array(
			'header'=>Yii::t('docs','Updated By'),
			'name'=>'updatedBy',
			'value'=>'User::getUserLinks($data->updatedBy,true,false)',
			'type'=>'raw',
		),
	),
	'excludedColumns' => array(
		'text',
		'type',
		'editPermissions',
	),
	'enableControls'=>false,
));
?>
<br />
<?php
//	$this->widget('zii.widgets.grid.CGridView', array(
//	'id'=>'attachments-grid',
//	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
//	'template'=> '<h2>'.Yii::t('docs','Uploaded Documents').'</h2><div class="title-bar">'
//		.'{summary}</div>{items}{pager}',
//	'dataProvider'=>$attachments,
//	'columns'=>array(
//		array(
//			'name'=>'fileName',
//			'value'=>'$data->getMediaLink()',
//			'type'=>'raw',
//			'htmlOptions'=>array('width'=>'30%'),
//		),
//		array(
//			'name'=>'uploadedBy',
//			'value'=>'User::getUserLinks($data->uploadedBy)',
//			'type'=>'raw',
//		),
//		array(
//			'name'=>'createDate',
//			'type'=>'raw',
//			'value'=>'Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"), $data->createDate)',
//		),
//	),
//)); 
	?>

<?php
$this->widget('Attachments',array('associationType'=>'docs','associationId'=>$model->id)); ?>
