<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/products'
			)
		);
	}
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('price', 'required'),
			array('createDate, lastUpdated, inventory', 'numerical', 'integerOnly'=>true),
			array('price', 'numerical', 'integerOnly'=>false),
			array('updatedBy', 'length', 'max'=>40),
			array('name', 'length', 'max'=>255),
			array('description', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, description, createDate, lastUpdated, updatedBy', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'order'=>array(self::HAS_MANY, 'QuoteProduct', 'productId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	// public function attributeLabels() {
		// $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Products'));
		// $arr=array();
		// foreach($fields as $field){
			// $arr[$field->fieldName]=Yii::t('app',$field->attributeLabel);
		// }
		
		// return $arr;
		// return array(
			// 'id' => Yii::t('module','ID'),
			// 'name' => Yii::t('module','Name'),
			// 'description' => Yii::t('module','Description'),
			// 'createDate' => Yii::t('module','Create Date'),
			// 'lastUpdated' => Yii::t('module','Last Updated'),
			// 'updatedBy' => Yii::t('module','Updated By'),
		// );
	// }

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
