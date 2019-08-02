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
 * This is the model class for table "x2_products".
 * @package application.modules.products.models
 */
class Product extends X2Model {

    public $supportsWorkflow = false;

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
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'products'
			),
            'ERememberFiltersBehavior' => array(
				'class'=>'application.components.behaviors.ERememberFiltersBehavior',
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
	public function search($pageSize=null) {
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
				'pageSize'=>Profile::getResultsPerPage(),
			),
		));*/
		return $this->searchBase($criteria, $pageSize);
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
