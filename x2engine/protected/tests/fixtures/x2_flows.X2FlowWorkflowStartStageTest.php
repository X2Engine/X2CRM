<?php

return array(
	'flow1' => array(
        'id' => '10',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'UserLoginTrigger',
        'modelClass' => 'Accounts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"UserLoginTrigger","options":{"user":{"value":"Anyone","operator":"="}}},"items":[{"type":"X2FlowWorkflowStartStage","options":{"workflowId":{"value":"1"},"stageNumber":{"value":"1"}},"modelClass":"modelClass"}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
