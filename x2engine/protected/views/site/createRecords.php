<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



?>

<?php
$form=$this->beginWidget('CActiveForm', array(
   'id'=>'create-records-form',
   'enableAjaxValidation'=>false,
)); ?>
<div class="page-title"><h2><?php echo Yii::t('contacts','Contact'); ?></h2></div>
<?php 
$this->widget ('FormView', array(
	'model' => $contact,
	'form' => $form,
));
//$this->renderPartial('application.components.views.@FORMVIEW', array('model'=>$contact, 'users'=>$users,'modelName'=>'contacts', 'form'=>$form, 'suppressForm'=>true, 'hideAccount'=>true)); ?>

<div class="page-title rounded-top"><h2><?php echo Yii::t('quotes','Account'); ?></h2></div>
<?php 
$this->widget ('FormView', array(
	'model' => $account,
	'form' => $form
));
//$this->renderPartial('application.components.views.@FORMVIEW', array('model'=>$account, 'users'=>$users,'modelName'=>'accounts', 'form'=>$form, 'suppressForm'=>true)); ?>

<div class="page-title rounded-top"><h2><?php echo Yii::t('opportunities','Opportunity'); ?></h2></div>
<?php 
$this->widget ('FormView', array(
	'model' => $opportunity,
	'form' => $form
));
//$this->renderPartial('application.components.views.@FORMVIEW', array('model'=>$opportunity, 'users'=>$users,'modelName'=>'Opportunity', 'form'=>$form, 'suppressForm'=>true, 'hideAccount'=>true)); ?>

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
