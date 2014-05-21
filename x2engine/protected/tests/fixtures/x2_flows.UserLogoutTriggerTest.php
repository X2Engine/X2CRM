<?php

return array(
	'flow1' => array(
        'id' => '10',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'UserLogoutTrigger',
        'flow' => '{"version":"3.0.1","trigger":{"type":"UserLogoutTrigger","options":{"user":{"value":"","operator":"="}}},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"hello"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow2' => array(
        'id' => '11',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'UserLogoutTrigger',
        'flow' => '{"version":"3.0.1","trigger":{"type":"UserLogoutTrigger","options":{"user":{"value":"admin","operator":"="}}},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"hello"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
