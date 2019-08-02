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




Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.modules.accounts.models.*');
Yii::import ('application.components.*');
Yii::import ('application.components.x2flow.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

class X2FlowSplitterTest extends X2FlowTestBase {

    public static function referenceFixtures () {
        return array (
            'x2flows' => array ('X2Flow', '.X2FlowSplitterTest'),
            'contacts' => 'Contacts',
            'actions' => 'Actions',
            'actionText' => 'ActionText',
        );
    }

    private function getCronBehavior () {
        return new CronBehavior;
    }
    
    public function setUp(){
        TestingAuxLib::loadControllerMock ();
        return parent::setUp();
    }
    
    public function tearDown(){
        TestingAuxLib::restoreController();
        parent::tearDown();
    }

    private function clearEphemeral () {
        Yii::app()->db->createCommand ("
            delete from x2_actions where type='note';
            delete from x2_cron_events;
        ")->execute ();
    }

    private function getComments () {
        return Yii::app()->db->createCommand ("
            select text from x2_action_text 
            join x2_actions on actionId=x2_actions.id
            where x2_actions.type='note'
            order by x2_actions.id asc;
        ")->queryColumn ();
    }

    /**
     * Ensure traversal order is as expected (depth-first and right to left)
     */
    public function testFlowWithSplitters () {
        $flow = $this->x2flows ('0');
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $this->clearEphemeral ();
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $comments = $this->getComments ();
        $this->assertEquals (ArrayUtil::sort ($comments), $comments);
    }

    /**
     * Testing combination of splitters and flow condition (since primary use case involves
     * combining the two)
     */
    public function testFlowWithSplittersAndCondition () {
        $flow = $this->x2flows ('2');
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $this->clearEphemeral ();
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $comments = $this->getComments ();
        $this->assertEquals (ArrayUtil::sort ($comments), $comments);
    }

    /**
     * Ensure traversal order is as expected when wait actions are added
     */
    public function testFlowWithSplittersAndWaits () {
        $flow = $this->x2flows ('1');
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $this->clearEphemeral ();
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $comments = $this->getComments ();
        $this->assertEquals (ArrayUtil::sort ($comments), $comments);
        sleep (2);
        $cronBehavior = $this->getCronBehavior ();
        $cronBehavior->runCron ();
        $newComments = $this->getComments ();
        $this->assertNotEquals ($newComments, $comments);
        $this->assertEquals (ArrayUtil::sort ($newComments), $newComments);
    }



}

?>
