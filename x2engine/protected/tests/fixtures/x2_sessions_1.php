<?php


return array(
	'session1' => array(
		'id' => 1,
		'user' => 'testUser1',
        'status' => '0',
        'IP' => '127.0.0.1',
        'lastUpdated' => time()-301, // Should not be expired*
	),
	'session2' => array(
		'id' => 2,
		'user' => 'testUser2',
        'status' => '1',
        'IP' => '127.0.0.1',
        'lastUpdated' => time()-301, // Should be expired*
	),
	'session3' => array(
		'id' => 3,
		'user' => 'testUser3',
        'status' => '1',
        'IP' => '127.0.0.1',
        'lastUpdated' => time()-301, // Should be expired*
	),
	'session4' => array(
		'id' => 4,
		'user' => 'testUser4',
        'status' => '0',
        'IP' => '127.0.0.1'
	),
);
// * Based on the content of the x2_roles fixture, and assuming $admin->timeout = 60.
// testUser1 has the "Executive" and "Peon" roles, and the greater timeout
// takes precedence, whereas testUser2 only has the "Peon" role.
?>
