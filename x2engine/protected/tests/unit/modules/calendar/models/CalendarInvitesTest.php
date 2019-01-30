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




class CalendarInvitesTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'calendar' => 'X2Calendar',
            'credentials' => 'Credentials',
            'credentialsDefault' => ':x2_credentials_default',
            'users' => 'User',
        );
    }
    
    public $fixtures = array(
        'actions' => array('Actions', '.CalendarInvites'),
        'invites' => 'CalendarInvites'
    );

    public function testCreateInvites() {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('admin');    
        $action = new Actions();
        $time = time();
        $action->dueDate = $time;
        $action->createDate = $time;
        $action->lastUpdated = $time;
        $action->assignedTo = Yii::app()->user->name;        
        $action->calendarId = $this->calendar(Yii::app()->user->name)->id;
        $action->type = 'event';
        $action->attachBehavior('CalendarInviteBehavior', array(
            'class' => 'CalendarInviteBehavior',
            'emailAddresses' => array('test@example.com'),
        ));
        
        $this->assertEquals(0, count($action->invites));
        
        $action->save();
        
        $this->assertEquals(1, count($action->invites));
        
        $invite = $action->invites[0];
        $this->assertNull($invite->status);
        $this->assertEquals('test@example.com',$invite->email);
        $this->assertNotNull($invite->inviteKey);
        
        TestingAuxLib::restoreX2WebUser();
    }
    
    public function testUpdateInvites(){
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('testuser');  
        
        $action = $this->actions('action1');
        $invite = $action->invites[0];
        
        $this->assertEquals('Yes',$invite->status);
        
        $action->dueDate = $action->dueDate + (60 * 60);
        $action->save();
        
        $invite->refresh();
        $this->assertNull($invite->status);
        
        TestingAuxLib::restoreX2WebUser();
    }

}
