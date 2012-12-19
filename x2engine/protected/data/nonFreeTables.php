<?php

/** 
 * All tables that do not exist in the open source edition
 */
$allEditions = array_merge(array('opensource'),require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'editions.php'));
$tables = array_fill_keys($allEditions,array());
$tables['pro'][] = 'x2_reports';
return $tables;
?>
