<?php

/**
 * This is the model class for table "x2_admin".
 *
 * The followings are the available columns in table 'x2_admin':
 * @property integer $id
 * @property integer $accounts
 * @property integer $sales
 * @property integer $timeout
 * @property string $webLeadEmail
 * @property string $currency
 * @property string $menuOrder
 * @property string $menuVisibility
 * @property string $menuNicknames
 * @property integer $chatPollTime
 * @property integer $ignoreUpdates
 */
class Admin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Admin the static model class
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
		return 'x2_admin';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('accounts, sales, timeout, chatPollTime, ignoreUpdates', 'numerical', 'integerOnly'=>true),
			array('webLeadEmail', 'length', 'max'=>200),
			array('currency', 'length', 'max'=>3),
			array('menuOrder, menuNicknames', 'length', 'max'=>255),
			array('menuVisibility', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, accounts, sales, timeout, webLeadEmail, currency, menuOrder, menuVisibility, menuNicknames, chatPollTime, ignoreUpdates', 'safe', 'on'=>'search'),
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
			'accounts' => 'Accounts',
			'sales' => 'Sales',
			'timeout' => 'Timeout',
			'webLeadEmail' => 'Web Lead Email',
			'currency' => 'Currency',
			'menuOrder' => 'Menu Order',
			'menuVisibility' => 'Menu Visibility',
			'menuNicknames' => 'Menu Nicknames',
			'chatPollTime' => 'Chat Poll Time',
			'ignoreUpdates' => 'Ignore Updates',
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
		$criteria->compare('accounts',$this->accounts);
		$criteria->compare('sales',$this->sales);
		$criteria->compare('timeout',$this->timeout);
		$criteria->compare('webLeadEmail',$this->webLeadEmail,true);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('menuOrder',$this->menuOrder,true);
		$criteria->compare('menuVisibility',$this->menuVisibility,true);
		$criteria->compare('menuNicknames',$this->menuNicknames,true);
		$criteria->compare('chatPollTime',$this->chatPollTime);
		$criteria->compare('ignoreUpdates',$this->ignoreUpdates);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}