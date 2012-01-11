<?php

/**
 * This is the model class for table "x2_contact_lists".
 *
 * The followings are the available columns in table 'x2_contact_lists':
 * @property integer $id
 * @property integer $campaignId
 * @property string $name
 * @property string $description
 * @property string $type
 * @property integer $createDate
 * @property integer $lastUpdated
 */
class ContactList extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return ContactList the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_contact_lists';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, createDate, lastUpdated', 'required'),
			array('id, campaignId, count, visibility, createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>100),
			array('description', 'length', 'max'=>250),
			array('type', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, campaignId, name, count, visibility, description, type, createDate, lastUpdated', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'campaignId' => 'Campaign',
			'assignedTo' => Yii::t('contacts','Assigned To'),
			'name' => Yii::t('contacts','List Name'),
			'description' => Yii::t('contacts','Description'),
			'type' => Yii::t('contacts','List Type'),
			'visibility' => Yii::t('contacts','Visibility'),
			'count' => Yii::t('contacts','Members'),
			'createDate' => Yii::t('contacts','Create Date'),
			'lastUpdated' => Yii::t('contacts','Last Updated'),
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
		$criteria->compare('campaignId',$this->campaignId,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	public static function getRoute($id) {
		if($id=='all')
			return array('contacts/index');
		else if (empty($id) || $id=='my')
			return array('contacts/viewMy');
		else
			return array('contacts/list','id'=>$id);
	}
}