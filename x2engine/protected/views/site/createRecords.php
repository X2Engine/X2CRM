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
?>

<?php
$form=$this->beginWidget('CActiveForm', array(
   'id'=>'create-records-form',
   'enableAjaxValidation'=>false,
)); ?>
<div class="page-title"><h2><?php echo Yii::t('contacts','Contact'); ?></h2></div>
<?php $this->renderPartial('application.components.views._form', array('model'=>$contact, 'users'=>$users,'modelName'=>'contacts', 'form'=>$form, 'isQuickCreate'=>true, 'hideAccount'=>true)); ?>

<div class="page-title rounded-top"><h2><?php echo Yii::t('quotes','Account'); ?></h2></div>
<?php $this->renderPartial('application.components.views._form', array('model'=>$account, 'users'=>$users,'modelName'=>'accounts', 'form'=>$form, 'isQuickCreate'=>true)); ?>

<div class="page-title rounded-top"><h2><?php echo Yii::t('opportunities','Opportunity'); ?></h2></div>
<?php $this->renderPartial('application.components.views._form', array('model'=>$opportunity, 'users'=>$users,'modelName'=>'Opportunity', 'form'=>$form, 'isQuickCreate'=>true, 'hideAccount'=>true)); ?>

<div class="row buttons">
	<?php echo CHtml::submitButton(Yii::t('app','Create'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n"; ?>
</div>
<?php $this->endWidget();?>

<?php Yii::app()->clientScript->registerScript('set-account-phone-website', "
$(function() {
	// first time user sets contact phone and website copy the values to account
	$('div.formInputBox #Contacts_phone').data('setAccountPhone', true);
	$('div.formInputBox #Contacts_website').data('setAccountWebsite', true);
	$('div.formInputBox #Contacts_phone').blur(function() {
		if($('div.formInputBox #Contacts_phone').data('setAccountPhone') == true && $('#Accounts_phone').val() == '' && $('div.formInputBox #Contacts_phone').val() != '') {
			$('#Accounts_phone').val($('div.formInputBox #Contacts_phone').val());
			$('div.formInputBox #Contacts_phone').data('setAccountPhone', false); // only set phone once
		}
	});
	$('div.formInputBox #Contacts_website').blur(function() {
		if($('div.formInputBox #Contacts_website').data('setAccountWebsite') == true && $('#Accounts_website').val() == '' && $('div.formInputBox #Contacts_website').val() != '') {
			$('#Accounts_website').val($('div.formInputBox #Contacts_website').val());
			$('div.formInputBox #Contacts_website').data('setAccountWebsite', false); // only set website once
		}
	});
});
"); ?>
