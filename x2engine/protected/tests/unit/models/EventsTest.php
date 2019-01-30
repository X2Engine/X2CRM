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

/**
 * Test case for the model class {@link Events}.
 * @package application.tests.unit.models
 * @author Jake Houser <jake@x2engine.com>
 */
class EventsTest extends X2DbTestCase {
    
    public $fixtures=array(
        'event'=> array ('Events', '.GetEvents'),
        'modules'=>'Modules',
        'users'=>'User',
        'groups'=>'Groups',
        'group_to_user'=>'GroupToUser',
    );

    public static function referenceFixtures(){
        return array(
            'profile' => 'Profile',
        );
    }

    public function tearDown () {
        Yii::app()->settings->historyPrivacy = null;
        return parent::tearDown ();
    }

    public function testGetFilteredEventsDataProvider () {
        /*
         * This test is marked as skipped until it can be refactored. The test 
         * assumes that Events::getEvents and Events::getFilteredEventsDataProvider
         * return the same data, which may be true in some circumstances but depends
         * on the parameters passed to getEvents, which are currently incorrect. 
         */
        $this->markTestIncomplete('Test requires refactor');
        TestingAuxLib::loadX2NonWebUser ();
        $testUser = $this->users ('testUser');
        TestingAuxLib::suLogin ($testUser->username);
        Yii::app()->settings->historyPrivacy = null;
        $profile = Profile::model()->findByAttributes(array('username' => $testUser->username));
        $retVal = Events::getFilteredEventsDataProvider ($profile, true, null, false);
        $dataProvider = $retVal['dataProvider'];
        $events = $dataProvider->getData ();
        $expectedEvents = Events::getEvents (0, 0, count ($events), $profile);
        
        // I'm not sure this assert is necessary, at the very least it needs
        // more thorough conditions
        // verify events from getData
        $this->assertEquals (
            Yii::app()->db->createCommand ("
                select id
                from x2_events
                where user='testuser' or visibility or (
                    associationType='User' and associationId=:userId)
                order by timestamp desc, id desc
            ")->queryColumn (array (':userId' => $testUser->id)),
            array_map (
                function ($event) { return $event->id; }, 
                $expectedEvents['events']
            )
        );

        // ensure that getFilteredEventsDataProvider returns same events as getData
        $this->assertEquals (
            array_map (
                function ($event) { return $event->id; }, 
                $expectedEvents['events']
            ),
            array_map (
                function ($event) { return $event->id; }, 
                $events
            )
        );
    }

    public function testGetEventsPublicProfile(){
        /*
         * This test is currently run with some faulty assumptions and is
         * marked to skip until a refactor can happen. Events::getEvents has far
         * more conditions applied to it than the findAllByAttributes it's being
         * compared against and so it is possible for them to not match up
         */
        $this->markTestIncomplete('Test requires refactor');
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('testuser');

        Yii::app()->settings->historyPrivacy = null;
        $lastEventId=0;
        $lastTimestamp=0;
        $myProfile = Profile::model()->findByAttributes(array('username' => 'testuser2'));
        $events=Events::getEvents(
            $lastEventId,$lastTimestamp,null,$myProfile, false);
        $this->assertEquals (
            array_map (
                function ($event) { return $event->id; }, 
                Events::model ()->findAllByAttributes (array (
                    'user' => 'testuser2',
                    'visibility' => 1 
                ))
            ),
            array_map (function ($event) { return $event->id; }, $events['events']));

    }
    
    public function testGetEvents(){
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('admin');

        Yii::app()->settings->historyPrivacy = null;
        $lastEventId = 0;
        $lastTimestamp = 0;
        $events = Events::getEvents ($lastEventId, $lastTimestamp, 4);
        $this->assertEquals (
            Yii::app()->db->createCommand (
                "select id from x2_events order by timestamp desc, id desc limit 4")
                ->queryColumn (),
            array_map(function ($event) { return $event->id; }, $events['events'])
        ); 
    }

    public function testGetAccessCriteria () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('admin');

        // admin privileges private profile
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $this->assertEquals ('TRUE', $accessCriteria->condition);
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, Events::model ()->findAll ()));

        // admin privileges public profile
        $accessCriteria = Events::model ()->getAccessCriteria (
            Profile::model ()->findByAttributes (array (
                'username' => 'testuser'
            )));
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, 
            Events::model ()->findAll ('user="testuser"')));

        // non-admin public profile
        TestingAuxLib::suLogin ('testuser2');
        Yii::app()->settings->historyPrivacy = null;
        $accessCriteria = Events::model ()->getAccessCriteria (
            Profile::model ()->findByAttributes (array (
                'username' => 'testuser'
            )));
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, 
            Events::model ()->findAll ('user="testuser" and visibility')));

        // non-admin private profile
        TestingAuxLib::suLogin ('testuser2');
        Yii::app()->settings->historyPrivacy = null;
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, 
            Events::model ()->findAll ('
                user="testuser2" or visibility or (associationType="User" and associationId=3)
            ')));

        // non-admin private profile, user history
        TestingAuxLib::suLogin ('testuser2');
        Yii::app()->settings->historyPrivacy = 'user';
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, 
            Events::model ()->findAll ('
                user="testuser2" or (associationType="User" and associationId=3)
            ')));

        // non-admin private profile, group history
        // assumes that testuser2 and testuser3 are groupmates
        Yii::app()->settings->historyPrivacy = 'group';
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, 
            Events::model ()->findAll ('
                user="testuser2" or user="testuser3" or
                (associationType="User" and (associationId=2 or associationId=3))
            ')));

    }

    /**
     * Attempts to ensure that isVisibleTo and getAccessCriteria check the same permissions
     */
    public function testPermissionsCheckEquivalence () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('testuser2');
        $allEvents = Events::model ()->findAll ();
        $that = $this; 

        $checkEquivalence = function ($events) use ($allEvents, $that) {
            $ids = array_map (function ($event) { return $event->id; }, $events);
            $that->assertTrue (count ($events) > 1);
            foreach ($events as $event) {
                $that->assertTrue ($event->isVisibleTo (Yii::app()->params->profile->user));
            }
            $found = false;
            foreach ($allEvents as $event) {
                if (!in_array ($event->id, $ids)) {
                    $found = true;
                    $that->assertFalse ($event->isVisibleTo (Yii::app()->params->profile->user));
                }
            }
            $that->assertTrue ($found);
        };

        Yii::app()->settings->historyPrivacy = null;
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $events = Events::model ()->findAll ($accessCriteria);
        $checkEquivalence ($events);

        Yii::app()->settings->historyPrivacy = 'group';
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $events = Events::model ()->findAll ($accessCriteria);
        $checkEquivalence ($events);

        Yii::app()->settings->historyPrivacy = 'user';
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $events = Events::model ()->findAll ($accessCriteria);
        $checkEquivalence ($events);

    }

    /**
     * Ensure that a model name can be properly parsed and resolved
     */
    public function testParseModelName() {
        $models = array(
            'Product' => 'product',
            'Opportunity' => 'opportunity',
            'Campaign' => 'campaign',
            
            'AnonContact' => 'anonymous contact',
            
            'Contacts' => 'contact',
            'Accounts' => 'account',
            'Marketing' => 'marketing',
            'Quote' => 'quote',
            'Non Existant Module' => null,
        );
        // First ensure parseModelName works as expected for default modules
        foreach ($models as $model => $expected) {
            $this->assertEquals ($expected, Events::parseModelName ($model));
        }

        // Now ensure renamed modules can be properly resolved
        foreach ($models as $model => $expected) {
            
            // Skip AnonContact: no dedicate module to test
            if ($model === 'AnonContact') continue;
            
            // Correct pluralization inconsistancies
            if ($model === 'Opportunity') $model = 'Opportunities';
            if ($model === 'Product' || $model === 'Quote') $model .= 's';
            $dummyTitle = $model . time();
            $module = Modules::model()->findByAttributes (array(
                'name' => strtolower($model)
            ));
            if ($module && $module->retitle ($dummyTitle)) {
                $this->assertEquals (strtolower($dummyTitle), Events::parseModelName ($model));
            }
        }
    }
    
    /**
     * TODO: Remove hardcoded references to events in the fixture.
     */
    public function testCheckPermissions(){
        TestingAuxLib::loadX2NonWebUser ();
        $event1 = $this->event(0);
        // Admin can do anything
        TestingAuxLib::suLogin ('admin');
        $this->assertTrue($event1->checkPermissions('view', true));
        $this->assertTrue($event1->checkPermissions('edit', true));
        $this->assertTrue($event1->checkPermissions('delete', true));
        // Private and no shared group means testuser can't do anything
        TestingAuxLib::suLogin ('testuser');
        $this->assertFalse($event1->checkPermissions('view', true));
        $this->assertFalse($event1->checkPermissions('edit', true));
        $this->assertFalse($event1->checkPermissions('delete', true));
        // Associated with testuser2, so they can view and delete but not edit
        TestingAuxLib::suLogin ('testuser2');
        $this->assertTrue($event1->checkPermissions('view', true));
        $this->assertFalse($event1->checkPermissions('edit', true));
        $this->assertTrue($event1->checkPermissions('delete', true));
        // Created by testuser3, so they can do anything
        TestingAuxLib::suLogin ('testuser3');
        $this->assertTrue($event1->checkPermissions('view', true));
        $this->assertTrue($event1->checkPermissions('edit', true));
        $this->assertTrue($event1->checkPermissions('delete', true));
        
        $event2 = $this->event(6);
        // Admin can do anything
        TestingAuxLib::suLogin ('admin');
        $this->assertTrue($event2->checkPermissions('view', true));
        $this->assertTrue($event2->checkPermissions('edit', true));
        $this->assertTrue($event2->checkPermissions('delete', true));
        // Public posts are visible but not editable or deletable by regular users
        TestingAuxLib::suLogin ('testuser');
        $this->assertTrue($event2->checkPermissions('view', true));
        $this->assertFalse($event2->checkPermissions('edit', true));
        $this->assertFalse($event2->checkPermissions('delete', true));
        // Public posts are visible but not editable or deletable by regular users
        TestingAuxLib::suLogin ('testuser2');
        $this->assertTrue($event2->checkPermissions('view', true));
        $this->assertFalse($event2->checkPermissions('edit', true));
        $this->assertFalse($event2->checkPermissions('delete', true));
        
        $event3 = $this->event(7);
        // Admin can do anything
        TestingAuxLib::suLogin ('admin');
        $this->assertTrue($event3->checkPermissions('view', true));
        $this->assertTrue($event3->checkPermissions('edit', true));
        $this->assertTrue($event3->checkPermissions('delete', true));
        // Non-social post is visible to user it's assigned to but they can't edit or delete
        TestingAuxLib::suLogin ('testuser');
        $this->assertTrue($event3->checkPermissions('view', true));
        $this->assertFalse($event3->checkPermissions('edit', true));
        $this->assertFalse($event3->checkPermissions('delete', true));
        // Private, so testuser3 can't do anything
        TestingAuxLib::suLogin ('testuser3');
        $this->assertFalse($event3->checkPermissions('view', true));
        $this->assertFalse($event3->checkPermissions('edit', true));
        $this->assertFalse($event3->checkPermissions('delete', true));
    }
}

?>
