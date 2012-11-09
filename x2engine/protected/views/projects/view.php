<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
	array('label'=>Yii::t('projects','Projects List'), 'url'=>array('index')),
	array('label'=>Yii::t('projects','Create Project'), 'url'=>array('create')),
	array('label'=>Yii::t('projects','View Project')),
	array('label'=>Yii::t('projects','Add A User'), 'url'=>array('addUser','id'=>$model->id)),
	array('label'=>Yii::t('projects','Remove A User'), 'url'=>array('removeUser','id'=>$model->id)),
	array('label'=>Yii::t('projects','Update Project Status'), 'url'=>array('updateStatus','id'=>$model->id)),
	array('label'=>Yii::t('projects','Set Project Due Date'), 'url'=>array('setEndDate','id'=>$model->id)),
	array('label'=>Yii::t('projects','Update Project'), 'url'=>array('update', 'id'=>$model->id)),
);
?>

<h1><?php echo Yii::t('projects','Project: {name}',array('{name}'=>$model->name)); ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
        'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/detailview',
	'attributes'=>array(
		'name',
		'status',
		'type',
		'priority',
		array(
			'name'=>'assignedTo',
			'value'=>$model->assignedTo,
			'type'=>'raw',
		),
		array(
			'name'=>'createDate',
			'type'=>'raw',
			'value'=>date("Y-m-d H:i:s",$model->createDate),
		),
		'endDate',
		'timeframe',
		'description',
	),
)); ?>
<a class="x2-button" href="#" onClick="toggleForm('#action-form',400);return false;"><span><?php echo Yii::t('app','Create To-do Action'); ?></span></a>
<?php
$this->widget('InlineActionForm',
		array(
			'associationType'=>'projects',
			'associationId'=>$model->id,
			'assignedTo'=>Yii::app()->user->getName(),
			'users'=>$users,
			'startHidden'=>true
		)
);
?>
<h2>Action History</h2>

<?php 
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'action-grid',
	'dataProvider'=>$actionHistory,
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview/',
	'columns'=>array(
		array(
			'name'=>'actionDescription',
			'type'=>'raw',
			'value'=>'CHtml::link(CHtml::encode($data->actionDescription),
						 array(\'/actions/view\',\'id\'=>$data->id))',
		),
		'assignedTo',
		'dueDate',

		'priority',
		'type',
		array(
			'name'=>'Status',
			'type'=>'raw',
			'value'=>'$data->complete=="Yes" ? CHtml::link("FINISHED",array("actions/update","id"=>$data->id)) : CHtml::link("Incomplete",array("actions/complete","id"=>$data->id, "param"=>"contact:".$data->associationId))',
		),
		array(
			'name'=>'Action',
			'type'=>'raw',
			'value'=>'CHtml::link("Complete + New",array("actions/completeNew","id"=>$data->id, "param"=>Yii::app()->user->getName().";contact:".$data->associationId))',
		),
	),
));
?>

<h2>Notes</h2>

<form name="noteForm" action="addNote" method="POST">
    <textarea name="note" rows="4" cols="69" onfocus="clearText(this);">Add a note...</textarea><br /><br />
    <input type="hidden" name="type" value="project" />
    <input type="hidden" name="associationId" value="<?php echo $model->id ?>" />
    <input type="submit" value="Add Note!" />
</form>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProviderNotes,
	'itemView'=>'../notes/_view',
)); ?>


<script>

/*
Clear default form value script- By Ada Shimar (ada@chalktv.com)
*/

function clearText(thefield){
if (thefield.defaultValue==thefield.value)
thefield.value = "";
} 
</script>

