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
 * This is the model class for table "x2_criteria".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $modelType
 * @property string $modelField
 * @property string $modelValue
 * @property string $comparisonOperator
 * @property string $users
 * @property string $type
 */
class Criteria extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Criteria the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'x2_criteria';
	}

	/**
	 * Validation rules for model attvributes.
	 *
	 * @return array
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('modelType', 'length', 'max'=>100),
			array('modelField, type', 'length', 'max'=>250),
			array('comparisonOperator', 'length', 'max'=>10),
			array('modelValue, users', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, modelType, modelField, modelValue, comparisonOperator, users, type', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('admin','ID'),
			'modelType' => Yii::t('admin','Model Type'),
			'modelField' => Yii::t('admin','Model Field'),
			'modelValue' => Yii::t('admin','Model Value'),
			'comparisonOperator' => Yii::t('admin','Comparison Operator'),
			'users' => Yii::t('admin','Users'),
			'type' => Yii::t('admin','Type'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('modelType',$this->modelType,true);
		$criteria->compare('modelField',$this->modelField,true);
		$criteria->compare('modelValue',$this->modelValue,true);
		$criteria->compare('comparisonOperator',$this->comparisonOperator,true);
		$criteria->compare('users',$this->users,true);
		$criteria->compare('type',$this->type,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}