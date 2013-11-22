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
 * @package X2CRM.modules.calendar.models 
 */
class X2CalendarPermissions extends CActiveRecord
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
		return 'x2_calendar_permissions';
	}
	
	public static function getViewableUserCalendarNames() {
		
		$users = User::model()->findAll( // all users
			array(
				'select'=>'id, username, firstName, lastName',
				'index'=>'id',
                'condition'=>'status=1'
			)
		);
		
		$names = array(); // array mapping username to user's full name for user calendars we can view

		
		if(Yii::app()->params->isAdmin) { // admin sees all user calendars
			foreach($users as $user) {
				$first = $user->firstName;
				$last = $user->lastName;
				$fullname = $first . ' ' . $last;
				$username = $user->username;
				$names[$username] = $fullname;
			}
		} else {
			$permissions = X2CalendarPermissions::model()->findAll( // permissions for user's that have set there permissions
				array(
					'select'=>'user_id, other_user_id, view',
					'condition'=>'other_user_id=:user_id',
					'params'=>array(':user_id'=>Yii::app()->user->id),
					'index'=>'user_id',
				)
			);
			
			
			$checked = array(); // user's who have there permission set up. Other user's will have default permissions
			foreach($permissions as $permission) { // loop through user's that have set there permissions
				if($permission->view && isset($users[$permission->user_id])) { // user gives us permission to view there calendar?
					$user = $users[$permission->user_id];
					$first = $user->firstName;
					$last = $user->lastName;
					$fullname = $first . ' ' . $last;
					$username = $user->username;
					$names[$username] = $fullname;
				}
				$checked[] = $permission->user_id;
			}
			
			// user's who have not set permissions default to letting everyone see there calendar
			foreach($users as $user) {
				if(!in_array($user->id, $checked)) {
					$first = $user->firstName;
					$last = $user->lastName;
					$fullname = $first . ' ' . $last;
					$username = $user->username;
					$names[$username] = $fullname;
				}
			}
			
			// let current user view there own calendar
			$user = $users[Yii::app()->user->id];
			$first = $user->firstName;
			$last = $user->lastName;
			$fullname = $first . ' ' . $last;
			$username = $user->username;
			$names[$username] = $fullname;
		
		}
		
		// put 'Web Admin' and 'Anyone' at the end of the list
		$names['Anyone'] = 'Anyone';
		if(isset($names['admin'])) {
			$adminName = ucwords($names['admin']); // Round-about way
			unset($names['admin']);       //          of putting admin
			$names['admin'] = $adminName; //                at the end of the list
		}
		if(isset($names['api']))
        	unset($names['api']);
		
		return $names;
	}
	
	public static function getEditableUserCalendarNames() {
		$users = User::model()->findAll( // all users
			array(
				'select'=>'id, username, firstName, lastName',
				'index'=>'id',
			)
		);
		
		$names = array('Anyone'=>'Anyone'); // array mapping username to user's full name for user calendars we can edit
		
		if(Yii::app()->params->isAdmin) {
			foreach($users as $user) {
				$first = $user->firstName;
				$last = $user->lastName;
				$fullname = $first . ' ' . $last;
				$username = $user->username;
				$names[$username] = $fullname;
			}
		} else {
		
			$permissions = X2CalendarPermissions::model()->findAll( // permissions for user's that have set there permissions
				array(
					'select'=>'user_id, other_user_id, edit',
					'condition'=>'other_user_id=:user_id',
					'params'=>array(':user_id'=>Yii::app()->user->id),
					'index'=>'user_id',
				)
			);
			
			$checked = array(); // user's who have there permission set up. Other user's will have default permissions
			foreach($permissions as $permission) { // loop through user's that have set there permissions
				if($permission->edit) { // user gives us permission to view there calendar?
					$user = $users[$permission->user_id];
					$first = $user->firstName;
					$last = $user->lastName;
					$fullname = $first . ' ' . $last;
					$username = $user->username;
					$names[$username] = $fullname;
				}
				$checked[] = $permission->user_id;
			}
			
			// user's who have not set permissions default to not letting everyone edit there calendar
			
			// let current user edit there own calendar
			$user = $users[Yii::app()->user->id];
			$first = $user->firstName;
			$last = $user->lastName;
			$fullname = $first . ' ' . $last;
			$username = $user->username;
			$names[$username] = $fullname;
		
		}
		
		return $names;
	}
	
	
	public static function getUserIdsWithViewPermission($id) {
	
		$users = User::model()->findAll( // all users
			array(
				'select'=>'id, username, firstName, lastName',
				'index'=>'id',
			)
		);
		$permissions = X2CalendarPermissions::model()->findAll( // permissions for user's that have set there permissions
			array(
				'select'=>'user_id, other_user_id, view',
				'condition'=>'user_id=:user_id',
				'params'=>array(':user_id'=>$id),
				'index'=>'other_user_id',
			)
		);
		
		$ids = array();
		$ids[] = 0;
		
		if(count($permissions) > 0) { // user has set permissions
			foreach($users as $user) {
				if(isset($permissions[$user->id]) && $permissions[$user->id]->view)
					$ids[] = $user->id;
			}
		} else {
			foreach($users as $user) {
				$ids[] = $user->id;
			}
		}
		
		return $ids;
	}
	
	public static function getUserIdsWithEditPermission($id) {
		$users = User::model()->findAll( // all users
			array(
				'select'=>'id, username, firstName, lastName',
				'index'=>'id',
			)
		);
		$permissions = X2CalendarPermissions::model()->findAll( // permissions for user's that have set there permissions
			array(
				'select'=>'user_id, other_user_id, edit',
				'condition'=>'user_id=:user_id',
				'params'=>array(':user_id'=>$id),
				'index'=>'other_user_id',
			)
		);
		
		$ids = array();
		$ids[] = 0;
		
		if(count($permissions) > 0) { // user has set permissions
			foreach($users as $user) {
				if(isset($permissions[$user->id]) && $permissions[$user->id]->edit)
					$ids[] = $user->id;
			}
		}
		
		// if user hasn't set permissions, default to not let anyone edit there calendar
		
		return $ids;
	}
}