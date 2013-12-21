<?php

return array(
    'name'=>"Groups",
    'install'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql'))
	),
    'uninstall'=>array(
        implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql'))
    ),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
	'version' => '2.0',
);
?>
