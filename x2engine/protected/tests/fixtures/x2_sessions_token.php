<?php

$now = time();
$expired = $now - 528400; 
$recent = $now - 100;

return array(
    /*'testUser_expired' => array(
        'id' => '827ccb0eea8a706c4c34a16891f84e7b', // md5('12345')
        'user' => 'testuser2',
        'lastUpdated' => $expired, // should be expired
        'IP' => '1.0.0.1',
        'status' => 0
    ),*/
    'testBruteforceUser' => array(
        'id' => 'fcc90fdbfb061b258cba50754cf71e92',
        'user' => 'testuser3',
        'lastUpdated' => $recent, // "password"
        'IP' => '1.0.0.2',
        'status' => -5
    ),
    'testNotExistsUser' => array(
        'id' => 'fcc90fdbfb061b258cba50754cf71e93',
        'user' => 'nonExistentUser',
        'lastUpdated' => $recent,
        'IP' => '1.0.0.3',
        'status' => 0
    ),
    'testDeactivatedUser' => array(
        'id' => 'fcc90fdbfb061b258cba50754cf71e94',
        'user' => 'deactivated',
        'lastUpdated' => $recent,
        'IP' => '1.0.0.4',
        'status' => 0
    ),
    'testWorkingUser' => array(
        'id' => 'fcc90fdbfb061b258cba50754cf71111',
        'user' => 'testuser',
        'lastUpdated' => $recent,
        'IP' => '1.0.0.5',
        'status' => 0
    ),
);
