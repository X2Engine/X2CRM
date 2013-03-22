<?php
return array(
	'name'=>"Bug Reports",
	'install'=>array(
		dirname(__FILE__).'/data/install.sql',
    ),
	'uninstall'=>array(
		dirname(__FILE__).'/data/uninstall.sql'
	),
	'editable'=>true,
	'searchable'=>true,
	'adminOnly'=>false,
	'custom'=>true,
	'toggleable'=>true,
	'version' => '2.8',
);
?>
