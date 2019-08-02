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
 * Tests X2FlowRecordCreate action
 *
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordCreateTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowRecordCreateAction'),
        'accounts' => array ('Accounts', '_1'),
        'contacts' => 'Contacts',
        'x2leads' => 'X2Leads',
    );

    /**
     * The flow creates a contact with a company field pointing to the account that
     * triggered the flow.
     */
    public function testCreateContactWithLinkTypeFieldSet () {
        TestingAuxLib::suLogin ('admin');
        $flow = $this->getFlow ($this,'flow1');
        $account = $this->accounts ('account1');
        $params = array (
            'model' => $account,
            'modelClass' => 'Accounts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal['trace']);

        // assert flow executed without errors
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $createdContact = Contacts::model ()->findByAttributes (array (
                'firstName' => 'test',
                'lastName' => 'test'
            ));

        // assert that contact with correct first name and last name was created by flow
        $this->assertTrue ($createdContact !== null);

        /*
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($createdContact->getAttributes ());
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($account->getAttributes ());*/

        $relatedX2Models = $createdContact->getRelatedX2Models ();

        // assert that relationship was created from link type field
        $this->assertTrue (sizeof ($relatedX2Models) !== 0);

        // assert that correct relationship was created from link type field
        $this->assertTrue (in_array ($account->id, array_map (function ($elem) {
            return $elem->id; 
        }, $relatedX2Models)));

    }

    /**
     * Tests the create relationship option 
     */
    public function testCreateRelationship () {
        $params = array (
            'user' => 'admin'
        );
        $account = $this->accounts ('account1');
        $params = array (
            'model' => $account,
            'modelClass' => 'Accounts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flow2'), $params);

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal['trace']);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $lead = X2Leads::model()->findByAttributes (array (
            'firstName' => 'test',
            'lastName' => 'test'
        ));
        $this->assertTrue ($lead !== null);

        // assert that lead is related to account
        $relatedModels = $lead->getRelatedX2Models ();
        $this->assertTrue (in_array ($account->id, array_map (function ($elem) {
            return $elem->id; 
        }, $relatedModels)));
    }

    /**
     * Tests record attribute replacement tokens 
     */
    public function testTokenReplacement () {
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flow3'), $params);

        $date = Formatter::formatDate(time(), "long", false);

        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal['trace']);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $this->assertTrue (Contacts::model ()->findByAttributes (array (
            'firstName' => 'Test '.$date,
            'lastName' => '1'.$date,
            'city' => $contact->firstName.' TEST',
        )) instanceof Contacts);
    }
}

?>
