<?php
$db=array(
	'connectionString' => 'mysql:host=localhost;dbname=x2merge',
	'emulatePrepare' => true,
	'username' => 'x3engine',
	'password' => 'x32011!!',
	'charset' => 'utf8',
);
$appName='X2Engine';
$gii=array('class'=>'system.gii.GiiModule',
		'password'=>'admin',
		// If removed, Gii defaults to localhost only. Edit carefully to taste.
		'ipFilters'=>false,
	);
$email='jake@x2engine.com';
$language='en';