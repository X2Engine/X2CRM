<?php

return array(
	'name' => "Docs",
	'install' => array(
		dirname(__FILE__) . '/data/install.sql',
		dirname(__FILE__) . '/data/sample_quote_template.sql'
	),
	'uninstall' => array(
		dirname(__FILE__) . '/data/uninstall.sql'
	),
	'editable' => false,
	'searchable' => false,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => false,
	'version' => '2.0',
);
?>
