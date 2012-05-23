<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/
/**
 * This is the model class for table "x2_profile".
 *
 * The followings are the available columns in table 'x2_profile':
 * @property integer $id
 * @property string $fullName
 * @property string $username
 * @property string $officePhone
 * @property string $cellPhone
 * @property string $emailAddress
 * @property string $notes
 * @property integer $status
 * @property string $tagLine
 * @property integer $lastUpdated
 * @property string $updatedBy
 * @property string $avatar
 * @property integer $allowPost
 * @property string $language
 * @property string $timeZone
 * @property integer $resultsPerPage
 * @property string $widgets
 * @property string $widgetOrder
 * @property string $backgroundColor
 * @property string $menuBgColor
 * @property string $menuTextColor
 * @property string $backgroundImg
 * @property integer $pageOpacity
 * @property string $startPage
 * @property integer $showSocialMedia
 * @property integer $showDetailView
 * @property integer $showWorkflow
 * @property string $gridviewSettings
 * @property string $formSettings
 * @property string emailUseSignature
 * @property string emailSignature
 * @property integer enableFullWidth
 */
class Profile extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Profile the static model class
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
		return 'x2_profile';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fullName, username, status', 'required'),
			array('status, lastUpdated, allowPost, resultsPerPage, pageOpacity', 'numerical', 'integerOnly'=>true),
			array('enableFullWidth,showWorkflow,showSocialMedia,showDetailView','boolean'),
			array('backgroundColor, menuBgColor, menuTextColor', 'length', 'max'=>6),
			array('emailUseSignature', 'length', 'max'=>10),
			array('startPage', 'length', 'max'=>30),
			array('fullName', 'length', 'max'=>60),
			array('username, updatedBy', 'length', 'max'=>20),
			array('officePhone, cellPhone, language', 'length', 'max'=>40),
			array('timeZone, backgroundImg', 'length', 'max'=>100),
			array('widgets, tagLine, emailAddress', 'length', 'max'=>255),
			array('widgetOrder, emailSignature', 'length', 'max'=>512),
			array('notes, avatar, gridviewSettings, formSettings, widgetSettings', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, fullName, username, officePhone, cellPhone, emailAddress, lastUpdated, language', 'safe', 'on'=>'search'),
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
			'id'=>Yii::t('profile','ID'),
			'fullName'=>Yii::t('profile','Full Name'),
			'username'=>Yii::t('profile','Username'),
			'officePhone'=>Yii::t('profile','Office Phone'),
			'cellPhone'=>Yii::t('profile','Cell Phone'),
			'emailAddress'=>Yii::t('profile','Email Address'),
			'notes'=>Yii::t('profile','Notes'),
			'status'=>Yii::t('profile','Status'),
			'tagLine'=>Yii::t('profile','Tag Line'),
			'lastUpdated'=>Yii::t('profile','Last Updated'),
			'updatedBy'=>Yii::t('profile','Updated By'),
			'avatar'=>Yii::t('profile','Avatar'),
			'allowPost'=>Yii::t('profile','Allow users to post on your profile?'),
			'language'=>Yii::t('profile','Language'),
			'timeZone'=>Yii::t('profile','Time Zone'),
			'widgets'=>Yii::t('profile','Widgets'),
			// 'groupChat'=>Yii::t('profile','Enable group chat?'),
			'widgetOrder'=>Yii::t('profile','Widget Order'),
			'widgetSettings'=>Yii::t('profile','Widget Settings'),
			'resultsPerPage'=>Yii::t('profile','Results Per Page'),
			'menuTextColor'=>Yii::t('profile','Menu Text Color'),
			'menuBgColor'=>Yii::t('profile','Menu Color'),
			'menuTextColor'=>Yii::t('profile','Menu Text Color'),
			'backgroundColor'=>Yii::t('profile','Background Color'),
			'pageOpacity'=>Yii::t('profile','Page Opacity'),
			'startPage'=>Yii::t('profile','Start Page'),
			'showSocialMedia'=>Yii::t('profile','Show Social Media'),
			'showDetailView'=>Yii::t('profile','Show Detail View'),
			'showWorkflow'=>Yii::t('profile','Show Workflow'),
			'gridviewSettings'=>Yii::t('profile','Gridview Settings'),
			'formSettings'=>Yii::t('profile','Form Settings'),
			'emailUseSignature' => Yii::t('admin','Email Signature'),
			'emailSignature' => Yii::t('admin','My Signature'),
			'enableFullWidth'=>Yii::t('profile','Enable Full Width Layout'),
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
		$criteria->compare('fullName',$this->fullName,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('officePhone',$this->officePhone,true);
		$criteria->compare('cellPhone',$this->cellPhone,true);
		$criteria->compare('emailAddress',$this->emailAddress,true);
		// $criteria->compare('notes',$this->notes,true);
		$criteria->compare('status',$this->status);
		// $criteria->compare('tagLine',$this->tagLine,true);
		// $criteria->compare('lastUpdated',$this->lastUpdated);
		// $criteria->compare('updatedBy',$this->updatedBy,true);
		// $criteria->compare('avatar',$this->avatar,true);
		// $criteria->compare('allowPost',$this->allowPost);
		// $criteria->compare('language',$this->language,true);
		// $criteria->compare('timeZone',$this->timeZone,true);
		// $criteria->compare('resultsPerPage',$this->resultsPerPage);
		// $criteria->compare('widgets',$this->widgets,true);
		// $criteria->compare('widgetOrder',$this->widgetOrder,true);
		// $criteria->compare('widgetSettings',$this->widgetSettings,true);
		// $criteria->compare('backgroundColor',$this->backgroundColor,true);
		// $criteria->compare('menuBgColor',$this->menuBgColor,true);
		// $criteria->compare('menuTextColor',$this->menuTextColor,true);
		// $criteria->compare('backgroundImg',$this->backgroundImg,true);
		// $criteria->compare('pageOpacity',$this->pageOpacity);
		// $criteria->compare('startPage',$this->startPage,true);
		// $criteria->compare('showSocialMedia',$this->showSocialMedia);
		// $criteria->compare('showDetailView',$this->showDetailView);
		// $criteria->compare('showWorkflow',$this->showWorkflow);
		
		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	public function syncActionToGoogleCalendar($action) {
		try { // catch google exceptions so the whole app doesn't crash if google has a problem syncing
			$admin = Yii::app()->params->admin;		
			if($admin->googleIntegration) {
				if(isset($this->syncGoogleCalendarId) && $this->syncGoogleCalendarId) {
					// Google Calendar Libraries
					$timezone = date_default_timezone_get();
					require_once "protected/extensions/google-api-php-client/src/apiClient.php";
					require_once "protected/extensions/google-api-php-client/src/contrib/apiCalendarService.php";
					date_default_timezone_set($timezone);
					
					$client = new apiClient();
					$client->setClientId($admin->googleClientId);
					$client->setClientSecret($admin->googleClientSecret);
					$client->setDeveloperKey($admin->googleAPIKey);
					$client->setAccessToken($this->syncGoogleCalendarAccessToken);
					$googleCalendar = new apiCalendarService($client);
					
					// check if the access token needs to be refreshed
					// note that the google library automatically refreshes the access token if we need a new one, 
					// we just need to check if this happend by calling a google api function that requires authorization, 
					// and, if the access token has changed, save this new access token
					$testCal = $googleCalendar->calendars->get($this->syncGoogleCalendarId);			
					if($this->syncGoogleCalendarAccessToken != $client->getAccessToken()) {
						$this->syncGoogleCalendarAccessToken = $client->getAccessToken();
						$this->update();
					}
					
					$summary = $action->actionDescription;
					if($action->associationType == 'contacts' || $action->associationType == 'contact')
						$summary = $action->associationName . ' - ' . $action->actionDescription;
					
					$event = new Event();
					$event->setSummary($summary);
					
					if($action->allDay) {
						$start = new EventDateTime();
						$start->setDate(date('Y-m-d', $action->dueDate));
						$event->setStart($start);
						
						if(!$action->completeDate)
							$action->completeDate = $action->dueDate;
						$end = new EventDateTime();
						$end->setDate(date('Y-m-d', $action->completeDate + 86400));
						$event->setEnd($end);
					} else {
						$start = new EventDateTime();
						$start->setDateTime(date('c', $action->dueDate));
						$event->setStart($start);
						
						if(!$action->completeDate)
							$action->completeDate = $action->dueDate; // if no end time specified, make event 1 hour long
						$end = new EventDateTime();
						$end->setDateTime(date('c', $action->completeDate));
						$event->setEnd($end);
					}
					
					if($action->color && $action->color != '#3366CC') {
					    $colorTable = array(
					    	10=>'Green',
					    	11=>'Red',
					    	6=>'Orange',
					    	8=>'Black',
					    );
					    if(($key = array_search($action->color, $colorTable)) != false)
					    	$event->setColorId($key);
					}
					
					$newEvent = $googleCalendar->events->insert($this->syncGoogleCalendarId, $event);
					$action->syncGoogleCalendarEventId = $newEvent['id'];
				}
			}
		} catch (Exception $e) {

		}
	}
	
	public function updateGoogleCalendarEvent($action) {
		try { // catch google exceptions so the whole app doesn't crash if google has a problem syncing
			$admin = Yii::app()->params->admin;		
			if($admin->googleIntegration) {
				if(isset($this->syncGoogleCalendarId) && $this->syncGoogleCalendarId) {
					// Google Calendar Libraries
					$timezone = date_default_timezone_get();
					require_once "protected/extensions/google-api-php-client/src/apiClient.php";
					require_once "protected/extensions/google-api-php-client/src/contrib/apiCalendarService.php";
					date_default_timezone_set($timezone);
					
					$client = new apiClient();
					$client->setClientId($admin->googleClientId);
					$client->setClientSecret($admin->googleClientSecret);
					$client->setDeveloperKey($admin->googleAPIKey);
					$client->setAccessToken($this->syncGoogleCalendarAccessToken);
					$client->setUseObjects(true); // return objects instead of arrays
					$googleCalendar = new apiCalendarService($client);
					
					// check if the access token needs to be refreshed
					// note that the google library automatically refreshes the access token if we need a new one, 
					// we just need to check if this happend by calling a google api function that requires authorization, 
					// and, if the access token has changed, save this new access token
					$testCal = $googleCalendar->calendars->get($this->syncGoogleCalendarId);			
					if($this->syncGoogleCalendarAccessToken != $client->getAccessToken()) {
						$this->syncGoogleCalendarAccessToken = $client->getAccessToken();
						$this->update();
					}
					
					$summary = $action->actionDescription;
					if($action->associationType == 'contacts' || $action->associationType == 'contact')
						$summary = $action->associationName . ' - ' . $action->actionDescription;
					
					$event = $googleCalendar->events->get($this->syncGoogleCalendarId, $action->syncGoogleCalendarEventId);
					$event->setSummary($summary);
					
					if($action->allDay) {
						$start = new EventDateTime();
						$start->setDate(date('Y-m-d', $action->dueDate));
						$event->setStart($start);
						
						if(!$action->completeDate)
							$action->completeDate = $action->dueDate;
						$end = new EventDateTime();
						$end->setDate(date('Y-m-d', $action->completeDate + 86400));
						$event->setEnd($end);
					} else {
						$start = new EventDateTime();
						$start->setDateTime(date('c', $action->dueDate));
						$event->setStart($start);
						
						if(!$action->completeDate)
							$action->completeDate = $action->dueDate; // if no end time specified, make event 1 hour long
						$end = new EventDateTime();
						$end->setDateTime(date('c', $action->completeDate));
						$event->setEnd($end);
					}
					
					if($action->color && $action->color != '#3366CC') {
					    $colorTable = array(
					    	10=>'Green',
					    	11=>'Red',
					    	6=>'Orange',
					    	8=>'Black',
					    );
					    if(($key = array_search($action->color, $colorTable)) != false)
					    	$event->setColorId($key);
					}
					
					$newEvent = $googleCalendar->events->update($this->syncGoogleCalendarId, $action->syncGoogleCalendarEventId, $event);
				}
			}
		} catch (Exception $e) {

		}
	}
	
	public function deleteGoogleCalendarEvent($action) {
		try { // catch google exceptions so the whole app doesn't crash if google has a problem syncing
			$admin = Yii::app()->params->admin;		
			if($admin->googleIntegration) {
				if(isset($this->syncGoogleCalendarId) && $this->syncGoogleCalendarId) {
					// Google Calendar Libraries
					$timezone = date_default_timezone_get();
					require_once "protected/extensions/google-api-php-client/src/apiClient.php";
					require_once "protected/extensions/google-api-php-client/src/contrib/apiCalendarService.php";
					date_default_timezone_set($timezone);
					
					$client = new apiClient();
					$client->setClientId($admin->googleClientId);
					$client->setClientSecret($admin->googleClientSecret);
					$client->setDeveloperKey($admin->googleAPIKey);
					$client->setAccessToken($this->syncGoogleCalendarAccessToken);
					$client->setUseObjects(true); // return objects instead of arrays
					$googleCalendar = new apiCalendarService($client);
					
					$googleCalendar->events->delete($this->syncGoogleCalendarId, $action->syncGoogleCalendarEventId);
				}
			}
			
		} catch (Exception $e) {
		
		}
	}
}