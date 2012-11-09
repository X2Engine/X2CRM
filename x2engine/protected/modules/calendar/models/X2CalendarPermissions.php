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
			)
		);
		
		$names = array(); // array mapping username to user's full name for user calendars we can view

		
		if(Yii::app()->user->name == 'admin') { // admin sees all user calendars
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
				if($permission->view) { // user gives us permission to view there calendar?
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
		$adminName = ucwords($names['admin']); // Round-about way
		unset($names['admin']);       //          of putting admin
		$names['admin'] = $adminName; //                at the end of the list
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
		
		if(Yii::app()->user->name == 'admin') {
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