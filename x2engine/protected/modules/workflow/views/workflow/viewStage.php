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

Yii::app()->clientScript->registerScript('getWorkflowStage',"

function getStageMembers(stage) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('/workflow/workflow/getStageMembers')) . "',
		type: 'GET',
		data: 'workflowId=".$model->id."&stage='+stage+'&modelId=".$model->id."&type=contacts',
		success: function(response) {
			if(response!='')
				$('#workflow-gridview').html(response);
		}
	});
}
",CClientScript::POS_HEAD);
$isAdmin = (Yii::app()->params->isAdmin);
$this->menu=array(
	array('label'=>Yii::t('workflow','All Workflows'), 'url'=>array('index')),
	array('label'=>Yii::t('app','Create'), 'url'=>array('create'), 'visible'=>$isAdmin),
	array('label'=>Yii::t('app','View')),
	array('label'=>Yii::t('app','Edit Workflow'), 'url'=>array('update', 'id'=>$model->id), 'visible'=>$isAdmin),
	array('label'=>Yii::t('app','Delete Workflow'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?')), 'visible'=>$isAdmin),
);
?>
<div class="page-title icon workflow"><h2><span class="no-bold"><?php echo Yii::t('workflow','Workflow:'); ?></span><?php echo $model->name; ?></h2></div>
<div style="clear:both;overflow:auto;margin-bottom:10px;">
<?php

$workflowStatus = Workflow::getWorkflowStatus($model->id);	// true = include dropdowns
echo Workflow::renderWorkflowStats($workflowStatus);
?>
</div>
<?php
$this->widget('application.components.X2GridView', array(
	'id'=>'contacts-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'title'=>$heading,
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> '<div class="page-title">{title}{buttons}{filterHint}{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Contacts',
	'viewName'=>'contacts_list'.$listModel->id,
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'gvCheckbox'=>35,
		'name'=>180,
		'phone'=>101,
		'lastUpdated'=>94,
		'leadSource'=>101,
		'gvControls'=>74
	),
	'selectableRows'=>2,
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('contacts','Name'),
			'value'=>'CHtml::link($data->firstName." ".$data->lastName,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'enableTags'=>true,
	'fullscreen'=>true,
));
?>

