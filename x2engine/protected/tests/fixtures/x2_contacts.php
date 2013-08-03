<?php

return array(
	// The sender of the forwarded message in GMail1_fixture_testAnyone
	// (treated as preexisting contact in email dropbox test)
	'testAnyone' => array(
		'id' => 12345,
		'name' => 'Testfirstname Testlastname',
		'firstName' => 'Testfirstname',
		'lastName' => 'Testlastname',
		'email' => 'contact@test.com',
		'assignedTo' => 'Anyone',
		'visibility' => 1,
		'phone' => '(234) 918-2348',
		'phone2' => '398-103-6291',
		'trackingKey' => '12345678901234567890'
	),
	// Treated as assigned to test user and preexisting.
	'testUser' => array(
		'id' => 67890,
		'name' => 'Testfirstnametwo Testlastnametwo',
		'firstName' => 'Testfirstnametwo',
		'lastName' => 'Testlastnametwo',
		'email' => 'contact2@test.com',
		'assignedTo' => 'testuser',
		'visibility' => 1,
		'phone' => '+13810482910',
		'phone2' => '18561029204',
		'trackingKey' => '9832749823842382398',
	),
);

?>
