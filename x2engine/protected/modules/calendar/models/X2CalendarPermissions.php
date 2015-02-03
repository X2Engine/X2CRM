<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * @package application.modules.calendar.models
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
        
        // array mapping username to user's full name for user calendars we can view
        $names = array(); 

        if(Yii::app()->params->isAdmin) { // admin sees all user calendars
            foreach($users as $user) {
                $first = $user->firstName;
                $last = $user->lastName;
                $fullname = Formatter::fullName($first, $last);
                $username = $user->username;
                $names[$username] = $fullname;
            }
        } else {
            // permissions for user's that have set there permissions
            $permissions = X2CalendarPermissions::model()->findAll( 
                array(
                    'select'=>'user_id, other_user_id, view',
                    'condition'=>'other_user_id=:user_id',
                    'params'=>array(':user_id'=>Yii::app()->user->id),
                    'index'=>'user_id',
                )
            );
            
            // user's who have there permission set up. Other user's will have default permissions
            $checked = array(); 

            // loop through user's that have set there permissions
            foreach($permissions as $permission) { 

                // user gives us permission to view there calendar?
                if($permission->view && isset($users[$permission->user_id])) { 
                    $user = $users[$permission->user_id];
                    $first = $user->firstName;
                    $last = $user->lastName;
                    $fullname = Formatter::fullName($first, $last);
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
                    $fullname = Formatter::fullName($first, $last);
                    $username = $user->username;
                    $names[$username] = $fullname;
                }
            }
            
            // let current user view there own calendar
            $user = $users[Yii::app()->user->id];
            $first = $user->firstName;
            $last = $user->lastName;
            $fullname = Formatter::fullName($first, $last);
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
                $fullname = Formatter::fullName($first, $last);
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

            /* x2tempstart */ 
            // safeguard to prevent invalid permissions from being used
            // TODO: write migration script to delete old invalid permissions
            $permissions = array_filter ($permissions, function ($permission) use ($users) {
                return in_array ($permission->user_id, array_keys ($users));
            });
            /* x2tempend */ 
            
            $checked = array(); // user's who have there permission set up. Other user's will have default permissions
            foreach($permissions as $permission) { // loop through user's that have set there permissions
                if($permission->edit) { // user gives us permission to view there calendar?
                    $user = $users[$permission->user_id];
                    $first = $user->firstName;
                    $last = $user->lastName;
                    $fullname = Formatter::fullName($first, $last);
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
            $fullname = Formatter::fullName($first, $last);
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
