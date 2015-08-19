<?php
return array(
'flow1' => array (
  'id' => '4',
  'active' => '1',
  'name' => 'test targeted content',
  'description' => NULL,
  'triggerType' => 'TargetedContentRequestTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"TargetedContentRequestTrigger","options":{"url":{"value":"","operator":"="},"content":{"value":"Default Web Content"}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"firstName","operator":"=","value":"test"}]},"items":[{"id":2,"type":"X2FlowPushWebContent","options":{"content":{"value":"Targeted Web Content"}}}],"flowName":"test targeted content"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
);
?>