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
	'template'=> '{items}',
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

