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

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/spectrumSetup.js', CClientScript::POS_END);

//support both the weblead capture and weblist signup
if(empty($type))
	$type = 'weblead';

$height = $type == 'weblist' ? 100 : 350;
$url = $type == 'weblist' ? 'marketing/weblist/weblist' : 'contacts/weblead';

$embedcode = '<iframe src="'. Yii::app()->createAbsoluteUrl($url) .'" frameborder="0" scrolling="no" width="200" height="'. $height .'"></iframe>';

Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/css/webleadForm.css','all');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/webleadFormDesigner.js');

// get form attributes only for generating json
$formAttrs = array();
foreach ($forms as $form) {
	$formAttrs[] = $form->attributes;
}
Yii::app()->clientScript->registerScript('webleadForm','
var savedforms = '.CJSON::encode($formAttrs).';
var embedcode = "'. addslashes($embedcode) .'";
var listId = '.(!empty($id) ? $id : 'null').';
var fields = ["fg","bgc","font","bs","bc","tags"];
var colorfields = ["fg","bgc","bc"];
x2.formSavedMsg = "'.addSlashes(Yii::t('marketing','Form Saved')).'";
x2.nameRequiredMsg = "'.addSlashes(Yii::t('marketing','Name cannot be blank.')).'";
',CClientScript::POS_HEAD);
?>
<div class="form" id="web-lead-form">
<div class="row">
	<h4><?php echo Yii::t('marketing','Embed Code') .':'; ?></h4>
	<textarea id="embedcode"><?php echo $embedcode; ?></textarea><br>
	<?php echo Yii::t('marketing','Copy and paste this code into your website to include the web lead form.'); ?><br><br>
</div>

<div style="margin-bottom: 1em;">
	<h4 style="display: inline;"><?php echo Yii::t('marketing','Saved Forms').':'; ?></h4>
	<?php array_unshift($formAttrs, array('id'=>'0', 'name'=>'------------')); /* so the dropdown will have a blank choice */?>
	<?php echo CHtml::dropDownList('saved-forms', '', CHtml::encodeArray(CHtml::listData($formAttrs, 'id', 'name'))); ?>
	<?php echo CHtml::link(Yii::t('marketing','Reset Form'), '', array('onclick'=>'$("#saved-forms").val("0").change();', 'class'=>'x2-button')); ?>
	<p class="fieldhelp" style="width: auto;"><?php echo Yii::t('marketing','Choose an existing form as a starting point.'); ?></p>
</div>

<div id="settings" class="cell">
	<?php echo CHtml::beginForm(); ?>
	<h4><?php echo Yii::t('marketing','Settings') .':'; ?></h4>
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Text Color'),'fg'); ?>
		<?php echo CHtml::textField('fg'); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','black'); ?></p>
	</div>
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Background Color'), 'bgc'); ?>
		<?php echo CHtml::textField('bgc'); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','transparent'); ?></p>
	</div>
	<?php $fontInput = new FontPickerInput(array('name'=>'font')); ?>
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Font'), 'font'); ?>
		<?php echo $fontInput->render(); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': Arial, Helvetica'; ?></p>
	</div>
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Border'), 'border'); ?>
		<p class="fieldhelp half"><?php echo Yii::t('marketing','Size') .' ('. Yii::t('marketing','pixels') .')'; ?></p>
		<p class="fieldhelp half"><?php echo Yii::t('marketing','Color'); ?></p><br/>
		<?php echo CHtml::textField('bs', '', array('class'=>'half')); ?>
		<?php echo CHtml::textField('bc', '', array('class'=>'half')); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','none'); ?></p>
	</div>
	<div class="row" <?php if ($type != 'weblead') echo 'style="display: none;"'; ?>>
		<?php echo CHtml::label(Yii::t('marketing','Tags'), 'tags'); ?>
		<?php echo CHtml::textField('tags'); ?>
		<p class="fieldhelp"><em><?php echo Yii::t('marketing','Example') .': web,newlead,urgent'; ?></em><br/><?php echo Yii::t('marketing','These tags will be applied to any contact created by the form.'); ?></p>
	</div>
	<div style="display: none;">
		<?php echo CHtml::hiddenField('type', $type); ?>
	</div>
	<h4><?php echo Yii::t('marketing','Save') .':'; ?></h4>
	<div class="row">
		<p class="fieldhelp" style="margin-top:0;"><?php echo Yii::t('marketing','Enter a name and save this form to edit later.'); ?></p>
		<?php echo CHtml::label(Yii::t('marketing','Name'), 'name'); ?>
		<?php echo CHtml::textField('name'); ?>
		<?php echo CHtml::ajaxSubmitButton(Yii::t('marketing','Save'), Yii::app()->createAbsoluteUrl('marketing/webleadForm'), array('success'=>'function(data, status, xhr) { saved(data, status, xhr); }'), array('name'=>'save')); ?>
	</div>
	<?php echo CHtml::endForm(); ?>
</div>

<div class="cell">
	<h4><?php echo Yii::t('marketing','Preview') .':'; ?></h4>
	<div id="iframe_example">
		<?php echo $embedcode; ?>
	</div>
</div>

</div>
