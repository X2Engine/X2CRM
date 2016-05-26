<?php
$host = '127.0.0.1';
$db='demo';
$user='testing';
$pass='aWd3VODVT67P2dNIkcw41mPub';
$app='X2Engine 5.0';
$currency = 'USD';
$lang = '';
$timezone = 'UTC';
$adminEmail = 'sales@x2engine.com';
$adminPassword = 'admin';
$adminUsername = 'admin';
$dummyData = 1;
$baseUrl = '';
$unique_id = 'none';
// Default visible modules (set manually to a comma-delineated list as desired)
$visibleModules = implode(',',(array) require(dirname(__FILE__).implode(DIRECTORY_SEPARATOR,array('','protected','data','')).'enabledModules.php'));
// Unit & functional testing configuration (auto-config for phpunit.xml not 
// implemented yet; edit protected/tests/phpunit.xml as desired for Selenium 
// configuration)
$test_db = 0;
$test_url = '';
$installType = 'Silent';
// Cron settings. 
//
// These settings have no effect except in X2Engine Professional Edition.
// 
// Set this to true to add a job to the user's cron table:
$startCron = false;
//
// The cron job that will be inserted into the cron table if $startCron is true.
// 
// You will need to change the URL in this setting to reflect the URL of the CRM
// (as it will be resolved from the local machine) once installed.
$cron = '* * * * * curl http://localhost/index.php/api/x2cron &>/dev/null #@X2CRM@default#@X2CRM@Default cron job for automation delays';
?>
