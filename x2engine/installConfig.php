<?php
$host = 'localhost';
$db='';
$user='';
$pass='';
$app='X2Engine';
$currency = 'USD';
$lang = '';
$timezone = 'UTC';
$adminEmail = '';
$adminPassword = 'admin';
$adminUsername = 'admin';
$dummyData = 0;
$webLeadUrl = '';
$unique_id = 'none';
// Default visible modules (set manually to a comma-delineated list as desired)
$visibleModules = implode(',',(array) require(dirname(__FILE__).implode(DIRECTORY_SEPARATOR,array('','protected','data','')).'enabledModules.php'));
// Unit & functional testing configuration (auto-config for phpunit.xml not 
// implemented yet; edit protected/tests/phpunit.xml as desired for Selenium 
// configuration)
$test_db = 0;
$test_url = '';
?>
