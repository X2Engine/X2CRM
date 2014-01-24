<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
$jsProductList = "\$(productList).append(\$('<option>', {value: 0}).append(''));\n";
$jsProductPrices = "var prices = [];\n";
$jsProductPrices .= "prices[0] = 0;\n";
foreach($products as $product) {
	$name = json_encode($product->name);
	$jsProductList .= "\$(productList).append(\$('<option>', {value: {$product->id}}).append($name));\n";
	$jsProductPrices .= "prices[{$product->id}] = ".(!is_null($product->price)?$product->price:0).";\n";
}

if(!empty($model->currency))
	$currency = "'".$model->currency."'";
else
	$currency = "'".Yii::app()->params['currency']."'";
// translate ISO 4217 currency into i18n
$region = array(
	'USD'=>'en-US',
	'EUR'=>'hsb-DE',
	'GBP'=>'en-GB',
	'CAD'=>'en-CA',
	'JPY'=>'ja-JP',
	'CNY'=>'zh-CN',
	'CHF'=>'de-CH',
	'INR'=>'hi-IN',
	'BRL'=>'pt-BR',
);

$productTableScript = "

// translate ISO 4217 currency into i18n
var currencyTable = {
	'USD': 'en-US',
	'EUR': 'hsb-DE',
	'GBP': 'en-GB',
	'CAD': 'en-CA',
	'JPY': 'ja-JP',
	'CNY': 'zh-CN',
	'CHF': 'de-CH',
	'INR': 'hi-IN',
	'BRL': 'pt-BR',
};

function removeProduct(object) {
	$(object).closest('tr').remove();
	updateProductTotal();
}

function updateProduct(price, quantity, adjustments, label) {
	price = parseFloat(price);
	quantity = parseFloat(quantity);
	var total = price * quantity;
	var index = $.inArray ('%', adjustments);
	if(index == -1) { // adjustment
	    total += parseFloat(adjustments);
	} else { // percent adjustment
	    adjustments = adjustments.substring(0, index);
	    adjustments = parseFloat(adjustments) / 100;
	    total += total * adjustments;
	}
	$(label).html('' + total);
	$(label).formatCurrency({'region': currencyTable[$currency]});
	updateProductTotal();
}

function updateProductTotal() {
	var total = 0;
	$('.product-list-price').each(function () {
		$(this).toNumber({'region': currencyTable[$currency]});
	    total += parseFloat($(this).html());
	    $(this).formatCurrency({'region': currencyTable[$currency]});
	});
	$('#product-list-total').html('' + total);
	$('#product-list-total').formatCurrency({'region': currencyTable[$currency]});

}

function addProduct() {
	var row = $('<tr></tr>');
	$('#product-list-footer').before(row);

	var td = $('<td></td>');
	
	var remove = $('<a>', {
		href: 'javascript:void(0)',
		'onClick': 'removeProduct(this);'
	});
	var removeImage = $('<img>', {
		src: '". Yii::app()->request->baseUrl .'/themes/x2engine/css/gridview/delete.png' . "',
		alt: '[". Yii::t('quotes', 'Delete Quote') ."]'
	});
	$(row).append(td.clone().append(remove));
	$(remove).append(removeImage);
	
	var productList = $('<select>', {
		name: 'ExistingProducts[id][]'
	});
	$(row).append(td.clone().append(productList));
	". $jsProductList ."
	
	var price = $('<input>', {
		type: 'text',
		size: 10,
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		'style': 'color:#aaa;',
		name: 'ExistingProducts[price][]',
		value: 0
	});
	$(row).append(td.clone().append(price));
	
	var quantity = $('<input>', {
		type: 'text',
		size: 10,
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		'style': 'color:#aaa;',
		name: 'ExistingProducts[quantity][]',
		value: 0
	});
	$(row).append(td.clone().append(quantity));
	
	var adjustments = $('<input>', {
		type: 'text',
		size: 10,
		title: '-5, -4.50, -25%',
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		'style': 'color:#aaa;',
		name: 'ExistingProducts[adjustment][]',
		value: 0
	});
	$(row).append(td.clone().append(adjustments));
	
	var label = $('<label>', {
		'class': 'product-list-price'
	});
	$(row).append(td.clone().append(label));
	$(label).append('0');

	". $jsProductPrices ."
	$(productList).change(function() {
		$(price).val('' + prices[$(this).attr('value')]);
		$(price).css('color', 'black');
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
	
	$(price).change(function() {
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
	
	$(quantity).change(function() {
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
	
	$(adjustments).change(function() {
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
}

function addFilledProduct(fillId, fillPrice, fillQuantity, fillAdjustment) {
	var row = $('<tr></tr>');
	$('#product-list-footer').before(row);

	var td = $('<td></td>');
	
	var remove = $('<a>', {
		href: 'javascript:void(0)',
		'onClick': 'removeProduct(this);'
	});
	var removeImage = $('<img>', {
		src: '". Yii::app()->request->baseUrl .'/themes/x2engine/css/gridview/delete.png' . "',
		alt: '[". Yii::t('quotes', 'Delete Quote') ."]'
	});
	$(row).append(td.clone().append(remove));
	$(remove).append(removeImage);
	
	var productList = $('<select>', {
		name: 'ExistingProducts[id][]',
	});
	$(row).append(td.clone().append(productList));
	". $jsProductList ."
	$(productList).val(fillId);
	
	var price = $('<input>', {
		type: 'text',
		size: 10,
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		name: 'ExistingProducts[price][]',
		value: 0,
	});
	$(row).append(td.clone().append(price));
	$(price).val(fillPrice);
	if(fillPrice == 0) {
		$(price).css('color', '#aaa');
	}
	
	var quantity = $('<input>', {
		type: 'text',
		size: 10,
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		name: 'ExistingProducts[quantity][]',
		value: 0,
	});
	$(row).append(td.clone().append(quantity));
	$(quantity).val(fillQuantity);
	if(fillQuantity == 0) {
		$(quantity).css('color', '#aaa');
	}
	
	var adjustments = $('<input>', {
		type: 'text',
		size: 10,
		title: '-5, -4.50, -25%',
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		name: 'ExistingProducts[adjustment][]',
		value: 0,
	});
	$(row).append(td.clone().append(adjustments));
	$(adjustments).val(fillAdjustment);
	if(fillAdjustment == 0) { 
		$(adjustments).css('color', '#aaa');
	}
	
	var label = $('<label>', {
		'class': 'product-list-price'
	});
	$(row).append(td.clone().append(label));
	$(label).append('0');
	
	updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);

	". $jsProductPrices ."
	$(productList).change(function() {
		$(price).val('' + prices[$(this).attr('value')]);
		$(price).css('color', 'black');
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
	
	$(price).change(function() {
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
	
	$(quantity).change(function() {
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
	
	$(adjustments).change(function() {
		updateProduct($(price).val(), $(quantity).val(), $(adjustments).val(), label);
	});
}

";

if(isset($orders)) { // update
	$productTableScript .= "$(function() {\n";
	foreach($orders as $order) {
		if($order->adjustmentType == 'percent')
			$order->adjustment = "'{$order->adjustment}%'";
		$productTableScript .= "	addFilledProduct({$order->productId}, {$order->price}, {$order->quantity}, {$order->adjustment});\n";
	}
	$productTableScript .= "});\n";
} else { // create
	$productTableScript .= "
$(function() {
	addProduct();
});
";
}

Yii::app()->clientScript->registerScript('productTable', $productTableScript ,CClientScript::POS_HEAD);

$productField = Fields::model()->findByAttributes(array('modelName'=>'Quote', 'fieldName'=>'products'));
?>

<div class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo $productField->attributeLabel; ?></span>
		</div>
	</div>
</div>

<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#eee;">
	<table frame="border">
	    <tr>
	    	<th></th>
	    	<th><?php echo Yii::t('products', 'Line Item'); ?></th>
	    	<th><?php echo Yii::t('products', 'Unit Price'); ?></th>
	    	<th><?php echo Yii::t('products', 'Quantity'); ?></th>
	    	<th><?php echo Yii::t('products', 'Adjustments'); ?></th>
	    	<th><?php echo Yii::t('products', 'Price'); ?></th>
	    </tr>
	    <tr id="product-list-footer">
	    	<td></td>
	    	<td>
	    		<a href="javascript:void(0)" onclick="addProduct();" class="add-workflow-stage">
	    			[<?php echo Yii::t('workflow','Add'); ?>]
	    		</a>
	    	</td>
	    	<td></td>
	    	<td></td>
	    	<td><b>Total</b></td>
	    	<td><label id="product-list-total" style="font-weight: bold;">0</label></td>
	    </tr>
	</table>
</div>
