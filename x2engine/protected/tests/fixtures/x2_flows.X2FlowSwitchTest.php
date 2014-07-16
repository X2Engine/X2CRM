<?php

return array(
    'flow10' => array(
        'id' => '10',
        'active' => '1',
        'name' => 'test',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"3","stageState":"completed"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}}]}],"flowName":"test"}',
        'createDate' => '1399919436',
        'lastUpdated' => '1399940125',
    ),
  'flow11' => 
  array (
    'id' => '11',
    'active' => '1',
    'name' => 'test',
    'triggerType' => 'RecordUpdateTrigger',
    'modelClass' => 'Contacts',
    'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"7","stageState":"completed"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}},{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}]}],"flowName":"test"}',
    'createDate' => '1399919436',
    'lastUpdated' => '1399945367',
  ),
  'flow12' => 
  array (
    'id' => '12',
    'active' => '1',
    'name' => 'test',
    'triggerType' => 'RecordUpdateTrigger',
    'modelClass' => 'Contacts',
    'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"1","stageState":"notCompleted"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}},{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}]}],"flowName":"test"}',
    'createDate' => '1399919436',
    'lastUpdated' => '1399945550',
  ),
  'flow13' => array (
    'id' => '13',
    'active' => '1',
    'name' => 'test',
    'triggerType' => 'RecordUpdateTrigger',
    'modelClass' => 'Contacts',
    'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"7","stageState":"notCompleted"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}},{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}]}],"flowName":"test"}',
    'createDate' => '1399919436',
    'lastUpdated' => '1399945550',
  ),
  'flow14' => 
  array (
    'id' => '14',
    'active' => '1',
    'name' => 'test',
    'triggerType' => 'RecordUpdateTrigger',
    'modelClass' => 'Contacts',
    'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"1","stageState":"started"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}},{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}]}],"flowName":"test"}',
    'createDate' => '1399919436',
    'lastUpdated' => '1399945825',
  ),
  'flow15' => 
  array (
    'id' => '15',
    'active' => '1',
    'name' => 'test',
    'triggerType' => 'RecordUpdateTrigger',
    'modelClass' => 'Contacts',
    'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"7","stageState":"started"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}},{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}]}],"flowName":"test"}',
    'createDate' => '1399919436',
    'lastUpdated' => '1399945825',
  ),
  'flow16' => 
  array (
    'id' => '16',
    'active' => '1',
    'name' => 'test',
    'triggerType' => 'RecordUpdateTrigger',
    'modelClass' => 'Contacts',
    'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"1","stageState":"notStarted"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}},{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}]}],"flowName":"test"}',
    'createDate' => '1399919436',
    'lastUpdated' => '1399945825',
  ),
  'flow17' => 
  array (
    'id' => '17',
    'active' => '1',
    'name' => 'test',
    'triggerType' => 'RecordUpdateTrigger',
    'modelClass' => 'Contacts',
    'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowSwitch","options":[],"conditions":[{"type":"workflow_status","workflowId":"2","stageNumber":"7","stageState":"notStarted"}],"trueBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"true"}}}],"falseBranch":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"false"}}},{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":""}}}]}],"flowName":"test"}',
    'createDate' => '1399919436',
    'lastUpdated' => '1399945825',
  ),
);
?>
