<?php
/*
 * Set this to configure the level out output during unit tests
 * 0 = No output other than test completion status
 * 1 = Output names of test classes and statuses of tests
 * 2 = Output names of test classes and methods and detailed information within tests
 */
defined('X2_TEST_DEBUG_LEVEL') or define('X2_TEST_DEBUG_LEVEL',0);

/*
 * Constant for running either costly or otherwise prohibitive tests. If false,
 * some tests (like translation which calls a billable API) will not be run for
 * the sake of expediency. Tests should be run with this constant set to true
 * before a release.
 */
defined('X2_THOROUGH_TESTING') or define('X2_THOROUGH_TESTING',false);

defined('X2_FTP_FILEOPER') or define('X2_FTP_FILEOPER', false);
defined('X2_DEBUG_EMAIL') or define('X2_DEBUG_EMAIL', true);
defined('X2_FTP_HOST') or define('X2_FTP_HOST', 'localhost');
defined('X2_FTP_USER') or define('X2_FTP_USER', 'root');
defined('X2_FTP_PASS') or define('X2_FTP_PASS', '');
defined('X2_FTP_CHROOT_DIR') or define('X2_FTP_CHROOT_DIR', false);
defined('X2_SCP_FILEOPER') or define('X2_SCP_FILEOPER', false);
defined('X2_SCP_HOST') or define('X2_SCP_HOST', 'localhost');
defined('X2_SCP_USER') or define('X2_SCP_USER', 'root');
defined('X2_SCP_PASS') or define('X2_SCP_PASS', '');

//Location of testing X2Planet installation for tests which reference the licensing server
defined('X2_TESTING_UPDATE_SERVER') or define('X2_TESTING_UPDATE_SERVER', 'https://x2planet.com');

// if set to false, prevents all fixtures from being loaded, unless X2_LOAD_FIXTURES_FOR_CLASS_ONLY
// is set to true
defined('X2_LOAD_FIXTURES') or define('X2_LOAD_FIXTURES', false);
// if set to true, causes all fixtures but the ones defined in test class and its ancestors from
// being loaded. Takes effect even if X2_LOAD_FIXTURES is false
defined('X2_LOAD_FIXTURES_FOR_CLASS_ONLY') or define('X2_LOAD_FIXTURES_FOR_CLASS_ONLY', true);
defined('X2_SKIP_ALL_TESTS') or define('X2_SKIP_ALL_TESTS', false);

?>
