<?php
require_once(realpath(implode(DIRECTORY_SEPARATOR,array(__DIR__,'..','constants.php'))));

// change the following paths if necessary
$yiic=dirname(__FILE__).'/../framework/yiic.php';
$config=dirname(__FILE__).'/config/console.php';
defined('PRO_VERSION') or define('PRO_VERSION',2);
require_once($yiic);
