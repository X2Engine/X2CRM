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
Yii::import('application.modules.groups.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.util.*');

/**
 *
 * @package application.tests.unit.components
 */
class LeadRoutingBehaviorTest extends X2DbTestCase {

    public $fixtures = array (
        'leadRouting' => array ('LeadRouting', '_1'),
        'users' => array ('User', '_1'),
        'sessions' => array ('Session', '_1'),
        'groups' => array ('Groups', '_1'),
        'groupToUser' => array ('GroupToUser', '_1'),
        'contacts' => array ('Contacts', '_1'),
        'profiles' => array ('Profile', '.LeadRoutingBehaviorTest'),
    );

    public function setUp () {
        // default onlineOnly value
        Yii::app()->settings->onlineOnly = 0;
        //$this->assertSaves (Yii::app()->settings);
        parent::setUp ();
    }

    public function testFreeForAll () {
        Yii::app()->settings->leadDistribution = '';
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('Anyone', $leadRouting->getNextAssignee ()); 
    }

    public function testRoundRobin () {
        Yii::app()->settings->rrId = 0;
        Yii::app()->settings->leadDistribution = 'trueRoundRobin';
        //$this->assertSaves (Yii::app()->settings);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser1', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser4', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser1', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
    }

    public function testRoundRobinOnlineOnly () {
        Yii::app()->settings->rrId = 0;
        Yii::app()->settings->leadDistribution = 'trueRoundRobin';
        Yii::app()->settings->onlineOnly = 1;
        //$this->assertTrue (Yii::app()->settings->save ());
        $testUser1 = $this->profiles ('testProfile1');
        $this->assertSaves ($testUser1);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
    }

    public function testRoundRobinAvailableOnly () {
        Yii::app()->settings->rrId = 0;
        Yii::app()->settings->leadDistribution = 'trueRoundRobin';
        //$this->assertTrue (Yii::app()->settings->save ());
        $testUser1 = $this->profiles ('testProfile1');
        $testUser1->leadRoutingAvailability = 0; 
        $this->assertSaves ($testUser1);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser4', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser4', $leadRouting->getNextAssignee ()); 
    }

    public function testSingleUser () {
        $testUser2 = $this->users ('user2');
        Yii::app()->settings->rrId = $testUser2->id;
        //$this->assertSaves (Yii::app()->settings);
        Yii::app()->settings->leadDistribution = 'singleUser';
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
    }

    public function testSingleUserOnlineOnly () {
        TestingAuxLib::setUpSessions($this->sessions);
        $testUser2 = $this->users ('user2');
        Yii::app()->settings->rrId = $testUser2->id;
        Yii::app()->settings->onlineOnly = 1;
        Yii::app()->settings->leadDistribution = 'singleUser';
        //$this->assertSaves (Yii::app()->settings);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 

        $testUser1 = $this->users ('user1');
        Yii::app()->settings->rrId = $testUser1->id;
        //$this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('Anyone', $leadRouting->getNextAssignee ()); 
    }

    public function testSingleUserAvailableOnly () {
        $testProfile2 = $this->profiles ('testProfile2');
        $testProfile2->leadRoutingAvailability = 0;
        $this->assertSaves ($testProfile2);
        $testUser2 = $this->users ('user2');
        Yii::app()->settings->rrId = $testUser2->id;
        Yii::app()->settings->leadDistribution = 'singleUser';
        //$this->assertSaves (Yii::app()->settings);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('Anyone', $leadRouting->getNextAssignee ()); 
    }

	public function testCustomRoundRobinOnlineOnly () {
        Yii::app()->settings->onlineOnly = 1;
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        //$this->assertSaves (Yii::app()->settings);
        TestingAuxLib::setUpSessions($this->sessions);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(X2_TEST_DEBUG_LEVEL > 1) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'Anyone');

        Yii::app()->settings->onlineOnly = 0;
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(X2_TEST_DEBUG_LEVEL > 1) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser1');
    }

	public function testCustomRoundRobin () {
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        //$this->assertSaves (Yii::app()->settings);
        TestingAuxLib::setUpSessions($this->sessions);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(X2_TEST_DEBUG_LEVEL > 1) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser1');

        $_POST['Contacts'] = array (
            'firstName' => 'contact2',
            'lastName' => 'contact2'
        );
        $username = $leadRouting->customRoundRobin (); 
        if(X2_TEST_DEBUG_LEVEL > 1) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser2');

        $_POST['Contacts'] = array (
            'firstName' => 'contact3',
            'lastName' => 'contact3'
        );
        $username = $leadRouting->customRoundRobin (); 
        if(X2_TEST_DEBUG_LEVEL > 1) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'Anyone');

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
	}

	public function testCustomRoundRobinAvailableOnly () {
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        //$this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();
        $testUser1 = $this->profiles ('testProfile1');
        $testUser1->leadRoutingAvailability = 0; 
        $this->assertSaves ($testUser1);

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
	}

	public function testCustomRoundRobinBetweenGroups () {
        $rule3 = $this->leadRouting ('leadRouting3'); 
        $rule3->groupType = 1;
        $rule3->users = '1, 2';
        $this->assertSaves ($rule3);
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        //$this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('1', $leadRouting->customRoundRobin ()); 
	}

	public function testCustomRoundRobinWithinGroups () {
        $rule3 = $this->leadRouting ('leadRouting3'); 
        $rule3->groupType = 0;
        $rule3->users = '1';
        $this->assertSaves ($rule3);
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        //$this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
	}

}

?>
