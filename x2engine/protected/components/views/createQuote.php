<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/*
$initScript = "$(function() {\n";
$initScript .= "	addProduct('create');\n";
if($showNewQuote)
	$initScript .= "	$('#show-new-quote-button').hide();\n";
else
	$initScript .= "	$('#new-quote').hide();\n";
$initScript .= "});\n";
*/

Yii::app()->clientScript->registerScript("productTableQuoteCreate", "
$(function() {
	addProduct('create');
	$('#new-quote').hide();
	/*
	$('#create-quote-button').click(function() {
		". CHtml::ajax(array('update'=>'#history-list-wrapper', 'url'=>Yii::app()->createUrl('contacts/quickUpdateHistory', array('id'=>$contactId)))) ."
	}); */
});
", CClientScript::POS_HEAD);

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'quote-form-inside',
	'enableAjaxValidation'=>false,
));
// Get Quotes field 'name'
$nameField = Fields::model()->findByAttributes(array('modelName'=>'Quotes', 'fieldName'=>'name'));
$expirationField = Fields::model()->findByAttributes(array('modelName'=>'Quotes', 'fieldName'=>'expirationDate'));
$existingProductsField = Fields::model()->findByAttributes(array('modelName'=>'Quotes', 'fieldName'=>'existingProducts'));
?>

<?php echo CHtml::button(
	Yii::t('quotes', 'New Quote'), 
	array('id'=>'show-new-quote-button', 'onclick'=>'toggleNewQuote();', 'class'=>'x2-button')
); ?>

<div id="new-quote">
<b><?php echo Yii::t('quotes', 'New Quote'); ?></b>
<br /><br />

<input name="associatedContacts[]" type="hidden" value="<?php echo $contactId; ?>">
<input name="redirect" type="hidden" value="<?php echo Yii::app()->request->url; ?>">

<div class="row">
	<div class="cell">
		<b><?php echo Yii::t('quotes', $nameField->attributeLabel); ?><span class="required">*</span></b>
		<?php echo $form->textField($model,'name',array('size'=>10,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
	<div class="cell">
		<?php echo $form->dropDownList($model,'status', Quote::statusList()); ?>
		<?php echo $form->error($model,'status'); ?>
	</div>
	<div>
		<b><?php echo Yii::t('quotes', $expirationField->attributeLabel); ?></b>
		<?php
		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		CHtml::$liveEvents=true;
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$model, //Model object
			'attribute'=>'expirationDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>'MM dd, yy',
				'timeFormat'=>'',
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			'htmlOptions' => array('liveEvents'=>true),
		)); 
		?>
		<?php echo $form->error($model,'expirationDate'); ?>
	</div>
</div>

<table id="product-table-create" class="product-table">
	<thead>
    	<tr>
    		<th></th>
    		<th>Name</th>
    		<th>Unit</th>
    		<th>Quantity</th>
    		<th>Adjustments</th>
    		<th>Price</th>
    	</tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
    	<tr id="product-list-footer-create">
    		<td></td>
    		<td>
    			<a href="javascript:void(0)" onclick="addProduct('create');" class="add-workflow-stage">
    				[<?php echo Yii::t('workflow','Add'); ?>]
    			</a>
    		</td>
    		<td></td>
    		<td></td>
    		<td><b>Total</b></td>
    		<td><label id="product-list-total-create" style="font-weight: bold; width: auto;">0</label></td>
    	</tr>
    </tfoot>
</table>

<?php echo CHtml::ajaxSubmitButton(
	Yii::t('app','Create'),
	array('quotes/quickCreate'),
	array(
		'success'=>"function(html){
			jQuery('#quote-form-wrapper').html(html);
			". CHtml::ajax(array('update'=>'#history-list-wrapper', 'url'=>Yii::app()->createUrl('contacts/quickUpdateHistory', array('id'=>$contactId)))) ."
		}",
		'type'=>'POST',
		'beforeSend'=>"function(){\$('body').die('a.delete', 'click');}",
	),
	array('id'=>"create-quote-button", 'class'=>'x2-button', 'live'=>false)
); ?>

<?php $this->endWidget(); ?>

</div>
