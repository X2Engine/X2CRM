<?php

$testDir = implode(DIRECTORY_SEPARATOR, array(__DIR__,'..','x2engine','protected','tests'));

require_once($testDir . DIRECTORY_SEPARATOR . 'testconstants.php');
require_once(implode(DIRECTORY_SEPARATOR,array($testDir,'..','..','constants.php')));
require_once($testDir.DIRECTORY_SEPARATOR.'WebTestConfig.php');
if (file_exists($testDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once($testDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
}
$yiit=implode(DIRECTORY_SEPARATOR,array($testDir,'..','..','framework','yiit.php'));
$config=require_once(implode(DIRECTORY_SEPARATOR,array($testDir,'..','config','test.php')));
PHPUnit_Extensions_SeleniumTestCase::shareSession(true);
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
