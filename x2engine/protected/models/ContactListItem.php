<?php

/**
 * This is the model class for table "x2_list_items".
 *
 * The followings are the available columns in table 'x2_list_items':
 * @property integer $contactId
 * @property integer $listId
 * @property string $code
 * @property integer $result
 */
class ContactListItem extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return ContactListItem the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_list_items';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('contactId, listId', 'required'),
			array('contactId, listId, result', 'numerical', 'integerOnly'=>true),
			array('code', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('contactId, listId, code, result', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'listId'=>array(self::BELONGS_TO, 'ContactList', 'id'),
			'contactId'=>array(self::HAS_ONE, 'ContactChild', 'id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'contactId' => 'Contact',
			'listId' => 'List',
			'code' => 'Code',
			'result' => 'Result',
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

		$criteria->compare('contactId',$this->contactId,true);
		$criteria->compare('listId',$this->listId,true);
		$criteria->compare('code',$this->code,true);
		$criteria->compare('result',$this->result);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}