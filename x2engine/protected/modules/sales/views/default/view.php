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
	array('label'=>Yii::t('sales','Sales List'), 'url'=>array('index')),
	array('label'=>Yii::t('sales','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('sales','View')),
	array('label'=>Yii::t('sales','Update'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('sales','Add A User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('sales','Add A Contact'), 'url'=>array('addContact', 'id'=>$model->id)),
	array('label'=>Yii::t('sales','Remove A User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('sales','Remove A Contact'), 'url'=>array('removeContact', 'id'=>$model->id)),
	array('label'=>Yii::t('sales','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
);
$this->actionMenu = array(
	array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleForm("#attachment-form",200); return false;')),
	array('label'=>Yii::t('accounts','Share Sale'),'url'=>array('shareSale','id'=>$model->id)),
);
?>
<?php echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<h2><?php echo Yii::t('sales','Sale:'); ?> <b><?php echo $model->name; ?></b> <a class="x2-button" href="update/<?php echo $model->id;?>">Edit</a></h2>

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));

$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'sales'));
$this->endWidget();

$this->widget('InlineTags', array('model'=>$model, 'modelName'=>'sales'));

// render workflow box
// $this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'sales','currentWorkflow'=>$currentWorkflow));
$this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'sales','currentWorkflow'=>$currentWorkflow));
?>

<div id="attachment-form" style="display:none;">
	<?php $this->widget('Attachments',array('type'=>'sales','associationId'=>$model->id)); ?>
</div>

<?php
$contactModel=new Contacts();
$links=Relationships::model()->findAllByAttributes(array('secondType'=>'Sales','secondId'=>$model->id));
$str="(";
foreach($links as $link){
    $str.=$link->firstId.", ";
}
if($str!="("){
    $flag=true;
    $str=substr($str,0,-2).")";
}else
    $flag=false;
$contactDataProvider=new CActiveDataProvider('Contacts',array(
    'criteria'=>array(
            'order'=>'lastName DESC, firstName DESC',
            'condition'=>$flag?'id IN '.$str:'id=null',
    )
));
$this->widget('application.components.X2GridView', array(
	'id'=>'contacts-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('contacts','Associated Contacts').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$contactDataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$contactModel,
	// 'columns'=>$columns,
	'modelName'=>'Contacts',
	'viewName'=>'salecontacts',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'name'=>234,
		'email'=>108,
		'leadsource'=>128,
		'assignedTo'=>115,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('contacts','Name'),
			'value'=>'CHtml::link($data->name,array("/contacts/".$data->id))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
));
echo "<br />";
$this->widget('Publisher',
	array(
		'associationType'=>'sales',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName()
	)
);

if(isset($_GET['history']))
    $history=$_GET['history'];
else
    $history="all";
$this->widget('zii.widgets.CListView', array(
	'id'=>'sales-history',
	'dataProvider'=>$actionHistory,
	'itemView'=>'application.modules.actions.views.default._view',
	'htmlOptions'=>array('class'=>'action list-view'),
	'template'=> 
            ($history=='all'?'<h3>'.Yii::t('app','History')."</h3>":CHtml::link(Yii::t('app','History'),'javascript:$.fn.yiiListView.update("sales-history", {data: "history=all"})')).
            " | ".($history=='actions'?'<h3>'.Yii::t('app','Actions')."</h3>":CHtml::link(Yii::t('app','Actions'),'javascript:$.fn.yiiListView.update("sales-history", {data: "history=actions"})')).
            " | ".($history=='comments'?'<h3>'.Yii::t('app','Comments')."</h3>":CHtml::link(Yii::t('app','Comments'),'javascript:$.fn.yiiListView.update("sales-history", {data: "history=comments"})')).
            " | ".($history=='attachments'?'<h3>'.Yii::t('app','Attachments')."</h3>":CHtml::link(Yii::t('app','Attachments'),'javascript:$.fn.yiiListView.update("sales-history", {data: "history=attachments"})')).
            '</h3>{summary}{sorter}{items}{pager}',
));
