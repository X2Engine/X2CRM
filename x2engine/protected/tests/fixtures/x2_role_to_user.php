<?php

return array(
    'adminIsExecutive' => array(
        'roleId' => 1,
        'userId' => 1,
        'type' => 'user',
    ),
    'testUserIsExecutive' => array(
        'roleId' => 1,
        'userId' => 2,
        'type' => 'user',
    ),
    'testUserIsAlsoAPeon' => array(
        'roleId' => 2,
        'userId' => 2,
        'type' => 'user',
    ),
    'secondTestUserIsOnlyAPeon' => array(
        'roleId' => 2,
        'userId' => 3,
        'type' => 'user',
    ),
    'overlordUserGroup' => array(
        'roleId' => 3,
        'userId' => 3,
        'type' => 'group'
    )
);
