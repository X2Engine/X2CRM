<?php

return array(
	'flow1' => array(
        'id' => '1',
        'active' => '1',
        'name' => 'test',
        'triggerType' => 'WorkflowStartStageTrigger',
        'modelClass' => 'Contacts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"WorkflowStartStageTrigger","options":{"workflowId":{"value":"2"},"stageNumber":{"value":"5"},"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
