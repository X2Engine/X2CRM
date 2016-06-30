<?php
return array(
    'flow1' => array(
        'id' => 1,
        'active' => 1,
        'name' => 'Increment Lead Score',
        'triggerType' => 'MacroTrigger',
        'modelClass' => 'Contacts',
        'description' => NULL, 
        'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"MacroTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordUpdate","options":[],"attributes":[{"name":"leadscore","value":"={leadscore} + 1"}]}],"flowName":"Increment Lead Score"}',
        'createDate' => 11,
        'lastUpdated' => 11,
    ),
);
