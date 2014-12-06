<?php

/**
 * All tables that do not exist in the open source edition
 */
$allEditions = array_keys(require(dirname(__FILE__).DIRECTORY_SEPARATOR.'editionHierarchy.php'));
$tables = array_fill_keys($allEditions,array());
// Professional Edition
$tables['pro'][] = 'x2_action_timers';
$tables['pro'][] = 'x2_reports';
$tables['pro'][] = 'x2_reports_2';
$tables['pro'][] = 'x2_forwarded_email_patterns';
$tables['pro'][] = 'x2_gallery';
$tables['pro'][] = 'x2_gallery_photo';
$tables['pro'][] = 'x2_gallery_to_model';
// Platinum Edition
$tables['pla'][] = 'x2_anon_contact';
$tables['pla'][] = 'x2_fingerprint';

return $tables;
?>
