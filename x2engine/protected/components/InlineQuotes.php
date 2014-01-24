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

	public $recordId; // quotes displayed here are related to this record
    private $_contactId; // id of associated contact (optional)
	public $contact;
	public $account = null; // name of associated account (optional)
    public $modelName;

	public $errors = array();
	public $startHidden = false;

	public function init() {
	
		if(isset($_POST))
			$startHidden = false;

		if($this->startHidden) {			
		// register css
		Yii::app()->clientScript->registerCss('inline-quotes-style','
            #wide-quote-form {
                background: #F8F8F8;
            }

            .viewQuote hr {
            	background-color: black;
            	overflow: visible;
            }

            .product-table th {
            	background-color: inherit;
            }

            .product-table tfoot {
            	font-style: normal;
            }

            .quote-detail-table {
            	padding: 0;
            	width: 100%;
            }

            .quote-detail-table th {
            	background-color: inherit;
            	color: #666;
            	font-weight: normal;
            	font-size: 0.8em;
            	padding: 5px 0 0 0;

            }

            .quote-detail-table td {
            	font-weight: bold;
            	padding: 0;
            }

            .quote-create-table {
            	width: 100%;
            	padding: 0;
            }

            .quote-create-table th {
            	background-color: inherit;
	            font-weight: bold;
	            font-size: 0.8em;
	            padding: 5px 0 0 0;
            }

            .quote-detail-table td {
            	padding: 0;
            }

            .items td {
	            border-top: none;
	            border-bottom: none;
            }');

        if($this->startHidden)
            Yii::app()->clientScript->registerScript('startQuotesHidden',"$('#quotes-form').hide();" ,CClientScript::POS_READY);
        
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

        // Set up the new create form:
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/inlineQuotes.js', CClientScript::POS_HEAD);
        $quotesAssetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias('application.modules.quotes.assets'), false, -1, true);
        Yii::app()->clientScript->registerCssFile($quotesAssetsUrl.'/css/lineItemsMain.css');
        Yii::app()->clientScript->registerCssFile($quotesAssetsUrl.'/css/lineItemsWrite.css');
        Yii::app()->clientScript->registerCoreScript('jquery.ui');

        $this->contact = X2Model::model ('Contacts')->findByPk ($this->contactId);

        //$this->contact = Contacts::model()->findByPk($this->contactId);
        $iqConfig = array(
                'contact' => ($this->contact instanceof Contacts) ? $this->contact->name : '',
                'account' => $this->account,
                'sendingQuote' => false,
                'lockedMessage' => Yii::t('quotes','This quote is locked. Are you sure you want to update this quote?'),
                'deniedMessage' => Yii::t('quotes','This quote is locked.'),
                'lockedDialogTitle' => Yii::t('quotes','Locked'),
                'failMessage' => Yii::t('quotes', 'Could not save quote.'),
                'reloadAction' => CHtml::normalizeUrl(
                    array(
                        '/quotes/quotes/viewInline',
                        'recordId' => $this->recordId,
                        'recordType' => $this->modelName,
                    )
                ),
                'createAction' => CHtml::normalizeUrl(
                    array(
                        '/quotes/quotes/create',
                        'quick' => 1,
                        'recordId' => $this->recordId,
                        'recordType' => $this->modelName,
                    )
                ),
                'updateAction' => CHtml::normalizeUrl(
                    array(
                        '/quotes/quotes/update', 
                        'quick' => 1,
                    )
                ),
            );
            Yii::app()->clientScript->registerScript('quickquote-vars', '
                if(typeof x2 == "undefined"){
                    x2 = {};
                }
                var iqConfig = '.CJSON::encode($iqConfig).';
                if(typeof x2.inlineQuotes=="undefined") {
                    x2.inlineQuotes = iqConfig;
                } else {
                    $.extend(x2.inlineQuotes,iqConfig);
                }', CClientScript::POS_HEAD);
        }
        parent::init();
	}

        /**
         * Getter and setter for contactId will also update recordId
         * in order to remain backwards compatible.
         */
        public function getContactId() {
          return $this->_contactId;  
        }

        public function setContactId($value) {
            $this->_contactId = $value;
            $this->recordId = $value;
        }

    /**
     * Returns all related invoices or quotes 
     * @param bool $invoices
     * @return array array of related quotes models
     */
    public function getRelatedQuotes ($invoices=false) {
        if ($this->contact instanceof Contacts) {
            $associatedContactCondition = "quotes.associatedContacts=".$this->recordId." AND ";
        } else {
            $associatedContactCondition = "";
        }

        if ($invoices) {
            $invoiceCondition = "type='invoice'";
        } else {
            $invoiceCondition = "type IS NULL OR type!='invoice'";
        }

        /*
        Select all quotes which have a related record with the current record's id
        */
        $quotes = Yii::app()->db->createCommand ()
            ->select ('quotes.id')
            ->from ('x2_quotes as quotes, x2_relationships as relationships')
            ->where ($associatedContactCondition."(".$invoiceCondition.") AND ".
                "((relationships.firstType='Quote' AND ".
                  "relationships.secondType=:recordType AND relationships.secondId=:recordId) OR ".
                "(relationships.secondType='Quote' AND ".
                  "relationships.firstType=:recordType AND relationships.firstId=:recordId)) AND ".
                '((quotes.id=relationships.firstId AND relationships.firstType="Quote") OR '.
                 '(quotes.id=relationships.secondId AND relationships.secondType="Quote"))',
                 array (
                    ':recordId' => $this->recordId,
                    ':recordType' => $this->modelName
                ))
            ->queryAll ();

        // get models from ids
        $getId = function ($a) { return $a['id']; };
        $quotes = X2Model::model('Quote')->findAllByPk (array_map ($getId, $quotes));

        return $quotes;
    }

	public function run() {
        // Permissions that affect the behavior of the widget:
        $canDo = array();
        foreach(array('QuickDelete','QuickUpdate','QuickCreate') as $subAction) {
            $canDo[$subAction] = Yii::app()->user->checkAccess('Quotes'.$subAction);
        }

		/*$relationships = Relationships::model()->findAllByAttributes(array(
			'firstType'=>'quotes', 
			'secondType'=>'contacts', 
			'secondId'=>$this->contactId,
		));*/
		
		echo '<div id="quotes-form">';
		echo '<div id="wide-quote-form" class="wide form" style="overflow: visible;">';
		echo '<div id="quote-create-form-wrapper" style="display:none"></div>';
		echo '<span style="font-weight:bold; font-size: 1.5em;">'. Yii::t('quotes','Quotes') .'</span>';
		echo '<br /><br />';

		// Mini Create Quote Form
		$model = new Quote;

        if($canDo['QuickCreate']){
            $this->render('createQuote');
            echo '<br /><hr />';
        }

		// get a list of products for adding to quotes
		$products = Product::model()->findAll(array('select'=>'id, name'));
		// $productNames = array(0 => '');
		// foreach($products as $product) {
			// $productNames[$product->id] = $product->name;
		// }	
		
		//$quotes = Quote::model()->findAll("associatedContacts=:associatedContacts AND (type IS NULL OR type!='invoice')", array(':associatedContacts'=>$this->contactId));
		/*$quotes = Quote::model()->findAll(
            "associatedContacts=:associatedContacts AND (type IS NULL OR type!='invoice')", array(':associatedContacts'=>$this->contactId));*/

        /*if ($this->contact instanceof Contacts) {
            $associatedContactCondition = "x2_quotes.associatedContacts=".$this->contactId." AND ";
        } else {
            $associatedContactCondition = "";
        }

        $quotes = Yii::app()->db->createCommand ()
            ->select ('*')
            ->from ('x2_quotes')
            ->join (
                'x2_quotes.id=x2_relationships.firstId AND x2_relationships.firstType=qutoes OR '.
                'x2_quotes.id=x2_relationships.secondId AND x2_relationships.secondType=qutoes')
            ->where ($associatedContactCondition."(type IS NULL OR type!='invoice') AND ".
                "((x2_relationships.firstType=quotes AND x2_relationships.secondId=:recordId) OR ".
                 "(x2_relationships.secondType=quotes AND x2_relationships.firstId=:recordId))",
                 array (':recordId' => $this->contactId))
            ->queryAll ();*/

        $quotes = $this->getRelatedQuotes ();
        
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
				'recordId'=>$this->recordId,
				'modelName'=>$this->modelName,
				'dataProvider'=>$dataProvider,
				'products'=>$products,
				// 'productNames'=>$productNames,
				'orders'=>$quoteProducts,
				'total'=>$total,
                'canDo' => $canDo
			));
		}
		
		
		echo '<br /><br />';
		echo '<span style="font-weight:bold; font-size: 1.5em;">'. Yii::t('quotes','Invoices') .'</span>';
		echo '<br /><br />';
		
		/*$quotes = Quote::model()->findAll("associatedContacts=:associatedContacts AND type='invoice'", array(':associatedContacts'=>$this->contactId));*/
        $quotes = $this->getRelatedQuotes (true);
		
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
				'recordId'=>$this->recordId,
				'modelName'=>$this->modelName,
				'dataProvider'=>$dataProvider,
				'products'=>$products,
				// 'productNames'=>$productNames,
				'orders'=>$quoteProducts,
				'total'=>$total,
                'canDo' => $canDo,
			));
		}

		
		echo "</div>";		
		echo "</div>";
	}
}
