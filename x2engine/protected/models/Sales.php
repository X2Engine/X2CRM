<?php

/**
 * This is the model class for table "x2_sales".
 *
 * The followings are the available columns in table 'x2_sales':
 * @property integer $id
 * @property string $name
 * @property string $accountName
 * @property integer $accountId
 * @property integer $quoteAmount
 * @property string $salesStage
 * @property string $expectedCloseDate
 * @property integer $probability
 * @property string $leadSource
 * @property string $description
 * @property string $assignedTo
 * @property integer $createDate
 * @property string $associatedContacts
 * @property integer $lastUpdated
 * @property string $updatedBy
 */
class Sales extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Sales the static model class
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
		return 'x2_sales';
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
			array('accountId, quoteAmount, probability, createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>40),
			array('accountName', 'length', 'max'=>100),
			array('salesStage, expectedCloseDate, updatedBy', 'length', 'max'=>20),
			array('leadSource', 'length', 'max'=>10),
			array('description, assignedTo, associatedContacts', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, accountName, accountId, quoteAmount, salesStage, expectedCloseDate, probability, leadSource, description, assignedTo, createDate, associatedContacts, lastUpdated, updatedBy', 'safe', 'on'=>'search'),
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
	
	public function attributeLabels() {
		return array(
			'id' => Yii::t('sales','ID'),
			'name' => Yii::t('sales','Name'),
			'accountId' => Yii::t('sales','Account ID'),
			'accountName' => Yii::t('sales','Account'),
			'quoteAmount' => Yii::t('sales','Quote Amount'),
			'salesStage' => Yii::t('sales','Sales Stage'),
			'expectedCloseDate' => Yii::t('sales','Expected Close Date'),
			'probability' => Yii::t('sales','Probability'),
			'leadSource' => Yii::t('sales','Lead Source'),
			'description' => Yii::t('sales','Description'),
			'assignedTo' => Yii::t('sales','Assigned To'),
			'createDate' => Yii::t('sales','Create Date'),
			'associatedContacts' => Yii::t('sales','Contacts'),
			'lastUpdated' => Yii::t('sales','Last Updated'),
			'updatedBy' => Yii::t('sales','Updated By'),
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
		$criteria->compare('accountName',$this->accountName,true);
		$criteria->compare('accountId',$this->accountId);
		$criteria->compare('quoteAmount',$this->quoteAmount);
		$criteria->compare('salesStage',$this->salesStage,true);
		$criteria->compare('expectedCloseDate',$this->expectedCloseDate,true);
		$criteria->compare('probability',$this->probability);
		$criteria->compare('leadSource',$this->leadSource,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('createDate',$this->createDate);
		$criteria->compare('associatedContacts',$this->associatedContacts,true);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('updatedBy',$this->updatedBy,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}