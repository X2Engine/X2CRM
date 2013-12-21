<?php

return array(
	'testUser' => array(
		'id' => '1',
		'masterId' => NULL,
		'name' => 'Follow-up',
		'assignedTo' => 'Anyone',
		'listId' => 30,
		'active' => '0',
		'description' => 'Simple keeping-in-touch email (test fixture data)',
		'type' => 'Email',
		'cost' => NULL,
		'template' => '0',
		'subject' => 'Hello again {firstName}',
		'content' => 'Is there anybody OUT THERE? {firstName}?',
		'createdBy' => 'admin',
		'complete' => '0',
		'visibility' => '1',
		'createDate' => '1373603168',
		'launchDate' => '1373603170',
		'lastUpdated' => '1373603170',
		'lastActivity' => '1373603170',
		'updatedBy' => 'admin',
		'sendAs' => 1,
	),
	'newsletterCampaign' => array(
		'id' => '2',
		'masterId' => NULL,
		'name' => 'Follow-up',
		'assignedTo' => 'Anyone',
		'listId' => 32,
		'active' => '0',
		'description' => 'Simple keeping-in-touch email (test fixture data)',
		'type' => 'Email',
		'cost' => NULL,
		'template' => '0',
		'subject' => 'Hello again',
		'content' => 'Is there anybody OUT THERE?',
		'createdBy' => 'admin',
		'complete' => '0',
		'visibility' => '1',
		'createDate' => '1373603168',
		'launchDate' => '1373603170',
		'lastUpdated' => '1373603170',
		'lastActivity' => '1373603170',
		'updatedBy' => 'admin',
		'sendAs' => NULL,
	),

    'launchedEmailCampaign' => array(
        'id' => '5',
        'masterId' => NULL,
        'name' => 'Test Email Campaign',
        'assignedTo' => 'admin',
        'listId' => '16',
        'active' => '1',
        'description' => '',
        'type' => 'Email',
        'cost' => NULL,
        'template' => '0',
        'subject' => 'This is a test',
        'content' => '<html>
<head>
        <title></title>
</head>
<body>Hello {name},<br />
<br />
This is a test.<br />
<br />
Have a nice day!</body>
</html>
',
        'createdBy' => 'admin',
        'complete' => '0',
        'visibility' => '1',
        'createDate' => '1387560038',
        'launchDate' => '1387560057',
        'lastUpdated' => '1387560063',
        'lastActivity' => '1387560063',
        'updatedBy' => 'admin',
        'sendAs' => '-1',
    ),

);
?>
