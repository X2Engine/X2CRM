<?php

return array(
	'flow1' => array(
        'id' => '1',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'RecordTagAddTrigger',
        'modelClass' => 'Accounts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"RecordTagAddTrigger","options":{"modelClass":{"value":"Accounts"},"tags":{"value":"#successful"}},"modelClass":"Accounts"},"items":[{"type":"X2FlowEmail","options":{"from":{"value":"-1"},"to":{"value":""},"template":{"value":""},"subject":{"value":""},"cc":{"value":""},"bcc":{"value":""},"body":{"value":""}}}]}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow2' => array(
        'id' => '2',
        'active' => '1',
        'name' => 'flow2',
        'triggerType' => 'RecordTagAddTrigger',
        'modelClass' => 'Accounts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"RecordTagAddTrigger","options":{"modelClass":{"value":"Accounts"},"tags":{"value":"#successful"}},"modelClass":"Accounts","conditions":[{"type":"attribute","name":"name","operator":"=","value":"account1"}]},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}]}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow3' => array(
        'id' => '3',
        'active' => '1',
        'name' => 'flow3',
        'triggerType' => 'WebleadTrigger',
        'modelClass' => 'Contacts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"WebleadTrigger","options":{"tags":{"value":"","operator":"="}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"leadSource","operator":"=","value":"Google"}]},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}]}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow4' => array(
        'id' => '4',
        'active' => '1',
        'name' => 'flow4',
        'triggerType' => 'WebleadTrigger',
        'modelClass' => 'Contacts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"WebleadTrigger","options":{"tags":{"value":"#successful","operator":"="}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"leadSource","operator":"=","value":"Google"}]},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}]}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
