<?php
$install = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'install.sql'));
$uninstall = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'uninstall.sql'));
$installPro = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'install-pro.sql'));
$uninstallPro = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'data', 'uninstall-pro.sql'));

return array(
    'name'=>"Actions",
    'install'=>file_exists($installPro) ? array($install, $installPro) : array($install),
    'uninstall'=>file_exists($uninstallPro) ? array($uninstall, $uninstallPro) : array($uninstall),
    'editable'=>false,
    'searchable'=>true,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
    'version' => '2.0',
);
?>
