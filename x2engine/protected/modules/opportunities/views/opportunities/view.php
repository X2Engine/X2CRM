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

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('opportunities','Opportunities List'), 'url'=>array('index')),
	array('label'=>Yii::t('opportunities','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('opportunities','View')),
	array('label'=>Yii::t('opportunities','Edit Opportunity'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Opportunity'),'url'=>array('shareOpportunity','id'=>$model->id)),
	array('label'=>Yii::t('contacts','View Relationships'),'url'=>'#', 'linkOptions'=>array('onclick'=>'toggleRelationshipsForm(); return false;')),
	array('label'=>Yii::t('contacts','Create Contact'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-contact')),
	array('label'=>Yii::t('contacts','Create Account'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-account')),
	array('label'=>Yii::t('opportunities','Add A User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Add A Contact'), 'url'=>array('addContact', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Remove A User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Remove A Contact'), 'url'=>array('removeContact', 'id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
),$authParams);
?>
<div id="main-column" class="half-width">
<div class="record-title">
<?php echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
<h2><?php echo Yii::t('opportunities','Opportunity:'); ?> <b><?php echo $model->name; ?></b> <a class="x2-button" href="update/<?php echo $model->id;?>">Edit</a></h2>
</div>
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));

$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Opportunity'));
$this->endWidget();

$this->widget('InlineTags', array('model'=>$model, 'modelName'=>'opportunities'));

// render workflow box
// $this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
$this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
?>
<?php $this->widget('Attachments',array('associationType'=>'opportunities','associationId'=>$model->id,'startHidden'=>true)); ?>

<?php
$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'Opportunity'));

$linkModel = CActiveRecord::model('Accounts')->findByPk($model->accountName);
if (isset($linkModel))
	$accountName = json_encode($linkModel->name);
else
	$accountName = json_encode('');
$createContactUrl = $this->createUrl('/contacts/create');
$createAccountUrl = $this->createUrl('/accounts/create');
$assignedTo = json_encode($model->assignedTo);
$contactTooltip = json_encode(Yii::t('contacts', 'Create a new Contact associated with this Opportunity.'));
$accountsTooltip = json_encode(Yii::t('contacts', 'Create a new Account associated with this Opportunity.'));

Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
		// init create account button
		$('#create-account').initCreateAccountDialog2('$createAccountUrl', 'Opportunity', '{$model->id}', $accountName, $assignedTo, '', '', $accountsTooltip);
		
		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Opportunity', '{$model->id}', $accountName, $assignedTo, '', '', $contactTooltip);
	});
");

?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'opportunities',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'opportunities','associationId'=>$model->id));
?>
</div>

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>