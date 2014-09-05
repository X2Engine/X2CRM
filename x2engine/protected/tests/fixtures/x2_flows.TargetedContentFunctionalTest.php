<?php

return array(
	'flow1' => array(
        'id' => '4',
        'active' => '1',
        'name' => 'test targeted content',
        'triggerType' => 'TargetedContentRequestTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
'{"version":"3.0.1","trigger":{"type":"TargetedContentRequestTrigger","options":{"url":{"value":"","operator":"="},"content":{"value":"Default Web Content"}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"firstName","operator":"=","value":"test"}]},"items":[{"type":"X2FlowPushWebContent","options":{"content":{"value":"Targeted Web Content"}}}],"flowName":"test targeted content"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
