<?php

return array(
    'name'=>"Actions",
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
    'toggleable'=>false,
    'version' => '2.0',
);
?>
