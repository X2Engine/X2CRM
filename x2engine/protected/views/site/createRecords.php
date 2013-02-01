<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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
?>

<?php 
$form=$this->beginWidget('CActiveForm', array(
   'id'=>'create-records-form',
   'enableAjaxValidation'=>false,
)); ?>
<div class="page-title"><h2><?php echo Yii::t('quotes','Contact'); ?></h2></div>
<?php $this->renderPartial('application.components.views._form', array('model'=>$contact, 'users'=>$users,'modelName'=>'contacts', 'form'=>$form, 'isQuickCreate'=>true, 'hideAccount'=>true)); ?>

<div class="page-title"><h2><?php echo Yii::t('quotes','Account'); ?></h2></div>
<?php $this->renderPartial('application.components.views._form', array('model'=>$account, 'users'=>$users,'modelName'=>'accounts', 'form'=>$form, 'isQuickCreate'=>true)); ?>

<div class="page-title"><h2><?php echo Yii::t('quotes','Opportunity'); ?></h2></div>
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
