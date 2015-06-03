<?php

return array(
    'flow1' => array (
      'id' => '1',
      'active' => '1',
      'name' => 'test',
      'triggerType' => 'RecordCreateTrigger',
      'modelClass' => 'Contacts',
      'flow' => '{"version":"3.0.1","trigger":{"type":"RecordCreateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test"}}}],"flowName":"test"}',
      'createDate' => '1430429238',
      'lastUpdated' => '1430429238',
    ),
    'flow2' => array (
      'id' => '2',
      'active' => '1',
      'name' => 'test2',
      'triggerType' => 'RecordUpdateTrigger',
      'modelClass' => 'Contacts',
      'flow' => '{"version":"3.0.1","trigger":{"type":"RecordUpdateTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"test2"}}}],"flowName":"test2"}',
      'createDate' => '1430429265',
      'lastUpdated' => '1430429265',
    ),
);
?>
