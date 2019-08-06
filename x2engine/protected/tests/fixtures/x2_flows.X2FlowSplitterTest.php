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




return array(
'0' => array (
  'id' => '4',
  'active' => '1',
  'name' => 'test',
  'description' => 'test',
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":11,"trigger":{"id":1,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"0"}}},{"id":3,"type":"X2FlowSplitter","options":[],"upperBranch":[{"id":4,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"1"}}},{"id":6,"type":"X2FlowSplitter","options":[],"upperBranch":[],"lowerBranch":[{"id":10,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"2"}}},{"id":11,"type":"X2FlowSplitter","options":[],"upperBranch":[],"lowerBranch":[]}]}],"lowerBranch":[{"id":5,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"3"}}}]}],"flowName":"test"}',
  'createDate' => '1441399928',
  'lastUpdated' => '1441399928',
),
'1' => array (
  'id' => '5',
  'active' => '0',
  'name' => 'test1',
  'description' => '',
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":15,"trigger":{"id":1,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"0"}}},{"id":3,"type":"X2FlowSplitter","options":[],"upperBranch":[{"id":4,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"1"}}},{"id":6,"type":"X2FlowSplitter","options":[],"upperBranch":[{"id":12,"type":"X2FlowWait","options":{"delay":{"value":"1"},"unit":{"value":"secs"}}},{"id":13,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"4"}}}],"lowerBranch":[{"id":10,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"2"}}},{"id":11,"type":"X2FlowSplitter","options":[],"upperBranch":[],"lowerBranch":[]}]}],"lowerBranch":[{"id":5,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"3"}}},{"id":14,"type":"X2FlowWait","options":{"delay":{"value":"1"},"unit":{"value":"secs"}}},{"id":15,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"5"}}}]}],"flowName":"test1"}',
  'createDate' => '1441399943',
  'lastUpdated' => '1441399987',
),
'2' => array (
  'id' => '6',
  'active' => '1',
  'name' => 'test2',
  'description' => 'test',
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":14,"trigger":{"id":1,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"0"}}},{"id":3,"type":"X2FlowSplitter","options":[],"upperBranch":[{"id":4,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"1"}}},{"id":6,"type":"X2FlowSplitter","options":[],"upperBranch":[{"id":12,"type":"X2FlowSwitch","options":[],"trueBranch":[{"id":13,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"2"}}}],"falseBranch":[{"id":14,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"2"}}}]}],"lowerBranch":[{"id":10,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"3"}}},{"id":11,"type":"X2FlowSplitter","options":[],"upperBranch":[],"lowerBranch":[]}]}],"lowerBranch":[{"id":5,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"4"}}}]}],"flowName":"test2"}',
  'createDate' => '1441399928',
  'lastUpdated' => '1441405288',
),

);
?>
