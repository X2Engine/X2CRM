<?php

return array(
	'flow0' => array(
        'id' => '1',
        'active' => '1',
        'name' => 'test',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowApiCall","options":{"url":{"value":"http:\/\/localhost"},"method":{"value":"POST"}},"modelClass":"API_params","attributes":[{"name":"paramName0","value":"paramValue0"},{"name":"paramName1","value":"paramValue1"}]}],"flowName":"test0"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow1' => array(
        'id' => '2',
        'active' => '1',
        'name' => 'test',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowApiCall","options":{"url":{"value":"http:\/\/localhost"},"method":{"value":"POST"}},"modelClass":"API_params","attributes":[{"name":"paramName0","value":"paramValue0"},{"name":"paramName1","value":"paramValue1"}],"headerRows":[{"name":"Content-Type","value":"application\/json"}]}],"flowName":"test1"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow2' => array(
        'id' => '3',
        'active' => '1',
        'name' => 'test',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowApiCall","options":{"url":{"value":"http:\/\/localhost"},"method":{"value":"GET"}},"modelClass":"API_params","attributes":[{"name":"paramName0","value":"paramValue0"},{"name":"paramName1","value":"paramValue1"}]}],"flowName":"test2"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow3' => array(
        'id' => '4',
        'active' => '1',
        'name' => 'test',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowApiCall","options":{"url":{"value":"http:\/\/localhost\/index.php/api2/Contacts"},"method":{"value":"POST"}},"modelClass":"API_params","attributes":[{"name":"firstName","value":"testApiFlowAction"},{"name":"visibility","value":1},{"name":"lastName","value":"testApiFlowAction"},{"name":"email","value":"test@test.com"}],"headerRows":[{"name":"Authorization","value":"Basic ENCODED_AUTH_INFO"},{"name":"Content-Type","value":"application\/json"}]}],"flowName":"flow3"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
