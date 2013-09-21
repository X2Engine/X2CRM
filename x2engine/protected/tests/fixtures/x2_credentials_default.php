<?php
$custom = __DIR__.'/x2_credentials_default-local.php'; // All references to default values (i.e. "sysNotification") should be defined in this file
$customCreds = file_exists($custom) ? require($custom) : array();
return array_merge($customCreds,array(
	'testUser' => array(
		'credId' => 2,
		'userId' => 12345,
		'serviceType' => 'email'
	),

));

?>
