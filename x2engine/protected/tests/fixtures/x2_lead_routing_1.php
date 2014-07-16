<?php

return array(
    'leadRouting1' => array (
        'criteria' => '["firstName,=,contact1"]', 
        'priority' => 1,
        'users' => 'testUser1',

    ),
    'leadRouting2' => array (
        'criteria' => '["firstName,=,contact2"]', 
        'priority' => 2,
        'users' => 'testUser2',
    ),
    'leadRouting3' => array (
        'criteria' => '["firstName,=,contact4"]', 
        'priority' => 2,
        'users' => 'testUser1, testUser2, testUser3, testUser4',
    ),
);

?>
