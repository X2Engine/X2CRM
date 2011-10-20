<?php
$db=array(
	'connectionString' => 'mysql:host=localhost;dbname=x2engine',
	'emulatePrepare' => true,
	'username' => 'root',
	'password' => ' ',
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