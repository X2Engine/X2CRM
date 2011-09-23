<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

class ContactChild extends Contacts {

	public static function getNames() {
		$contactArray = ContactChild::model()->findAll($condition='assignedTo=\''.Yii::app()->user->getName().'\' OR assignedTo=\'Anyone\'');
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
		$contactArray = ContactChild::model()->findAll($condition='visibility=1');
		$names=array(0=>'None');
		foreach($contactArray as $user){
			$first = $user->firstName;
			$last = $user->lastName;
			$name = $first . ' ' . $last;
			$names[$user->id]=$name;
		}
		return $names;
	}

	public static function getContactLinks($str) {
		$pieces = explode(' ',$str);
		$links='';
			foreach($pieces as $user){
				if($user==0) {
					$link='';
				} else {
					$model = Contacts::model()->findByPk($user);
					$link = CHtml::link($model->firstName.' '.$model->lastName,array('contacts/view','id'=>$user));
					$links.=$link.', ';
					
				}
		}
		$links=substr($links,0,strlen($links)-2);
		return $links;
	}
	
	public static function getMailingList($criteria) {
		
		$mailingList=array();
		
		$arr=ContactChild::model()->findAll();
		foreach($arr as $contact){
			$i=preg_match("/$criteria/i",$contact->backgroundInfo);
			if($i>=1){
				$mailingList[]=$contact->email;
			}
		}
		return $mailingList;
	}
	
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.
		
		$profile = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preferred results per page
		$resultsPerPage = $profile->resultsPerPage;
		
		$criteria=new CDbCriteria;
		$parameters=array('condition'=>"visibility='1' || assignedTo='Anyone' || assignedTo='".Yii::app()->user->getName()."'",'limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));
		$criteria->compare('id',$this->id);
		$criteria->compare('firstName',$this->firstName,true);
		$criteria->compare('CONCAT(firstName," ",lastName)',$this->lastName,true);
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

		
		

		return new SmartDataProvider('Contacts', array(
			'sort'=>array(
				'defaultOrder'=>'lastUpdated DESC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

	public function searchAdmin() {
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


		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'lastName ASC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

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
}