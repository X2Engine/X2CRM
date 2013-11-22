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
			'empty'=>Yii::t('contacts','empty'),
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
