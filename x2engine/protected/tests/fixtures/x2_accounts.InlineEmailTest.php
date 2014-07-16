<?php

return array(
	'testAccount' => array(
		'id' => 10,
		'name' => 'Black Mesa',
        'nameId' => 'Black Mesa_1',
		'website' => 'www.blackmesa.com',
		'type' => 'Manufacturing',
		'annualRevenue' => '0',
		'phone' => '831-555-5555',
		'tickerSymbol' => 'MESA',
		'employees' => '30',
		'assignedTo' => 'testuser',
		'createDate' => 1365203393,
		'associatedContacts' => '',

        // an email is placed in the description field in order to test insertable attributes inside
        // the the email To: field.
		'description' => 'test@test.com',
		'lastUpdated' => 1365203393,
		'lastActivity' => NULL,
		'updatedBy' => 'testuser',
	),
);
?>
