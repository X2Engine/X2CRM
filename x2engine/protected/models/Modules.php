<?php

/**
 * This is the model class for table "x2_modules".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property integer $visible
 * @property integer $menuPosition
 * @property integer $searchable
 * @property integer $editable
 * @property integer $adminOnly
 * @property integer $custom
 * @property integer $toggleable
 */
class Modules extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Modules the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_modules';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('visible, menuPosition, searchable, editable, adminOnly, custom, toggleable', 'numerical', 'integerOnly'=>true),
			array('name, title', 'length', 'max'=>250),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable', 'safe', 'on'=>'search'),
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
			'name' => 'Name',
			'title' => 'Title',
			'visible' => 'Visible',
			'menuPosition' => 'Menu Position',
			'searchable' => 'Searchable',
			'editable' => 'Editable',
			'adminOnly' => 'Admin Only',
			'custom' => 'Custom',
			'toggleable' => 'Toggleable',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('visible',$this->visible);
		$criteria->compare('menuPosition',$this->menuPosition);
		$criteria->compare('searchable',$this->searchable);
		$criteria->compare('editable',$this->editable);
		$criteria->compare('adminOnly',$this->adminOnly);
		$criteria->compare('custom',$this->custom);
		$criteria->compare('toggleable',$this->toggleable);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public static function moduleLabel($model) {
		
	}
	public static function recordLabel($model) {
		
	}
}