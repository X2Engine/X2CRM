<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * This is the model class for table "x2_list_items".
 *
 * @package application.models
 * @property integer $contactId
 * @property integer $listId
 * @property string $code
 * @property integer $result
 */
class X2ListItem extends CActiveRecord {
    public $verifyCode; // CAPTCHA for weblead form

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
			array(
                'contactId, listId, sent, opened, clicked, unsubscribed',
                'numerical',
                'integerOnly'=>true
            ),
			array('uniqueId', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('contactId, listId, uniqueId, result, opened', 'safe', 'on'=>'search'),
            array(
                'verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements(),
                    'on' => 'webFormWithCaptcha', 'captchaAction' => 'site/webleadCaptcha')
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
                        'account'=>array(self::BELONGS_TO, 'Accounts', 'accountId'),
                        'opportunity'=>array(self::BELONGS_TO, 'Opportunities', 'opportunityId'),
                        'x2Lead'=>array(self::BELONGS_TO, 'X2Leads', 'x2LeadId'),
		);
	}

	/**
	 * Yii needs this since this model does not have a primary key column in db
	 * If this isn't here, referring to this as a relation in other models will fail
     * -Commented out since this started causing issues in Yii 1.1.16-
	 */
//	public function primaryKey() {
//		return array('id','contactId','listId');
//	}

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
                            if($this->list->modelName == "Contacts"){
       				X2Flow::trigger('CampaignEmailOpenTrigger',array(
					'model'=>$this->contact,
					'campaign'=>$this->list->campaign->name
				));
                                
                            }
                            if($this->list->modelName == "Accounts"){
       				X2Flow::trigger('CampaignEmailOpenTrigger',array(
					'model'=> Accounts::model()->findByPk($this->contact->id),
					'campaign'=>$this->list->campaign->name
				));                                
                            }
                            if($this->list->modelName == "X2Leads"){
       				X2Flow::trigger('CampaignEmailOpenTrigger',array(
					'model'=>X2Leads::model()->findByPk($this->contact->id),
					'campaign'=>$this->list->campaign->name
				));                                
                            }
                            if($this->list->modelName == "Opportunity"){
        				X2Flow::trigger('CampaignEmailOpenTrigger',array(
					'model'=>Opportunity::model()->findByPk($this->contact->id),
					'campaign'=>$this->list->campaign->name
				));                               
                            }

			} else {
				X2Flow::trigger('NewsletterEmailOpenTrigger',array(
					'item'=>$this,
					'email'=>$this->emailAddress,
					'campaign'=>$this->list->campaign->name,
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
				X2Flow::trigger('CampaignEmailClickTrigger',array(
					'model'=>$this->contact,
					'campaign'=>$this->list->campaign->name,
					'url'=>$url
				));
			} else {
				X2Flow::trigger('NewsletterEmailClickTrigger',array(
					'item'=>$this,
					'email'=>$this->emailAddress,
					'campaign'=>$this->list->campaign->name,
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
				
				X2Flow::trigger('CampaignUnsubscribeTrigger',array(
					'model'=>$this->contact,
					'campaign'=>$this->list->campaign->name
				));
			} elseif(isset($this->list)) {		// no contact, must be a newsletter
			
				X2Flow::trigger('NewsletterUnsubscribeTrigger',array(
					'item'=>$this,
					'email'=>$this->emailAddress,
					'campaign'=>$this->list->campaign->name
				));
			}
		}
	}

    /**
     * Render a link to view the location the email was opened
     */
    public static function getLocationLink($uniqueId) {
        $listItem = X2ListItem::model()->findByAttributes(array('uniqueId' => $uniqueId));
        if (!$listItem || empty($listItem->opened)) return;
        // Find campaignEmailOpened Action from this contact at the time of opening
        $locationId = Yii::app()->db->createCommand()
            ->select('locationId')
            ->from('x2_actions')
            ->where('type = "campaignEmailOpened" AND associationType = "contacts" '.
                'AND associationId = :contactId AND createDate = :time',
                array(':contactId' => $listItem->contactId, ':time' => $listItem->opened))
            ->queryScalar();
        if ($locationId) {
            $location = Locations::model()->findByPk($locationId);
            return $location->getLocationLink(X2Html::fa('crosshairs'));
        }
    }
}
