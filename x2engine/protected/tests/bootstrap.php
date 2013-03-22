<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../framework/yiit.php';
$config=require_once(dirname(__FILE__).'/../config/test.php');

// Some last-minute modifications (for unit testing only)
$config['params']['noSession'] = true;
require_once($yiit);
Yii::createWebApplication($config);

?>