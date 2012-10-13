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

/**
 * Class for creating quotes from a view page.
 * 
 * @package X2CRM.components 
 */
class InlineQuotes extends X2Widget {

	public $contactId;

	public $errors = array();
	public $startHidden = false;

	public function init() {
	
		if(isset($_POST))
			$startHidden = false;

		if($this->startHidden) {			
		// register css
		Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl() .'/css/inlinequotes.css');

		// register (lots of) javascript
		
		Yii::app()->clientScript->registerScript('toggleQuotes',
			($this->startHidden? "$(document).ready(function() { $('#quotes-form').hide();
			 });\n" : '')
			. "function toggleQuotes() {
				
				if($('#quotes-form').is(':hidden')) {
					$('.focus-mini-module').removeClass('focus-mini-module');
					$('#quotes-form').find('.wide.form').addClass('focus-mini-module');
					$('html,body').animate({
						scrollTop: ($('#publisher-form').offset().top - 200)
					}, 300);
				}
				$('#quotes-form').toggle('blind',300,function() {
					$('#quotes-form').focus();
				});
			}
			
			$(function() {
				$('#quotes-form').click(function() {
					if(sendingQuote) {
						sendingQuote = false;
					} else {
						if(!$('#quotes-form').find('.wide.form').hasClass('focus-mini-module')) {
							$('.focus-mini-module').removeClass('focus-mini-module');
							$('#quotes-form').find('.wide.form').addClass('focus-mini-module');
						}
					}

				});
			});
			",CClientScript::POS_HEAD);
			
		Yii::app()->clientScript->registerScript('emailQuote', "
		function appendEmail(line) {
			var email = $('#email-message');
			email.val(email.val() + line + ".'""'.");
		}
		
		var sendingQuote = false;
		function sendQuoteEmail(quote) {
			var notes = ".'"\n"'." + quote['notes']['label'] + ".'"\n"'." + quote['notes']['notes'] + ".'"\n"'.";
			toggleEmailForm();
			teditor.e.body.innerHTML = '' + quote['name'] + quote['products'] + notes;
			var value = $('#email-template option:contains(\"Quote\")').val();
			$('#email-template').val(value);
			$('#InlineEmail_subject').val('Quote');
			$('#email-template').change();
		    sendingQuote = true; // stop quote mini-module from stealing focus away from email
		}
		", CClientScript::POS_HEAD);

		$products = Product::model()->findAll(array('select'=>'id, name, price'));
		$jsProductList = "\$(productList).append(\$('<option>', {value: 0}).append(''));\n";
		$jsProductPrices = "var prices = [];\n";
		$jsProductPrices .= "prices[0] = 0;\n";
		foreach($products as $product) {
			$jsProductList .= "\$(productList).append(\$('<option>', {value: {$product->id}}).append('{$product->name}'));\n";
			$jsProductPrices .= "prices[{$product->id}] = {$product->price};\n";
		}
		
		$productNames = Product::productNames();
		$jsonProductList = json_encode($productNames);

		$region = Yii::app()->getLocale()->getId();
		Yii::app()->clientScript->registerScript('productTable', "

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

function updateProduct(id, price, quantity, adjustments, label, currency) {
	price = parseFloat(price);
	quantity = parseFloat(quantity);
	var total = price * quantity;
	var index = adjustments.indexOf('%');
	if(index == -1) { // adjustment
	    total += parseFloat(adjustments);
	} else { // percent adjustment
	    adjustments = adjustments.substring(0, index);
	    adjustments = parseFloat(adjustments) / 100;
	    total += total * adjustments;
	}
	$(label).html('' + total);
	$(label).formatCurrency({'region': currencyTable[currency]});
	updateProductTotal(id, currency);
}

function updateProductTotal(id, currency) {
	var total = 0;
	$('#product-table-' + id + ' .product-list-price').each(function () {
		$(this).toNumber({'region': currencyTable[currency]});
	    total += parseFloat($(this).html());
	    $(this).formatCurrency({'region': currencyTable[currency]});
	});
	$('#product-list-total-' + id).html('' + total);
	$('#product-list-total-' + id).formatCurrency({'region': currencyTable[currency]});
}



function addProduct(id, currency, productNames, prices) {
	var row = $('<tr></tr>');
	$('#product-table-' + id + ' tbody').append(row);

	var td = $('<td></td>');
	var tdRemove = $('<td></td>', {
		'style': 'padding: 0;'
	});
	
	var remove = $('<a>', {
		href: 'javascript:void(0)',
		'onClick': 'removeProduct(this);'
	});
	var removeImage = $('<img>', {
		src: '". Yii::app()->request->baseUrl .'/themes/x2engine/css/gridview/delete.png' . "',
		alt: '[". Yii::t('quotes', 'Delete Quote') ."]'
	});
	$(row).append(tdRemove.clone().append(remove));
	$(remove).append(removeImage);
	
	var productList = $('<select>', {
		name: 'ExistingProducts[id][]'
	});
	$(row).append(td.clone().append(productList));
	for(var i in productNames) {
		$(productList).append($('<option>', {value: i}).append(productNames[i]));
	}
	
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
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		'style': 'color:#aaa;',
		name: 'ExistingProducts[adjustment][]',
		value: 0
	});
	$(row).append(td.clone().append(adjustments));
	
	var label = $('<label>', {
		'class': 'product-list-price',
		'style': 'width: auto;',
	});
	$(row).append(td.clone().append(label));
	$(label).append('0');
	
	$(productList).change(function() {
		$(price).val('' + prices[$(this).attr('value')]);
		$(price).css('color', 'black');
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
	
	$('#update-quote-button-' + id).css('background', 'yellow');
	
	$(price).change(function() {
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
	
	$(quantity).change(function() {
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
	
	$(adjustments).change(function() {
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
} 


function addFilledProduct(id, fillId, fillPrice, fillQuantity, fillAdjustment, currency, productNames, prices) {
	var row = $('<tr></tr>');
	$('#product-table-' + id + ' tbody').append(row);

	var td = $('<td></td>');
	var tdRemove = $('<td></td>', {
		'style': 'padding: 0;'
	});
	
	var remove = $('<a>', {
		href: 'javascript:void(0)',
		'onClick': 'removeProduct(this);'
	});
	var removeImage = $('<img>', {
		src: '". Yii::app()->request->baseUrl .'/themes/x2engine/css/gridview/delete.png' . "',
		alt: '[". Yii::t('quotes', 'Delete Quote') ."]'
	});
	$(row).append(tdRemove.clone().append(remove));
	$(remove).append(removeImage);
	
	var productList = $('<select>', {
		name: 'ExistingProducts[id][]',
	});
	$(row).append(td.clone().append(productList));
	for(var i in productNames) {
		$(productList).append($('<option>', {value: i}).append(productNames[i]));
	}
	$(productList).val(fillId);
	
	var price = $('<input>', {
		type: 'text',
		size: 10,
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		name: 'ExistingProducts[price][]',
		value: 0,
		defaultValue: 0,
	});
	$(row).append(td.clone().append(price));
	$(price).val(fillPrice);
	
	var quantity = $('<input>', {
		type: 'text',
		size: 10,
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		name: 'ExistingProducts[quantity][]',
		value: 0,
		defaultValue: 0,
	});
	$(row).append(td.clone().append(quantity));
	$(quantity).val(fillQuantity);
	
	var adjustments = $('<input>', {
		type: 'text',
		size: 10,
		'onFocus': 'toggleText(this);',
		'onBlur': 'toggleText(this);',
		name: 'ExistingProducts[adjustment][]',
		value: 0,
		defaultValue: 0,
	});
	$(row).append(td.clone().append(adjustments));
	$(adjustments).val(fillAdjustment);
	
	var label = $('<label>', {
		'class': 'product-list-price',
		'style': 'width: auto;',
	});
	$(row).append(td.clone().append(label));
	$(label).append('0');
	
	updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);

	$(productList).change(function() {
		$(price).val('' + prices[$(this).attr('value')]);
		$(price).css('color', 'black');
		$('#update-quote-button-' + id).css('background', 'yellow');
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
	
	$(price).change(function() {
		$('#update-quote-button-' + id).css('background', 'yellow');
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
	
	$(quantity).change(function() {
		$('#update-quote-button-' + id).css('background', 'yellow');
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
	
	$(adjustments).change(function() {
		$('#update-quote-button-' + id).css('background', 'yellow');
		updateProduct(id, $(price).val(), $(quantity).val(), $(adjustments).val(), label, currency);
	});
}

function toggleUpdateQuote(id, locked, strict) {
	var confirmBox = $('<div></div>')
		.html('This quote is locked. Are you sure you want to update this quote?')
		.dialog({
			title: 'Locked', 
			autoOpen: false,
			resizable: false,
			buttons: {
				'Yes': function() {
					$('#quote-detail-' + id).hide('blind', 'slow');
					$('#quote-update-' + id).show('slow');
					$(this).dialog('close');
				},
				'No': function() {
					$(this).dialog('close');
				}
			},
		});

	var denyBox = $('<div></div>')
		.html('This quote is locked.')
		.dialog({
			title: 'Locked', 
			autoOpen: false,
			resizable: false,
			buttons: {
				'OK': function() {
					$(this).dialog('close');
				},
			},
		});
		
	if(locked)
		if(strict)
			denyBox.dialog('open');
		else
			confirmBox.dialog('open');
	else {
		$('#quote-detail-' + id).hide('blind', 'slow');
		$('#quote-update-' + id).show('slow');
	}
}

function toggleNewQuote() {
	$('#show-new-quote-button').hide('blind', 'slow');
	$('#new-quote').show('slow');
}

function duplicateQuote(quote) {
	$('#new-quote [name=\"Quote[status]\"]').val(quote['status']);
	$('#new-quote [name=\"Quote[expirationDate]\"]').val(quote['expirationDate']);
	
	$('#product-table-create tbody tr').remove();
	for(var i in quote['products']) {
		addFilledProduct('create', quote['products'][i]['id'], quote['products'][i]['price'], quote['products'][i]['quantity'], '' + quote['products'][i]['adjustment'], quote['currency'], quote['productNames'], quote['prices']);
	}
	
	$('#new-quote').show('slow');
}

", CClientScript::POS_HEAD);
	
	}
		parent::init();
	}

	public function run() {
	
		Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->getBaseUrl() .'/css/gridview/jquery.yiigridview.js');

		$relationships = Relationships::model()->findAllByAttributes(array(
			'firstType'=>'quotes', 
			'secondType'=>'contacts', 
			'secondId'=>$this->contactId,
		));
		
		echo '<div id="quotes-form">';
		echo '<div id="wide-quote-form" class="wide form" style="overflow: visible;">';
		echo '<span style="font-weight:bold; font-size: 1.5em;">'. Yii::t('quotes','Quotes') .'</span>';
		echo '<br /><br />';
		
		// get a list of products for adding to quotes
		$products = Product::model()->findAll(array('select'=>'id, name'));
		// $productNames = array(0 => '');
		// foreach($products as $product) {
			// $productNames[$product->id] = $product->name;
		// }	
		
		$quotes = Quote::model()->findAllByAttributes(array('associatedContacts'=>$this->contactId));
		
		foreach($quotes as $quote) {
			$products = Product::model()->findAll(array('select'=>'id, name, price'));
			$quoteProducts = QuoteProduct::model()->findAllByAttributes(array('quoteId'=>$quote->id));
			
			// find associated products and their quantities
			$quotesProducts = QuoteProduct::model()->findAllByAttributes(array('quoteId'=>$quote->id));
			$orders = array(); // array of product-quantity pairs
			$total = 0; // total price for the quote
			foreach($quotesProducts as $qp) {
		    	$price = $qp->price * $qp->quantity;
		    	if($qp->adjustmentType == 'percent') {
		    	    $price += $price * ($qp->adjustment / 100);
		    	    $qp->adjustment = "{$qp->adjustment}%";
		    	} else {
		    		$price += $qp->adjustment;
		    	}
				$orders[] = array(
		    		'name' => $qp->name,
					'id' => $qp->productId,
			    	'unit' => $qp->price,
					'quantity'=> $qp->quantity,
					'adjustment' => $qp->adjustment,
					'price' => $price,
				);
				$order = end($orders);
				$total += $order['price'];
			}
			
			$dataProvider = new CArrayDataProvider($orders, array(
				'keyField'=>'name',
				'sort'=>array(
					'attributes'=>array('name', 'unit', 'quantity', 'price'),
				),
				'pagination'=>array('pageSize'=>false),
				
			));
			$newProductId = "new_product_" . $quote->id;
			$this->render('viewQuotes', array(
				'quote'=>$quote,
				'contactId'=>$this->contactId,
				'dataProvider'=>$dataProvider,
				'products'=>$products,
				// 'productNames'=>$productNames,
				'orders'=>$quoteProducts,
				'total'=>$total,
			));
		}
		
		
		// Mini Create Quote Form
		$model = new Quote;
		
		$this->render('createQuote', array(
			'model'=>$model,
			'contactId'=>$this->contactId,
			// 'productNames'=>$productNames,
	//		'showNewQuote'=>$showNewQuote,
		));
		
		echo "</div>";		
		echo "</div>";
	}
}