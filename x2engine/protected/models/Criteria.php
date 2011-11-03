<?php

/**
 * This is the model class for table "x2_criteria".
 *
 * The followings are the available columns in table 'x2_criteria':
 * @property integer $id
 * @property string $modelType
 * @property string $modelField
 * @property string $modelValue
 * @property string $comparisonOperator
 * @property string $users
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
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('modelType, modelField, comparisonOperator', 'length', 'max'=>100),
			array('modelValue, users', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, modelType, modelField, modelValue, comparisonOperator, users', 'safe', 'on'=>'search'),
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
			'modelType' => 'Model Type',
			'modelField' => 'Model Field',
			'modelValue' => 'Model Value',
			'comparisonOperator' => 'Comparison Operator',
			'users' => 'Users',
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

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}