<?php

return array(
	'flow1' => array(
        'id' => '1',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'RecordCreateTrigger',
        'modelClass' => 'Accounts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"RecordCreateTrigger","options":{"modelClass":{"value":"Accounts"}},"modelClass":"Accounts"},"items":[{"type":"X2FlowRecordCreate","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts","attributes":[{"name":"firstName","value":"test"},{"name":"lastName","value":"test"},{"name":"company","value":"{name}"},{"name":"visibility","value":"1"}]}]}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
