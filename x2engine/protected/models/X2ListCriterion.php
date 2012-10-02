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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * This is the model class for table "x2_list_criteria".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property integer $listId
 * @property string $type
 * @property string $attribute
 * @property string $comparison
 * @property string $value
 */
class X2ListCriterion extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return ContactListCriterion the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_list_criteria';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('listId', 'required'),
			array('id, listId', 'numerical', 'integerOnly'=>true),
			array('comparison', 'length', 'max'=>10),
			array('type', 'length', 'max'=>20),
			array('attribute', 'length', 'max'=>40),
			array('value', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, listId, type, attribute, comparison, value', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('app','ID'),
			'listId' => Yii::t('contacts','List'),
			'type' => Yii::t('contacts','Type'),
			'attribute' => Yii::t('contacts','Attribute'),
			'comparison' => Yii::t('contacts','Comparison'),
			'value' => Yii::t('contacts','Value'),
		);
	}
	
	/**
	 * @return array available comparison types (value=>label)
	 */
	public function getComparisonList() {
		return array(
			'='=>Yii::t('contacts','equals'),
			'>'=>Yii::t('contacts','greater than'),
			'<'=>Yii::t('contacts','less than'),
			'<>'=>Yii::t('contacts','not equal to'),
			'list'=>Yii::t('contacts','in list'),
			'notList'=>Yii::t('contacts','not in list'),
			'empty'=>Yii::t('empty','empty'),
			'notEmpty'=>Yii::t('contacts','not empty'),
			'contains'=>Yii::t('contacts','contains'),
			'noContains'=>Yii::t('contacts','does not contain'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('listId',$this->listId,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('attribute',$this->attribute,true);
		$criteria->compare('comparison',$this->comparison,true);
		$criteria->compare('value',$this->value,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
