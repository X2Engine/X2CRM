<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

$authParams['assignedTo'] = $model->assignedTo;
$menuItems = array(
	array('label'=>Yii::t('opportunities','Opportunities List'), 'url'=>array('index')),
	array('label'=>Yii::t('opportunities','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('opportunities','View')),
	array('label'=>Yii::t('opportunities','Edit Opportunity'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Opportunity'),'url'=>array('shareOpportunity','id'=>$model->id)),
	array('label'=>Yii::t('opportunities','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
);
$modelType = json_encode("Opportunities");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

if($contactModule->visible && $accountModule->visible)
	$menuItems[] = 	array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'opportunities'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.')));


$menuItems[] = array(
	'label' => Yii::t('app', 'Print Record'), 
	'url' => '#',
	'linkOptions' => array (
		'onClick'=>"window.open('".
			Yii::app()->createUrl('/site/printRecord', array (
				'modelClass' => 'Opportunity', 
				'id' => $model->id, 
				'pageTitle' => Yii::t('app', 'Opportunity').': '.$model->name
			))."');"
	)
);

$this->actionMenu = $this->formatMenu($menuItems, $authParams);
$themeUrl = Yii::app()->theme->getBaseUrl();
?>
<div class="page-title icon opportunities">
	<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
	<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo Yii::t('opportunities','Opportunity:'); ?> </span><?php echo $model->name; ?></h2>
	<?php echo CHtml::link('<span></span>',array('update', 'id'=>$model->id),array('class'=>'x2-button icon edit right')); ?>
</div>
<div id="main-column" class="half-width">
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));

$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Opportunity'));
$this->endWidget();

$this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'opportunities'));

// $this->widget('InlineTags', array('model'=>$model));

// render workflow box
// $this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
// $this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
?>
<?php $this->widget('Attachments',array('associationType'=>'opportunities','associationId'=>$model->id,'startHidden'=>true)); ?>

<?php
//$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'Opportunity'));

$linkModel = X2Model::model('Accounts')->findByPk($model->accountName);
if (isset($linkModel))
	$accountName = json_encode($linkModel->name);
else
	$accountName = json_encode('');
$createContactUrl = $this->createUrl('/contacts/contacts/create');
$createAccountUrl = $this->createUrl('/accounts/accounts/create');
$createOpportunityUrl=$this->createUrl('/opportunities/opportunities/create');
$assignedTo = json_encode($model->assignedTo);
$tooltip = json_encode(Yii::t('opportunities', 'Create a new Opportunity associated with this Opportunity.'));
$contactTooltip = json_encode(Yii::t('opportunities', 'Create a new Contact associated with this Opportunity.'));
$accountsTooltip = json_encode(Yii::t('opportunities', 'Create a new Account associated with this Opportunity.'));

Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
        // init create opportunity button
		$('#create-opportunity').initCreateOpportunityDialog('$createOpportunityUrl', 'Opportunity', {$model->id}, $accountName, $assignedTo, $tooltip);

		// init create account button
		$('#create-account').initCreateAccountDialog2('$createAccountUrl', 'Opportunity', '{$model->id}', $accountName, $assignedTo, '', '', $accountsTooltip);

		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Opportunity', '{$model->id}', $accountName, $assignedTo, '', '', $contactTooltip, '', '', '');
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
