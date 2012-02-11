<?php

class QuoteProduct extends CActiveRecord
{
	// return: QuoteProduct static model
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	// return: database table name
	public function tableName()
	{
		return 'x2_quotes_products';
	}
	
	// return: array validation rules for model attributes.
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('quantity', 'numerical', 'integerOnly'=>true),
		);
	}
	
	public function relations() {
		return array(
			'product'=>array(self::BELONGS_TO, 'Product', 'productId'),
			'quote'=>array(self::BELONGS_TO, 'Quote', 'quoteId'),
		);
	}
}