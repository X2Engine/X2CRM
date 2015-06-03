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
    );

    public static function referenceFixtures(){
        return array(
            'profile' => 'Profile',
        );
    }

    public function testGetFilteredEventsDataProvider () {
        TestingAuxLib::loadX2NonWebUser ();
        TestingAuxLib::suLogin ('testuser');
        Yii::app()->settings->historyPrivacy = null;
        $profile = Profile::model()->findByAttributes(array('username' => 'testuser'));
        $retVal = Events::getFilteredEventsDataProvider ($profile, true, null, false);
        $dataProvider = $retVal['dataProvider'];
        $events = $dataProvider->getData ();
        $expectedEvents = Events::getEvents (0, 0, count ($events), $profile);

        // verify events from getData
        $this->assertEquals (
            Yii::app()->db->createCommand ("
                select id
                from x2_events
                where user='testuser' or visibility
                order by timestamp desc, id desc
            ")->queryColumn (),
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
        TestingAuxLib::restoreX2WebUser ();
    }

    public function testGetEventsPublicProfile(){
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

        TestingAuxLib::restoreX2WebUser ();
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
        TestingAuxLib::restoreX2WebUser ();
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
            Events::model ()->findAll ('user="testuser2" or visibility')));

        // non-admin private profile, user history
        TestingAuxLib::suLogin ('testuser2');
        Yii::app()->settings->historyPrivacy = 'user';
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, 
            Events::model ()->findAll ('user="testuser2"')));

        // non-admin private profile, group history
        // assumes that testuser2 and testuser3 are groupmates
        Yii::app()->settings->historyPrivacy = 'group';
        $accessCriteria = Events::model ()->getAccessCriteria ();
        $this->assertEquals (
            array_map (function ($event) { return $event->id; }, 
                Events::model ()->findAll ($accessCriteria)),
            array_map (function ($event) { return $event->id; }, 
            Events::model ()->findAll ('user="testuser2" or user="testuser3"')));

        Yii::app()->settings->historyPrivacy = null;
        TestingAuxLib::restoreX2WebUser ();
    }

    /**
     * Ensure that a model name can be properly parsed and resolved
     */
    public function testParseModelName() {
        $models = array(
            'Product' => 'product',
            'Opportunity' => 'opportunity',
            'Campaign' => 'campaign',
            
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
}

?>
