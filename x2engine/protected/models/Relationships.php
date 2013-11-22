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
 * This is the model class for table "x2_relationships".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $firstType
 * @property integer $firstId
 * @property string $secondType
 * @property integer $secondId
 */
class Relationships extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Relationships the static model class
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
		return 'x2_relationships';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('firstId, secondId', 'numerical', 'integerOnly'=>true),
			array('firstType, secondType', 'length', 'max'=>100),
			array('firstType,secondType', 'linkables','on'=>'api'),
			array('firstType,firstId, secondType, secondId','required','on'=>'api'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, firstType, firstId, secondType, secondId', 'safe', 'on'=>'search,api'),
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
			'id' => 'ID',
			'firstType' => 'First Type',
			'firstId' => 'First',
			'secondType' => 'Second Type',
			'secondId' => 'Second',
		);
	}
	
	
	/**
	 * Creates a relationship between two models.
	 *
	 * Before the relationship is created, this function checks that the relationship
	 * does not already exist
	 * @param X2Model $firstType name of the class for the first model in this relationship
	 * @param X2Model $firstId id of the first model in this relationship
	 * @param X2Model $secondType name of the class for the second model in this relationship
	 * @param X2Model $secondId id of the second model in this relationship
	 * @return true if the relationship was created, false if it already exists
	 *
	 */
	public static function create($firstType, $firstId, $secondType, $secondId) {
		$relationship = Relationships::model()->findByAttributes(array('firstType'=>$firstType, 'firstId'=>$firstId, 'secondType'=>$secondType, 'secondId'=>$secondId));
		if($relationship)
			return false;

		$relationship = Relationships::model()->findByAttributes(array('firstType'=>$secondType, 'firstId'=>$secondId, 'secondType'=>$firstType, 'secondId'=>$firstId));
		if($relationship)
			return false;
		
		$relationship = new Relationships;
		$relationship->firstType=$firstType;
		$relationship->firstId=$firstId;
		$relationship->secondType=$secondType;
		$relationship->secondId=$secondId;
		$relationship->save();
		
		return true;
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
		$criteria->compare('firstType',$this->firstType,true);
		$criteria->compare('firstId',$this->firstId);
		$criteria->compare('secondType',$this->secondType,true);
		$criteria->compare('secondId',$this->secondId);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * 
	 */
	public function linkables($attribute, $params) {
		if(!class_exists($this->$attribute))
			$this->addError($attribute,Yii::t('app','Class "{class}" specified for {attribute} does not exist, so cannot create relationships with it.',array('{class}'=>$this->$attribute)));
		// See if the active record class has the linkable behavior:
		$staticModel = CActiveRecord::model($this->$attribute);
		$has = false;
		foreach($staticModel->behaviors() as $name=>$config){
			if($config['class'] == 'X2LinkableBehavior'){
				$has = true;
				break;
			}
		}
		if(!$has)
			$this->addError($attribute,Yii::t('app','Class "{class}" specified for {attribute} does not have X2LinkableBehavior, and thus cannot be used with relationships.',array('{class}'=>$this->$attribute)));
		$model = $staticModel->findByPk($attribute=='firstType' ? $this->firstId : $this->secondId);
		if(!$model)
			$this->addError($attribute,Yii::t('app','Model record not found for {attribute}.'));
	}
}