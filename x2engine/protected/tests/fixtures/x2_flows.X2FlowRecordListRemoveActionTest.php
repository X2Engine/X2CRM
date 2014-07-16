<?php

return array(
	'flow1' => array(
        'id' => '10',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowRecordListRemove","options":{"listId":{"value":"Follow-up_30"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
