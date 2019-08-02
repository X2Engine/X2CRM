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




Yii::import ('application.modules.users.models.*');

class X2CalendarPermissionsTest extends X2DbTestCase {

    public $fixtures = array (
        'calendar' => 'X2Calendar',
        'calendarPermissions' => 'X2CalendarPermissions',
        'users' => 'User',
    );

    /**
     * Ensure that list of viewable calendars correctly reflects calendar permissions records
     */
    public function testGetViewableUserCalendarNames () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('admin');        
        $viewable = array_keys (X2CalendarPermissions::getViewableUserCalendarNames ());
        //Admin can see all calendars
        $this->assertEquals (ArrayUtil::sort(Yii::app()->db->createCommand ("
                SELECT id
                FROM x2_calendars
            ")->queryColumn ()), 
            ArrayUtil::sort ($viewable));

        $user = $this->users ('testUser');
        TestingAuxLib::suLogin ('testuser');      
        //testUser can see all but one calendar
        $viewable = array_keys (X2CalendarPermissions::getViewableUserCalendarNames ());
        $grantedUsers = ArrayUtil::sort(array_unique(array_merge(array(Yii::app()->user->id), Yii::app()->db->createCommand ("
                SELECT calendarId
                FROM x2_calendar_permissions
                WHERE userId=:userId AND view = 1
            ")->queryColumn (array (':userId' => $user->id)))));
        $this->assertEquals (ArrayUtil::sort ($grantedUsers), 
            ArrayUtil::sort ($viewable));
        
        $user = $this->users ('testUser2');
        TestingAuxLib::suLogin ('testuser2'); 
        //testUser2 can see no calendars
        $viewable = array_keys (X2CalendarPermissions::getViewableUserCalendarNames ());
        $grantedUsers = ArrayUtil::sort(array_unique(array_merge(array(Yii::app()->user->id), Yii::app()->db->createCommand ("
                SELECT calendarId
                FROM x2_calendar_permissions
                WHERE userId=:userId AND view = 1
            ")->queryColumn (array (':userId' => $user->username)))));
        $this->assertEquals (ArrayUtil::sort ($grantedUsers), 
            ArrayUtil::sort ($viewable));
        TestingAuxLib::restoreX2WebUser ();
    }
    
    public function testGetEditableUserCalendarNames () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('admin');        
        $editable = array_keys (X2CalendarPermissions::getEditableUserCalendarNames ());
        //Admin can edit all calendars
        $this->assertEquals (ArrayUtil::sort(Yii::app()->db->createCommand ("
                SELECT id
                FROM x2_calendars
            ")->queryColumn ()), 
            ArrayUtil::sort ($editable));

        $user = $this->users ('testUser');
        TestingAuxLib::suLogin ('testuser');      
        //testUser can edit their own + 1 calendar
        $editable = array_keys (X2CalendarPermissions::getEditableUserCalendarNames ());
        $grantedUsers = ArrayUtil::sort(array_unique(array_merge(array(Yii::app()->user->id),Yii::app()->db->createCommand ("
                SELECT calendarId
                FROM x2_calendar_permissions
                WHERE userId=:userId AND edit = 1
            ")->queryColumn (array (':userId' => $user->id)))));
        $this->assertEquals (ArrayUtil::sort ($grantedUsers), 
            ArrayUtil::sort ($editable));
        
        $user = $this->users ('testUser2');
        TestingAuxLib::suLogin ('testuser2'); 
        //testUser2 can edit only their own
        $editable = array_keys (X2CalendarPermissions::getEditableUserCalendarNames ());
        $grantedUsers = ArrayUtil::sort(array_unique(array_merge(array(Yii::app()->user->id), Yii::app()->db->createCommand ("
                SELECT calendarId
                FROM x2_calendar_permissions
                WHERE userId=:userId AND edit = 1
            ")->queryColumn (array (':userId' => $user->username)))));
        $this->assertEquals (ArrayUtil::sort ($grantedUsers), 
            ArrayUtil::sort ($editable));
        TestingAuxLib::restoreX2WebUser ();
    }

}

?>
