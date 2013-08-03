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

/**
 *  Class for creating quotes from a contact view.
 *  
 *  This is used for creating, updating, deleting, duplicating
 *  a quote or invoice from the contact view page. It makes heavy use of
 *  javascript and ajax calls to the QuotesController.
 * 
 * @package X2CRM.components 
 */
class InlineQuotes extends X2Widget {

	public $contactId; // quotes displayed here are associated with this contact
	public $contact;
	public $account = null;

	public $errors = array();
	public $startHidden = false;

	public function init() {
	
		if(isset($_POST))
			$startHidden = false;

		if($this->startHidden) {			
		// register css
		Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl() .'/css/inlinequotes.css');

		// register (lots of) javascript
		// toggleQuotes() - show/hide the quotes form in the contact view
		// sendQuoteEmail(quote) - fill the inline email form with some info about a quote: name, table of products, description
		Yii::app()->clientScript->registerScript('toggleQuotes',
			($this->startHidden? "$(document).ready(function() { $('#quotes-form').hide();
			 });\n" : '')
			. "function toggleQuotes() {

				var wasHidden = $('#quotes-form').is(':hidden');
				if(wasHidden) {
					$('.focus-mini-module').removeClass('focus-mini-module');
					$('#quotes-form').find('.wide.form').addClass('focus-mini-module');
					$('html,body').animate({
						scrollTop: ($('#quote-form-wrapper').offset().top - 100)
					}, 300);
				}
				$('#quotes-form').toggle('blind',300,function() {
					$('#quotes-form').focus();
				});
				if(!wasHidden) {
					$('html,body').animate({
						scrollTop: ($('body').offset().top)
					}, 300);
				}
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
		function sendQuoteEmail(quoteId,quoteTemplate) {  // fill the inline email form with some info about a quote: name, table of products, description
			quickQuote.setInlineEmail(quoteId,quoteTemplate);
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
function toggleUpdateQuote(id, locked, strict) {
	var confirmBox = $('<div></div>')
		.html('This quote is locked. Are you sure you want to update this quote?')
		.dialog({
			title: 'Locked', 
			autoOpen: false,
			resizable: false,
			buttons: {
				'Yes': function() {
					$(this).dialog('close');
					quickQuote.openForm(id);
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
		quickQuote.openForm(id);
	}
}", CClientScript::POS_HEAD);

			// Set up the new create form:
			Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/quickquote.js',CClientScript::POS_HEAD);
			$quotesAssetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias('application.modules.quotes.assets'), false, -1, true);

			Yii::app()->clientScript->registerCssFile($quotesAssetsUrl.'/css/lineItemsMain.css');
			Yii::app()->clientScript->registerCssFile($quotesAssetsUrl.'/css/lineItemsWrite.css');
			Yii::app()->clientScript->registerCoreScript('jquery.ui');

			$this->contact = Contacts::model()->findByPk($this->contactId);
			$qqConfig = array(
				'contact'=>($this->contact instanceof Contacts) ? $this->contact->name :'',
				'account'=> $this->account,
				'failMessage'=>Yii::t('quotes', 'Could not save quote.'),
				'reloadAction'=>CHtml::normalizeUrl(array('quotes/viewInline'))."?contactId={$this->contactId}",
				'createAction'=>CHtml::normalizeUrl(array('quotes/create')).'?quick=1',
				'updateAction'=>CHtml::normalizeUrl(array('quotes/update')).'?quick=1'
			);
			Yii::app()->clientScript->registerScript('quickquote-vars', 'quickQuote = '.CJSON::encode($qqConfig).';', CClientScript::POS_HEAD);
		}
		parent::init();
	}

	public function run() {
	
//		Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->getBaseUrl() .'/css/gridview/jquery.yiigridview.js');

		$relationships = Relationships::model()->findAllByAttributes(array(
			'firstType'=>'quotes', 
			'secondType'=>'contacts', 
			'secondId'=>$this->contactId,
		));
		
		echo '<div id="quotes-form">';
		echo '<div id="wide-quote-form" class="wide form" style="overflow: visible;">';
		echo '<div id="quote-create-form-wrapper" style="display:none"></div>';
		echo '<span style="font-weight:bold; font-size: 1.5em;">'. Yii::t('quotes','Quotes') .'</span>';
		echo '<br /><br />';

		// Mini Create Quote Form
		$model = new Quote;

		$this->render('createQuote', array(
			'model'=>$model,
			'contactId'=>$this->contactId,
			// 'productNames'=>$productNames,
	//		'showNewQuote'=>$showNewQuote,
		));
		echo '<br /><hr />';

		// get a list of products for adding to quotes
		$products = Product::model()->findAll(array('select'=>'id, name'));
		// $productNames = array(0 => '');
		// foreach($products as $product) {
			// $productNames[$product->id] = $product->name;
		// }	
		
		$quotes = Quote::model()->findAll("associatedContacts=:associatedContacts AND (type IS NULL OR type!='invoice')", array(':associatedContacts'=>$this->contactId));
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
		
		
		echo '<br /><br />';
		echo '<span style="font-weight:bold; font-size: 1.5em;">'. Yii::t('quotes','Invoices') .'</span>';
		echo '<br /><br />';
		
		$quotes = Quote::model()->findAll("associatedContacts=:associatedContacts AND type='invoice'", array(':associatedContacts'=>$this->contactId));
		
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

		
		echo "</div>";		
		echo "</div>";
	}
}