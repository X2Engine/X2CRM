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

class X2Calendar extends CActiveRecord
{

	public $googleCalendarName;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Contacts the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_calendars';
	}
	
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'googleCalendar'=>Yii::t('calendar', 'Google Calendar'),
			'googleFeed'=>Yii::t('calendar', 'Google Feed'),
			'googleCalendarName' => Yii::t('calendar','Google Calendar Name'),
		);
	}
	
	public static function getNames() {
		$calendars = X2Calendar::model()->findAllByAttributes(array('googleCalendar'=>false));
		
		$names = array();
		foreach($calendars as $calendar)
			$names["{$calendar->id}"] = $calendar->name;
		
		return $names;
	}
	
	public static function getViewableUserCalendarNames() {
		$order = 'desc';
		$userArray = CActiveRecord::model('User')->findAll();
		$names = array('Anyone' => 'Anyone');
		foreach ($userArray as $user) {
			if(in_array(Yii::app()->user->name, explode(',', $user->calendarViewPermission)) || 
				!$user->setCalendarPermissions || // user hasn't set up calendar permissions?
				Yii::app()->user->name == 'admin' || 
				Yii::app()->user->name == $user->username) {
				$first = $user->firstName;
				$last = $user->lastName;
				$userName = $user->username;
				$name = $first . ' ' . $last;
				$names[$userName] = $name;
			}
		}
		return $names;
	}
	
	public static function getEditableUserCalendarNames() {
		$order = 'desc';
		$userArray = CActiveRecord::model('User')->findAll();
		$names = array('Anyone' => 'Anyone');
		foreach ($userArray as $user) {
			if(in_array(Yii::app()->user->name, explode(',', $user->calendarEditPermission)) || 
				Yii::app()->user->name == 'admin' || 
				Yii::app()->user->name == $user->username) {
				$first = $user->firstName;
				$last = $user->lastName;
				$userName = $user->username;
				$name = $first . ' ' . $last;
				$names[$userName] = $name;
			}
		}
		return $names;
	}
	
	public static function getViewableGroupCalendarNames() {
		
		$names = array();

		if(Yii::app()->user->name == 'admin') { // admin sees all
			$groups = Yii::app()->db->createCommand()->select()->from('x2_groups')->queryAll();			
		} else {
			$groups = Yii::app()->db->createCommand()->select('x2_groups.id, x2_groups.name')->from('x2_group_to_user')->join('x2_groups', 'groupId = x2_groups.id')->where('userId='.Yii::app()->user->id)->queryAll();
		}
		
		foreach($groups as $group) {
			$names[$group['id']] = $group['name'];
		}
		
		return $names;
	}
	
/*	// get a list of calendar names that the current user has permission to view
	public static function getViewableCalendarNames() {
		$calendars = X2Calendar::model()->findAllByAttributes(array('googleCalendar'=>false));
		
		$names = array();
		foreach($calendars as $calendar) {
			$viewPermissions = explode(',', $calendar->viewPermission);
			if (in_array(Yii::app()->user->name, $viewPermissions))  // current user has permission to view calendar?
				$names["{$calendar->id}"] = $calendar->name;
			else if (Yii::app()->user->name == 'admin' || // current user is admin?
					Yii::app()->user->name == $calendar->createdBy) // current user created this calendar?
				$names["{$calendar->id}"] = $calendar->name;
			else { // check if user belongs to a group that can view this calendar
				foreach($viewPermissions as $permission)
					if(is_numeric($permission)) {
						$groups = GroupToUser::model()->findAllByAttributes(array('groupId'=>$permission));
						foreach($groups as $group)
							if(Yii::app()->user->id == $group->userId)
								$names["{$calendar->id}"] = $calendar->name;
					}
			}
		}
		
		return $names;
	}
	
	// get a list of calendar names that the current user has permission to view
	public static function getViewableGoogleCalendarNames() {
		$calendars = X2Calendar::model()->findAllByAttributes(array('googleCalendar'=>true));
		
		$names = array();
		foreach($calendars as $calendar) {
			$viewPermissions = explode(',', $calendar->viewPermission);
			$viewPermissions = array_map('trim', $viewPermissions); // fix bug with extra space causing user names not to be found
			if (in_array(Yii::app()->user->name, $viewPermissions))  // current user has permission to view calendar?
				$names["{$calendar->id}"] = $calendar->name;
			else if (Yii::app()->user->name == 'admin' || // current user is admin?
					Yii::app()->user->name == $calendar->createdBy) // current user created calendar?
				$names["{$calendar->id}"] = $calendar->name;
			else { // check if user belongs to a group that can view this calendar
				foreach($viewPermissions as $permission)
					if(is_numeric($permission)) {
						$groups = GroupToUser::model()->findAllByAttributes(array('groupId'=>$permission));
						foreach($groups as $group)
							if(Yii::app()->user->id == $group->userId)
								$names["{$calendar->id}"] = $calendar->name;
					}
			}
		}
				
		return $names;
	}
	
	// get a list of calendar names that the current user has permission to edit
	public static function getEditableCalendarNames() {
		$calendars = X2Calendar::model()->findAll();
		
		foreach($calendars as $key=>$calendar)
			if($calendar->googleCalendar && !$calendar->googleCalendarId)
				unset($calendars[$key]);
		
		$names = array();
		foreach($calendars as $calendar) {
			$editPermissions = explode(',', $calendar->editPermission);
			if (in_array(Yii::app()->user->name, $editPermissions)) // current user has permission to view calendar?
				$names["{$calendar->id}"] = $calendar->name;
			else if (Yii::app()->user->name == 'admin' || // current user is admin?
					Yii::app()->user->name == $calendar->createdBy) // current user created this calendar?
				$names["{$calendar->id}"] = $calendar->name;
			else { // check if user belongs to a group that can view this calendar
				foreach($editPermissions as $permission)
					if(is_numeric($permission)) {
						$groups = GroupToUser::model()->findAllByAttributes(array('groupId'=>$permission));
						foreach($groups as $group)
							if(Yii::app()->user->id == $group->userId)
								$names["{$calendar->id}"] = $calendar->name;
					}
			}
		}
		
		return $names;
	}
	
	public static function getGoogleCalendarNames() {
		$calendars = X2Calendar::model()->findAllByAttributes(array('googleCalendar'=>true));
		
		$names = array();
		foreach($calendars as $calendar)
			$names["{$calendar->id}"] = $calendar->name;
		
		return $names;
	}
	*/
	
	public static function getCalendarFilters() {
		$user = User::model()->findByPk(Yii::app()->user->id);
		$calendarFilters = explode(',', $user->calendarFilter);
		$filters = X2Calendar::getCalendarFilterNames();
		
		$filterList = array();
		foreach($filters as $filter)
			if(in_array($filter, $calendarFilters))
				$filterList[$filter] = true;
			else
				$filterList[$filter] = false;
		
		return $filterList;
	}
	
	// get a list of the names of all filters
	public static function getCalendarFilterNames() {
		return array('contacts', 'accounts', 'sales', 'quotes', 'products', 'media', 'completed', 'email', 'attachment');
	}
	
	// get a google calendar service instance using an access token and,
	// if necesary, refresh the access token
	public function getGoogleCalendar() {
		// Google Calendar Libraries
		$timezone = date_default_timezone_get();
		require_once "protected/extensions/google-api-php-client/src/apiClient.php";
		require_once "protected/extensions/google-api-php-client/src/contrib/apiCalendarService.php";
		date_default_timezone_set($timezone);

		$admin = Yii::app()->params->admin;
		if($admin->googleIntegration) {
			$client = new apiClient();
			$client->setClientId($admin->googleClientId);
			$client->setClientSecret($admin->googleClientSecret);
			$client->setDeveloperKey($admin->googleAPIKey);
			$client->setAccessToken($this->googleAccessToken);
			$service = new apiCalendarService($client);

			// check if the access token needs to be refreshed
			// note that the google library automatically refreshes the access token if we need a new one, 
			// we just need to check if this happend by calling a google api function that requires authorization, 
			// and, if the access token has changed, save this new access token
			$googleCalendar = $service->calendars->get($this->googleCalendarId);			
			if($this->googleAccessToken != $client->getAccessToken()) {
				$this->googleAccessToken = $client->getAccessToken();
				$this->update();
			}

			return $service;
		}
		
		return null;
	}
	
	public function createGoogleEvent($action) {
	
		// Google Calendar Libraries
		$timezone = date_default_timezone_get();
		require_once "protected/extensions/google-api-php-client/src/apiClient.php";
		require_once "protected/extensions/google-api-php-client/src/contrib/apiCalendarService.php";
		date_default_timezone_set($timezone);
		
		$googleCalendar = $this->getGoogleCalendar();
	
		$event = new Event();
		$event->setSummary($action->actionDescription);
		
		if($action->allDay) {
			$start = new EventDateTime();
			$start->setDate(date('Y-m-d', $action->dueDate));
			$event->setStart($start);
			
			if(!$action->completeDate)
				$action->completeDate = $action->dueDate + 86400;
			$end = new EventDateTime();
			$end->setDate(date('Y-m-d', $action->completeDate));
			$event->setEnd($end);
		} else {
			$start = new EventDateTime();
			$start->setDateTime(date('c', $action->dueDate));
			$event->setStart($start);
			
			if(!$action->completeDate)
				$action->completeDate = $action->dueDate + 3600; // if no end time specified, make event 1 hour long
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
		
		$googleCalendar->events->insert($this->googleCalendarId, $event);
	}

	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		if(Yii::app()->user->name != 'admin') // if not admin 
			$criteria->condition = "createdBy='". Yii::app()->user->name . "'"; // user can only edit shared calendar they have created
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}
	
	private function searchBase($criteria) {
		$criteria->compare('name',$this->name,true);
		
		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}
	
}