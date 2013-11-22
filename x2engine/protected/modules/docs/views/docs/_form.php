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

// editor javascript files
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');



$autosaveUrl = $this->createUrl('autosave').'?id='.$model->id;

$js = '';

if($model->type==='email' || $model->type ==='quote') {
	$attributes = array();
	if($model->type === 'email')
		foreach(X2Model::model('Contacts')->attributeLabels() as $fieldName => $label)
			$attributes[$label] = '{'.$fieldName.'}';
	else {
		$accountAttributes = array();
		$contactAttributes = array();
		$quoteAttributes = array();
		foreach(Contacts::model()->attributeLabels() as $fieldName => $label)
			$contactAttributes[Yii::t('contacts',"Contact").": $label"] = "{Contact.$fieldName}";
		foreach(Accounts::model()->attributeLabels() as $fieldName => $label)
			$accountAttributes[Yii::t('accounts',"Account").": $label"] = "{Account.$fieldName}";
		$quoteAttributes[Yii::t('quotes',"Quote").": ".Yii::t('quotes',"Item Table")] = '{Quote.lineItems}';
		$quoteAttributes[Yii::t('quotes',"Quote").": ".Yii::t('quotes',"Date printed/emailed")] = '{Quote.dateNow}';
		$quoteAttributes[Yii::t('quotes',"Quote").": ".Yii::t('quotes','"Quote" or "Invoice"')] = '{Quote.quoteOrInvoice}';
		foreach(Quote::model()->attributeLabels() as $fieldName => $label)
			$quoteAttributes["Quote: $label"] = "{Quote.$fieldName}";
	}
	if($model->type === 'email') {
		$js = 'x2.insertableAttributes = '.CJSON::encode(array(Yii::t('contacts','Contact Attributes')=>$attributes)).';';
	} else {
		$js = 'x2.insertableAttributes = '.CJSON::encode(array(
			Yii::t('docs','Contact Attributes')=>$contactAttributes,
			Yii::t('docs','Account Attributes')=>$accountAttributes,
			Yii::t('docs','Quote Attributes')=>$quoteAttributes
		)).';';
	}
}

$js .='
var typingTimer;

function autosave() {
	window.docEditor.updateElement();
	$("#savetime").html("'.addslashes(Yii::t('app','Saving...')).'");
	$.post("'.$autosaveUrl.'", $("form").serializeArray(), function(response) {
		$("#savetime").html(response);
	});
}

if(window.docEditor)
	window.docEditor.destroy(true);
window.docEditor = createCKEditor("input",{
	'.($model->type==='email' || $model->type == 'quote' ? 'insertableAttributes:x2.insertableAttributes,':'').'
	// toolbar:"Full",
	fullPage:true,
	height:600
}'.($model->isNewRecord? '' : ',setupAutosave').');
function setupAutosave() {
	if($.browser.msie)
		return;
	// save after 1.5 seconds when the user is done typing

	window.docEditor.document.on("keyup",function(e) {
		clearTimeout(typingTimer);
		typingTimer = setTimeout(autosave, 1500);
	});
	window.docEditor.on("saveSnapshot",function(e) {
		clearTimeout(typingTimer);
		typingTimer = setTimeout(autosave, 1500);
	});
	window.docEditor.document.on("keydown",function(){ clearTimeout(typingTimer); });
}';

Yii::app()->clientScript->registerScript('doc-editor',$js,CClientScript::POS_READY);

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'docs-form',
	'enableAjaxValidation'=>false,
)); ?>
<div class="form no-border">
	<div class="row">
		<div class="cell">
			<?php echo $form->errorSummary($model); ?>
			<?php echo $form->label($model,'name'); ?>
			<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100)); ?>
			<?php echo $form->error($model,'name'); ?>
		</div>
		<div class="cell">
			<?php echo $form->label($model,'visibility'); ?>
			<?php echo $form->dropDownList($model,'visibility',array(1=>Yii::t('app','Public'),0=>Yii::t('app','Private'))); ?>
			<?php echo $form->error($model,'visibility'); ?>
		</div>
		<div class="cell right">
			<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button float')); ?>
		</div>
	</div>
	<div class="row">
        <?php if(in_array($model->type,array('email','quote'))){ ?>
            <?php echo $form->label($model,'subject'); ?>
            <?php echo $form->textField($model,'subject',array('size'=>60,'maxlength'=>255)); ?>
            <?php echo $form->error($model,'subject'); ?>
        <?php } ?>
		<span id="savetime">
			<?php if(isset($_GET['saved'])){
				$date=date("g:i:s A",$_GET['time']);
				echo Yii::t('docs', 'Saved at') ." $date";
			} ?>
		</span>
	</div><?php  ?>
	<div class="row" style="margin-top:5px;">
		<?php
		if($model->isNewRecord && isset($users) && !in_array($model->type,array('email','quote'))){
			echo $form->label($model,'editPermissions');
			echo $form->dropDownList($model,'editPermissions',$users,array('multiple'=>'multiple','size'=>'5'));
			echo $form->error($model,'editPermissions');
		}
		if($model->type == 'email'){
?>
		<div class="row">
	<?php echo Yii::t('docs', '<b>Note:</b> You can use dynamic variables such as {firstName}, {lastName} or {phone} in your template. When you email a contact, these will be replaced by the appropriate value.'); ?>
		</div><?php }elseif($model->type == 'quote'){ ?>
		<div class="row">
	<?php echo Yii::t('docs', '<strong>Note:</strong> You can use dynamic variables such as {Contact.firstName}, {Quote.dateCreated}, {Account.name} etc. in your template. When you email or print the quote, these will be replaced with the appropriate values from the quote or its associated contact/account.'); ?>
		</div>
<?php
}
		echo $form->error($model,'text');
		echo $form->textArea($model,'text',array('id'=>'input'));
		?>
	</div>

</div>
<?php echo $form->error($model,'text'); ?>

<?php $this->endWidget(); ?>
