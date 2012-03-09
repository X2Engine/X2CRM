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
 
class Calendar extends CActiveRecord
{
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
	
	public static function getNames() {
		$calendars = Calendar::model()->findAllByAttributes(array('googleCalendar'=>false));
		
		$names = array();
		foreach($calendars as $calendar)
			$names["{$calendar->id}"] = $calendar->name;
		
		return $names;
	}
	
	public static function getViewableUserCalendarNames() {
		$order = 'desc';
		$userArray = CActiveRecord::model('UserChild')->findAll();
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
		$userArray = CActiveRecord::model('UserChild')->findAll();
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
	
	// get a list of calendar names that the current user has permission to view
	public static function getViewableCalendarNames() {
		$calendars = Calendar::model()->findAllByAttributes(array('googleCalendar'=>false));
		
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
		$calendars = Calendar::model()->findAllByAttributes(array('googleCalendar'=>true));
		
		$names = array();
		foreach($calendars as $calendar) {
			$viewPermissions = explode(',', $calendar->viewPermission);
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
		$calendars = Calendar::model()->findAllByAttributes(array('googleCalendar'=>false));
		
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
		$calendars = Calendar::model()->findAllByAttributes(array('googleCalendar'=>true));
		
		$names = array();
		foreach($calendars as $calendar)
			$names["{$calendar->id}"] = $calendar->name;
		
		return $names;
	}
	
	
	public static function getCalendarFilters() {
		$user = UserChild::model()->findByPk(Yii::app()->user->id);
		$calendarFilters = explode(',', $user->calendarFilter);
		$filters = Calendar::getCalendarFilterNames();
		
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
		return array('completed', 'quote', 'email');
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