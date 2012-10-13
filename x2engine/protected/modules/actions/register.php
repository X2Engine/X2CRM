<?php

return array(
    'name'=>"Actions",
    'install'=>array(
		dirname(__FILE__).'/data/install.sql',
    ),
    'uninstall'=>array(
        dirname(__FILE__).'/data/uninstall.sql'
    ),
    'editable'=>false,
    'searchable'=>true,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    'version' => '2.0',
);
?>
