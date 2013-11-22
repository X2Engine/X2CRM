<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_products".
 * @package X2CRM.modules.products.models
 */
class Product extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_products';
	}

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'products'
			),
            'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		));
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array_merge(parent::relations(),array(
			'order'=>array(self::HAS_MANY, 'QuoteProduct', 'productId'),
		));
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		/*
		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('createDate',$this->createDate);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('updatedBy',$this->updatedBy,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
		));*/
		return $this->searchBase($criteria);
	}

	/**
	 *
	 */
	public static function activeProducts() {
		return Product::model()->findAllByAttributes(array('status'=>'Active'));
	}

	/**
	 * Get a list of active product names indexed by id
	 */
	public static function productNames() {
		$products = Product::model()->findAll(
			array(
				'select'=>'id, name',
				'condition'=>'status=:active',
				'params'=>array(':active'=>'Active'),
			)
		);
		$productNames = array(0 => '');
		foreach($products as $product)
			$productNames[$product->id] = $product->name;

		return $productNames;
	}

	/**
	 * Get a list of active product currencys indexed by id
	 */
	public static function productCurrency() {
		$products = Product::model()->findAll(
			array(
				'select'=>'id, currency',
				'condition'=>'status=:active',
				'params'=>array(':active'=>'Active'),
				)
		);
		$productCurrency = array(0 => '');
		foreach($products as $product)
			$productCurrency[$product->id] = $product->currency;

		return $productCurrency;
	}

	/**
	 * Get a list of active product currencys indexed by id
	 */
	public static function productPrices() {
		$products = Product::model()->findAll(
			array(
				'select'=>'id, price',
				'condition'=>'status=:active',
				'params'=>array(':active'=>'Active'),
				)
		);
		$productPrices = array(0 => '');
		foreach($products as $product)
			$productPrices[$product->id] = $product->price;

		return $productPrices;
	}
}
