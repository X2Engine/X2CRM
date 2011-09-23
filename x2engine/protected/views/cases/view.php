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

$this->menu=array(
	array('label'=>Yii::t('cases','Case List'), 'url'=>array('index')),
	array('label'=>Yii::t('cases','Create Case'), 'url'=>array('create')),
	array('label'=>Yii::t('cases','View Case')),
	array('label'=>Yii::t('cases','Update Case'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('cases','Close Case'),'url'=>array('closeCase','id'=>$model->id)),
);
?>
<h1><?php echo Yii::t('cases','Case: {name}',array('{name}'=>$model->name)); ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/detailview',
	'attributes'=>array(
		'name',
		'status',
		'type',
		'priority',
		'endDate',
		'timeframe',
		array(
			'name'=>'assignedTo',
			'value'=>$model->assignedTo,
			'type'=>'raw',
		),
		array(
			'name'=>'associatedContacts',
			'value'=>$model->associatedContacts,
			'type'=>'raw',
		),
		'description',
		array(
			'name'=>'createDate',
			'type'=>'raw',
			'value'=>date("Y-m-d H:i:s",$model->createDate),
		),
	),
)); ?><br />

<b><center><?php echo CHtml::link('Create Action',array('/actions/newCreate','param'=>Yii::app()->user->getName().";case:".$model->id)); ?></center></b><br /><br /><br /><br />

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
			 array(\'actions/view\',\'id\'=>$data->id))',
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
    <input type="hidden" name="type" value="case" />
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
