<?php
/**
 * Date fields with values that should under all circumstances be permitted
 * to exist in the future
 */
return array(
	'x2_actions' => array('dueDate'),
	'x2_contacts' => array('closedate'),
	'x2_opportunities' => array('expectedCloseDate'),
	'x2_quotes' => array('expectedCloseDate','expirationDate'),
);
?>
