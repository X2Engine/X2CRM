<?php

/**
 * This is the model class for table "x2_users".
 *
 * The followings are the available columns in table 'x2_users':
 * @property integer $id
 * @property string $firstName
 * @property string $lastName
 * @property string $username
 * @property string $password
 * @property string $title
 * @property string $department
 * @property string $officePhone
 * @property string $cellPhone
 * @property string $homePhone
 * @property string $address
 * @property string $backgroundInfo
 * @property string $emailAddress
 * @property integer $status
 * @property string $lastUpdated
 * @property string $updatedBy
 * @property string $recentItems
 * @property string $topContacts
 * @property integer $lastLogin
 * @property integer $login
 */
class Users extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Users the static model class
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
		return 'x2_users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('firstName, lastName, username, password, emailAddress, status', 'required'),
			array('status, lastLogin, login', 'numerical', 'integerOnly'=>true),
			array('firstName, username, title, updatedBy', 'length', 'max'=>20),
			array('lastName, department, officePhone, cellPhone, homePhone', 'length', 'max'=>40),
			array('password, address, emailAddress, recentItems, topContacts', 'length', 'max'=>100),
			array('lastUpdated', 'length', 'max'=>30),
			array('backgroundInfo', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, firstName, lastName, username, password, title, department, officePhone, cellPhone, homePhone, address, backgroundInfo, emailAddress, status, lastUpdated, updatedBy, recentItems, topContacts, lastLogin, login', 'safe', 'on'=>'search'),
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
			'firstName' => 'First Name',
			'lastName' => 'Last Name',
			'username' => 'Username',
			'password' => 'Password',
			'title' => 'Title',
			'department' => 'Department',
			'officePhone' => 'Office Phone',
			'cellPhone' => 'Cell Phone',
			'homePhone' => 'Home Phone',
			'address' => 'Address',
			'backgroundInfo' => 'Background Info',
			'emailAddress' => 'Email Address',
			'status' => 'Status',
			'lastUpdated' => 'Last Updated',
			'updatedBy' => 'Updated By',
			'recentItems' => 'Recent Items',
			'topContacts' => 'Top Contacts',
			'lastLogin' => 'Last Login',
			'login' => 'Login',
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
		$criteria->compare('firstName',$this->firstName,true);
		$criteria->compare('lastName',$this->lastName,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('department',$this->department,true);
		$criteria->compare('officePhone',$this->officePhone,true);
		$criteria->compare('cellPhone',$this->cellPhone,true);
		$criteria->compare('homePhone',$this->homePhone,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('backgroundInfo',$this->backgroundInfo,true);
		$criteria->compare('emailAddress',$this->emailAddress,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);
		$criteria->compare('updatedBy',$this->updatedBy,true);
		$criteria->compare('recentItems',$this->recentItems,true);
		$criteria->compare('topContacts',$this->topContacts,true);
		$criteria->compare('lastLogin',$this->lastLogin);
		$criteria->compare('login',$this->login);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}