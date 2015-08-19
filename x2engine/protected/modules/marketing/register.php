<?php
$install = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql'));
$installPla = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install-pla.sql'));
$uninstall = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql'));
$uninstallPla = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall-pla.sql'));
$formLayouts = array();

return array(
	'name' => 'Marketing',
	'install' => file_exists($installPla)? array($install, $installPla, $formLayouts) : array($install, $formLayouts),
	'uninstall' => file_exists($uninstallPla)? array($uninstall, $uninstallPla) : array($uninstall),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => true,
	'version' => '2.0',
);
?>
