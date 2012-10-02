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
$authParams['assignedTo']=$model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('accounts','All Accounts'), 'url'=>array('index')),
	array('label'=>Yii::t('accounts','Create Account'), 'url'=>array('create')),
	array('label'=>Yii::t('accounts','View')),
	array('label'=>Yii::t('accounts','Edit Account'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Account'),'url'=>array('shareAccount','id'=>$model->id)),
	array('label'=>Yii::t('accounts','Add a User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Remove a User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Delete Account'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Attach a File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
),$authParams);
?>
<div id="main-column" class="half-width">
<div class="record-title">
<?php echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>

<h2><?php echo Yii::t('accounts','Account:'); ?> <b><?php echo CHtml::encode($model->name); ?></b> 
<?php if(Yii::app()->user->checkAccess('AccountsUpdate',$authParams)){ ?>
    <a class="x2-button" href="<?php echo $this->createUrl('update/'.$model->id);?>"><?php echo Yii::t('app','Edit');?></a>
<?php } ?>
</h2>
</div>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accounts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'form'=>$form,'modelName'=>'accounts'));

$this->endWidget();

$this->widget('InlineTags', array('model'=>$model, 'modelName'=>'accounts'));
?>

<?php $this->widget('Attachments',array('associationType'=>'accounts','associationId'=>$model->id,'startHidden'=>true)); ?>
<?php
$contactFilter=new Contacts('search');
$contactModel=new Contacts();
$links=Relationships::model()->findAllByAttributes(array('firstType'=>'Contacts', 'secondType'=>'Accounts','secondId'=>$model->id));
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
            'condition'=>($flag?'id IN '.$str:'id=null') . " OR company={$model->id}",
    )
));
//if (intval(Yii::app()->request->getParam('clearFilters'))==1) {
	//		EButtonColumnWithClearFilters::clearFilters($this,$contactModel);//where $this is the controller
	//	}
$this->widget('application.components.X2GridView', array(
	'id'=>'associated-contacts-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('contacts','Associated Contacts').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Clear Filters'),array($model->id,'clearFilters'=>1)) . ' | '
		.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$contactDataProvider,
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$contactFilter,
	// 'columns'=>$columns,
	'modelName'=>'Contacts',
	'viewName'=>'accountcontacts',
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
?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'accounts',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'accounts','associationId'=>$model->id));
?>
</div>