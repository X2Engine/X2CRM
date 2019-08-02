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




Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_quotes".
 *
 * @property array $adjustmentLines (read-only) Line items that are adjustments to the subtotal
 * @property Contacts $contact First contact associated with this quote.
 * @property array $lineItems All line items for the quote.
 * @property array $productLines (read-only) Line items that are products/services.
 * @package application.modules.quotes.models
 * @author David Visbal, Demitri Morgan <demitri@x2engine.com>
 */
class Quote extends X2Model {

    public $supportsWorkflow = false;

	/**
	 * Holds the set of line items
	 * @var array
	 */
	private $_lineItems;

	private $_contact;

	/**
	 * Holds the set of line items to be deleted
	 * @var array
	 */
	private $_deleteLineItems;

	/**
	 * Value stored for {@link productLines}
	 * @var array
	 */
	private $_productLines;
	/**
	 * Value stored for {@link adjustmentLines}
	 * @var array
	 */
	private $_adjustmentLines;


	/**
	 * Whether the line item set has errors in it.
	 * @var bool
	 */
	public $hasLineItemErrors = false;
	public $lineItemErrors = array();

	public static function lineItemOrder($i0,$i1) {
		return $i0->lineNumber < $i1->lineNumber ? -1 : 1;
	}

	/**
	 * Magic getter for {@link lineItems}.
	 */
	public function getLineItems() {
		if (!isset($this->_lineItems)) {
			$lineItems = $this->getRelated('products');
			if(count(array_filter($lineItems,function($li){return empty($li->lineNumber);})) > 0) {
				// Cannot abide null line numbers. Use indexes to set initial line numbers!
				foreach($lineItems as $i => $li) {
					$li->lineNumber = $i;
					$li->save();
				}
			}
			usort($lineItems,'self::lineItemOrder');
			$this->_lineItems = array();
			foreach($lineItems as $li) {
				$this->_lineItems[(int) $li->lineNumber] = $li;
			}
		}
		return $this->_lineItems;
	}

	/**
	 * Magic getter for {@link adjustmentLines}
	 */
	public function getAdjustmentLines(){
		if(!isset($this->_adjustmentLines))
			$this->_adjustmentLines = array_filter(
                $this->lineItems,function($li){return $li->isTotalAdjustment;});
		return $this->_adjustmentLines;
	}

	/**
	 * Magic getter for {@link productLines}
	 */
	public function getProductLines(){
		if(!isset($this->_productLines))
			$this->_productLines = array_filter(
                $this->lineItems,function($li){return !$li->isTotalAdjustment;});
		return $this->_productLines;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @return Quotes the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
     * @return array relational rules.
     */
    public function relations(){
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array_merge(parent::relations(), array(
            'products' => array(
                self::HAS_MANY, 'QuoteProduct', 'quoteId', 'order' => 'lineNumber ASC'),
            'contact' => array(
                self::BELONGS_TO, 'Contacts', array('associatedContacts' => 'nameId'))
        ));
    }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_quotes';
	}

	public function behaviors() {
		return array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'quotes'
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            )
        ));
	}

	/**
	 * Check a new set of line items against the existing set and update/delete as necessary
	 *
	 * Note: line numbers should be computed client-side and thus shouldn't need to be recalculated.
	 *
	 * @param array $items Each entry is an associative array of QuoteProduct [attribute]=>[value] 
     *  pairs
	 * @param integer $quoteId ID of quote for which to update items
	 * @param bool $save Whether or not to save changes in the database after finishing
	 * @return array Array of QuoteProduct instances representing the item set after changes.
	 * @throws CException
	 */
	public function setLineItems(array $items, $save = false, $skipProcessing=false) {
        if ($skipProcessing) {
            $this->_lineItems = $items;
            return;
        }

		$this->_deleteLineItems = array();
		if (count($items) === 0) {
			QuoteProduct::model()->deleteAllByAttributes(array('quoteId' => $this->id));
			return true;
		}

		// Check for valid input:
		$typeErrMsg = 'The setter of Quote.lineItems requires an array of QuoteProduct objects or '.
            '[attribute]=>[value] arrays.';
		$firstElt = reset($items);
		$type = gettype($firstElt);
		if ($type != 'object' && $type != 'array') // Must be one or the other
			throw new Exception($typeErrMsg);
		if ($type == 'object') // If object, must be of the QuoteProduct class
			if (get_class($firstElt) != 'QuoteProduct')
				throw new Exception($typeErrMsg);

		// Gather existing line items into an array indexed by ID.
		$existingItemIds = array();
		$newItems = array();
		$itemSet = array();
		$existingItems = array();
		foreach ($this->lineItems as $item) {
            if ($item->isNewRecord) {
                // this line might not be needed anymore. Used to be used for record duplication,
                // but now now skipProcessing is used instead, bypassing this line.
                $item->save();
            }
			$existingItems[$item->id] = $item;
			$existingItemIds[] = (int) $item->id;
		}

		// Gather the new set of line items into arrays
		if (isset($items['']))
			unset($items['']);
		if ($type == 'object') {
			foreach ($items as $item) {
				if (in_array($item->id, $existingItemIds)) {
					$itemSet[$item->id] = $existingItems[$item->id];
					$itemSet[$item->id]->attributes = $item->attributes;
				} else {
					$newItems[] = $item;
				}
			}
		} else if ($type == 'array') {
			foreach ($items as $item) {
				$new = false;
				if (isset($item['id'])) {
					$id = $item['id'];
					if (in_array($id, $existingItemIds)) {
						$itemSet[$id] = $existingItems[$item['id']];
						$itemSet[$id]->attributes = $item;
					} else
						$new = true;
				} else
					$new = true;

				if ($new) {
					$itemObj = new QuoteProduct;
					$itemObj->attributes = $item;
					$newItems[] = $itemObj;
				}
			}
		}

		// Compute set changes:
		$itemIds = array_keys($itemSet);
		$deleteItemIds = array_diff($existingItemIds, $itemIds);
		$updateItemIds = array_intersect($existingItemIds, $itemIds);

		// Put all the items together into the same arrays
		$this->_lineItems = array_merge($newItems, array_values($itemSet));
		usort($this->_lineItems,'self::lineItemOrder');
		$this->_deleteLineItems = array_map(
            function($id) use($existingItems) {return $existingItems[$id];}, $deleteItemIds);

		// Remove symbols from numerical input values and convert to numeric.
		// Behavior:
		// - Use the quote's currency if it isn't empty.
		// - Use the app's currency otherwise.
		$defaultCurrency = empty($this->currency)?
            Yii::app()->settings->currency:$this->currency;

		$curSym = Yii::app()->locale->getCurrencySymbol($defaultCurrency);
        if (is_null($curSym))
            $curSym = $defaultCurrency;

		foreach($this->_lineItems as $lineItem) {
			$lineItem->quoteId = $this->id;
            $product = X2Model::model('Products')->findByAttributes(array('name'=>$lineItem->name));
            if (isset($product))
                $lineItem->productId = $product->id;
            if(empty($lineItem->currency))
				$lineItem->currency = $defaultCurrency;
			if($lineItem->isPercentAdjustment) {
				$lineItem->adjustment = Fields::strToNumeric(
                    $lineItem->adjustment,'percentage');
			} else {
				$lineItem->adjustment = Fields::strToNumeric(
                    $lineItem->adjustment,'currency',$curSym);
			}
			$lineItem->price = Fields::strToNumeric($lineItem->price,'currency',$curSym);
			$lineItem->total = Fields::strToNumeric($lineItem->total,'currency',$curSym);
		}

		// Validate
		$this->hasLineItemErrors = false;
		$this->lineItemErrors = array();
		foreach ($this->_lineItems as $item) {
			$itemValid = $item->validate();
			if (!$itemValid) {
				$this->hasLineItemErrors = true;
				foreach ($item->errors as $attribute => $errors)
					foreach ($errors as $error)
						$this->lineItemErrors[] = $error;
			}
		}
		$this->lineItemErrors = array_unique($this->lineItemErrors);

		// Reset derived properties:
		$this->_adjustmentLines = null;
		$this->_productLines = null;

		// Save
		if($save && !$this->hasLineItemErrors)
			$this->saveLineItems();
	}

	/**
	 * Saves line item set changes to the database.
	 */
	public function saveLineItems(){
		// Insert/update new/existing items:
		if(isset($this->_lineItems)){
			foreach($this->_lineItems as $item){
				$item->quoteId = $this->id;
                $product = X2Model::model('Products')->findByAttributes(array(
                    'name'=>$item->name
                ));
                if (isset($product))
                    $item->productId = $product->id;
				$item->save();
			}
		}
		if(isset($this->_deleteLineItems)) {
			// Delete all deleted items:
			foreach($this->_deleteLineItems as $item)
				$item->delete();
			$this->_deleteLineItems = null;
		}
	}

    public function getContactId () {
        list ($name, $id) = Fields::nameAndId ($this->associatedContacts);
        return $id;
    }

    public function getAccountId () {
        list ($name, $id) = Fields::nameAndId ($this->accountName);
        return $id;
    }

	/**
	 * Creates an action history event record in the contact/account
	 */
	public function createActionRecord() {
		if(!empty($this->contactId)) {
            $this->createAssociatedAction ('contacts', $this->contactId);
		}
		if(!empty($this->accountName)) {
            $this->createAssociatedAction ('accounts', $this->accountId);
		}
	}

    public function createAssociatedAction ($type, $id) {
		$now = time();
		$actionAttributes = array(
			'type' => 'quotes',
			'actionDescription' => $this->id,
			'completeDate' => $now,
			'dueDate' => $now,
			'createDate' => $now,
			'lastUpdated' => $now,
			'complete' => 'Yes',
			'completedBy' => $this->createdBy,
			'updatedBy' => $this->updatedBy
		);
        $action = new Actions();
        $action->attributes = $actionAttributes;
        $action->associationType = $type;
        $action->associationId = $id;
        $action->save();
    }

	/**
	 * Creates an event record for the creation of the model.
	 */
	public function createEventRecord() {
//		$event = new Events();
//		$event->type = 'record_create';
//		$event->subtype = 'quote';
//		$event->associationId = $this->id;
//		$event->associationType = 'Quote';
//		$event->timestamp = time();
//		$event->lastUpdated = $event->timestamp;
//		$event->user = $this->createdBy;
//		$event->save();
	}

	public static function getStatusList() {
		$field = Fields::model()->findByAttributes(array('modelName' => 'Quote', 'fieldName' => 'status'));
		$dropdown = Dropdowns::model()->findByPk($field->linkType);
		return CJSON::decode($dropdown->options, true);

		/*
		  return array(
		  'Draft'=>Yii::t('quotes','Draft'),
		  'Presented'=>Yii::t('quotes','Presented'),
		  "Issued"=>Yii::t('quotes','Issued'),
		  "Won"=>Yii::t('quotes','Won')
		  ); */
	}

	/**
	 * Generates markup for a quote line items table.
	 *
	 * @param type $emailTable Style hooks for emailing the quote
	 * @return string
	 */
	public function productTable($emailTable = false) {
        if (!YII_UNIT_TESTING)
            Yii::app()->clientScript->registerCssFile (
                Yii::app()->getModule('quotes')->assetsUrl.'/css/productTable.css'
            );
		$pad = 4;
		// Declare styles
		$tableStyle = 'border-collapse: collapse; width: 100%;';
		$thStyle = 'padding: 5px; border: 1px solid black; background:#eee;';
		$thProductStyle = $thStyle;
		if(!$emailTable)
			$tableStyle .= 'display: inline;';
		else
			$thProductStyle .=  "width:60%;";
		$defaultStyle =  'padding: 5px;border-spacing:0;';
		$tdStyle = "$defaultStyle;border-left: 1px solid black; border-right: 1px solid black;";
		$tdFooterStyle = "$tdStyle;border-bottom: 1px solid black";
		$tdBoxStyle = "$tdFooterStyle;border-top: 1px solid black";

		// Declare element templates
		$thProduct = '<th style="'.$thProductStyle.'">{c}</th>';
		$tdDef = '<td style="'.$defaultStyle.'">{c}</td>';
		$td = '<td style="'.$tdStyle.'">{c}</td>';
		$tdFooter = '<td style="'.$tdFooterStyle.'">{c}</td>';
		$tdBox = '<td style="'.$tdBoxStyle.'">{c}</td>';
		$hr = '<hr style="width: 100%;height:2px;background:black;" />';
		$tr = '<tr>{c}</tr>';
		$colRange = range(2,7);
		$span = array_combine($colRange,array_map(function($s){
            return "<td colspan=\"$s\"></td>";},$colRange));
		$span[1] = '<td></td>';

		$markup = array();

		// Table opening and header
		$markup[] = "<table class='quotes-product-table' style=\"$tableStyle\"><thead>";
        $row = array ();
		foreach(array(
            'Line Item' => '20%; min-width: 200px;',
            'Unit Price' => '17.5%',
            'Quantity' => '15%',
            'Adjustment' => '15%',
            'Comments' => '15%',
            'Price' => '20%'
        ) as $columnHeader => $width) {
            $row[] = 
                '<th style="'.$thStyle."width: $width;".'">'.
                    Yii::t('products',$columnHeader).
                '</th>';
		}
		$markup[] = str_replace('{c}',implode("\n",$row),$tr);

		// Table header ending and body
		$markup[] = "</thead>";

		// Number of non-adjustment line items:
		$n_li = count($this->productLines);
		$i = 1;

		// Run through line items:
		$markup[] = '<tbody>';
		foreach($this->productLines as $ln=>$li) {
			// Begin row.
			$row = array();
			// Add columns for this line
			foreach(array('name','price','quantity','adjustment','description','total') as $attr) {
				$row[] = str_replace('{c}',$li->renderAttribute($attr),($i==$n_li?$tdFooter:$td));
			}
			// Row done.
			$markup[] = str_replace('{c}',implode('',$row),$tr);
			$i++;
		}

		$markup[] = '</tbody>';
		$markup[] = '<tbody>';
		// The subtotal and adjustment rows, if applicable:
		$i = 1;
		$n_adj = count($this->adjustmentLines);

		if($n_adj) {
			// Subtotal:
			$row = array($span[$pad]);
			$row[] = str_replace('{c}','<strong>'.Yii::t('quotes','Subtotal').'</strong>',$tdDef);
			$row[] = str_replace('{c}','<strong>'.Yii::app()->locale->numberFormatter->formatCurrency($this->subtotal,$this->currency).'</strong>',$tdDef);
			$markup[] = str_replace('{c}',implode('',$row),$tr);
			$markup[] = '</tbody>';
			// Adjustments:
			$markup[] = '<tbody>';
			foreach($this->adjustmentLines as $ln => $li) {
				// Begin row
				$row = array($span[$pad]);
				$row[] = str_replace('{c}',$li->renderAttribute('name').(!empty($li->description) ? ' ('.$li->renderAttribute('description').')':''),$tdDef);
				$row[] = str_replace('{c}',$li->renderAttribute('adjustment'),$tdDef);
				// Row done
				$markup[] = str_replace('{c}',implode('',$row),$tr);
				$i++;
			}
			$markup[] = '</tbody>';
			$markup[] = '<tbody>';
		}

		// Total:
		$row = array($span[$pad]);
		$row[] = str_replace('{c}','<strong>'.Yii::t('quotes','Total').'</strong>',$tdDef);
		$row[] = str_replace('{c}','<strong>'.Yii::app()->locale->numberFormatter->formatCurrency($this->total,$this->currency).'</strong>',$tdBox);
		$markup[] = str_replace('{c}',implode('',$row),$tr);
		$markup[] = '</tbody>';

		// Done.
		$markup[] = '</table>';

		return implode("\n",$markup);
	}

	public static function getNames() {

		$names = array(0 => "None");

		foreach (Yii::app()->db->createCommand()->select('id,name')->from('x2_quotes')->queryAll(false) as $row)
			$names[$row[0]] = $row[1];

		return $names;
	}

	public static function parseUsers($userArray) {
		return implode(', ', $userArray);
	}

	public static function parseUsersTwo($arr) {
		$str = "";
        if(is_array($arr)){
            $arr=array_keys($arr);
            $str=implode(', ',$arr);
        }
		$str = substr($str, 0, strlen($str) - 2);

		return $str;
	}

	public static function parseContacts($contactArray) {
		return implode(' ', $contactArray);
	}

	public static function parseContactsTwo($arr) {
		$str = "";
		foreach ($arr as $id => $contact) {
			$str.=$id . " ";
		}
		return $str;
	}

	public static function getQuotesLinks($accountId) {

		$quotesList = X2Model::model('Quote')->findAllByAttributes(array('accountName' => $accountId));
		// $quotesList = $this->model()->findAllByAttributes(array('accountId'),'=',array($accountId));

		$links = array();
		foreach ($quotesList as $model) {
			$links[] = CHtml::link($model->name, array('/quotes/quotes/view', 'id' => $model->id));
		}
		return implode(', ', $links);
	}

	public static function editContactArray($arr, $model) {

		$pieces = explode(" ", $model->associatedContacts);
		unset($arr[0]);

		foreach ($pieces as $contact) {
			if (array_key_exists($contact, $arr)) {
				unset($arr[$contact]);
			}
		}

		return $arr;
	}

	public static function editUserArray($arr, $model) {

		$pieces = explode(', ', $model->assignedTo);
		unset($arr['Anyone']);
		unset($arr['admin']);
		foreach ($pieces as $user) {
			if (array_key_exists($user, $arr)) {
				unset($arr[$user]);
			}
		}
		return $arr;
	}

	public static function editUsersInverse($arr) {

		$data = array();

		foreach ($arr as $username) {
			if ($username != '')
				$data[] = User::model()->findByAttributes(array('username' => $username));
		}

		$temp = array();
		if (isset($data)) {
			foreach ($data as $item) {
				if (isset($item))
					$temp[$item->username] = $item->firstName . ' ' . $item->lastName;
			}
		}
		return $temp;
	}

	public static function editContactsInverse($arr) {
		$data = array();

		foreach ($arr as $id) {
			if ($id != '')
				$data[] = X2Model::model('Contacts')->findByPk($id);
		}
		$temp = array();

		foreach ($data as $item) {
			$temp[$item->id] = $item->firstName . ' ' . $item->lastName;
		}
		return $temp;
	}

	public function search($pageSize=null, $uniqueId=null) {
	    $pageSize = $pageSize === null ? Profile::getResultsPerPage() : $pageSize;
		$criteria = new CDbCriteria;
		$parameters = array('limit' => ceil($pageSize));
		$criteria->scopes = array('findAll' => array($parameters));
		$criteria->addCondition("(t.type!='invoice' and t.type!='dummyQuote') OR t.type IS NULL");

		return $this->searchBase($criteria, $pageSize);
	}

	public function searchInvoice() {
		$criteria = new CDbCriteria;
		$parameters = array('limit' => ceil(Profile::getResultsPerPage()));
		$criteria->scopes = array('findAll' => array($parameters));
		$criteria->addCondition("t.type='invoice'");

		return $this->searchBase($criteria);
	}

    public function getName () {
        if ($this->name == '') {
            return $this->id;
        } else {
            return $this->name;
        }
    }

	public function searchAdmin() {
		$criteria = new CDbCriteria;

		return $this->searchBase($criteria);
	}

	public function searchBase(
        $criteria, $pageSize=null, $showHidden = false) {

		return parent::searchBase($criteria, $pageSize, $showHidden);
	}

	/**
	 * Get all active products indexed by their id,
	 * and any inactive products still in this quote
	 */
	public function productNames() {
		$products = Product::model()->findAll(
				array(
					'select' => 'id, name',
					'condition' => 'status=:active',
					'params' => array(':active' => 'Active'),
				)
		);
		$productNames = array(0 => '');
		foreach ($products as $product)
			$productNames[$product->id] = $product->name;

		// get any inactive products in this quote
		$quoteProducts = QuoteProduct::model()->findAll(
				array(
					'select' => 'productId, name',
					'condition' => 'quoteId=:quoteId',
					'params' => array(':quoteId' => $this->id),
				)
		);
		foreach ($quoteProducts as $qp)
			if (!isset($productNames[$qp->productId]))
				$productNames[$qp->productId] = $qp->name;

		return $productNames;
	}

	public function productPrices() {
		$products = Product::model()->findAll(
				array(
					'select' => 'id, price',
					'condition' => 'status=:active',
					'params' => array(':active' => 'Active'),
				)
		);
		$productPrices = array(0 => '');
		foreach ($products as $product)
			$productPrices[$product->id] = $product->price;

		// get any inactive products in this quote
		$quoteProducts = QuoteProduct::model()->findAll(
				array(
					'select' => 'productId, price',
					'condition' => 'quoteId=:quoteId',
					'params' => array(':quoteId' => $this->id),
				)
		);
		foreach ($quoteProducts as $qp)
			if (!isset($productPrices[$qp->productId]))
				$productPrices[$qp->productId] = $qp->price;

		return $productPrices;
	}

	public function activeProducts() {
		$products = Product::model()->findAllByAttributes(array('status' => 'Active'));
		$inactive = Product::model()->findAllByAttributes(array('status' => 'Inactive'));
		$quoteProducts = QuoteProduct::model()->findAll(
				array(
					'select' => 'productId',
					'condition' => 'quoteId=:quoteId',
					'params' => array(':quoteId' => $this->id),
				)
		);
		foreach ($quoteProducts as $qp)
			foreach ($inactive as $i)
				if ($qp->productId == $i->id)
					$products[] = $i;
		return $products;
	}

	/**
	 * Clear out records associated with this quote before deletion.
	 */
	public function beforeDelete(){
		QuoteProduct::model()->deleteAllByAttributes(array('quoteId'=>$this->id));

        // for old relationships generated with incorrect type name
		Relationships::model()->deleteAllByAttributes(
            array('firstType' => 'quotes', 'firstId' => $this->id));

		// generate action record for history
		$contact = $this->contact;
		if(!empty($contact)){
			$action = new Actions;
			$action->associationType = 'contacts';
			$action->type = 'quotesDeleted';
			$action->associationId = $contact->id;
			$action->associationName = $contact->name;
			$action->assignedTo = Yii::app()->getSuModel()->username; 
			$action->completedBy = Yii::app()->getSuModel()->username;
			$action->createDate = time();
			$action->dueDate = time();
			$action->completeDate = time();
			$action->visibility = 1;
			$action->complete = 'Yes';
			$action->actionDescription = 
                "Deleted Quote: <span style=\"font-weight:bold;\">{$this->id}</span> {$this->name}";
            // Save after deletion of the model so that this action itself doensn't get deleted
			$action->save(); 
		}
		return parent::beforeDelete();
	}
}
