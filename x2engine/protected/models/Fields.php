<?php

/**
 * This is the model class for table "x2_fields".
 *
 * The followings are the available columns in table 'x2_fields':
 * @property integer $id
 * @property string $modelName
 * @property string $fieldName
 * @property string $attributeLabel
 * @property integer $show
 * @property integer $custom
 */
class Fields extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Fields the static model class
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
		return 'x2_fields';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('visible, custom', 'numerical', 'integerOnly'=>true),
			array('modelName, fieldName, attributeLabel', 'length', 'max'=>250),
                        array('modelName','required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, modelName, fieldName, attributeLabel, visible, custom', 'safe', 'on'=>'search'),
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
			'modelName' => 'Model Name',
			'fieldName' => 'Field Name',
			'attributeLabel' => 'Attribute Label',
			'visible' => 'Visible',
			'custom' => 'Custom',
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
		$criteria->compare('modelName',$this->modelName,true);
		$criteria->compare('fieldName',$this->fieldName,true);
		$criteria->compare('attributeLabel',$this->attributeLabel,true);
		$criteria->compare('visible',$this->visible);
		$criteria->compare('custom',$this->custom);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}