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




Yii::import('application.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.groups.models.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.X2Settings.*');
Yii::import('application.components.sortableWidget.profileWidgets.*');
Yii::import('application.components.sortableWidget.recordViewWidgets.*');

/**
 * Test for the Actions class
 * @package application.tests.unit.modules.actions.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ActionsTest extends X2DbTestCase {

    public $fixtures = array(
        'actions'=>array ('Actions', '.ActionsTest'),
        'users'=> 'User',
        'profiles'=> 'Profile',
        'groupToUser'=>array ('GroupToUser', '.ActionsTest'),
        'groups'=>array ('Groups', '.ActionsTest'),
    );

    /**
     * Test special validation that avoids empty association when the type is
     * something meant to be associated, i.e. a logged call, note, etc.
     */
    public function testValidate() {
        $action = new Actions();
        $action->type = 'call';
        $action->actionDescription = 'Contacted. Will call back later';
        $this->assertFalse($action->validate());
        $this->assertTrue($action->hasErrors('associationId'));
        $this->assertTrue($action->hasErrors('associationType'));
        // Do the same thing but with "None" association type. Validation should fail.
        $action = new Actions();
        $action->type = 'call';
        $action->associationType = 'None';
        $this->assertFalse($action->validate());
        $this->assertTrue($action->hasErrors('associationId'));
        $this->assertTrue($action->hasErrors('associationType'));
    }

    public function testIsAssignedTo () {
        $action = $this->actions('action1');

        // test assignedTo field consisting of single username
        $this->assertTrue ($action->isAssignedTo ('testuser'));
        $this->assertFalse ($action->isAssignedTo ('testuser2'));

        $action = $this->actions('action2');

        // test assignedTo field consisting of a group id
        $this->assertTrue ($action->isAssignedTo ('testuser'));
        $this->assertFalse ($action->isAssignedTo ('testuser2'));

        $action = $this->actions('action3');

        // test assignedTo field consisting of username and group id
        $this->assertTrue ($action->isAssignedTo ('testuser'));
        $this->assertTrue ($action->isAssignedTo ('testuser2'));
        $this->assertFalse ($action->isAssignedTo ('testuser3'));

        $action = $this->actions('action4');

        // test assignedTo field consisting of 'Anyone'
        $this->assertTrue ($action->isAssignedTo ('testuser4'));
        $this->assertFalse ($action->isAssignedTo ('testuser4', true));

        // test assignedTo field consisting of '' (i.e. no one)
        $this->assertTrue ($action->isAssignedTo ('testuser4'));
        $this->assertFalse ($action->isAssignedTo ('testuser4', true));
    }

    public function testGetProfilesOfAssignees () {
        // action assignedTo field consists of username and group id
        $action = $this->actions('action3');
        $profiles = $action->getProfilesOfAssignees ();

        // this should return profile for username and all profiles in group, without duplicates
        $profileUsernames = array_map (function ($a) { return $a->username; }, $profiles);

        X2_TEST_DEBUG_LEVEL > 1 && print ('count ($profiles) = ');
        X2_TEST_DEBUG_LEVEL > 1 && print (count ($profiles)."\n");

        X2_TEST_DEBUG_LEVEL > 1 && print ('$profileUsernames  = ');
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($profileUsernames);

        $this->assertTrue (count ($profiles) === 2);
        $this->assertTrue (in_array ('testuser', $profileUsernames));
        $this->assertTrue (in_array ('testuser2', $profileUsernames));

        /* 
        action assignedTo field consists of username and group id. Here the username is included
        twice: once explicitly in the assignedTo field and a second time, implicitly, by its 
        membership to the group.
        */
        $action = $this->actions('action6');
        $profiles = $action->getProfilesOfAssignees ();

        // this should return profile for username and all profiles in group, without duplicates
        $profileUsernames = array_map (function ($a) { return $a->username; }, $profiles);

        X2_TEST_DEBUG_LEVEL > 1 && print ('count ($profiles) = ');
        X2_TEST_DEBUG_LEVEL > 1 && print (count ($profiles)."\n");

        X2_TEST_DEBUG_LEVEL > 1 && print ('$profileUsernames  = ');
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($profileUsernames);

        $this->assertTrue (count ($profiles) === 2);
        $this->assertTrue (in_array ('testuser', $profileUsernames));
        $this->assertTrue (in_array ('admin', $profileUsernames));
        
    }

    public function testGetAssignees () {
        // action assignedTo field consists of username and group id
        $action = $this->actions('action3');
        $assignees = $action->getAssignees (true);

        X2_TEST_DEBUG_LEVEL > 1 && print ('count ($assignees) = ');
        X2_TEST_DEBUG_LEVEL > 1 && print (count ($assignees)."\n");

        $this->assertTrue (count ($assignees) === 2);
        $this->assertTrue (in_array ('testuser', $assignees));
        $this->assertTrue (in_array ('testuser2', $assignees));

        /* 
        action assignedTo field consists of username and group id. Here the username is included
        twice: once explicitly in the assignedTo field and a second time, implicitly, by its 
        membership to the group.
        */
        $action = $this->actions('action6');

        /* 
        here assignees usernames are retrieved, if a group id is in the assignedTo string,  
        usernames of all users in that group are also retrieved. duplicate usernames should
        get removed.
        */
        $assignees = $action->getAssignees (true);

        X2_TEST_DEBUG_LEVEL > 1 && print ('count ($assignees) = ');
        X2_TEST_DEBUG_LEVEL > 1 && print (count ($assignees)."\n");

        $this->assertTrue (count ($assignees) === 2);
        $this->assertTrue (in_array ('testuser', $assignees));
        $this->assertTrue (in_array ('admin', $assignees));
        
    }

    public function testCreateNotification () {
        // assigned to testuser and group 1
        $action = $this->actions('action6');

        $notifs = $action->createNotifications ('assigned');
        X2_TEST_DEBUG_LEVEL > 1 && print (count ($notifs));
        $this->assertTrue (count ($notifs) === 2);
        $notifAssignees = array_map (function ($a) { return $a->user; }, $notifs);
        $this->assertTrue (in_array ('admin', $notifAssignees));
        $this->assertTrue (in_array ('testuser', $notifAssignees));

        $notifs = $action->createNotifications ('me');
        $this->assertTrue (count ($notifs) === 1);
        $notifAssignees = array_map (function ($a) { return $a->user; }, $notifs);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($notifAssignees);
        $this->assertTrue (in_array ('Guest', $notifAssignees));

        $notifs = $action->createNotifications ('both');
        $this->assertTrue (count ($notifs) === 3);
        $notifAssignees = array_map (function ($a) { return $a->user; }, $notifs);
        $this->assertTrue (in_array ('admin', $notifAssignees));
        $this->assertTrue (in_array ('testuser', $notifAssignees));
        $this->assertTrue (in_array ('Guest', $notifAssignees));
    }

    public function testChangeCompleteState () {
        TestingAuxLib::suLogin ('admin');
        X2_TEST_DEBUG_LEVEL > 1 && print (Yii::app()->user->name ."\n");
        X2_TEST_DEBUG_LEVEL > 1 && print ((int) Yii::app()->params->isAdmin);
        X2_TEST_DEBUG_LEVEL > 1 && print ("\n");
        $action = $this->actions('action6');
        $completedNum = Actions::changeCompleteState ('complete', array ($action->id));
        $this->assertEquals (1, $completedNum);
        $action = Actions::model()->findByPk ($action->id);
        X2_TEST_DEBUG_LEVEL > 1 && print ($action->complete."\n");
        $this->assertTrue ($action->complete === 'Yes');
        Actions::changeCompleteState ('uncomplete', array ($action->id));
        $action = Actions::model()->findByPk ($action->id);
        $this->assertTrue ($action->complete === 'No');
    }

    public function testDeleteOldNotifications () {
        TestingAuxLib::suLogin ('admin');
        // assigned to testuser
        $action = $this->actions('action1');
        $reminders = $action->getReminders (true);
        foreach ($reminders as $reminder) $this->assertTrue ($reminder->delete ());
        $deleteOldNotifications = TestingAuxLib::setPublic ($action, 'deleteOldNotifications');
        $this->assertEquals (0, count ($action->getReminders (true)));
        $action->createNotifications ('assigned', 1234, 'action_reminder');
        $this->assertGreaterThan (0, count ($action->getReminders (true)));
        $deleteOldNotifications ('me');
        $this->assertGreaterThan (0, count ($action->getReminders (true)));
        $deleteOldNotifications ('assigned');
        $this->assertEquals (0, count ($action->getReminders (true)));
    }

    public function testUpdateWithNotifications () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('admin');
        // assigned to testuser
        $action = $this->actions('action1');
        $reminders = $action->getReminders (true);
        foreach ($reminders as $reminder) $this->assertTrue ($reminder->delete ());
        $this->assertEquals (0, count ($action->getReminders (true)));

        // ensure that we can create a reminder
        $action->reminder = true;
        $action->notificationUsers = 'assigned';
        $action->notificationTime = 1234;
        // adjust dueDate so the reminder time is in the future (dueDate - 60 * notificationTime)
        $action->dueDate = time() + 60 * 60 * 24;
        $this->assertSaves ($action);
        $this->assertEquals (1, count ($action->getReminders (true)));
        $reminders = $action->getReminders (true);
        $assignees = array_map (function ($reminder) {
            return $reminder->user;
        }, $reminders);
        $this->assertEquals (array ('testuser'), $assignees);

        // now ensure that we can create another reminder and that the old reminder was deleted
        TestingAuxLib::suLogin ('testuser');
        $action->reminder = true;
        $action->notificationUsers = 'assigned';
        $action->notificationTime = 1234;
        // adjust dueDate so the reminder time is in the future (dueDate - 60 * notificationTime)
        $action->dueDate = time() + 60 * 60 * 24;
        $this->assertSaves ($action);
        $this->assertEquals (1, count ($action->getReminders (true)));
        $reminders = $action->getReminders (true);
        $assignees = array_map (function ($reminder) {
            return $reminder->user;
        }, $reminders);
        $this->assertEquals (array ('testuser'), $assignees);
    }
}

?>
