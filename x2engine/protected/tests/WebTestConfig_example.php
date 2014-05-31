<?php

// For web tests, i.e. Selenium functional tests or API tests via CURL
defined('TEST_BASE_URL') or define('TEST_BASE_URL','http://localhost/x2engine/index-test.php/');
defined('TEST_WEBROOT_URL') or define('TEST_WEBROOT_URL','http://localhost/x2engine/');

// For Selenium webtracking functional tests
define('WEBTRACKING_TEST_BASE_URL','http://www.testdomain.com/index-test.php/');
define('WEBTRACKING_TEST_WEBROOT_URL','http://www.testdomain.com/');

?>
