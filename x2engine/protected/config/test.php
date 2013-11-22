<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the testing application configuration. Any writable
// CWebApplication properties can be configured here.
$config = require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'main.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'X2Config-test.php');

$config['components']['db'] = array(
	'connectionString' => "mysql:host=$host;dbname=$dbname",
	'emulatePrepare' => true,
	'username' => $user,
	'password' => $pass,
	'charset' => 'utf8',
	//'enableProfiling'=>true,
	//'enableParamLogging' => true,
	'schemaCachingDuration' => 84600
);
$config['components']['fixture'] = array(
	'class' => 'application.components.X2FixtureManager',
	'initScriptSuffix' => '.init.php'
);
$config['import'] = array_merge($config['import'], array('application.tests.*', 'application.components.*', 'application.models.*'));
$config['components']['log']['routes'] = array(
	array(
		'class' => 'CFileLogRoute',
		'logFile' => 'test.log',
	)
);
$config['params']['automatedTesting'] = true;

$custom = dirname(__FILE__).'/../../custom/protected/config/test.php';
if($custom = realpath($custom)) {
	include($custom);
}

return $config;

