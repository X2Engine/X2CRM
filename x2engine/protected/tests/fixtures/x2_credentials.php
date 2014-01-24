<?php
$custom = __DIR__.'/x2_credentials-local.php'; // The liveDeliveryTest alias should be defined in this file
$customCreds = file_exists($custom) ? require($custom) : array();
return array_merge($customCreds,array(
	'testUser' => array(
		'id' => '1',
		'name' => 'Sales Rep\'s Email Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'EmailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'DjFnHn8VbWx0qEWmDeEfV4zECPDOEZA27vMtNKxgw/iiviG5IMtNBHWQxnp/33BzEehNv893SUjZYmhAWMcixx/qsr1SVOGeH52Ho7NMPSHPUAGK8x1Aqd77VPh3d9jR++asU/H80cswjr8Vyu2h1UeBtWzYP0LYR0Bsn1HPrC54ouFy2wgtse5YOfBAOE6tNhsgISAH066jfLryOsYZlg==',
	),
	'gmail1' => array(
		'id' => '2',
		'name' => 'Sales Rep\'s 1st GMail Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'GMailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'DjFnHn8VbWx0qEWmDeEfV4zECPDOEZA27vMtNKxgw/gPDU43IhOgEPVozjUZXw1qNWZsxw3+0dV5wGkyRsXnVbqh7ik7/D0J0sNJCyHmB/Gnrna4IRiLnpbEqGChcbw2',
	),
	'gmail2' => array(
		'id' => '3',
		'name' => 'Sales Rep\'s 2nd GMail Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'GMailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'DjFnHn8VbWx0qEWmDeEfV4zECPDOEZA27vMtNKxgw/gPDU43IhOgEPVozjUZXw1qNWZsxw3+0dV5wGkyRsXnVbqh7ik7/D0J0sNJCyHmB/Gnrna4IRiLnpbEqGChcbw2',
	),
	'backupUser' => array(
		'id' => '4',
		'name' => 'Sales Rep\'s Backup Email Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'GMailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'DjFnHn8VbWx0qEWmDeEfV4zECPDOEZA27vMtNKxgw/gPDU43IhOgEPVozjUZXw1qNWZsxw3+0dV5wGkyRsXnVbqh7ik7/D0J0sNJCyHmB/Gnrna4IRiLnpbEqGChcbw2',
	),
));
?>