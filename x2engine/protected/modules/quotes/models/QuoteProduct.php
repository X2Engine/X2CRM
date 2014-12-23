<?php

/**
 * Model class for line items and adjustments in a quote.
 *
 * @property bool $isTotalAdjustment Tells whether the adjustment type
 * designates it as an adjustment to the subtotal
 * @property bool $isPercentAdjustment Tells whether the adjustment units is
 * a percentage
 * @package application.modules.quotes.models
 * @author David Visbal, Demitri Morgan <demitri@x2engine.com>
 */
class QuoteProduct extends CActiveRecord {

	private $_isPercentAdjustment;
	private $_isTotalAdjustment;

	public function resolveAdjustmentType() {
		if(empty($this->adjustmentType))
			$this->adjustmentType = 'linear';
		return $this->adjustmentType;
	}

	public function getIsPercentAdjustment() {
		if (!isset($this->_isPercentAdjustment)) {
			$adjType = $this->resolveAdjustmentType();
			$this->_isPercentAdjustment = $adjType == 'percent' || $adjType == 'totalPercent';
		}
		return $this->_isPercentAdjustment;
	}


	/**
	 * Magic getter for {@link isTotalAdjustment}
	 * @return bool
	 */
	public function getIsTotalAdjustment() {
		if(!isset($this->_isTotalAdjustment)) {
			$adjType = $this->resolveAdjustmentType();
			$this->_isTotalAdjustment = strpos($adjType,'total') === 0;
		}
		return $this->_isTotalAdjustment;
	}


	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

// return: database table name
	public function tableName() {
		return 'x2_quotes_products';
	}


// return: array validation rules for model attributes.
	public function rules() {
// NOTE: you should only define rules for those attributes that
// will receive user inputs.
		return array(
			array('lineNumber,productId', 'numerical', 'integerOnly' => true),
			array('quantity','numerical'),
			array('price,adjustment,total','numerical','allowEmpty'=>true),
			array('name','required'),
			array('name,type,currency', 'length', 'max' => 100),
			array('description', 'safe', 'safe' => true),
			array('adjustmentType', 'in', 'range' => array('percent', 'linear', 'totalPercent', 'totalLinear')),
		);
	}

	public function relations() {
		return array(
			'product' => array(self::BELONGS_TO, 'Product', 'productId'),
			'quote' => array(self::BELONGS_TO, 'Quote', 'quoteId'),
		);
	}

	/**
	 * Formats an attribute that's a numeric value so that it reflects its type,
	 * i.e. currency or percentage.
	 */
	public function formatAttribute($attr,$value=null) {
		$percentage =  $this->isPercentAdjustment;
		if(empty($this->currency) && !empty($this->quoteId))
			$this->currency = $this->quote->currency;
		if($value===null)
			$value = $this->$attr;
		if($attr == 'adjustment' && $percentage) {
			return $value.'%';
		} else if($attr == 'price' || $attr == 'adjustment' || $attr == 'total') {
            return $this->formatCurrency ($value);
		} else {
			return $value;
		}
	}

	/**
	 * Wrapper for {@link formatAttribute()} used in direct markup output
	 * @param string $attr
	 * @param mixed $value
	 * @return type
	 */
	public function renderAttribute($attr,$value=null) {
		return CHtml::encode($this->formatAttribute($attr,$value));
	}

    /**
     * Wrapper around formatCurrency which forces use of '-' negative prefix
     */
    private function formatCurrency ($value) {
        return Yii::app ()->locale->numberFormatter->formatCurrency (
            $value, $this->currency, '-¤');

    }

}
