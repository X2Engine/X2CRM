<?php

defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('PRO_VERSION') or define('PRO_VERSION',true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
defined('YII_LOGGING') or define('YII_LOGGING',false);

$yiit=dirname(__FILE__).'/../../framework/yiit.php';
$config=require_once(dirname(__FILE__).'/../config/test.php');

// Some last-minute modifications (for unit testing only)
$config['params']['noSession'] = true;
require_once($yiit);
Yii::createWebApplication($config);

?>