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
  'id' => '1',
  'active' => '1',
  'name' => 'Overdue Action',
  'description' => '',
  'triggerType' => 'ActionOverdueTrigger',
  'modelClass' => 'Actions',
  'flow' => '{"version":"5.2","idCounter":7,"trigger":{"type":"ActionOverdueTrigger","options":{"duration":{"value":"=(60*60*24*2)"}},"modelClass":"Actions","id":1,"conditions":[{"type":"attribute","name":"assignedTo","operator":"<>","value":["chames"]}]},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"Action overdue; please resolve: {link}"}},"id":2},{"type":"X2FlowWait","options":{"delay":{"value":"2"},"unit":{"value":"days"}},"id":3},{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"attribute","name":"complete","operator":"=","value":"yes"}],"id":4,"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"Overdue action handled: {linK}"}},"id":5}],"falseBranch":[{"type":"X2FlowRecordReassign","options":{"user":{"value":"auto"}},"id":6},{"type":"X2FlowRecordTag","options":{"tags":{"value":"reassigned"},"action":{"value":"add"}},"id":7}]}],"flowName":"Overdue Action"}',
  'createDate' => '1477406039',
  'lastUpdated' => '1477565121',
),
'flow2' => array (
  'id' => '2',
  'active' => '1',
  'name' => 'flow2',
  'description' => NULL,
  'triggerType' => 'RecordTagAddTrigger',
  'modelClass' => 'Accounts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordTagAddTrigger","options":{"modelClass":{"value":"Accounts"},"tags":{"value":"#successful"}},"modelClass":"Accounts","conditions":[{"type":"attribute","name":"name","operator":"=","value":"account1"}]},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}]}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow3' => array (
  'id' => '3',
  'active' => '1',
  'name' => 'flow3',
  'description' => NULL,
  'triggerType' => 'WebleadTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"WebleadTrigger","options":{"tags":{"value":"","operator":"="}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"leadSource","operator":"=","value":"Google"}]},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}]}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow4' => array (
  'id' => '4',
  'active' => '1',
  'name' => 'flow4',
  'description' => NULL,
  'triggerType' => 'WebleadTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"WebleadTrigger","options":{"tags":{"value":"#successful","operator":"="}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"leadSource","operator":"=","value":"Google"}]},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}]}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow5' => array (
  'id' => '5',
  'active' => '1',
  'name' => 'flow5',
  'description' => NULL,
  'triggerType' => 'NewsletterEmailClickTrigger',
  'modelClass' => NULL,
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"NewsletterEmailClickTrigger","options":{"campaign":{"value":""},"url":{"value":"","operator":"contains"}}},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow6' => array (
  'id' => '6',
  'active' => '1',
  'name' => 'flow6',
  'description' => NULL,
  'triggerType' => 'CampaignEmailClickTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"CampaignEmailClickTrigger","options":{"campaign":{"value":"Test Email Campaign_5"},"url":{"value":"test url","operator":"="}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"firstName","operator":"=","value":"Test1"}]},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow7' => array (
  'id' => '7',
  'active' => '1',
  'name' => 'flow7',
  'description' => NULL,
  'triggerType' => 'CampaignEmailClickTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"CampaignEmailClickTrigger","options":{"campaign":{"value":""},"url":{"value":"","operator":"="}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow8' => array (
  'id' => '8',
  'active' => '1',
  'name' => 'flow8',
  'description' => NULL,
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"company","operator":"=","value":"Aperture Science_3"}]},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow9' => array (
  'id' => '9',
  'active' => '1',
  'name' => 'flow9',
  'description' => NULL,
  'triggerType' => 'CampaignEmailOpenTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"CampaignEmailOpenTrigger","options":{"campaign":{"value":"Test Email Campaign"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow10' => array (
  'id' => '10',
  'active' => '1',
  'name' => 'flow10',
  'description' => NULL,
  'triggerType' => 'CampaignUnsubscribeTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"CampaignUnsubscribeTrigger","options":{"campaign":{"value":"Test Email Campaign"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
);
?>
