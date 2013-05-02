<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
    			window.location = '". Yii::app()->createUrl('quotes/quotes/update/', array('id'=>$model->id)) ."';
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
	if($strict && Yii::app()->user->name != 'admin')
		$this->actionMenu[] = array('label'=>Yii::t('quotes','Update'), 'url'=>'#', 'linkOptions'=>array('onClick'=>'dialogStrictLock();'));
	else
		$this->actionMenu[] = array('label'=>Yii::t('quotes','Update'), 'url'=>'#', 'linkOptions'=>array('onClick'=>'dialogLock();'));
else
	$this->actionMenu[] = array('label'=>Yii::t('quotes','Update'), 'url'=>array('update', 'id'=>$model->id));

$this->actionMenu[] = array('label'=>Yii::t('quotes','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?'));
$this->actionMenu[] = array('label'=>Yii::t('app','Attach A File/Photo'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;'));
$this->actionMenu[] = array('label'=>($model->type == 'invoice'? Yii::t('quotes', 'Print Invoice') : Yii::t('quotes','Print Quote')), 'url'=>'#', 'linkOptions'=>array('onClick'=>"window.open('". Yii::app()->createUrl('quotes/quotes/print', array('id'=>$model->id)) ."')"));
$themeUrl = Yii::app()->theme->getBaseUrl();

?>
<div id="main-column" class="half-width">
<div class="page-title">
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo ($model->type == 'invoice'? Yii::t('quotes', 'Invoice:') : Yii::t('quotes','Quote:')); ?></span> <?php echo $model->name; ?></h2>

<?php if($model->locked) { ?>
	<?php if($strict && Yii::app()->user->name != 'admin') { ?>
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
								<label><?php echo Yii::t('media', 'Invoice Status'); ?></label>
								<div class="formInputBox" style="width: 150px; height: auto;">
									<?php echo $model->renderAttribute('invoiceStatus'); ?>
								</div>
							</div>
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Invoice Created'); ?></label>
								<div class="formInputBox" style="width: 150px; height: auto;">
									<?php echo $model->renderAttribute('invoiceCreateDate'); ?>
								</div>
							</div>
						</td>
						<td style="width: 300px">
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Invoice Issued'); ?></label>
								<div class="formInputBox" style="width: 150px; height: auto;">
									<?php echo $model->renderAttribute('invoiceIssuedDate'); ?>
								</div>
							</div>
							<div class="formItem leftLabel">
								<label><?php echo Yii::t('media', 'Invoice Payed'); ?></label>
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

<div class="form">
	<b><?php echo Yii::t('app', 'Tags'); ?></b>
	<?php $this->widget('InlineTags', array('model'=>$model)); ?>
</div>

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
