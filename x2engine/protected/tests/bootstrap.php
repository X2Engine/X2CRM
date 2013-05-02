<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../framework/yiit.php';
$config=require_once(dirname(__FILE__).'/../config/test.php');
defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 4);

// Some last-minute modifications (for unit testing only)
$config['params']['noSession'] = true;
require_once($yiit);
Yii::createWebApplication($config);

?>