<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

if($this->route=='actions/index') {
	$heading = Yii::t('actions','Today\'s Actions');
	$dataProvider=$model->search();
	$dataProvider2=$model->searchComplete();
	
	$this->menu=array(
		array('label'=>Yii::t('actions','Today\'s Actions')),
		array('label'=>Yii::t('actions','All My Actions'),'url'=>array('viewAll')),
		array('label'=>Yii::t('actions','Everyone\'s Actions'),'url'=>array('viewGroup')),
		array('label'=>Yii::t('actions','Create Lead'),'url'=>array('quickCreate')),
		array('label'=>Yii::t('actions','Create Action'),'url'=>array('create')), 
	);
} else if($this->route=='actions/viewAll') {
	$heading = Yii::t('actions','All My Actions'); 
	$dataProvider=$model->searchAll();
	$dataProvider2=$model->searchComplete();
	
	$this->menu=array(
		array('label'=>Yii::t('actions','Today\'s Actions'),'url'=>array('index')),
		array('label'=>Yii::t('actions','All My Actions')),
		array('label'=>Yii::t('actions','Everyone\'s Actions'),'url'=>array('viewGroup')),
		array('label'=>Yii::t('actions','Create Lead'),'url'=>array('quickCreate')),
		array('label'=>Yii::t('actions','Create Action'),'url'=>array('create')),
	);
}else{
	$heading = Yii::t('actions','Everyone\'s Actions'); 
	$dataProvider=$model->searchGroup();
	$dataProvider2=$model->searchAllComplete();
	
	$this->menu=array(
		array('label'=>Yii::t('actions','Today\'s Actions'),'url'=>array('index')),
		array('label'=>Yii::t('actions','All My Actions'),'url'=>array('viewAll')),
		array('label'=>Yii::t('actions','Everyone\'s Actions')),
		array('label'=>Yii::t('actions','Create Lead'),'url'=>array('quickCreate')),
		array('label'=>Yii::t('actions','Create Action'),'url'=>array('create')),
	);
}

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

function trimText($text) {
	if(strlen($text)>150)
		return substr($text,0,147).'...';
	else
		return $text;
}

?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model, 
)); ?>
</div><!-- search-form -->
<?php

$this->widget('application.components.X2GridView', array(
	'id'=>'actions-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.$heading.'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Actions',
	'viewName'=>'actions',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'actionDescription'=>257,
		'associationName'=>132,
		'dueDate'=>91,
		'assignedTo'=>105,
	),
	'specialColumns'=>array(
		'actionDescription'=>array(
			'name'=>'actionDescription',
			'value'=>'CHtml::link(($data->type=="attachment")? MediaChild::attachmentActionText($data->actionDescription) : trimText($data->actionDescription),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'associationName'=>array(
			'name'=>'associationName',
			'header'=>Yii::t('actions','Association Name'),
			'value'=>'$data->associationName=="None" ? Yii::t("app","None") : CHtml::link($data->associationName,array("./".$data->associationType."/view","id"=>$data->associationId))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
));

echo "<br />\n";

$this->widget('application.components.X2GridView', array(
	'id'=>'actionsComplete-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('actions','Completed Actions').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1))// . ' | '
		// .CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider2,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Actions',
	'viewName'=>'actionscomplete',
	'enableGvSettings'=>false,
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'actionDescription'=>257,
		'associationName'=>113,
		'completeDate'=>110,
		'completedBy'=>105,
	),
	'specialColumns'=>array(
		'actionDescription'=>array(
			'name'=>'actionDescription',
			'value'=>'CHtml::link(($data->type=="attachment")? MediaChild::attachmentActionText($data->actionDescription) : trimText($data->actionDescription),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'associationName'=>array(
			'name'=>'associationName',
			'header'=>Yii::t('actions','Association Name'),
			'value'=>'$data->associationName=="None" ? Yii::t("app","None") : CHtml::link($data->associationName,array("./".$data->associationType."/view","id"=>$data->associationId))',
			'type'=>'raw',
		),
		'completedBy'=>array(
			'name'=>'completedBy',
			'header'=>Yii::t('actions','Completed By'),
			'value'=>'UserChild::getUserLinks($data->completedBy)',
			'type'=>'raw',
		)
	),
	'enableControls'=>true,
));
?>


