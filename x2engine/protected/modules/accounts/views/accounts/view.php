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
	array('label'=>Yii::t('contacts','View Relationships'),'url'=>'#', 'linkOptions'=>array('onclick'=>'toggleRelationshipsForm(); return false;')),
	array('label'=>Yii::t('contacts','Create Contact'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-contact')),
	array('label'=>Yii::t('contacts','Create Opportunity'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-opportunity')),
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
    <a class="x2-button" href="<?php echo $this->createUrl('update',array('id'=>$model->id));?>"><?php echo Yii::t('app','Edit');?></a>
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
$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'Accounts'));

$createContactUrl = $this->createUrl('/contacts/create');
$createOpportunityUrl = $this->createUrl('/opportunities/create');
$accountName = json_encode($model->name);
$assignedTo = json_encode($model->assignedTo);
$phone = json_encode($model->phone);
$website = json_encode($model->website);
$opportunityTooltip = json_encode(Yii::t('contacts', 'Create a new Opportunity associated with this Account.'));
$contactTooltip = json_encode(Yii::t('contacts', 'Create a new Contact associated with this Account.'));

Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
		// init create opportunity button
		$('#create-opportunity').initCreateOpportunityDialog('$createOpportunityUrl', 'Accounts', '{$model->id}', $accountName, $assignedTo, $opportunityTooltip);
		
		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Accounts', '{$model->id}', $accountName, $assignedTo, $phone, $website, $contactTooltip);
	});
");
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

<?php $this->widget('CStarRating',array('name'=>'rating-js-fix', 'htmlOptions'=>array('style'=>'display:none;'))); ?>
