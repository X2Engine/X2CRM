<?php

return array(
	'testEmailTemplate' => array(
		'name' => 'quis',
		'subject' => 'Re: Hi {name}, I am an email subject',
		'type' => 'email',
		'text' => '<html><head></head><body>full name: {name}</body></html>',
		'createdBy' => 'testuser',
		'createDate' => '1363992038',
		'editPermissions' => 'testuser',
		'updatedBy' => 'testuser',
		'lastUpdated' => '1364078438',
		'visibility' => '0',
	),
    'testAccountEmailTemplate' => array (
        'id' => '53',
        'name' => 'test',
        'nameId' => 'test_53',
        'subject' => '',
        'emailTo' => '{description}',
        'type' => 'email',
        'associationType' => 'Accounts',
        'text' => '<html> <head> <title></title> </head> <body>test account email template</body> </html> ',
        'createdBy' => 'admin',
        'createDate' => '1399401396',
        'editPermissions' => '',
        'updatedBy' => 'admin',
        'lastUpdated' => '1399407574',
        'visibility' => '1',
    )
);
?>
