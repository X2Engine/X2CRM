<?php
return array(
    'flow1' => array(
        'id' => 1,
        'active' => 1,
        'name' => 'Test Record Change',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Accounts',
        'description' => NULL, 
        'flow' => '{"version":"5.2","idCounter":6,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Accounts"}},"modelClass":"Accounts"},"items":[{"id":5,"type":"X2FlowRecordUpdate","options":[],"attributes":[{"name":"name","value":"Test 1"}]},{"id":2,"type":"X2FlowRecordChange","options":{"linkField":{"value":"primaryContact"}},"linkType":"Contacts"},{"id":6,"type":"X2FlowRecordUpdate","options":[],"attributes":[{"name":"firstName","value":"Test 2"}]}],"flowName":"Test Record Change"}',
        'createDate' => 11,
        'lastUpdated' => 11,
    ),
);