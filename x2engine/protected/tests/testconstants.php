<?php
// set this to true to enable verbose output during tests
defined('VERBOSE_MODE') or define('VERBOSE_MODE',true);

defined('X2_FTP_FILEOPER') or define('X2_FTP_FILEOPER', false);
defined('X2_FTP_HOST') or define('X2_FTP_HOST', 'localhost');
defined('X2_FTP_USER') or define('X2_FTP_USER', 'root');
defined('X2_FTP_PASS') or define('X2_FTP_PASS', '');
defined('X2_FTP_CHROOT_DIR') or define('X2_FTP_CHROOT_DIR', false);
// if set to false, prevents all fixtures from being loaded, unless LOAD_FIXTURES_FOR_CLASS_ONLY
// is set to true
defined('LOAD_FIXTURES') or define('LOAD_FIXTURES', false);
// if set to true, causes all fixtures but the ones defined in test class and its ancestors from
// being loaded. Takes effect even if LOAD_FIXTURES is false
defined('LOAD_FIXTURES_FOR_CLASS_ONLY') or define('LOAD_FIXTURES_FOR_CLASS_ONLY', true);

?>
