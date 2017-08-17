<?php
if(file_exists($customContstants = __DIR__.DIRECTORY_SEPARATOR.'constants-custom.php'))
    require_once $customContstants;

if(file_exists($brandingConstants = implode(DIRECTORY_SEPARATOR, array(__DIR__,'protected','partner','branding_constants.php'))))
    require_once $brandingConstants;



// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',false);

if (!YII_DEBUG) {
    assert_options (ASSERT_ACTIVE, false);
}

// To view pages according to how they'd look in a given edition, set YII_DEBUG
// to true and PRO_VERSION to:
// 0 for opensource
// 1 for pro
// 2 for pla (superset)
defined('PRO_VERSION') or define('PRO_VERSION',2);

// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

// Enable all logging or bare minimum logging:
defined('YII_LOGGING') or define('YII_LOGGING',true);

// Enable translation message logging
defined('X2_TRANSLATION_LOGGING') or define('X2_TRANSLATION_LOGGING',false);

// If true, adds debug toolbar route to array of debug log routes 
defined('YII_DEBUG_TOOLBAR') or define('YII_DEBUG_TOOLBAR',false);

// Indicates that the application is being run as part of a unit test. 
defined('YII_UNIT_TESTING') or define('YII_UNIT_TESTING',false);

// ID of the default admin user
defined('X2_PRIMARY_ADMIN_ID') or define('X2_PRIMARY_ADMIN_ID',1);

// This should be set to false in production environments
defined('X2_DEV_MODE') or define('X2_DEV_MODE',false);

/*
Set to true to enable updating to beta versions.
Before enabling this, please read 
http://wiki.x2engine.com/wiki/Software_Updates_and_Upgrades#Updating_to_Beta_Versions.
*/ 
defined('X2_UPDATE_BETA') or define('X2_UPDATE_BETA',false);

?>
