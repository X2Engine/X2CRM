<?php
// Placeholders in brackets will be replaced in a strtr during automated trial installation
// They should correspond to keys of $_GET and $sendargPatterns (in secrets.php)
$host = '127.0.0.1';
$db='{dbname}';
$user='{dbuser}';
$pass='{dbpass}';
$app='{name}';
$currency = '{currency}';
$lang = '{language}';
$timezone = '{timezone}';
$adminEmail = '{email}';
$adminPassword = '{sitepass}';
$adminUsername = 'admin';
$dummyData = {dummydata};
$webLeadUrl = '';
$unique_id = 'TTTT-TRIAL-TRIAL';
$visibleModules = '{visibleModules}';
$baseUrl = '';
$baseUri = '';
// Unit & functional testing configuration (auto-config for phpunit.xml not 
// implemented yet; edit protected/tests/phpunit.xml as desired for Selenium 
// configuration)
$test_db = 0;
$test_url = '';
$installType = 'Trial';
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
