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

$this->pageTitle = $model->name; 

$this->menu=array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View')),
	array('label'=>Yii::t('contacts','Share'),'url'=>array('shareContact','id'=>$model->id)),
);
$this->actionMenu = array(
	array('label'=>Yii::t('app','Send Email'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;')),
	array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleForm("#attachment-form",200); return false;')),
	array('label'=>Yii::t('contacts','Share Contact'),'url'=>array('shareContact','id'=>$model->id)),
	array('label'=>Yii::t('contacts','View Sales'),'url'=>array('viewSales','id'=>$model->id)),
	array('label'=>Yii::t('quotes','Quotes'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleQuotes(); return false;')),
);


$editPermissions = ($model->assignedTo == Yii::app()->user->getName() || Yii::app()->user->getName() == 'admin' || $model->assignedTo == 'Anyone');
/* x2temp */
$groups = GroupToUser::model()->findAllByAttributes(array('userId'=>Yii::app()->user->getId()));
$temp = array();
foreach($groups as $group) {
    $temp[]=Groups::model()->findByPk($group->groupId)->name;
}
if(array_search($model->assignedTo,$temp)!==false){
    $editPermissions=true;
}
/* end x2temp */
if ($editPermissions) {
	$this->menu[] = array('label'=>Yii::t('contacts','Update'), 'url'=>array('update', 'id'=>$model->id));
	$this->menu[] = array('label'=>Yii::t('contacts','Delete'),'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?'));
}

?>
<?php

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
?>
<?php echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<h2><?php echo Yii::t('contacts','Contact:'); ?> <b><?php echo $model->firstName.' '.$model->lastName; ?></b> <a class="x2-button" href="update/<?php echo $model->id;?>"><?php echo Yii::t('app','Edit');?></a></h2>
<?php
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'contacts'));
$this->endWidget();

// render workflow box
$this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'contacts','currentWorkflow'=>$currentWorkflow));
?>
<div id="attachment-form" style="display:none;">
	<?php $this->widget('Attachments',array('type'=>'contacts','associationId'=>$model->id)); ?>
</div>
        
<?php
$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>'"'.$model->name.'" <'.$model->email.'>, ',
			// 'subject'=>'hi',
			// 'redirect'=>'contacts/'.$model->id,
			'modelName'=>'Contacts',
			'modelId'=>$model->id,
		),
		'startHidden'=>true,
	)
);
?>
<div id="quote-form-wrapper">
<?php
$this->widget('InlineQuotes',
	array(
		'startHidden'=>true,
		'contactId'=>$model->id,
	)
);
?>
</div>
<?php
$this->widget('InlineActionForm',
	array(
		'associationType'=>'contacts',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'users'=>$users,
		'startHidden'=>false,
	)
);

if(isset($_GET['history']))
	$history=$_GET['history'];
else
	$history='all';
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$actionHistory,
	'itemView'=>'../actions/_view',
	'id'=>'contact-history',
	'htmlOptions'=>array('class'=>'action list-view'),
	'template'=> 
	($history=='all'?'<h3>'.Yii::t('app','History').'</h3>':CHtml::link(Yii::t('app','History'),'?history=all')).
	' | '.($history=='actions'?'<h3>'.Yii::t('app','Actions').'</h3>':CHtml::link(Yii::t('app','Actions'),'?history=actions')).
	' | '.($history=='comments'?'<h3>'.Yii::t('app','Comments').'</h3>':CHtml::link(Yii::t('app','Comments'),'?history=comments')).
	' | '.($history=='attachments'?'<h3>'.Yii::t('app','Attachments').'</h3>':CHtml::link(Yii::t('app','Attachments'),'?history=attachments')).
	'</h3>{summary}{sorter}{items}{pager}',
));
?>
