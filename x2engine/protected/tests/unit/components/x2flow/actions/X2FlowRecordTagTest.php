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
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordTagTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowRecordTagTest'),
        'contacts' => array ('Contacts', '.WorkflowTests'),
    );


    public function testAddTags () {
        $flow = $this->x2flow ('flow1');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->removeTags (array ('test1', 'test2'));
        $tags = $this->contacts ('contact935')->getTags ();
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($tags);
        $this->assertEmpty ($tags);

        $retVal = $this->executeFlow ($flow, $params);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal);
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $tags = $this->contacts ('contact935')->getTags ();
        $this->assertTrue (in_array ('#test1', $tags));
        $this->assertTrue (in_array ('#test2', $tags));
    }

    public function testRemoveTags () {
        $flow = $this->x2flow ('flow2');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        $tags = $this->contacts ('contact935')->getTags ();
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($tags);
        $this->assertEmpty ($tags);

        $tags = $this->contacts ('contact935')->addTags (array ('test1', 'test2'));
        $retVal = $this->executeFlow ($flow, $params);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal);
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $tags = $this->contacts ('contact935')->getTags ();
        $this->assertTrue (!in_array ('#test1', $tags));
        $this->assertTrue (!in_array ('#test2', $tags));
    }

    public function testClearTags () {
        $flow = $this->x2flow ('flow3');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        $tags = $this->contacts ('contact935')->addTags (array ('test1', 'test2'));
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($tags);
        $this->assertNotEmpty ($tags);

        $retVal = $this->executeFlow ($flow, $params);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($retVal);
        $trace = $this->flattenTrace ($retVal['trace']);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $tags = $this->contacts ('contact935')->getTags ();
        $this->assertEmpty ($tags);
    }
}

?>
