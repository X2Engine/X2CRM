<?php
return array(
'flow1' => array (
  'id' => '1',
  'active' => '1',
  'name' => 'test',
  'description' => NULL,
  'triggerType' => 'WorkflowStartStageTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"WorkflowStartStageTrigger","options":{"workflowId":{"value":"2"},"stageNumber":{"value":"5"},"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
);
?>