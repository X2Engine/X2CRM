<?php
$host = 'localhost';
$db='x2t_3_3';
$user='x2engine';
$pass='x2engine';
$app='X2CRM v 3.3 opensource edition';
$currency = 'USD';
$lang = '';
$timezone = 'UTC';
$adminEmail='x2crm@x2contact.com';
$adminPassword='admin';
$adminUsername='admin';
$dummyData=True;
$webLeadUrl = '';
$unique_id='TTTT-TTTTT-TTTTT';
// Default visible modules (set manually to a comma-delineated list as desired)
$visibleModules = implode(',',(array) require(dirname(__FILE__).implode(DIRECTORY_SEPARATOR,array('','protected','data','')).'enabledModules.php'));
// Unit & functional testing configuration (auto-config for phpunit.xml not 
// implemented yet; edit protected/tests/phpunit.xml as desired for Selenium 
// configuration)
$test_db = 0;
$test_url = '';
$installType = 'Silent';
?>
