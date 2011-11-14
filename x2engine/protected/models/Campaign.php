<?php

/**
 * This is the model class for table "x2_campaigns".
 *
 * The followings are the available columns in table 'x2_campaigns':
 * @property string $id
 * @property string $masterId
 * @property string $name
 * @property string $description
 * @property string $type
 * @property string $cost
 * @property string $result
 * @property string $content
 * @property string $createdBy
 * @property string $createDate
 * @property string $launchDate
 * @property string $lastUpdated
 */
class Campaign extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Campaign the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_campaigns';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('masterId, name, createdBy, createDate, launchDate, lastUpdated', 'required'),
			array('masterId, createDate, launchDate, lastUpdated', 'length', 'max'=>10),
			array('name, cost', 'length', 'max'=>100),
			array('type, createdBy', 'length', 'max'=>20),
			array('description, result, content', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, masterId, name, description, type, cost, result, content, createdBy, createDate, launchDate, lastUpdated', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('marketing','ID'),
			'masterId' => Yii::t('marketing','Master Campaign'),
			'name' => Yii::t('marketing','Name'),
			'description' => Yii::t('marketing','Description'),
			'type' => Yii::t('marketing','Type'),
			'cost' => Yii::t('marketing','Cost'),
			'result' => Yii::t('marketing','Result'),
			'content' => Yii::t('marketing','Content'),
			'createdBy' => Yii::t('marketing','Created By'),
			'createDate' => Yii::t('marketing','Create Date'),
			'launchDate' => Yii::t('marketing','Launch Date'),
			'lastUpdated' => Yii::t('marketing','Last Updated'),
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
		$criteria->compare('masterId',$this->masterId,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('cost',$this->cost,true);
		$criteria->compare('result',$this->result,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('createdBy',$this->createdBy,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('launchDate',$this->launchDate,true);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}