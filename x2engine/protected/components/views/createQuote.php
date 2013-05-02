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

?>

<?php

$productNames = json_encode((object)Product::productNames());
$prices = json_encode((object)Product::productPrices());
$defaultCurrency = Yii::app()->params['currency'];

Yii::app()->clientScript->registerScript("productTableQuoteCreate", "
$(function() {
	addProduct('create', '$defaultCurrency', $productNames, $prices);
	$('#new-quote').hide();
   	/*
	$('#create-quote-button').click(function() {
		". CHtml::ajax(array('update'=>'#history-list-wrapper', 'url'=>Yii::app()->createUrl('/contacts/quickUpdateHistory', array('id'=>$contactId)))) ."
	}); */
});
", CClientScript::POS_END);

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
	array('id'=>'show-new-quote-button', 'onclick'=>'quickQuote.openForm();', 'class'=>'x2-button')
); ?>


<div id="new-quote">
<b><?php echo Yii::t('quotes', 'New Quote'); ?></b>
<br><br>

<input name="associatedContacts[]" type="hidden" value="<?php echo $contactId; ?>">
<input name="redirect" type="hidden" value="<?php echo Yii::app()->request->url; ?>">

<table class="quote-create-table">
	<tbody>
		<tr>
			<th><?php echo Yii::t('quotes', 'Name'); ?><span class="required">*</span></th>
			<th><?php echo Yii::t('quotes', 'Status'); ?></th>
			<th><?php echo Yii::t('quotes', 'Expires'); ?></th>
		</tr>
		<tr>
			<td>
				<?php echo $form->textField($model,'name',array('size'=>10,'maxlength'=>40)); ?>
				<?php echo $form->error($model,'name'); ?>
			</td>
			<td>
				<?php echo $form->dropDownList($model,'status', Quote::getStatusList()); ?>
				<?php echo $form->error($model,'status'); ?>
				<span style="padding-left: 5px;">
				    <?php echo Yii::t('quotes', 'Locked'); ?>
				    <?php echo $form->checkBox($model, 'locked'); ?>
				</span>
			</td>
			<td>
				<?php Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
				$model->expirationDate = Formatter::formatDate($model->expirationDate);
				$this->widget('CJuiDateTimePicker',array(
					'model'=>$model, //Model object
					'attribute'=>'expirationDate', //attribute name
					'mode'=>'date', //use "time","date" or "datetime" (default)
					'options'=>array(
						'dateFormat'=>Formatter::formatDatePicker(),
						'changeMonth'=>true,
						'changeYear'=>true
					), // jquery plugin options
					'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
					'htmlOptions' => array('id'=>'create-quote-expires'),
				));
				?>
				<?php echo $form->error($model,'expirationDate'); ?>
			</td>
		</tr>
		<tr>
			<th><?php echo Yii::t('quotes', 'Notes/Terms'); ?></th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<td colspan="3">
				<?php echo $form->textArea($model,'description',array('rows'=>3, 'cols'=>50)); ?>
				<?php echo $form->error($model,'description'); ?>
			</td>
		</tr>
	</tbody>
</table>

<table id="product-table-create" class="product-table">
	<thead>
    	<tr>
	    	<th style="padding: 0;"></th>
	    	<th><?php echo Yii::t('products', 'Line Item'); ?></th>
	    	<th><?php echo Yii::t('products', 'Unit Price'); ?></th>
	    	<th><?php echo Yii::t('products', 'Quantity'); ?></th>
	    	<th><?php echo Yii::t('products', 'Adjustments'); ?></th>
	    	<th><?php echo Yii::t('products', 'Price'); ?></th>
    	</tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
    	<tr id="product-list-footer-create">
    		<td></td>
    		<td>
    			<?php echo CHtml::link('['. Yii::t('workflow','Add') .']', 'javascript:void(0)', array('class'=>"add-workflow-stage", 'onClick'=>"addProduct('create', '$defaultCurrency', $productNames, $prices);"));?>
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
	array('/quotes/quotes/quickCreate'),
	array(
		'success'=>"function(html) { jQuery('#quote-form-wrapper').html(html); }",
		'complete'=>"function(response) { $.fn.yiiListView.update('history'); }",
		'type'=>'POST',
	),
	array('id'=>"create-quote-button", 'class'=>'x2-button', 'live'=>false)
); ?>
</div>
<?php $this->endWidget(); ?>

