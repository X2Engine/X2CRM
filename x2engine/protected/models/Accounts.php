<?php

/**
 * This is the model class for table "x2_accounts".
 *
 * The followings are the available columns in table 'x2_accounts':
 * @property integer $id
 * @property string $name
 * @property string $website
 * @property string $type
 * @property integer $annualRevenue
 * @property string $phone
 * @property string $tickerSymbol
 * @property integer $employees
 * @property string $assignedTo
 * @property integer $createDate
 * @property string $associatedContacts
 * @property string $description
 * @property integer $lastUpdated
 * @property string $updatedBy
 */
class Accounts extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Accounts the static model class
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
		return 'x2_accounts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('annualRevenue, employees, createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('name, website, phone', 'length', 'max'=>40),
			array('type', 'length', 'max'=>60),
			array('tickerSymbol', 'length', 'max'=>10),
			array('updatedBy', 'length', 'max'=>20),
			array('assignedTo, associatedContacts, description', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, website, type, annualRevenue, phone, tickerSymbol, employees, assignedTo, createDate, associatedContacts, description, lastUpdated, updatedBy', 'safe', 'on'=>'search'),
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
	public function attributeLabels() {
		return array(
			'id' => Yii::t('accounts','ID'),
			'name' => Yii::t('accounts','Name'),
			'website' => Yii::t('accounts','Website'),
			'type' => Yii::t('accounts','Type'),
			'annualRevenue' => Yii::t('accounts','Revenue'),
			'phone' => Yii::t('accounts','Phone'),
			'tickerSymbol' => Yii::t('accounts','Symbol'),
			'employees' => Yii::t('accounts','Employees'),
			'assignedTo' => Yii::t('accounts','Assigned To'),
			'createDate' => Yii::t('accounts','Create Date'),
			'associatedContacts' => Yii::t('accounts','Contacts'),
			'description' => Yii::t('accounts','Description'),
			'lastUpdated' => Yii::t('accounts','Last Updated'),
			'updatedBy' => Yii::t('accounts','Updated By')
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('website',$this->website,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('annualRevenue',$this->annualRevenue);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('tickerSymbol',$this->tickerSymbol,true);
		$criteria->compare('employees',$this->employees);
		$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('createDate',$this->createDate);
		$criteria->compare('associatedContacts',$this->associatedContacts,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('updatedBy',$this->updatedBy,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}