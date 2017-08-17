<?php
return array(
'flow1' => array (
  'id' => '10',
  'active' => '1',
  'name' => 'flow1',
  'description' => NULL,
  'triggerType' => 'UserLoginTrigger',
  'modelClass' => 'Accounts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"UserLoginTrigger","options":{"user":{"value":"Anyone","operator":"="}}},"items":[{"id":2,"type":"X2FlowProcessStartStage","options":{"workflowId":{"value":"1"},"stageNumber":{"value":"1"}},"modelClass":"modelClass"}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
);
?>