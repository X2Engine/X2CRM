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
Yii::import ('application.modules.models.*');

/**
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowWaitActionTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowWaitActionTest'),
        'cronEvents' => array ('CronEvent', '.X2FlowWaitActionTest'),
        'contacts' => 'Contacts',
    );

    public function getCronBehavior () {
        return new CronBehavior;
    }
    
    public function setUp(){
        TestingAuxLib::loadControllerMock();
        return parent::setUp();
    }
    
    public function tearDown(){
        TestingAuxLib::restoreController();
        parent::tearDown();
    }

    private function assertWaitActionPausesAndResumes ($flowName) {
        Yii::app()->db->createCommand ("
            delete from x2_cron_events;
            delete from x2_notifications;
        ")->execute ();
        $this->clearLogs ();
        $flow = $this->x2flow ($flowName);
        $flowData = $flow->getFlow ();
        $contacts = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contacts,
            'modelClass' => 'Contacts',
        );

        $createdCronEvents = CronEvent::model ()->findAllByAttributes (array (
            'associationType' => 'Contacts',
            'associationId' => $contacts->id
        ));
        $this->assertEquals (0, count ($createdCronEvents));

        X2Flow::executeFlow ($flow, $params);
        $trace = X2FlowTestingAuxLib::getTraceByFlowId ($flow->id);

        // assert flow executed without errors
        $this->assertTrue ($this->checkTrace ($trace));

        $createdCronEvents = CronEvent::model ()->findAllByAttributes (array (
            'associationType' => 'Contacts',
            'associationId' => $contacts->id
        ));

        $this->assertEquals (1, count ($createdCronEvents));
        $event = $createdCronEvents[0];

        $cronData = CJSON::decode ($event->data);
        $waitAction = X2FlowTestingAuxLib::findFlowItem ($flowData, 'X2FlowWait');
        $this->assertEquals ($waitAction['id'], $cronData['flowPath']);

        sleep (2); // wait for wait action duration to elapse
        // trigger flow unpause and ensure that notification was created by flow
        $cronBehavior = $this->getCronBehavior ();
        $cronBehavior->runCron ();

        // flow action after wait action should create a notification
        $notifs = Notification::model ()->findAll ();

        $this->assertEquals (1, count ($notifs));
        $this->assertEquals ('test', $notifs[0]->text);

        // trigger log should get updated
        $newTrace = X2FlowTestingAuxLib::getTraceByFlowId ($flow->id);
        X2_TEST_DEBUG_LEVEL > 1 && print 'newTrace = ';
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($newTrace);
        $this->assertNotEquals ($trace, $newTrace);
    }

    /**
     * Ensure that flow wait action creates cron event and tha flow resumes correctly from wait
     * action when cron action is triggered
     */
    public function testCronEventCreation () {
        $this->assertWaitActionPausesAndResumes ('flow1');
        //$this->assertWaitActionPausesAndResumes ('flow2');
    }

    /**
     * Ensure that flows can resume from wait actions using legacy cron events (pre 5.2) with stored
     * flow paths
     */
    public function testLegacyWaitActionCronEvent () {
        Yii::app()->db->createCommand ("
            delete from x2_notifications;
        ")->execute ();
        $this->clearLogs ();

        $flow = $this->x2flow ('legacyFlow');
        // add log from older version
        $triggerLogForLegacyFlow = array (
            'id' => '76',
            'flowId' => '5',
            'triggeredAt' => '1437181207',
            'triggerLog' => '[{"triggerName":"Record Viewed","modelLink":"View record: <a class=\\"contact-name\\" href=\\"http:\\/\\/localhost\\/\\/index.php\\/contacts\\/id\\/12345\\"><span>Amanda Bailey<\\/span><\\/a>"},[true,[["X2FlowSwitch",true,[["X2FlowRecordListAdd",[false,"Required flow item input missing"]],["X2FlowSwitch",true,[["X2FlowWait",[true,"Waiting for 1 second(s)"]]]]]]]]]',
        );
        $log = new TriggerLog;
        $log->setAttributes ($triggerLogForLegacyFlow, false);
        $this->assertSaves ($log);
        $trace = X2FlowTestingAuxLib::getTraceByFlowId ($flow->id);
        X2_TEST_DEBUG_LEVEL > 1 && print 'trace = ';
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);

        // ensure that flow is able to resume using legacy cron event
        $cronBehavior = $this->getCronBehavior ();
        $cronBehavior->runCron ();

        // flow action after wait action should create a notification
        $notifs = Notification::model ()->findAll ();
        $this->assertEquals (1, count ($notifs));
        $this->assertEquals ('test', $notifs[0]->text);

        // trigger log should get updated
        $newTrace = X2FlowTestingAuxLib::getTraceByFlowId ($flow->id);
        X2_TEST_DEBUG_LEVEL > 1 && print 'newTrace = ';
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($newTrace);
        $this->assertNotEquals ($trace, $newTrace);
    }

}

?>
