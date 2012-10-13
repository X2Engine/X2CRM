<?php

return array(
    'name'=>"Users",
    'install' => array(
		dirname(__FILE__) . '/data/install.sql',
	),
	'uninstall' => array(
		dirname(__FILE__) . '/data/uninstall.sql',
	),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>true,
    'custom'=>false,
    'toggleable'=>false,
	'version' => '2.0',
);
?>
