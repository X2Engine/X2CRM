<?php

/**
 * This is the model class for table "x2_actions".
 *
 * The followings are the available columns in table 'x2_actions':
 * @property integer $id
 * @property string $assignedTo
 * @property string $actionDescription
 * @property integer $visibility
 * @property integer $associationId
 * @property string $associationType
 * @property string $associationName
 * @property integer $dueDate
 * @property integer $showTime
 * @property string $priority
 * @property string $type
 * @property integer $createDate
 * @property string $complete
 * @property string $reminder
 * @property string $completedBy
 * @property integer $completeDate
 * @property integer $lastUpdated
 * @property string $updatedBy
 */
class Actions extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Actions the static model class
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
		return 'x2_actions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('actionDescription, visibility, associationId', 'required'),
			array('visibility, associationId, dueDate, showTime, createDate, completeDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('assignedTo, associationType, type, completedBy, updatedBy', 'length', 'max'=>20),
			array('associationName', 'length', 'max'=>100),
			array('priority', 'length', 'max'=>10),
			array('complete, reminder', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, assignedTo, actionDescription, visibility, associationId, associationType, associationName, dueDate, showTime, priority, type, createDate, complete, reminder, completedBy, completeDate, lastUpdated, updatedBy', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('actions','ID'),
			'assignedTo' => Yii::t('actions','Assigned To'),
			'actionDescription' => Yii::t('actions','Description'),
			'visibility' => Yii::t('actions','Visibility'),
			'associationId' => Yii::t('actions','Contact'),
			'associationType' => Yii::t('actions','Association Type'),
			'associationName' => Yii::t('actions','Association'),
			'dueDate' => Yii::t('actions','Due Date'),
			'priority' => Yii::t('actions','Priority'),
			'type' => Yii::t('actions','Action Type'),
			'createDate' => Yii::t('actions','Create Date'),
			'complete' => Yii::t('actions','Complete'),
			'reminder' => Yii::t('actions','Reminder'),
			'completedBy' => Yii::t('actions','Completed By'),
			'completeDate' => Yii::t('actions','Date Completed'),
			'lastUpdated' => Yii::t('actions','Last Updated'),
			'updatedBy' => Yii::t('actions','Updated By')
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
		$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('actionDescription',$this->actionDescription,true);
		$criteria->compare('visibility',$this->visibility);
		$criteria->compare('associationId',$this->associationId);
		$criteria->compare('associationType',$this->associationType,true);
		$criteria->compare('associationName',$this->associationName,true);
		$criteria->compare('dueDate',$this->dueDate);
		$criteria->compare('showTime',$this->showTime);
		$criteria->compare('priority',$this->priority,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('createDate',$this->createDate);
		$criteria->compare('complete',$this->complete,true);
		$criteria->compare('reminder',$this->reminder,true);
		$criteria->compare('completedBy',$this->completedBy,true);
		$criteria->compare('completeDate',$this->completeDate);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('updatedBy',$this->updatedBy,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}