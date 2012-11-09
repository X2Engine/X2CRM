<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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

Yii::import('application.components.X2LinkableBehavior');

/**
 * This is the model class for table "x2_profile".
 * @package X2CRM.models
 */
class Profile extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Profile the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_profile';
	}

	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/profile',
				'autoCompleteSource'=>null
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
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
	public function relations() {
		return array();
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
			'googleId'=>Yii::t('profile','Google ID'),
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
		$criteria->compare('fullName',$this->fullName,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('officePhone',$this->officePhone,true);
		$criteria->compare('cellPhone',$this->cellPhone,true);
		$criteria->compare('emailAddress',$this->emailAddress,true);
		$criteria->compare('status',$this->status);
		
		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	public static function setDetailView($value) {
		$model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// set user's preference for contact detail view
		$model->showDetailView = ($value == 1)? 1 : 0;
		$model->save();
	}
	
	public static function getDetailView() {
		$model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preference for contact detail view
		return $model->showDetailView;
	}

	// public static function getSocialMedia() {
		// $model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preference for contact social media info
		// return $model->showSocialMedia;
	// }
	
	public function getSignature($html = false) {
		
		$adminRule = Yii::app()->params->admin->emailUseSignature;
		$userRule = $this->emailUseSignature;
		
		$userModel = CActiveRecord::model('User')->findByPk($this->id);
		$signature = '';
		
		switch($adminRule) {
			case 'admin': $signature = Yii::app()->params->admin->emailSignature; break;
			case 'user':
				switch($userRule) {
					case 'user': $signature = $signature = $this->emailSignature; break;
					case 'admin': Yii::app()->params->admin->emailSignature; break;
					case 'group': $signature == ''; break;
					default: $signature == '';
				}
				break;
			case 'group': $signature == ''; break;
			default: $signature == '';
		}
		
		
		$signature = preg_replace(
			array(
				'/\{first\}/',
				'/\{last\}/',
				'/\{phone\}/',
				'/\{group\}/',
				'/\{email\}/',
			),
			array(
				$userModel->firstName,
				$userModel->lastName,
				$this->officePhone,
				'',
				$html? CHtml::mailto($this->emailAddress) : $this->emailAddress,
			),
			$signature
		);
		if($html)
			$signature = x2base::convertLineBreaks($signature);
			// $signature = '<span style="color:grey;">' . x2base::convertLineBreaks($signature) . '</span>';
			
		return $signature;
	}
	
	public static function getResultsPerPage() {
	
		$resultsPerPage = Yii::app()->params->profile->resultsPerPage;
		// $model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preferred results per page
		// $resultsPerPage = $model->resultsPerPage;
		
		return empty($resultsPerPage)? 15 : $resultsPerPage;
	}
	
	public static function getPossibleResultsPerPage() {
		return array(
			5=>'5',
			10=>'10',
			15=>'15',
			20=>'20',
			25=>'25',
			30=>'30',
			50=>'50',
			100=>'100',
		);
	}
	
	// lookup user's settings for a gridview (visible columns, column widths)
	public static function getGridviewSettings($viewName = null) {
		$gvSettings = json_decode(Yii::app()->params->profile->gridviewSettings,true);	// converts JSON string to assoc. array
		if(isset($viewName)) {
			$viewName = strtolower($viewName);
			if(isset($gvSettings[$viewName]))
				return $gvSettings[$viewName];
			else
				return null;
		} else {
			return $gvSettings;
		}
	}
	// add/update settings for a specific gridview, or save all at once
	public static function setGridviewSettings($gvSettings,$viewName = null) {
		if(isset($viewName)) {
			$fullGvSettings = ProfileChild::getGridviewSettings();
			$fullGvSettings[strtolower($viewName)] = $gvSettings;
			Yii::app()->params->profile->gridviewSettings = json_encode($fullGvSettings);	// encode array in JSON
		} else {
			Yii::app()->params->profile->gridviewSettings = json_encode($gvSettings);	// encode array in JSON
		}
		return Yii::app()->params->profile->save();
	}
	
	// lookup user's settings for a gridview (visible columns, column widths)
	public static function getFormSettings($formName = null) {
		$formSettings = json_decode(Yii::app()->params->profile->formSettings,true);	// converts JSON string to assoc. array
		if($formSettings == null)
			$formSettings = array();
		if(isset($formName)) {
			$formName = strtolower($formName);
			if(isset($formSettings[$formName]))
				return $formSettings[$formName];
			else
				return array();
		} else {
			return $formSettings;
		}
	}
	// add/update settings for a specific form, or save all at once
	public static function setFormSettings($formSettings,$formName = null) {
		if(isset($formName)) {
			$fullFormSettings = ProfileChild::getFormSettings();
			$fullFormSettings[strtolower($formName)] = $formSettings;
			Yii::app()->params->profile->formSettings = json_encode($fullFormSettings);	// encode array in JSON
		} else {
			Yii::app()->params->profile->formSettings = json_encode($formSettings);	// encode array in JSON
		}
		return Yii::app()->params->profile->save();
	}

	
	public static function getWidgets() {
		
		if(Yii::app()->user->isGuest)	// no widgets if the user isn't logged in
			return array();
		// $model = ProfileChild::model('ProfileChild')->findByPk(Yii::app()->user->getId());
		$model = Yii::app()->params->profile;
		
		$registeredWidgets = array_keys(Yii::app()->params->registeredWidgets);
		
		$widgetNames = ($model->widgetOrder=='')? array() : explode(":",$model->widgetOrder);
		$visibility = ($model->widgets=='')? array() : explode(":",$model->widgets);
		
		$widgetList = array();
		
		$updateRecord = false;
		
		for($i=0;$i<count($widgetNames);$i++) {
		
			if(!in_array($widgetNames[$i],$registeredWidgets)) {	// check the main cfg file
				unset($widgetNames[$i]);							// if widget isn't listed,
				unset($visibility[$i]);								// remove it from database fields
				$updateRecord = true;
			} else {
				$widgetList[$widgetNames[$i]] = array('id'=>'widget_'.$widgetNames[$i],'visibility'=>$visibility[$i],'params'=>array());
			}
		}

		foreach($registeredWidgets as $class) {			// check list of widgets in main cfg file
			if(!in_array($class,array_keys($widgetList))) {								// if they aren't in the list,
				$widgetList[$class] = array('id'=>'widget_'.$class,'visibility'=>1,'params'=>array());	// add them at the bottom
				
				$widgetNames[] = $class;	// add new widgets to widgetOrder array
				$visibility[] = 1;			// and visibility array
				
				$updateRecord = true;
			}
		}

		if($updateRecord) {
			$model->widgetOrder = implode(':',$widgetNames);	// update database fields
			$model->widgets = implode(':',$visibility);			// if there are new widgets
			$model->save();
		}
		
		return $widgetList;
	}
	
	public static function getWidgetSettings() {
		if(Yii::app()->user->isGuest)	// no widgets if the user isn't logged in
			return array();
				
		if(Yii::app()->params->profile->widgetSettings == null) { // if widget settings haven't been set, give them default values
			$widgetSettings = array(
				'ChatBox'=>array(
					'chatboxHeight'=>200,
					'chatmessageHeight'=>50,
				),
				'NoteBox'=>array(
					'noteboxHeight'=>200,
					'notemessageHeight'=>50,
				),
				'DocViewer'=>array(
					'docboxHeight'=>200,
				),
				'TopSites'=>array(
					'topsitesHeight'=>200,
					'urltitleHeight'=>10,
				),
			);
			
			Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
			Yii::app()->params->profile->update();
		}
		
		$widgetSettings = json_decode(Yii::app()->params->profile->widgetSettings);
		if(!isset($widgetSettings->MediaBox)) {
			$widgetSettings->MediaBox = array('mediaBoxHeight'=>150, 'hideUsers'=>array());
			Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
			Yii::app()->params->profile->update();
		}
		
		return json_decode(Yii::app()->params->profile->widgetSettings);
	}
	
	public function getLink() {
	
		if($this->id == Yii::app()->user->id)
			return CHtml::link(Yii::t('app','your feed'),array($this->baseRoute.'/'.$this->id));
		else
			return CHtml::link(Yii::t('app','{name}\'s feed',array('{name}'=>$this->fullName)),array($this->baseRoute.'/'.$this->id));
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
					$action->update();
					
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