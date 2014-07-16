<?php

require_once(implode(DIRECTORY_SEPARATOR,array(__DIR__, 'testconstants.php')));
require_once(implode(DIRECTORY_SEPARATOR,array(__DIR__,'..','..','constants.php')));
require_once(__DIR__.DIRECTORY_SEPARATOR.'WebTestConfig.php');
$yiit=implode(DIRECTORY_SEPARATOR,array(__DIR__,'..','..','framework','yiit.php'));
$config=require_once(implode(DIRECTORY_SEPARATOR,array(__DIR__,'..','config','test.php')));

// Some last-minute modifications (for unit testing only)
$config['params']['noSession'] = true;
require_once($yiit);
// Automatically write logs immediately so that framework assertion errors don't
// cause important debugging messages to be lost
Yii::getLogger()->autoFlush = 1;
Yii::getLogger()->autoDump = true;

function println ($message) {
    print ($message . "\n");
}

Yii::createWebApplication($config);

?>
