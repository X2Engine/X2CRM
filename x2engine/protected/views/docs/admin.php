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

$this->menu=array(
	array('label'=>Yii::t('docs','List Docs'), 'url'=>array('index')),
	array('label'=>Yii::t('docs','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('docs','Create Email'), 'url'=>array('createEmail')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('docs-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>
<h2><?php echo Yii::t('docs','Manage Docs'); ?></h2>
<?php echo Yii::t('app','You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.'); ?>
<br />
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'docs-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'createdBy',
		'updatedBy',
		'createDate',
		'lastUpdated',	
		'title',
		
		array(
			'class'=>'CButtonColumn',
		),
	),
));
/* $this->widget('application.components.X2GridView', array(
	'id'=>'accounts-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('accounts','Accounts').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	// 'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'DocChild',
	'viewName'=>'docsadmin',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'title'=>213,
		'createdBy'=>117,
		'lastUpdated'=>100,
		'createDate'=>89,
		'gvControls'=>66,
	),
	'specialColumns'=>array(
		array(
			'header'=>Yii::t('docs','Title'),
			'name'=>'title',
			'value'=>'CHtml::link($data->title,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		array(
			'header'=>Yii::t('docs','Type'),
			'name'=>'title',
			'value'=>'$data->parseType()',
			'type'=>'raw',
		),
		array(
			'header'=>Yii::t('docs','Created By'),
			'name'=>'createdBy',
			'value'=>'UserChild::getUserLinks($data->createdBy)',
			'type'=>'raw',
		),
		array(
			'header'=>Yii::t('docs','Updated By'),
			'name'=>'updatedBy',
			'value'=>'UserChild::getUserLinks($data->updatedBy)',
			'type'=>'raw',
		),
		array(
			'header'=>Yii::t('docs','Last Updated'),
			'name'=>'lastUpdated',
			'value'=>'date("Y-m-d",$data->lastUpdated)',
			'type'=>'raw',
		),
		array(
			'header'=>Yii::t('docs','Create Date'),
			'name'=>'createDate',
			'value'=>'date("Y-m-d",$data->createDate)',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
)); */
?><br />
<?php
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'attachments-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('docs','Uploaded Documents').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$attachments,
	'columns'=>array(
		array(
			'name'=>'fileName',
			'value'=>'CHtml::link($data->fileName,array("media/view","id"=>$data->id))',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'30%'),
		),
		array(
			'name'=>'uploadedBy',
			'value'=>'UserChild::getUserLinks($data->uploadedBy)',
			'type'=>'raw',
		),
		array(
			'name'=>'createDate',
			'type'=>'raw',
			'value'=>'Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"), $data->createDate)',
		),
	),
)); ?>


<div id="attachment-form">
	<?php $this->widget('Attachments',array('type'=>'docs','associationId'=>$model->id)); ?>
</div>
