<?php

// For web tests, i.e. Selenium functional tests or API tests via CURL
defined('TEST_BASE_URL') or define('TEST_BASE_URL','http://127.0.0.1/x2engine/index-test.php/');
defined('TEST_WEBROOT_URL') or define('TEST_WEBROOT_URL','http://127.0.0.1/x2engine/');

// For web tracking related Selenium functional tests
defined ('TEST_BASE_URL_ALIAS_1') or define('TEST_BASE_URL_ALIAS_1','http://www.x2enginetestdomain.com/x2engine/index-test.php/');
defined ('TEST_BASE_URL_ALIAS_2') or define('TEST_BASE_URL_ALIAS_2','http://www.x2enginetestdomain2.com/x2engine/index-test.php/');
defined ('TEST_BASE_URL_ALIAS_3') or define('TEST_BASE_URL_ALIAS_3','http://www2.x2enginetestdomain.com/x2engine/index-test.php/');
defined ('TEST_WEBROOT_URL_ALIAS_1') or define('TEST_WEBROOT_URL_ALIAS_1','http://www.x2enginetestdomain.com/x2engine/');
defined ('TEST_WEBROOT_URL_ALIAS_2') or define('TEST_WEBROOT_URL_ALIAS_2','http://www.x2enginetestdomain2.com/x2engine/');
defined ('TEST_WEBROOT_URL_ALIAS_3') or define('TEST_WEBROOT_URL_ALIAS_3','http://www2.x2enginetestdomain.com/x2engine/');


// Used to send test emails during InlineEmail test
defined('TEST_EMAIL_TO') or define('TEST_EMAIL_TO','');

defined('VALID_LICENSE_KEY_PRO') or define('VALID_LICENSE_KEY_PRO','');
defined('VALID_LICENSE_KEY_PLA') or define('VALID_LICENSE_KEY_PLA','');

?>
