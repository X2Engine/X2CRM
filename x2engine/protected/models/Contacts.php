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
 * @property string $phone2
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
 * @property string $newField
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
			array('firstName, lastName, title, phone, phone2, city, state, country, priority, leadSource', 'length', 'max'=>40),
			array('company, email, website, address, linkedin, googleplus, facebook, otherUrl', 'length', 'max'=>100),
			array('zipcode, assignedTo, twitter, updatedBy', 'length', 'max'=>20),
			array('skype', 'length', 'max'=>32),
			array('lastUpdated', 'length', 'max'=>30),
			array('backgroundInfo', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, firstName, lastName, title, company, accountId, phone, phone2, email, website, address, city, state, zipcode, country, visibility, assignedTo, backgroundInfo, twitter, linkedin, skype, googleplus, lastUpdated, updatedBy, priority, leadSource, rating, createDate, facebook, otherUrl', 'safe', 'on'=>'search'),
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
                $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Contacts'));
                $arr=array();
                foreach($fields as $field){
                    $arr[$field->fieldName]=Yii::t('contacts',$field->attributeLabel);
                }
                
                return $arr;
                
	}
        public static function getNames() {
		$contactArray = Contacts::model()->findAll($condition='assignedTo=\''.Yii::app()->user->getName().'\' OR assignedTo=\'Anyone\'');
		$names=array(0=>'None');
		foreach($contactArray as $user){
			$first = $user->firstName;
			$last = $user->lastName;
			$name = $first . ' ' . $last;
			$names[$user->id]=$name;
		}
		return $names;
	}
	
	// creates virtual "name" attribute
	public function getName() {
		return $this->firstName.' '.$this->lastName;
	}
	
	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),		/* optional line */
				'defaultStickOnClear'=>false	/* optional line */
			),
		);
	}

	/**
	*	Returns all public contacts.
	*	@return $names An array of strings containing the names of contacts.
	*/
	public static function getAllNames() {
		$contactArray = Contacts::model()->findAll($condition='visibility=1');
		$names=array(0=>'None');
		foreach($contactArray as $user){
			$first = $user->firstName;
			$last = $user->lastName;
			$name = $first . ' ' . $last;
			$names[$user->id]=$name;
		}
		return $names;
	}

	public static function getContactLinks($contacts) {
		if(!is_array($contacts))
			$contacts = explode(' ',$contacts);
		
		$links = array();
		foreach($contacts as &$id){
			if($id !=0 ) {
				$model = CActiveRecord::model('Contacts')->findByPk($id);
				$links[] = CHtml::link($model->name,array('contacts/view','id'=>$id));
				//$links.=$link.', ';
				
			}
		}
		//$links=substr($links,0,strlen($links)-2);
		return implode(', ',$links);
	}
	
	public static function getMailingList($criteria) {
		
		$mailingList=array();
		
		$arr=Contacts::model()->findAll();
		foreach($arr as $contact){
			$i=preg_match("/$criteria/i",$contact->backgroundInfo);
			if($i>=1){
				$mailingList[]=$contact->email;
			}
		}
		return $mailingList;
	}
	
	public function searchAll() {
		$criteria=new CDbCriteria;
		$parameters=array('condition'=>"visibility='1' || assignedTo='Anyone' || assignedTo='".Yii::app()->user->getName()."'",'limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));
		
		return $this->searchBase($criteria);
	}

	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('condition'=>"assignedTo='".Yii::app()->user->getName()."'",'limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));
		
		return $this->searchBase($criteria);
	}
	
	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}

	public function searchList($id) {
	
		if(!empty($id))
			$list = CActiveRecord::model('ContactList')->findByPk($id);

		if(isset($list)) {
			$contactIds = Yii::app()->db->createCommand()->select('contactId')->from('x2_list_items')->where('x2_list_items.listId='.$id)->queryColumn();
			// die(var_dump($contactIds));
			// $search = CActiveRecord::model('Contacts')->findAllByPk($contactIds);
			// return $search;
			
			$sql = Yii::app()->db->createCommand()
				->select('x2_contacts.*')
				->from('x2_contacts')
				->join('x2_list_items','x2_contacts.id = x2_list_items.contactId')
				->where('x2_list_items.listId='.$id.' AND (x2_contacts.visibility=1 OR x2_contacts.assignedTo="'.Yii::app()->user->getName().'")')
				->getText();
			
			$count = Yii::app()->db->createCommand()->select('COUNT(*)')->from('x2_list_items')->where('x2_list_items.listId='.$id)->queryScalar();

			return new CSqlDataProvider($sql,array(
				// 'criteria'=>$criteria,
				// 'data'=>$results,
				// 'modelClass'=>'Contacts',
				'totalItemCount'=>$count,
				'sort'=>array(
					'attributes'=>array('firstName','lastName','phone','phone2','createDate','lastUpdated','leadSource'),
					'defaultOrder'=>'lastUpdated DESC',
				),
				'pagination'=>array(
					'pageSize'=>ProfileChild::getResultsPerPage(),
				),
			));
		} else {
			return new CActiveDataProvider('Contacts',array(
				// 'criteria'=>$criteria,
				// 'data'=>$results,
				// 'modelClass'=>'Contacts',
				// 'totalItemCount'=>$count,
				'sort'=>array(
					'defaultOrder'=>'lastUpdated DESC',
				),
				'pagination'=>array(
					'pageSize'=>ProfileChild::getResultsPerPage(),
				),
			));
			// Yii::app()->controller->redirect(array('contacts/listAll'));
		}
		

		// $criteria=new CDbCriteria;
		// $parameters=array(
			
			// 'condition'=>"(SELECT count(*) FROM x2_list_items WHERE listId=".$id." AND contactId = t.id) > 0 AND visibility='1' || assignedTo='Anyone' || assignedTo='".Yii::app()->user->getName()."'",
			// 'limit'=>ProfileChild::getResultsPerPage()
		// );
		// $criteria->scopes=array('findAll'=>array($parameters));
		// return $this->searchBase($criteria);
	}
	
	
	public function searchBase($criteria) {
		// $criteria->compare('id',$this->id);
		$criteria->compare('firstName',$this->firstName,true);
		$criteria->compare('lastName',$this->lastName,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('company',$this->company,true);
		$criteria->compare('accountId',$this->accountId);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('phone2',$this->phone2,true);
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
		// $criteria->compare('lastUpdated',$this->lastUpdated,true);
		$criteria->compare('updatedBy',$this->updatedBy,true);
		$criteria->compare('priority',$this->priority,true);
		$criteria->compare('leadSource',$this->leadSource,true);
		$criteria->compare('rating',$this->rating);
		//$criteria->compare('createDate',$this->createDate);
		
		$dateRange = Yii::app()->controller->partialDateRange($this->createDate);
		if($dateRange !== false)
			$criteria->addCondition('createDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
			
		$dateRange = Yii::app()->controller->partialDateRange($this->lastUpdated);
		if($dateRange !== false)
			$criteria->addCondition('lastUpdated BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);

		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'lastUpdated DESC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
}