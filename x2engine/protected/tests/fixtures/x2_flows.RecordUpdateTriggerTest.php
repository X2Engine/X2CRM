<?php
return array(
'flow1' => array (
  'id' => '10',
  'active' => '1',
  'name' => 'flow1',
  'description' => NULL,
  'triggerType' => 'RecordUpdateTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"firstName","operator":"changed","value":""},{"type":"attribute","name":"firstName","operator":"=","value":"test"}]},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
);
?>