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
$authParams['assignedTo']=$model->assignedTo;
$menuItems = array(
	array('label'=>Yii::t('accounts','All Accounts'), 'url'=>array('index')),
	array('label'=>Yii::t('accounts','Create Account'), 'url'=>array('create')),
	array('label'=>Yii::t('accounts','View')),
	array('label'=>Yii::t('accounts','Edit Account'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Account'),'url'=>array('shareAccount','id'=>$model->id)),
	array('label'=>Yii::t('accounts','Delete Account'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')),
//	array('label'=>Yii::t('quotes','Quotes/Invoices'),'url'=>'javascript:void(0)','linkOptions'=>array('onclick'=>'x2.inlineQuotes.toggle(); return false;')),
);
$modelType = json_encode("Accounts");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));

if($opportunityModule->visible && $contactModule->visible)
	$menuItems[] = 	array('label'=>Yii::t('app', 'Quick Create'), 'url'=>array('/site/createRecords', 'ret'=>'accounts'), 'linkOptions'=>array('id'=>'x2-create-multiple-records-button', 'class'=>'x2-hint', 'title'=>Yii::t('app', 'Create a Contact, Account, and Opportunity.')));

$menuItems[] = array(
	'label' => Yii::t('app', 'Print Record'), 
	'url' => '#',
	'linkOptions' => array (
		'onClick'=>"window.open('".
			Yii::app()->createUrl('/site/printRecord', array (
				'modelClass' => 'Accounts', 
				'id' => $model->id, 
				'pageTitle' => Yii::t('app', 'Account').': '.$model->name
			))."');"
	)
);

$this->actionMenu = $this->formatMenu($menuItems, $authParams);
$themeUrl = Yii::app()->theme->getBaseUrl();
?>
<div class="page-title icon accounts">
	<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
	<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>

	<h2><span class="no-bold"><?php echo Yii::t('accounts','Account:'); ?></span> <?php echo CHtml::encode($model->name); ?></h2>
	<?php
	if(Yii::app()->user->checkAccess('AccountsUpdate',$authParams)){ ?>
		<a class="x2-button icon edit right" href="<?php echo $this->createUrl('update',array('id'=>$model->id));?>"><span></span></a>

	<?php } 
    echo CHtml::link(
        '<img src="'.Yii::app()->request->baseUrl.'/themes/x2engine/images/icons/email_button.png'.
            '"></img>', '#',
        array(
            'class' => 'x2-button icon right email',
            'title' => Yii::t('app', 'Open email form'),
            'onclick' => 'toggleEmailForm(); return false;'
        )
    );
    ?>
</div>
<div id="main-column" class="half-width">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accounts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'form'=>$form,'modelName'=>'accounts'));

$this->endWidget();


$this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'accounts'));

?><?php
$accountContactsArray = array();
foreach($model->relatedX2Models as $relatedModel)
	if($relatedModel instanceof Contacts)
		if($relatedModel->email != '')
			$accountContactsArray[] = '"'.$relatedModel->name.'" <'.$relatedModel->email.'>';
$accountContacts = implode(', ',$accountContactsArray);
// Limit insertable attributes
$insertableAttributes = array();
foreach($model->attributeLabels() as $fieldName => $label) {
	$attr = trim($model->renderAttribute($fieldName,false));
	if($attr !== '')
		$insertableAttributes[$label] = $attr;
}
$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>$accountContacts,
			'modelName'=>'Accounts',
			'modelId'=>$model->id,
		),
		'templateType' => 'accountEmail',
		'insertableAttributes' => array(Yii::t('accounts','Account Attributes')=>$insertableAttributes),
		'startHidden'=>true,
	)
);


/* <div id="quote-form-wrapper"><?php
$this->widget('InlineQuotes',
	 array(
		 'startHidden'=>true,
		 'account'=>$model->name,
	 )
 );
?></div> */
?>

<?php $this->widget('Attachments',array('associationType'=>'accounts','associationId'=>$model->id,'startHidden'=>true)); ?>
<?php
//$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'Accounts'));

$createContactUrl = $this->createUrl('/contacts/contacts/create');
$createOpportunityUrl = $this->createUrl('/opportunities/opportunities/create');
$createAccountUrl = $this->createUrl('/accounts/accounts/create');
$accountName = json_encode($model->name);
$assignedTo = json_encode($model->assignedTo);
$phone = json_encode($model->phone);
$website = json_encode($model->website);
$opportunityTooltip = json_encode(Yii::t('accounts', 'Create a new Opportunity associated with this Account.'));
$contactTooltip = json_encode(Yii::t('accounts', 'Create a new Contact associated with this Account.'));
$accountsTooltip = json_encode(Yii::t('accounts', 'Create a new Account associated with this Account.'));

Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
		// init create opportunity button
		$('#create-opportunity').initCreateOpportunityDialog('$createOpportunityUrl', 'Accounts', '{$model->id}', $accountName, $assignedTo, $opportunityTooltip);

		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Accounts', '{$model->id}', $accountName, $assignedTo, $phone, $website, $contactTooltip, '', '', '');

        // init create account button
		$('#create-account').initCreateAccountDialog2('$createAccountUrl', 'Accounts', '{$model->id}', $accountName, $assignedTo, '', '', $accountsTooltip);
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


