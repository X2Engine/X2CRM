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
 * This is the model class for table "x2_changelog".
 *
 * The followings are the available columns in table 'x2_changelog':
 * @property integer $id
 * @property string $type
 * @property integer $itemId
 * @property string $changedBy
 * @property string $changed
 * @property string $fieldName
 * @property string $oldValue
 * @property string $newValue
 * @property boolean $diff
 * @property integer $timestamp
 */
class Changelog extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Changelog the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_changelog';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array();
			// array('type, itemId, changedBy', 'required'),
			// array('itemId, timestamp', 'numerical', 'integerOnly'=>true),
			// array('type, changedBy', 'length', 'max'=>50),
			// array('fieldName', 'length', 'max'=>255),
			// array('diff', 'boolean'),
			// array('changed, oldValue, newValue', 'safe'),
			// array('id, type, itemId, changedBy, changed, fieldName, oldValue, newValue, timestamp', 'safe', 'on'=>'search'),
		// );
	}

	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'type' => Yii::t('admin','Type'),
			'itemId' => Yii::t('admin','Item'),
			'changedBy' => Yii::t('admin','Changed By'),
			'changed' => Yii::t('admin','Changed'),
			'fieldName' => Yii::t('admin','Field Name'),
			'oldValue' => Yii::t('admin','Old Value'),
			'newValue' => Yii::t('admin','New Value'),
			'diff' => Yii::t('admin','Diff'),
			'timestamp' => Yii::t('admin','Timestamp'),
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

		$parameters = array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes = array('findAll'=>array($parameters));

		$criteria->compare('id',$this->id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('itemId',$this->itemId);
		$criteria->compare('changedBy',$this->changedBy,true);
		$criteria->compare('recordName',$this->recordName,true);
		$criteria->compare('fieldName',$this->fieldName,true);
		$criteria->compare('oldValue',$this->oldValue,true);
		$criteria->compare('newValue',$this->newValue,true);
		$criteria->compare('diff',$this->diff,true);
		$criteria->compare('timestamp',$this->timestamp);

		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'timestamp DESC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}
}