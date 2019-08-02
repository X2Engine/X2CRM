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




Yii::import ('application.modules.accounts.models.*');

/**
 * Tests X2FlowRecordCreateAction action
 *
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordCreateActionTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowRecordCreateAction'),
        'accounts' => array ('Accounts', '_1'),
        'campaigns' => 'Campaign',
        'contacts' => 'Contacts',
        'opportunities' => 'Opportunity',
        'x2leads' => 'X2Leads',
    );

    /**
     * These flows ensure that an action can be created for different record types,
     * including those with singular/plural mismatches
     */
    public function testCreateActionForRecord () {
        TestingAuxLib::suLogin ('admin');

        // Test create action for Contacts
        $contact = $this->contacts('testUser');
        $this->executeCreateActionForRecordFlow('flow4', $contact);
        $this->assertRecordsActionCreated($contact, 'contacts');

        // Test create action for Opportunities
        $opportunity = $this->opportunities('ddp');
        $this->executeCreateActionForRecordFlow('flow5', $opportunity);
        $this->assertRecordsActionCreated($opportunity, 'opportunities');

        // Test create action for X2Leads
        $lead = $this->x2leads('0');
        $this->executeCreateActionForRecordFlow('flow6', $lead);
        $this->assertRecordsActionCreated($lead, 'x2leads');

        // Test create action for Campaigns
        $campaign = $this->campaigns('testUser');
        $this->executeCreateActionForRecordFlow('flow7', $campaign);
        $this->assertRecordsActionCreated($campaign, 'marketing');
    }

    private function executeCreateActionForRecordFlow($flowName, X2Model $model) {
        $flow = $this->x2flow ($flowName);
        $params = array (
            'model' => $model,
            'modelClass' => get_class($model),
        );
        $retVal = $this->executeFlow ($flow, $params);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal['trace']);

        // assert flow executed without errors and action was created
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
    }

    private function assertRecordsActionCreated(X2Model $model, $associationType) {
        $createdAction = Actions::model ()->findByAttributes (array (
            'associationType' => $associationType,
            'associationId' => $model->id,
            'subject' => 'take action',
        ));

        // assert that a related action was created
        $this->assertTrue ($createdAction !== null);
    }
}

?>
