<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * This is the model class for table "x2_list_items".
 *
 * @package X2CRM.models
 * @property integer $contactId
 * @property integer $listId
 * @property string $code
 * @property integer $result
 */
class X2ListItem extends CActiveRecord {
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
			array('listId', 'required'),
			array('contactId, listId, sent, opened, clicked, unsubscribed', 'numerical', 'integerOnly'=>true),
			array('uniqueId', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('contactId, listId, uniqueId, result, opened', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'list'=>array(self::BELONGS_TO, 'X2List', 'listId'),
			'contact'=>array(self::BELONGS_TO, 'Contacts', 'contactId'),
		);
	}

	/**
	 * Yii needs this since this model does not have a primary key column in db
	 * If this isn't here, referring to this as a relation in other models will fail
	 */
	public function primaryKey() {
		return array('contactId','listId');
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'contactId' => 'Contact',
			'listId' => 'List',
			'uniqueId' => 'Code',
			'error' => 'Error',
			'sent' => 'Email Sent',
			'opened' => 'Opened Emal',
			'clicked' => 'Clicked Link',
			'unsubscribed' => 'Unsubscribed',
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
		$criteria->compare('uniqueId',$this->uniqueId,true);
		$criteria->compare('error',$this->error,true);
		$criteria->compare('sent',$this->sent,true);
		$criteria->compare('opened',$this->opened,true);
		$criteria->compare('clicked',$this->clicked,true);
		$criteria->compare('unsubscribed',$this->unsubscribed,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Marks this campaign/newsletter item as opened.
	 */
	public function markOpened() {
		if($this->opened == 0) {
			$this->opened = time();
			$this->update(array('opened'));
		}
		
		if($this->list->campaign !== null) {
			if($this->contact !== null) {
				X2Flow::trigger('CampaingEmailOpenTrigger',array(
					'model'=>$this->contact,
					'campaign'=>$this->list->campaign
				));
			} else {
				X2Flow::trigger('NewsletterEmailOpenTrigger',array(
					'item'=>$this,
					'email'=>$this->emailAddress,
					'campaign'=>$this->list->campaign,
				));
			}
		}
	}
	
	/**
	 * Marks this campaign/newsletter item as clicked.
	 * 
	 *@param string $url the URL of the link clicked on
	 */
	public function markClicked($url) {
		if($this->opened == 0)
			$this->markOpened();	// mark as opened, run automation for email open
		
		if($this->clicked == 0) {
			$this->clicked = time();
			$this->update(array('clicked'));
		}
		
		if($this->list->campaign !== null) {
			if($this->contact !== null) {
				X2Flow::trigger('CampaingEmalClickTrigger',array(
					'model'=>$this->contact,
					'campaign'=>$this->list->campaign,
					'url'=>$url
				));
			} else {
				X2Flow::trigger('NewsletterEmalClickTrigger',array(
					'item'=>$this,
					'email'=>$this->emailAddress,
					'campaign'=>$this->list->campaign,
					'url'=>$url
				));
			}
		}
	}

	/**
	 * Marks this campaign/newsletter item as unsubscribed.
	 * If a contact record is available, unsubscribe them from all other lists as well.
	 */
	public function unsubscribe($email=null) {
		if($this->opened == 0)
			$this->markOpened();	// mark as opened, run automation for email open
			
		if($this->unsubscribed == 0) {
			$this->unsubscribed = time();
			$this->update(array('unsubscribed'));
		}
		// unsubscribe this email from all other newsletters
		CActiveRecord::model('X2ListItem')->updateAll(array('unsubscribed'=>time()),'emailAddress=:email AND unsubscribed=0',array('email'=>$this->emailAddress));
		
		if($this->list->campaign !== null) {
			if($this->contact !== null) {		// regular campaign
				// update the contact
				$this->contact->doNotEmail = true;
				$this->contact->lastActivity = time();
				$this->contact->update(array('doNotEmail','lastActivity'));
				
				X2Flow::trigger('CampaingUnsubscribeTrigger',array(
					'model'=>$this->contact,
					'campaign'=>$this->list->campaign
				));
			} elseif(isset($this->list)) {		// no contact, must be a newsletter
			
				X2Flow::trigger('NewsletterUnsubscribeTrigger',array(
					'item'=>$this,
					'email'=>$this->emailAddress,
					'campaign'=>$this->list->campaign
				));
			}
		}
	}
}
