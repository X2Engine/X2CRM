<?php
return array(
'flow1' => array (
  'id' => '1',
  'active' => '1',
  'name' => 'test',
  'description' => NULL,
  'triggerType' => 'RecordCreateTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordCreateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '1430429238',
  'lastUpdated' => '1430429238',
),
'flow2' => array (
  'id' => '2',
  'active' => '1',
  'name' => 'test2',
  'description' => NULL,
  'triggerType' => 'RecordUpdateTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test2"}}}],"flowName":"test2"}',
  'createDate' => '1430429265',
  'lastUpdated' => '1430429265',
),
);
?>
