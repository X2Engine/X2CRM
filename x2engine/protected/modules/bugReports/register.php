<?php
return array(
	'name'=>"Bug Reports",
	'install'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
    ),
	'uninstall'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql'))
	),
	'editable'=>true,
	'searchable'=>true,
	'adminOnly'=>false,
	'custom'=>true,
	'toggleable'=>true,
	'version' => '2.8',
);
?>
