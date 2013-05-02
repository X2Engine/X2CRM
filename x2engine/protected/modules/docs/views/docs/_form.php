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
			$contactAttributes["Contact: $label"] = '{Contact.'.$fieldName.'}';
		foreach(Accounts::model()->attributeLabels() as $fieldName => $label)
			$accountAttributes["Account: $label"] = '{Account.'.$fieldName.'}';
		$quoteAttributes["Quote: Item Table"] = '{Quote.lineItems}';
		$quoteAttributes["Quote: Date printed/emailed"] = '{Quote.dateNow}';
		foreach(Quote::model()->attributeLabels() as $fieldName => $label)
			$quoteAttributes["Quote: $label"] = '{Quote.'.$fieldName.'}';
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
        <?php if($this->action->id=='createEmail' || ($this->action->id=='update' && $model->type=='email')){ ?>
            <?php echo $form->label($model,'subject'); ?>
            <?php echo $form->textField($model,'subject',array('size'=>60,'maxlength'=>255)); ?>
            <?php echo $form->error($model,'subject'); ?>
        <?php } ?>
		<span id="savetime">
			<?php if(isset($_GET['saved'])){
				$date=date("g:i:s A",$_GET['time']);
				echo Yii::t('Docs', 'Saved at') ." $date";
			} ?>
		</span>
	</div><?php if($this->action->id=='createEmail') { ?>
	<div class="row">
		<?php echo Yii::t('docs','<b>Note:</b> You can use dynamic variables such as {firstName}, {lastName} or {phone} in your template. When you email a contact, these will be replaced by the appropriate value.'); ?>
	</div><?php } ?>
	<div class="row" style="margin-top:5px;">
		<?php 
		if($model->isNewRecord && isset($users)){
			echo $form->label($model,'editPermissions');
			echo $form->dropDownList($model,'editPermissions',$users,array('multiple'=>'multiple','size'=>'5'));
			echo $form->error($model,'editPermissions');
		}
		echo $form->error($model,'text');
		echo $form->textArea($model,'text',array('id'=>'input'));
		?>
	</div>

</div>
<?php echo $form->error($model,'text'); ?>

<?php $this->endWidget(); ?>