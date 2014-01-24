<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
Yii::import('application.modules.groups.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.util.*');

/**
 *
 * @package X2CRM.tests.unit.components
 */
class LeadRoutingBehaviorTest extends CDbTestCase {

    const VERBOSE = 0;

    public $fixtures = array (
        'leadRouting' => array ('LeadRouting', '_1'),
        'users' => array ('User', '_1'),
        'sessions' => array ('Session', '_1'),
        'groups' => array ('Groups', '_1'),
        'groupToUser' => array ('GroupToUser', '_1'),
        'contacts' => array ('Contacts', '_1'),
    );

    public static function setUpBeforeClass () {
        X2DbTestCase::setUpAppEnvironment ();
        Yii::app()->params->admin->leadDistribution = 'customRoundRobin';
        Yii::app()->db->createCommand ()
            ->delete ('x2_users', 'true');
        parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        parent::tearDownAfterClass ();
    }

    protected function setUp () {
        Yii::app()->params->admin->onlineOnly = false;
        parent::setUp ();
    }

	public function testCustomRoundRobinOnlineOnly () {
        Yii::app()->params->admin->onlineOnly = true;
        TestingAuxLib::setUpSessions($this->sessions);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(self::VERBOSE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'Anyone');

        Yii::app()->params->admin->onlineOnly = false;
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(self::VERBOSE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser1');
    }

	public function testCustomRoundRobin () {
        TestingAuxLib::setUpSessions($this->sessions);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(self::VERBOSE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser1');

        $_POST['Contacts'] = array (
            'firstName' => 'contact2',
            'lastName' => 'contact2'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(self::VERBOSE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser2');

        $_POST['Contacts'] = array (
            'firstName' => 'contact3',
            'lastName' => 'contact3'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(self::VERBOSE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'Anyone');
	}

}

?>
