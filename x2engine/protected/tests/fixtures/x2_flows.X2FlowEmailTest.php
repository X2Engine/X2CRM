<?php
return array(
'flow1' => array (
  'id' => '1',
  'active' => '1',
  'name' => 'flow1',
  'description' => NULL,
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowEmail","options":{"from":{"value":"-1"},"to":{"value":"contact@test.com"},"template":{"value":""},"subject":{"value":"test subject"},"cc":{"value":""},"bcc":{"value":""},"body":{"value":"test body"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
'flow2' => array (
  'id' => '2',
  'active' => '1',
  'name' => 'flow2',
  'description' => NULL,
  'triggerType' => 'RecordViewTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowEmail","options":{"from":{"value":"-1"},"to":{"value":"contact@test.com"},"template":{"value":""},"subject":{"value":"test subject"},"cc":{"value":"contact3@test.com, contact2@test.com"},"bcc":{"value":""},"body":{"value":"test body"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
);
?>