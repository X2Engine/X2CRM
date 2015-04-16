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
	'flow2' => array(
        'id' => '2',
        'active' => '1',
        'name' => 'flow2',
        'triggerType' => 'RecordCreateTrigger',
        'modelClass' => 'Accounts',
        'flow' => '{"version":"3.0.1","trigger":{"type":"RecordViewTrigger","options":{"modelClass":{"value":"Accounts"}},"modelClass":"Accounts"},"items":[{"type":"X2FlowRecordCreate","options":{"modelClass":{"value":"X2Leads"},"createRelationship":{"value":true}},"modelClass":"X2Leads","attributes":[{"name":"firstName","value":"test"},{"name":"lastName","value":"test"}]}],"flowName":"test create relationship"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
    'flow3' => array (
      'id' => '3',
      'active' => '1',
      'name' => 'test',
      'triggerType' => 'RecordViewTrigger',
      'modelClass' => 'Contacts',
      'flow' => '{"version":"3.0.1","trigger":{"type":"RecordViewTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowRecordCreate","options":{"modelClass":{"value":"Contacts"},"createRelationship":{"value":false}},"modelClass":"Contacts","attributes":[{"name":"firstName","value":"test {date}"},{"name":"lastName","value":"=1 . {date}"},{"name":"visibility","value":"1"},{"name":"city","value":"{firstName} TEST"}]}],"flowName":"test"}',
      'createDate' => '1427335491',
      'lastUpdated' => '1427336959',
    ),
);
?>
