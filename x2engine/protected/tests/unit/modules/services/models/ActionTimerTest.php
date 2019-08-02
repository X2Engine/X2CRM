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






Yii::import('application.modules.services.models.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.contacts.models.*');

/**
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ActionTimerTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'users'=>'User',
        );
    }

    public $fixtures = array(
        'timers' => 'ActionTimer',
        'actions' => 'Actions',
        'contacts'=>'Contacts'
    );

    /**
     * Instantiation test
     */
    public function testSetup() {
        $user = $this->users('testUser');
        Yii::app()->suModel = $user;
        $timer = ActionTimer::setup(true);
        $time = time();
        // No existing timer, default values:
        $this->assertEquals($user->id,$timer->userId);
        $this->assertEquals(null,$timer->associationId);
        $this->assertEquals(null,$timer->type);
        $this->assertTrue(abs($time-$timer->timestamp) <= 1);

        $timer->data = 'match this text';
        $timer->save();
        // Now there is such a timer. Assert they're the same.
        $anotherTimer = ActionTimer::setup();
        foreach($anotherTimer->attributes as $name=>$value) {
            $this->assertEquals($timer->$name,$value);
        }
        $timer = ActionTimer::setup(true, array('associationId' => 1, 'userId' => $this->users('testUser')->id));
        $this->assertEquals(1, $timer->associationId);
        $this->assertEquals($this->users('testUser')->id, $timer->userId);
        
    }

    /**
     * Test for ending the timer and creating an associated action record
     */
    public function testEnd() {
        $timer = $this->timers('testcontact_timelog');
        $timerAttr = $timer->attributes;

        $actionOut = $timer->stop();
        // Assert it's a timestamp... Insertions should never take more than a second
        $this->assertTrue(abs($timer->endtime - time()) <= 1,'ActionTimer.stop() did not return, or took WAAAAY too long to run.');
    }
    
    public function testTimeSpent() {
        $user = $this->users('testUser');
        Yii::app()->suModel = $user;
        $timeSpent = ActionTimer::getTimeSpent(67890);
        $this->assertEquals(2, $timeSpent);
    }
    
}

?>
