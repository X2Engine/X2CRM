<?php

/**
 * This is the model class for table "x2_list_criteria".
 *
 * The followings are the available columns in table 'x2_list_criteria':
 * @property integer $listId
 * @property string $type
 * @property string $attribute
 * @property string $comparison
 * @property string $value
 */
class ContactListCriterion extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return ContactListCriterion the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_list_criteria';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('listId, value', 'required'),
			array('listId', 'numerical', 'integerOnly'=>true),
			array('comparison', 'length', 'max'=>10),
			array('type', 'length', 'max'=>20),
			array('attribute', 'length', 'max'=>40),
			array('value', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('listId, type, attribute, comparison, value', 'safe', 'on'=>'search'),
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
			'listId' => 'List',
			'type' => 'Type',
			'attribute' => Yii::t('contacts','Attribute'),
			'comparison' => Yii::t('contacts','Comparison'),
			'value' => Yii::t('contacts','Value'),
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

		$criteria->compare('listId',$this->listId,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('attribute',$this->attribute,true);
		$criteria->compare('comparison',$this->comparison,true);
		$criteria->compare('value',$this->value,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}