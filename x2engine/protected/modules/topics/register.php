<?php
return array(
	'name'=>"Topics",
	'install'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
    ),
	'uninstall'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql'))
	),
	'editable'=>false,
	'searchable'=>true,
	'adminOnly'=>false,
	'custom'=>false,
	'toggleable'=>true,
	'version' => '3.6',
);
?>
