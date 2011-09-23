<?php

/**
 * This is the model class for table "x2_contacts".
 *
 * The followings are the available columns in table 'x2_contacts':
 * @property integer $id
 * @property string $firstName
 * @property string $lastName
 * @property string $title
 * @property string $company
 * @property integer $accountId
 * @property string $phone
 * @property string $email
 * @property string $website
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zipcode
 * @property string $country
 * @property integer $visibility
 * @property string $assignedTo
 * @property string $backgroundInfo
 * @property string $twitter
 * @property string $linkedin
 * @property string $skype
 * @property string $googleplus
 * @property string $lastUpdated
 * @property string $updatedBy
 * @property string $priority
 * @property string $leadSource
 * @property integer $rating
 * @property integer $createDate
 * @property string $facebook
 * @property string $otherUrl
 */
class Contacts extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Contacts the static model class
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
		return 'x2_contacts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('firstName, lastName, visibility', 'required'),
			array('accountId, visibility, rating, createDate', 'numerical', 'integerOnly'=>true),
			array('email','email'),
			array('firstName, lastName, title, phone, city, state, country, priority, leadSource', 'length', 'max'=>40),
			array('company, email, website, address, linkedin, googleplus, facebook, otherUrl', 'length', 'max'=>100),
			array('zipcode, assignedTo, twitter, updatedBy', 'length', 'max'=>20),
			array('skype', 'length', 'max'=>32),
			array('lastUpdated', 'length', 'max'=>30),
			array('backgroundInfo', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, firstName, lastName, title, company, accountId, phone, email, website, address, city, state, zipcode, country, visibility, assignedTo, backgroundInfo, twitter, linkedin, skype, googleplus, lastUpdated, updatedBy, priority, leadSource, rating, createDate, facebook, otherUrl', 'safe', 'on'=>'search'),
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
			'id'=>Yii::t('contacts','ID'),
			'firstName'=>Yii::t('contacts','First Name'),
			'lastName'=>Yii::t('contacts','Last Name'),
			'title'=>Yii::t('contacts','Title'),
			'company'=>Yii::t('contacts','Account'),
			'accountId'=>Yii::t('contacts','Account ID'),
			'phone'=>Yii::t('contacts','Phone'),
			'email'=>Yii::t('contacts','Email'),
			'website'=>Yii::t('contacts','Website'),
			'twitter'=>Yii::t('contacts','Twitter'),
			'linkedin'=>Yii::t('contacts','Linkedin'),
			'skype'=>Yii::t('contacts','Skype'),
			'googleplus'=>Yii::t('contacts','Googleplus'),
			'address'=>Yii::t('contacts','Address'),
			'city'=>Yii::t('contacts','City'),
			'state'=>Yii::t('contacts','State'),
			'zipcode'=>Yii::t('contacts','Zip Code'),
			'country'=>Yii::t('contacts','Country'),
			'visibility'=>Yii::t('contacts','Visibility'),
			'assignedTo'=>Yii::t('contacts','Assigned To'),
			'backgroundInfo'=>Yii::t('contacts','Background Info'),
			'lastUpdated'=>Yii::t('contacts','Last Updated'),
			'updatedBy'=>Yii::t('contacts','Updated By'),
			'leadSource'=>Yii::t('contacts','Lead Source'),
			'priority'=>Yii::t('contacts','Priority'),
			'rating'=>Yii::t('contacts','Rating'),
			'createDate'=>Yii::t('contacts','Create Date'),
			'facebook'=>Yii::t('contacts','Facebook'),
			'otherUrl'=>Yii::t('contacts','Other'),
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
		$criteria->compare('title',$this->title,true);
		$criteria->compare('company',$this->company,true);
		$criteria->compare('accountId',$this->accountId);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('website',$this->website,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zipcode',$this->zipcode,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('visibility',$this->visibility);
		$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('backgroundInfo',$this->backgroundInfo,true);
		$criteria->compare('twitter',$this->twitter,true);
		$criteria->compare('linkedin',$this->linkedin,true);
		$criteria->compare('skype',$this->skype,true);
		$criteria->compare('googleplus',$this->googleplus,true);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);
		$criteria->compare('updatedBy',$this->updatedBy,true);
		$criteria->compare('priority',$this->priority,true);
		$criteria->compare('leadSource',$this->leadSource,true);
		$criteria->compare('rating',$this->rating);
		$criteria->compare('createDate',$this->createDate);
		$criteria->compare('facebook',$this->facebook,true);
		$criteria->compare('otherUrl',$this->otherUrl,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}