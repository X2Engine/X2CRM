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
 
Yii::app()->clientScript->registerScript('getWorkflowStage',"

function getStageMembers(stage) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('workflow/getStageMembers')) . "',
		type: 'GET',
		data: 'workflowId=".$model->id."&stage='+stage+'&modelId=".$model->id."&type=contacts',
		success: function(response) {
			if(response!='')
				$('#workflow-gridview').html(response);
		}
	});
}
",CClientScript::POS_HEAD);
$isAdmin = (Yii::app()->user->getName()=='admin');
$this->menu=array(
	array('label'=>Yii::t('workflow','List Workflows'), 'url'=>array('index')),
	array('label'=>Yii::t('workflow','Create Workflow'), 'url'=>array('create'), 'visible'=>$isAdmin),
	array('label'=>Yii::t('workflow','View Workflow')),
	array('label'=>Yii::t('workflow','Update Workflow'), 'url'=>array('update', 'id'=>$model->id), 'visible'=>$isAdmin),
	array('label'=>Yii::t('workflow','Delete Workflow'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?')), 'visible'=>$isAdmin),
);
?>

<h2><?php echo Yii::t('workflow','Workflow:'); ?> <b><?php echo $model->name; ?></b></h2>

<?php

$workflowStatus = Workflow::getWorkflowStatus($model->id);	// true = include dropdowns
echo Workflow::renderWorkflowStats($workflowStatus);
?>
<br />
<div id="workflow-gridview">
<?php
if(isset($viewStage))
	echo Yii::app()->controller->actionGetStageMembers($model->id,$viewStage);
else {
$this->widget('zii.widgets.grid.CGridView', array(
	// 'id'=>'docs-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '{items}{pager}',
	'dataProvider'=>CActiveRecord::model('WorkflowStage')->search($model->id),
	// 'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'stageNumber',
			'header'=>'#',
			'headerHtmlOptions'=>array('style'=>'width:8%;'),
		),
		array(
			'name'=>'name',
			// 'value'=>'CHtml::link($data->title,array("view","id"=>$data->name))',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'30%'),
		),
		array(
			'name'=>'conversionRate',
			// 'value'=>'UserChild::getUserLinks($data->createdBy)',
			// 'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:25%;'),
		),
		array(
			'name'=>'value',
			// 'value'=>'UserChild::getUserLinks($data->createdBy)',
			// 'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:25%;'),
		),
	),
));
}
?>
</div>

