<?php

return array(
    'name'=>"Users",
    'install' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
	),
	'uninstall' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql')),
	),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>true,
    'custom'=>false,
    'toggleable'=>false,
	'version' => '2.0',
);
?>
