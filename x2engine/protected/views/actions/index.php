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


