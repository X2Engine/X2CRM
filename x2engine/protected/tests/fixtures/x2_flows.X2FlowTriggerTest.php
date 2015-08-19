<?php
return array(
'flowOnListCondition' => array (
  'id' => '10',
  'active' => '1',
  'name' => 'flow1',
  'description' => NULL,
  'triggerType' => 'RecordUpdateTrigger',
  'modelClass' => 'Contacts',
  'flow' => '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts","conditions":[{"type":"on_list","value":"Follow-up"}]},"items":[{"id":2,"type":"X2FlowRecordComment","options":{"assignedTo":{"value":"{assignedTo}"},"comment":{"value":"test"}}}],"flowName":"test"}',
  'createDate' => '11',
  'lastUpdated' => '11',
),
);
?>