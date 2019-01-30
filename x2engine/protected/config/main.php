<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




// uncomment the following to define a path alias
// Yii::setPathOfAlias('custom','custom');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
if (YII_UNIT_TESTING) {
    include "X2Config-test.php";
} else {
    include "X2Config.php";
}

$defaultLogRoutes = array(
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.api',
        'logFile' => 'api.log',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'exception.*,php',
        'logFile' => 'errors.log'
    ),
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.update',
        'logFile' => 'updater.log',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),
);

$debugLogRoutes = array(
    array(
        'class' => 'CWebLogRoute',
        'categories' => 'translations',
        'levels' => 'missing',
    ),
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.debug',
        'logFile' => 'debug.log',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),
    array(
        'class' => 'CFileLogRoute',
        'logFile' => 'trace.log',
        'levels' => 'trace',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),
);

if (YII_DEBUG && YII_DEBUG_TOOLBAR) {
    $debugLogRoutes[] = array(
        'class' => 'ext.yii-debug-toolbar.YiiDebugToolbarRoute',
        'ipFilters' => array('*'),
    );
}

$noSession = php_sapi_name() == 'cli';
if (!$noSession || YII_UNIT_TESTING) {
    $userConfig = array(
        'class' => 'X2WebUser',
        // enable cookie-based authentication
        'allowAutoLogin' => true,
    );
} else {
    $userConfig = array(
        'class' => 'X2NonWebUser',
    );
}

$config = array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => $appName,
    'theme' => 'x2engine',
    'sourceLanguage' => 'en',
    'language' => $language,
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.components.behaviors.ApplicationConfigBehavior',
        'application.components.X2UrlRule',
        'application.components.ThemeGenerator.ThemeGenerator',
        'application.components.formatters.*'
    ),
    'modules' => array(
        'mobile',
    ),
    'behaviors' => array('ApplicationConfigBehavior'),
    'controllerMap' => array(
        'googlePlus' => array(
            'class' => 'application.integration.Google.GooglePlusController'
        )
    ),
    // application components
    'components' => array(
        'user' => $userConfig,
        'file' => array(
            'class' => 'application.extensions.CFile',
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager' => array(
            'class' => 'X2UrlManager',
            'urlFormat' => 'path',
            'urlRuleClass' => 'X2UrlRule',
            'showScriptName' => !isset($_SERVER['HTTP_MOD_REWRITE']),
            //'caseSensitive'=>false,
            'rules' => array(
                'api/tags/<model:[A-Z]\w+>/<id:\d+>/<tag:\w+>' => 'api/tags/model/<model>/id/<id>/tag/<tag>',
                'api/tags/<model:[A-Z]\w+>/<id:\d+>' => 'api/tags/model/<model>/id/<id>',
                'x2touch' => '/mobile/mobile/home',
                '<module:(mobile)>/<controller:\w+>/<id:\d+>' => '<module>/<controller>/view',
                '<module:(mobile)>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:(mobile)>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
                'gii' => 'gii',
                'gii/<controller:\w+>' => 'gii/<controller>',
                'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
                '<controller:(site|admin|profile|search|notifications|studio|gallery|relationships)>/<id:\d+>' => '<controller>/view',
                '<controller:(site|admin|profile|search|notifications|studio|gallery|relationships)>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:(site|admin|profile|search|notifications|studio|gallery|relationships)>/<action:\w+>/id/<id:\d+>' => '<controller>/<action>',
                '<controller:(site|admin|profile|api|search|notifications|studio|gallery|relationships)>/<action:\w+>' => '<controller>/<action>',
                '<controller:(googlePlus)>/<action:\w+>' => '<controller>/<action>',
                // REST-ful 2nd-gen API URLs
                //
                // Note, all "reserved" GET parameters begin with an underscore.
                // This is to avoid name conflict when querying records by
                // attributes, because column names might interfere with 
                // parameters (i.e. "class" might be a column name, whereas it
                // would need to be used to specify the active record class).
                //
                // The URL formatting rules are listed in ascending generality and
                // decreasing specificity, so that when using CController.createUrl
                // to create URLs, the "prettiest" format will be chosen first
                // (because it will match before one of the more general URL
                // formats, and thus be chosen)
                //
                // Working with actions associated with a model:
                'api2/<associationType:[A-Z]\w+>/<associationId:\d+>/<_class:Actions>/<_id:\d+>.json' => 'api2/model',
                'api2/<associationType:[A-Z]\w+>/<associationId:\d+>/<_class:Actions>' => 'api2/model',
                // Relationships manipulation:
                'api2/<_class:[A-Z]\w+>/<_id:\d+>/relationships/<_relatedId:\d+>.json' => 'api2/relationships',
                // Tags manipulation:
                'api2/<_class:[A-Z]\w+>/<_id:\d+>/tags/<_tagName:.+>.json' => 'api2/tags',
                // Special fields URL format:
                'api2/<_class:[A-Z]\w+>/fields/<_fieldName:\w+>.json' => 'api2/fields',
                // REST hooks:
                'api2/<_class:[A-Z]\w+>/hooks/:<_id:\d+>' => 'api2/hooks',
                'api2/<_class:[A-Z]\w+>/hooks' => 'api2/hooks',
                'api2/hooks/:<_id:\d+>' => 'api2/hooks',
                // Directly access an X2Model instance
                // ...By attributes
                // Example: api2/Contacts/by:firstName=John;lastName=Doe.json
                'api2/<_class:[A-Z]\w+>/by:<_findBy:.+>.json' => 'api2/model',
                // Count records by attributes
                // Example: api2/Contacts/by:firstName=John;lastName=Doe.json
                'api2/<_class:[A-Z]\w+>/count/by:<_findBy:.+>.json' => 'api2/count',
                // ...By ID
                // Example: api2/Contacts/1121.json = Contact #1121
                'api2/<_class:[A-Z]\w+>/<_id:\d+>.json' => 'api2/model',
                // Run the "model" action, with class parameter (required); the
                // base URI for the "model" function
                'api2/<_class:[A-Z]\w+>' => 'api2/model',
                // Run an action "on" a class with a record ID for that class
                // Example: api2/Contacts/1121/relationships = query
                // relationships for contact #1121
                'api2/<_class:[A-Z]\w+>/<_id:\d+>/<action:[a-z]\w+>' => 'api2/<action>',
                // Run an action "on" a class (run action with class parameter)
                // but without any ID specified, i.e. for metadata
                // Example: api2/Contacts/fields = query fields for Contacts model
                'api2/<_class:[A-Z]\w+>/<action:[a-z]\w+>.json' => 'api2/<action>',
                'api2/<_class:[A-Z]\w+>/<action:[a-z]\w+>' => 'api2/<action>',
                // Tag searches:
                'api2/tags/<_tags:[^\/]+>/<_class:[A-Z]\w+>' => 'api2/model',
                // Run a generic action with an ID:
                'api2/<action:[a-z]\w+>/<_id:\d+>.json' => 'api2/<action>',
                // Run a generic action with no additional parameters
                'api2/<action:[a-z]\w+>.json' => 'api2/<action>',
                // Everything else:
                'api2/<action:[a-z]\w+>' => 'api2/<action>',
                // End REST API URL rules
                '<module:calendar>/<action:ical>/<user:\w+>:<key:[^\/]+>.ics' => '<module>/<module>/<action>',
                'weblist/<action:\w+>' => 'marketing/weblist/<action>',
                '<module:\w+>' => '<module>/<module>/index',
                '<module:\w+>/<id:\d+>' => '<module>/<module>/view',
                '<module:\w+>/id/<id:\d+>' => '<module>/<module>/view',
                '<module:\w+>/<action:\w+>/id/<id:\d+>' => '<module>/<module>/<action>',
                '<module:\w+>/<action:\w+>' => '<module>/<module>/<action>',
                '<module:\w+>/<action:\w+>/<id:\d+>' => '<module>/<module>/<action>',
                '<module:\w+>/<controller:\w+>/<id:\d+>' => '<module>/<controller>/view',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
            ),
        ),
        'zip' => array(
            'class' => 'application.extensions.EZip',
        ),
        'session' => array(
            'timeout' => 3600,
        ),
        'request' => array(
            'class' => 'application.components.X2HttpRequest',
            'enableCsrfValidation' => true,
        ),
        // 'db'=>array(
        // 'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
        // ),
        'db' => array(
            'connectionString' => "mysql:host=$host;dbname=$dbname",
            'emulatePrepare' => true,
            'username' => $user,
            'password' => $pass,
            'charset' => 'utf8',
            'enableProfiling' => YII_DEBUG,
            'enableParamLogging' => YII_DEBUG,
            'schemaCachingDuration' => 84600
        ),
        'authManager' => array(
            'class' => 'application.components.X2AuthManager',
            'connectionID' => 'db',
            'defaultRoles' => array('guest', 'authenticated', 'admin'),
            'itemTable' => 'x2_auth_item',
            'itemChildTable' => 'x2_auth_item_child',
            'assignmentTable' => 'x2_auth_assignment',
        ),
        'clientScript' => array(
            'class' => 'application.components.X2ClientScript',
            'mergeJs' => false,
            'mergeCss' => false,
        ),
        'assetManager' => array(
            'class' => 'application.components.X2AssetManager',
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => '/site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => (YII_DEBUG && YII_LOGGING ? array_merge($defaultLogRoutes, $debugLogRoutes) : (YII_LOGGING ? $defaultLogRoutes : array()))
        ),
        'messages' => array(
            'class' => 'application.components.X2MessageSource',
//			 'forceTranslation'=>true,
//             'logBlankMessages'=>false,
//			 'onMissingTranslation'=>create_function('$event', 'Yii::log("[".$event->category."] ".$event->message,"missing","translations");'),
        ),
        'cache' => array(
            'class' => 'system.caching.CFileCache',
        ),
        // cache which doesn't get cleared when admin index is visited
        'cache2' => array(
            'class' => 'X2FileCache',
            'cachePath' => 'application.runtime.cache2',
        ),
        'authCache' => array(
            'class' => 'application.components.X2AuthCache',
            'connectionID' => 'db',
            'tableName' => 'x2_auth_cache',
        // 'autoCreateCacheTable'=>false,
        ),
        'sass' => array(
            'class' => 'SassHandler',
            'enableCompass' => true
        )
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        // this is used in contact page
        'adminEmail' => $email,
        'adminModel' => null,
        'profile' => null,
        'adminProfile' => null,
        'roles' => array(),
        'groups' => array(),
        'userCache' => array(),
        'isAdmin' => false,
        'sessionStatus' => 0,
        'logo' => "uploads/protected/logos/yourlogohere.png",
        'webRoot' => __DIR__ . DIRECTORY_SEPARATOR . '..',
        'trueWebRoot' => substr(__DIR__, 0, -17),
        'registeredWidgets' => array(
            'FlowMacros' => 'Execute Workflow',
            'OnlineUsers' => 'Active Users',
            'TimeZone' => 'Clock',
            'GoogleMaps' => 'Google Map',
            'ChatBox' => 'Activity Feed',
            'TagCloud' => 'Tag Cloud',
            'ActionMenu' => 'My Actions',
            'MessageBox' => 'Message Board',
            'QuickContact' => 'Quick Contact',
            'SmallCalendar' => 'Calendar',
            'NoteBox' => 'Note Pad',
            'MediaBox' => 'Files',
            'DocViewer' => 'Doc Viewer',
            'TopSites' => 'Top Sites',
        ),
        'currency' => '',
        'version' => $version,
        'edition' => '',
        'buildDate' => $buildDate,
        'noSession' => $noSession,
        'automatedTesting' => false,
        'supportedCurrencies' => array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'CNY', 'CHF', 'INR', 'BRL', 'VND'),
        'supportedCurrencySymbols' => array(),
        'modelPermissions' => 'X2PermissionsBehavior',
        'controllerPermissions' => 'X2ControllerPermissionsBehavior',
        'isPhoneGap' => false,
        'isMobileApp' => false,
    ),
);

if (YII_UNIT_TESTING)
    $config['components']['urlManager']['rules'] = array_merge(
            array('profileTest/<action:\w+>' => 'profileTest/<action>'), $config['components']['urlManager']['rules']);

if (file_exists('protected/config/proConfig.php')) {
    $proConfig = include('protected/config/proConfig.php');
    foreach ($proConfig as $attr => $proConfigData) {
        if (isset($config[$attr])) {
            $config[$attr] = array_merge($config[$attr], $proConfigData);
        } else {
            $config[$attr] = $proConfigData;
        }
    }
}
return $config;
