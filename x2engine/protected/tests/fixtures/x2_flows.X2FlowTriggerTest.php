<?php

return array(
	'flowOnListCondition' => array(
        'id' => '10',
        'active' => '1',
        'name' => 'flow1',
        'triggerType' => 'RecordUpdateTrigger',
        'modelClass' => 'Contacts',
        'flow' => 
            '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts","conditions":[{"type":"on_list","value":"Follow-up"}]},"items":[{"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"test"}}}],"flowName":"test"}',
        'createDate' => 01389906490,
        'lastUpdated' => 01389906490,
	),
);
?>
