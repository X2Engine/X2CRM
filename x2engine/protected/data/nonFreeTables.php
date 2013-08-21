<?php

/**
 * All tables that do not exist in the open source edition
 */
$allEditions = array_merge(array('opensource'),require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'editions.php'));
$tables = array_fill_keys($allEditions,array());
$tables['pro'][] = 'x2_reports';
$tables['pro'][] = 'x2_forwarded_email_patterns';
$tables['pro'][] = 'x2_gallery';
$tables['pro'][] = 'x2_gallery_photo';
$tables['pro'][] = 'x2_gallery_to_model';
return $tables;
?>
