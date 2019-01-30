<?php

/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the testing application configuration. Any writable
// CWebApplication properties can be configured here.
$config = require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'main.php');

require_once(dirname(__FILE__).'/../tests/testconstants.php');

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'X2Config-test.php');

$config['components']['db'] = array(
	'connectionString' => "mysql:host=$host;dbname=$dbname",
	'emulatePrepare' => true,
	'username' => $user,
	'password' => $pass,
	'charset' => 'utf8',
	'enableProfiling' => YII_DEBUG,
	'enableParamLogging' => YII_DEBUG,
	'schemaCachingDuration' => 84600
);
$config['components']['fixture'] = array(
	'class' => 'application.components.X2FixtureManager',
	'initScriptSuffix' => '.init.php'
);
$config['import'] = array_merge($config['import'], array('application.tests.*', 'application.components.*', 'application.models.*'));

$debugLogRoutes = array(
	array(
		'class' => 'CFileLogRoute',
		'logFile' => 'debug.log',
        'categories' => 'application.debug',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
	),
	array(
		'class' => 'CFileLogRoute',
		'logFile' => 'trace.log',
        'levels' => 'trace',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
	),
);

$config['components']['log']['routes'] = array_merge (array(
	array(
		'class' => 'CFileLogRoute',
		'logFile' => 'test-results.log',
        'levels' => 'error,warning,trace,info',
        'categories' => 'system.test-output'
	),
	array(
		'class' => 'CFileLogRoute',
		'logFile' => php_sapi_name() == 'cli' ? 'system-test.log' : 'system-test-web.log',
        'levels' => 'error,warning,trace,info',
        'categories' => 'system.*'
	),
	array(
		'class' => 'CFileLogRoute',
		'logFile' => php_sapi_name() == 'cli' ? 'test.log' : 'test-web.log',
        'levels' => 'error,warning,trace,info',
        'categories' => 'application.*'
	),
), YII_DEBUG ? $debugLogRoutes : array ());

$config['params']['automatedTesting'] = true;

$custom = dirname(__FILE__).'/../../custom/protected/config/test.php';
if($custom = realpath($custom)) {
	include($custom);
}

return $config;

