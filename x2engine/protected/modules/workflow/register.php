<?php

return array(
    'name'=>"Workflow",
    'install' => array(
		dirname(__FILE__) . '/data/install.sql',
	),
	'uninstall' => array(
		dirname(__FILE__) . '/data/uninstall.sql',
	),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    'version' => '2.0',
);
?>
