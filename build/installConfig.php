<?php
$host = '127.0.0.1';
$db='x2engine';
$user='root';
$pass='';
$app='X2CRM';
$currency = 'USD';
$lang = '';
$timezone = 'UTC';
$adminEmail = 'x2crm@x2contact.com';
$adminPassword = 'admin';
$adminUsername = 'admin';
$dummyData = 1;
$baseUrl = 'http://localhost';
$baseUri = '/x2engine';
$unique_id = 'TTTT-TTTTT-TTTTT';
// Default visible modules (set manually to a comma-delineated list as desired)
$visibleModules = implode(',',(array) require(dirname(__FILE__).implode(DIRECTORY_SEPARATOR,array('','protected','data','')).'enabledModules.php'));
// Unit & functional testing configuration (auto-config for phpunit.xml not 
// implemented yet; edit protected/tests/phpunit.xml as desired for Selenium 
// configuration)
$test_db = 1;
$test_url = 'http://localhost/x2engine/index-test.php';
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
