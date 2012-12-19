<?php
/**
 * Labels for the stages of the installation process.
 * 
 * These require translation, as do the names of the modules. They have been
 * placed in this separate file because they are used by both install.php (the
 * AJAX-driven install form) and initialize.php.
 */
return array(
	'validate' => 'validate input',
	'core' => 'set up core database structure',
	'RBAC' => 'set up role-based access control (RBAC) permissions system',
	'timezoneData' => 'insert time zone data',
	'module' => 'install modules',
	'config' => 'apply configuration',
	'dummy_data' => '%s sample data', /* %s = "insert" or "remove" */ 
	'finalize' => 'finish installation',
);

?>
