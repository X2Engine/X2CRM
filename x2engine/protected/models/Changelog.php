<?php

/**
 * This is the model class for table "x2_changelog".
 *
 * The followings are the available columns in table 'x2_changelog':
 * @property integer $id
 * @property string $type
 * @property integer $itemId
 * @property string $changedBy
 * @property string $changed
 * @property integer $timestamp
 */
class Changelog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Changelog the static model class
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
		return 'x2_changelog';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, itemId, changedBy, changed', 'required'),
			array('itemId, timestamp', 'numerical', 'integerOnly'=>true),
			array('type, changedBy', 'length', 'max'=>50),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, itemId, changedBy, changed', 'safe', 'on'=>'search'),
		);
	}
        
        public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),				/* optional line */
				'defaultStickOnClear'=>false		/* optional line */
			),
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
			'type' => 'Type',
			'itemId' => 'Item',
			'changedBy' => 'Changed By',
			'changed' => 'Changed',
			'timestamp' => 'Timestamp',
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
                $parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));

		$criteria->compare('id',$this->id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('itemId',$this->itemId);
		$criteria->compare('changedBy',$this->changedBy,true);
		$criteria->compare('changed',$this->changed,true);
		$criteria->compare('timestamp',$this->timestamp);

		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'timestamp DESC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}
}