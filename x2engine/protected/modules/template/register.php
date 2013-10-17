<?php
return array(
	'name'=>"Templates",
	'install'=>array(
		dirname(__FILE__).'/data/install.sql',
        dirname(__FILE__).'/sqlData.sql',
    ),
	'uninstall'=>array(
		dirname(__FILE__).'/data/uninstall.sql'
	),
	'editable'=>true,
	'searchable'=>true,
	'adminOnly'=>false,
	'custom'=>true,
	'toggleable'=>true,
	'version' => '3.6',
);
?>
