<?php

return array(
	'flow1' => array(
        'id' => '10',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
'{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts","conditions":[{"type":"attribute","name":"firstName","operator":"changed","value":""},{"type":"attribute","name":"firstName","operator":"=","value":"test"}]},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"admin"},"text":{"value":"test"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
