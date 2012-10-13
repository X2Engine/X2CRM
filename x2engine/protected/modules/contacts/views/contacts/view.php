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
$this->setPageTitle(empty($model->name)?$model->firstName." ".$model->lastName:$model->name);


// find out if we are subscribed to this contact
$result = Yii::app()->db->createCommand()
	->select()
	->from('x2_subscribe_contacts')
	->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id'=>$model->id, 'user_id'=>Yii::app()->user->id))
	->queryAll();
$subscribed = !empty($result); // if we got any results then user is subscribed

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View')),
    array('label'=>Yii::t('contacts','Edit Contact'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('contacts','Share Contact'),'url'=>array('shareContact','id'=>$model->id)),
	array('label'=>Yii::t('contacts','View Relationships'),'url'=>'#', 'linkOptions'=>array('onclick'=>'toggleRelationshipsForm(); return false;')),
	array('label'=>Yii::t('contacts','Create Opportunity'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-opportunity')),
	array('label'=>Yii::t('contacts','Create Account'), 'url'=>'#', 'linkOptions'=>array('onclick'=>'return false;', 'id'=>'create-account')),
    array('label'=>Yii::t('contacts','Delete Contact'),'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Send Email'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;')),
	array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
	array('label'=>Yii::t('quotes','Quotes'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleQuotes(); return false;')),
	array('label'=>Yii::t('quotes',($subscribed? 'Unsubscribe' : 'Subscribe' )), 'url'=>'#', 'linkOptions'=>array('class'=>'x2-subscribe-button', 'onclick'=>'return subscribe($(this));', 'title'=>Yii::t('contacts', 'Receive email updates every time information for {name} changes', array('{name}'=>$model->firstName.' '.$model->lastName)))),
),$authParams);

Yii::app()->clientScript->registerScript('subscribe', "
$(function() {
	$('body').data('subscribed', ". json_encode($subscribed) .");
	$('body').data('subscribeText', '". Yii::t('contacts', 'Subscribe') ."');
	$('body').data('unsubscribeText', '". Yii::t('contacts', 'Unsubscribe') ."');
	
	$('.x2-subscribe-button').qtip();
});

// subscribe or unsubscribe from this contact
function subscribe(link) {
	$('body').data('subscribed', !$('body').data('subscribed')); // subscribe or unsubscribe
	$.post('subscribe', {ContactId: '{$model->id}', Checked: $('body').data('subscribed')}); // tell server to subscribe / unsubscribe
	if( $('body').data('subscribed') )
		link.html($('body').data('unsubscribeText'));
	else
		link.html($('body').data('subscribeText'));
	return false; // stop event propagation
}

",CClientScript::POS_HEAD);


?>
<div id="main-column" class="half-width">
<div class="record-title" style="background-image:url(<?php echo Yii::app()->theme->baseUrl; ?>/images/contacts.png);">
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>


<?php $this->renderPartial('_vcrControls', array('model'=>$model)); ?>
<h2><b><?php echo $this->ucwords_specific($model->name,array('-',"'"),'UTF-8'); ?></b> 
<?php if (Yii::app()->user->checkAccess('ContactsUpdate',$authParams)) { ?>
	<?php echo CHtml::link(Yii::t('app','Edit'),$this->createUrl('update',array('id'=>$model->id)),array('class'=>'x2-button right')); ?>
<?php } ?>

</h2>
</div>

<?php
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'contacts'));

$this->widget('InlineTags', array('model'=>$model, 'modelName'=>'contacts'));

$this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'contacts','currentWorkflow'=>$currentWorkflow));
// render workflow box
// $this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'contacts','currentWorkflow'=>$currentWorkflow));
?>
<?php
$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'contacts'));

$linkModel = CActiveRecord::model('Accounts')->findByPk($model->company);
if (isset($linkModel))
	$accountName = json_encode($linkModel->name);
else
	$accountName = json_encode('');
$createOpportunityUrl = $this->createUrl('/opportunities/create');
$createAccountUrl = $this->createUrl('/accounts/create');
$assignedTo = json_encode($model->assignedTo);
$tooltip = json_encode(Yii::t('contacts', 'Create a new Opportunity associated with this Contact.'));
$accountsTooltip = json_encode(Yii::t('contacts', 'Create a new Account associated with this Contact.'));
$phone = json_encode($model->phone);
$website = json_encode($model->website);
Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
		// init create opportunity button
		$('#create-opportunity').initCreateOpportunityDialog('$createOpportunityUrl', 'Contacts', {$model->id}, $accountName, $assignedTo, $tooltip);

		// init create account button
		$('#create-account').initCreateAccountDialog2('$createAccountUrl', 'Contacts', {$model->id}, $accountName, $assignedTo, $phone, $website, $accountsTooltip);
	});
");
?>
<?php $this->widget('Attachments',array('associationType'=>'contacts','associationId'=>$model->id,'startHidden'=>true)); ?>

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
</div>
<div class="history half-width">
<?php

$this->widget('Publisher',
	array(
		'associationType'=>'contacts',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'contacts','associationId'=>$model->id));
?>
</div>

