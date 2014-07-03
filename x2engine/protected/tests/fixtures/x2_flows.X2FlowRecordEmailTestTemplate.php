<?php

return array(
	'flow1' => array(
        'id' => '1',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'RecordViewTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowRecordEmail","options":{"from":{"value":"EMAIL_CREDENTIAL_ID"},"template":{"value":""},"subject":{"value":"test subject"},"cc":{"value":""},"bcc":{"value":""},"doNotEmailLink":{"value":true},"body":{"value":"test body"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
	'flow2' => array(
        'id' => '2',
        'active' => '1',
        'name' => 'flow2',
        'triggerType' => 'RecordViewTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowRecordEmail","options":{"from":{"value":"EMAIL_CREDENTIAL_ID"},"template":{"value":""},"subject":{"value":"test subject"},"cc":{"value":"contact3@test.com, contact2@test.com"},"bcc":{"value":""},"doNotEmailLink":{"value":true},"body":{"value":"test body"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);

?>
