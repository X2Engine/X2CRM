<?php
return array(
'flow1' => array (
  'id' => '4',
  'active' => '1',
  'name' => 'flow1',
  'description' => '',
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":3,"trigger":{"id":2,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":1,"type":"X2FlowWait","options":{"delay":{"value":"1"},"unit":{"value":"secs"}}},{"id":3,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '1437175410',
  'lastUpdated' => '1437175420',
),
'legacyFlow' => array (
  'id' => '5',
  'active' => '1',
  'name' => 'legacyFlow',
  'description' => '',
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":6,"trigger":{"type":"RecordViewTrigger","options":[],"modelClass":"Contacts","id":1},"items":[{"type":"X2FlowSwitch","options":[],"id":3,"trueBranch":[{"type":"X2FlowRecordListAdd","options":{"listId":{"value":""}},"id":4},{"type":"X2FlowSwitch","options":[],"id":5,"trueBranch":[{"type":"X2FlowWait","options":{"delay":{"value":"1"},"unit":{"value":"secs"}},"id":6},{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"falseBranch":[]}],"falseBranch":[]}],"flowName":"flow1"}',
  'createDate' => '1437181427',
  'lastUpdated' => '1437181648',
),
'flow2' => array (
  'id' => '6',
  'active' => '1',
  'name' => 'flow2',
  'description' => '',
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":6,"trigger":{"id":2,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":4,"type":"X2FlowSwitch","options":[],"trueBranch":[{"id":5,"type":"X2FlowSwitch","options":[],"trueBranch":[{"id":1,"type":"X2FlowWait","options":{"delay":{"value":"1"},"unit":{"value":"secs"}}},{"id":6,"type":"X2FlowSwitch","options":[],"trueBranch":[{"id":3,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"falseBranch":[]}],"falseBranch":[]}],"falseBranch":[]}],"flowName":"flow1"}',
  'createDate' => '1437175410',
  'lastUpdated' => '1437175420',
),
);
?>
