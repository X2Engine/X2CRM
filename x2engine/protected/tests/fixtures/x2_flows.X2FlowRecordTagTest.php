<?php
return array(
'flow1' => array (
  'id' => '4',
  'active' => '1',
  'name' => 'test',
  'description' => NULL,
  'triggerType' => 'RecordUpdateTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordTag","options":{"tags":{"value":"test1,test2"},"action":{"value":"add"}}}],"flowName":"test"}',
  'createDate' => '1408072711',
  'lastUpdated' => '1408072711',
),
'flow2' => array (
  'id' => '5',
  'active' => '1',
  'name' => 'test',
  'description' => NULL,
  'triggerType' => 'RecordUpdateTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordTag","options":{"tags":{"value":"test1,test2"},"action":{"value":"remove"}}}],"flowName":"test"}',
  'createDate' => '1408072711',
  'lastUpdated' => '1408072711',
),
'flow3' => array (
  'id' => '6',
  'active' => '1',
  'name' => 'test',
  'description' => NULL,
  'triggerType' => 'RecordUpdateTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordTag","options":{"tags":{"value":""},"action":{"value":"clear"}}}],"flowName":"test"}',
  'createDate' => '1408072711',
  'lastUpdated' => '1408075951',
),
);
?>