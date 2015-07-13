<?php

return array(
	'testUser' => array(
		'id' => '1',
		'masterId' => NULL,
		'name' => 'Follow-up',
		'assignedTo' => 'testUser',
		'listId' => 'Follow-up_30',
		'active' => '0',
		'description' => 'Simple keeping-in-touch email (test fixture data)',
		'type' => 'Email',
		'cost' => NULL,
		'template' => '0',
		'subject' => 'Hello again {firstName}',
		'content' => 'Is there anybody OUT THERE? {firstName}? {signature} -- visit http://example.com/?x2_key={trackingKey}',
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
	'redirectLinkGeneration' => array(
		'id' => '10',
		'masterId' => NULL,
		'name' => 'Follow-up',
		'assignedTo' => 'testUser',
		'listId' => 'Follow-up_30',
		'active' => '0',
		'description' => 'Simple keeping-in-touch email (test fixture data)',
		'type' => 'Email',
		'cost' => NULL,
		'template' => '0',
		'subject' => 'Hello again {firstName}',
		'content' => '<a href="http://example.com/?getParam=\'getParamValue\'">link text</a>',
		'createdBy' => 'admin',
		'complete' => '0',
		'visibility' => '1',
		'createDate' => '1373603168',
		'launchDate' => '1373603170',
		'lastUpdated' => '1373603170',
		'lastActivity' => '1373603170',
		'updatedBy' => 'admin',
		'sendAs' => 1,
		'enableRedirectLinks' => 1,
	),
	'newsletterCampaign' => array(
		'id' => '2',
		'masterId' => NULL,
		'name' => 'Follow-up',
		'assignedTo' => 'Anyone',
		'listId' => 'test newsletter campaign_32',
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
        'nameId' => 'Test Email Campaign_5',
        'assignedTo' => 'admin',
        'listId' => 'Campaign Testing_16',
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

    'unsubToken' => array(
        'id' => '6',
        'masterId' => NULL,
        'name' => 'Test Email Campaign',
        'nameId' => 'Test Email Campaign_6',
        'assignedTo' => 'admin',
        'listId' => 'Campaign Testing_16',
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
Have a nice day!
{_unsub}
</body>
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
