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
Yii::import ('application.components.*');
Yii::import ('application.components.x2flow.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

/**
 * @package application.tests.unit.components.x2flow.triggers
 */
class RecordTagAddTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '_1'),
        'accounts' => array ('Accounts', '_1')
    );

    /**
     * Trigger config only contains tag condition 
     */
    public function testCheckWithTags () {
        $flow = $this->getFlow ($this,'flow1');
        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account1']),
            'modelClass' => 'Accounts',
        );
        $trigger = X2FlowItem::create ($flow['trigger']);
        $retVal = $trigger->check ($params);

        // tag condition fails
        $this->assertFalse ($retVal[0]);

        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account1']),
            'modelClass' => 'Accounts',
            'tags' => '#successful'
        );
        $retVal = $trigger->check ($params);

        // tag condition succeeds
        $this->assertTrue ($retVal[0]);
    }

    /**
     * Trigger config contains tag condition (must have '#successful') and name='account1' condition
     */
    public function testCheckWithTagsAndConditions () {
        $flows = $this->getFlows ($this);
        $flow2 = $flows['flow2'];
        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account2']),
            'modelClass' => 'Accounts',
            'tags' => '#successful'
        );
        $trigger = X2FlowItem::create ($flow2['trigger']);
        $retVal = $trigger->check ($params);

        // tag condition succeeds, name condition fails
        $this->assertFalse ($retVal[0]);

        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account1']),
            'modelClass' => 'Accounts',
            'tags' => '#successful'
        );
        $retVal = $trigger->check ($params);

        // tag condition succeeds, name condition succeeds
        $this->assertTrue ($retVal[0]);
    }

    /**
     * Add a tag to a record and ensure that flow gets triggered 
     */
    public function testAddTag () {
        $this->clearLogs ();
        $account = $this->accounts ('account1');
        $account->addTags (array ('successful'));
        $flow = $this->x2flow ('flow1');
        $trace = $this->getTraceByFlowId ($flow->id);
        $this->assertTrue (is_array ($trace));
        $this->assertTrue ($this->checkTrace ($trace));

        $this->clearLogs ();
        $account->disableTagTriggers ();
        $account = $this->accounts ('account1');
        $account->addTags (array ('successful'));
        $flow = $this->x2flow ('flow1');
        $trace = $this->getTraceByFlowId ($flow->id);
        $this->assertFalse (is_array ($trace));
    }

}

?>
