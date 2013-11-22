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

// quotes can be locked meaning they can't be changed anymore
Yii::app()->clientScript->registerScript('LockedQuoteDialog', "
function dialogStrictLock() {
var denyBox = $('<div></div>')
    .html('This quote is locked.')
    .dialog({
    	title: 'Locked',
    	autoOpen: false,
    	resizable: false,
    	buttons: {
    		'OK': function() {
    			$(this).dialog('close');
    		}
    	}
    });

denyBox.dialog('open');
}

function dialogLock() {
var confirmBox = $('<div></div>')
    .html('This quote is locked. Are you sure you want to update this quote?')
    .dialog({
    	title: 'Locked',
    	autoOpen: false,
    	resizable: false,
    	buttons: {
    		'Yes': function() {
    			window.location = '". Yii::app()->createUrl('/quotes/quotes/update', array('id'=>$model->id)) ."';
    			$(this).dialog('close');
    		},
    		'No': function() {
    			$(this).dialog('close');
    		}
    	}
    });
confirmBox.dialog('open');
}

",CClientScript::POS_HEAD);
$modelType = json_encode("Quotes");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
if($contactId) {
	$contact = Contacts::model()->findByPk($contactId); // used to determine if 'Send Email' menu item is displayed
} else {
  $contact = false;
}

$authParams['assignedTo']=$model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('quotes','Quotes List'), 'url'=>array('index')),
	array('label'=>Yii::t('quotes','Invoice List'), 'url'=>array('indexInvoice')),
	array('label'=>Yii::t('quotes','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('quotes','View')),
	array('label'=>Yii::t('app','Email '.($model->type=='invoice'?'Invoice':'Quote')),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;'),'visible'=>(bool) $contact),
),$authParams);

$strict = Yii::app()->params['admin']['quoteStrictLock'];
if($model->locked)
	if($strict && !Yii::app()->user->checkAccess('QuotesAdminAccess'))
		$this->actionMenu[] = array('label'=>Yii::t('quotes','Update'), 'url'=>'#', 'linkOptions'=>array('onClick'=>'dialogStrictLock();'));
	else
		$this->actionMenu[] = array('label'=>Yii::t('quotes','Update'), 'url'=>'#', 'linkOptions'=>array('onClick'=>'dialogLock();'));
else
	$this->actionMenu[] = array('label'=>Yii::t('quotes','Update'), 'url'=>array('update', 'id'=>$model->id));

$this->actionMenu[] = array('label'=>Yii::t('quotes','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?'));
$this->actionMenu[] = array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;'));
$this->actionMenu[] = array(
	'label'=>($model->type == 'invoice'? Yii::t('quotes', 'Print Invoice') : Yii::t('quotes','Print Quote')), 
	'url'=>'#', 'linkOptions'=>array(
		'onClick'=>"window.open('". Yii::app()->createUrl('/quotes/quotes/print', 
		array('id'=>$model->id)) ."')")
);
$themeUrl = Yii::app()->theme->getBaseUrl();

?>
<div class="page-title icon quotes">
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo ($model->type == 'invoice'? Yii::t('quotes', 'Invoice:') : Yii::t('quotes','Quote:')); ?></span> <?php echo $model->name==''?'#'.$model->id:$model->name; ?></h2>

<?php if($model->locked) { ?>
	<?php if($strict && !Yii::app()->user->checkAccess('QuotesAdminAccess')) { ?>
		<a class="x2-button icon edit right" href="#" onClick="dialogStrictLock();"><span></span></a>
	<?php } else { ?>
		<a class="x2-button icon edit right" href="#" onClick="dialogLock();"><span></span></a>
	<?php } ?>
<?php } else { ?>
	<a class="x2-button icon edit right" href="update/<?php echo $model->id;?>"><span></span></a>
<?php } ?>

<?php if($model->type != 'invoice') { ?>
	<a class="x2-button right" href="convertToInvoice/<?php echo $model->id;?>"><?php echo Yii::t('quotes', 'Convert To Invoice'); ?></a>
<?php } ?>
</div>
<div id="main-column" class="half-width">
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'quotes-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id)
));

$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Quote'));
?>
<?php
if($model->type == 'invoice') { ?>
<div class="x2-layout form-view">
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle" title="Invoice"><?php echo Yii::t('quotes', 'Invoice'); ?></span>
		</div>
		<div class="tableWrapper">
			<table>
				<tbody>
					<tr class="formSectionRow">
						<td style="width: 300px">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('quotes', 'Invoice Status'); ?></label>
								<div class="formInputBox" style="width: 150px; height: auto;">
									<?php echo $model->renderAttribute('invoiceStatus'); ?>
								</div>
							</div>
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('quotes', 'Invoice Created'); ?></label>
								<div class="formInputBox" style="width: 150px; height: auto;">
									<?php echo $model->renderAttribute('invoiceCreateDate'); ?>
								</div>
							</div>
						</td>
						<td style="width: 300px">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('quotes', 'Invoice Issued'); ?></label>
								<div class="formInputBox" style="width: 150px; height: auto;">
									<?php echo $model->renderAttribute('invoiceIssuedDate'); ?>
								</div>
							</div>
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('quotes', 'Invoice Payed'); ?></label>
								<div class="formInputBox" style="width: 150px; height: auto;">
									<?php echo $model->renderAttribute('invoicePayedDate'); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php } ?>

<?php
  $productField = Fields::model()->findByAttributes(array('modelName'=>'Quote', 'fieldName'=>'products'));
?>
<div class="x2-layout form-view">
	<div class="formSection showSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo $productField->attributeLabel; ?></span>
		</div>
		<div class="tableWrapper">
		<?php
    $this->renderPartial ('_lineItems', array ('model'=>$model,'readOnly'=>true));
		?>
		</div>

	</div>
</div>
<?php
/*
$this->renderPartial('_detailView',
	array(
		'model'=>$model,
		'form'=>$form,
		'currentWorkflow'=>$currentWorkflow,
		'dataProvider'=>$dataProvider,
		'total'=>$total
	)
);
*/
$this->endWidget();
?>

<?php $this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'Quote')); ?>

<?php $this->widget('Attachments',array('associationType'=>'quotes','associationId'=>$model->id,'startHidden'=>true)); ?>

<?php
if($contact){ // if associated contact exists, setup inline email form
	$this->widget('InlineEmailForm', array(
		'attributes' => array(
			'to' => '"'.$contact->name.'" <'.$contact->email.'>, ',
			// 'subject'=>'hi',
			// 'redirect'=>'contacts/'.$model->id,
			'modelName' => 'Quote',
			'modelId' => $model->id,
			'message' => $this->getPrintQuote($model->id, true),
			'subject' => $model->type == ('invoice' ? Yii::t('quotes', 'Invoice') : Yii::t('quotes', 'Quote')).'('.Yii::app()->name.'): '.$model->name,
		),
		'startHidden' => true,
		'templateType' => 'quote',
	)
	);
}

?>

</div>
<div class="history half-width">
<?php

$this->widget('Publisher',
	array(
		'associationType'=>'quotes',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);
$this->widget('History',array('associationType'=>'quotes','associationId'=>$model->id));
?>
