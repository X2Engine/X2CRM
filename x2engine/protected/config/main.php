<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
include "X2Config.php";

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>$appName,
	'theme'=>'x2engine',
	'sourceLanguage'=>'en',
	'language'=>$language,

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.components.ApplicationConfigBehavior',
        'application.components.X2UrlRule',
		// 'application.controllers.x2base',
		// 'application.models.*',
		// 'application.components.*',
		// 'application.components.ERememberFiltersBehavior',
		// 'application.components.EButtonColumnWithClearFilters',
	),
	'modules'=>array(
//		 'gii'=>array('class'=>'system.gii.GiiModule',
//            'password'=>'admin',
//            // If removed, Gii defaults to localhost only. Edit carefully to taste.
//            'ipFilters'=>false,
//        ),
		'mobile',
	),
	'behaviors' => array('ApplicationConfigBehavior'),

	// application components
	'components'=>array(
		'user'=>array(
            'class'=>'X2WebUser',
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'file'=>array(
			'class'=>'application.extensions.CFile',
		),
		// uncomment the following to enable URLs in path-format
		
		'urlManager'=>array(
			'urlFormat'=>'path',
			'urlRuleClass'=>'X2UrlRule',
			'showScriptName'=>!isset($_SERVER['HTTP_MOD_REWRITE']),
			'rules'=>array(
			
				'gii/<controller>'=>'gii/<controller>',
			
				'<controller:(site|admin|profile|api|search|notifications|studio)>/<id:\d+>'=>'<controller>/view',
				'<controller:(site|admin|profile|api|search|notifications|studio)>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:(site|admin|profile|api|search|notifications|studio)>/<action:\w+>'=>'<controller>/<action>',
				
                '<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow|services|bugReports)>'=>'<module>/<module>/index',
				'<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow|services|bugReports)>/<id:\d+>'=>'<module>/<module>/view',
				'<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow|services|bugReports)>/<action:\w+>'=>'<module>/<module>/<action>',
				'<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow|services|bugReports)>/<action:\w+>/<id:\d+>'=>'<module>/<module>/<action>',
				'<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow|services|bugReports)>/<controller:\w+>/<id:\d+>'=>'<module>/<module>/view',
				'<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow|services|bugReports)>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
				'<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow|services|bugReports)>/<controller:\w+>/<action:\w+>/<id:\d+>'=>'<module>/<controller>/<action>',
				
				'<module:\w+>/<id:\d+>'=>'<module>/default/view',
				'<module:\w+>/<action:\w+>'=>'<module>/default/<action>',
				'<module:\w+>/<action:\w+>/<id:\d+>'=>'<module>/default/<action>',
				'<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
				'<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>'=>'<module>/<controller>/<action>',
				
				'x2touch'=>'mobile/site/home', 
				
				
				/*
				// special HTTP methods for API
				array('api/view', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
				array('api/update', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'POST'),
				array('api/delete', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'DELETE'),
				array('api/create', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'),
				array('api/voip', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'),
				
				// 'gii/<controller>'=>'gii/<controller>',

				'contacts/<id:\d+>'							=>	'contacts/contacts/view',
				'contacts/<action:\w+>'						=>	'contacts/contacts/<action>',
				'contacts/<action:\w+>/<id:\d+>'			=>	'contacts/contacts/<action>',
				'contacts/contacts/<id:\d+>'				=>	'contacts/contacts/view',
				'contacts/contacts/<action:\w+>'			=>	'contacts/contacts/<action>',
				'contacts/contacts/<action:\w+>/<id:\d+>'	=>	'contacts/contacts/<action>',
				
				'actions/<id:\d+>'							=>	'actions/actions/view',
				'actions/<action:\w+>'						=>	'actions/actions/<action>',
				'actions/<action:\w+>/<id:\d+>'				=>	'actions/actions/<action>',
				'actions/actions/<id:\d+>'					=>	'actions/actions/view',
				'actions/actions/<action:\w+>'				=>	'actions/actions/<action>',
				'actions/actions/<action:\w+>/<id:\d+>'		=>	'actions/actions/<action>',
				
				'accounts/<id:\d+>'							=>	'accounts/accounts/view',
				'accounts/<action:\w+>'						=>	'accounts/accounts/<action>',
				'accounts/<action:\w+>/<id:\d+>'			=>	'accounts/accounts/<action>',
				'accounts/accounts/<id:\d+>'				=>	'accounts/accounts/view',
				'accounts/accounts/<action:\w+>'			=>	'accounts/accounts/<action>',
				'accounts/accounts/<action:\w+>/<id:\d+>'	=>	'accounts/accounts/<action>',
				
				'calendar/<id:\d+>'							=>	'calendar/calendar/view',
				'calendar/<action:\w+>'						=>	'calendar/calendar/<action>',
				'calendar/<action:\w+>/<id:\d+>'			=>	'calendar/calendar/<action>',
				'calendar/calendar/<id:\d+>'				=>	'calendar/calendar/view',
				'calendar/calendar/<action:\w+>'			=>	'calendar/calendar/<action>',
				'calendar/calendar/<action:\w+>/<id:\d+>'	=>	'calendar/calendar/<action>',
				
				'charts/<id:\d+>'							=>	'charts/charts/view',
				'charts/<action:\w+>'						=>	'charts/charts/<action>',
				'charts/<action:\w+>/<id:\d+>'				=>	'charts/charts/<action>',
				'charts/charts/<id:\d+>'					=>	'charts/charts/view',
				'charts/charts/<action:\w+>'				=>	'charts/charts/<action>',
				'charts/charts/<action:\w+>/<id:\d+>'		=>	'charts/charts/<action>',
				
				'docs/<id:\d+>'								=>	'docs/docs/view',
				'docs/<action:\w+>'							=>	'docs/docs/<action>',
				'docs/<action:\w+>/<id:\d+>'				=>	'docs/docs/<action>',
				'docs/docs/<id:\d+>'						=>	'docs/docs/view',
				'docs/docs/<action:\w+>'					=>	'docs/docs/<action>',
				'docs/docs/<action:\w+>/<id:\d+>'			=>	'docs/docs/<action>',
				
				'groups/<id:\d+>'							=>	'groups/groups/view',
				'groups/<action:\w+>'						=>	'groups/groups/<action>',
				'groups/<action:\w+>/<id:\d+>'				=>	'groups/groups/<action>',
				'groups/groups/<id:\d+>'					=>	'groups/groups/view',
				'groups/groups/<action:\w+>'				=>	'groups/groups/<action>',
				'groups/groups/<action:\w+>/<id:\d+>'		=>	'groups/groups/<action>',
				
				'marketing/<id:\d+>'						=>	'marketing/marketing/view',
				'marketing/<action:\w+>'					=>	'marketing/marketing/<action>',
				'marketing/<action:\w+>/<id:\d+>'			=>	'marketing/marketing/<action>',
				'marketing/marketing/<id:\d+>'				=>	'marketing/marketing/view',
				'marketing/marketing/<action:\w+>'			=>	'marketing/marketing/<action>',
				'marketing/marketing/<action:\w+>/<id:\d+>'	=>	'marketing/marketing/<action>',
				
				'media/<id:\d+>'							=>	'media/media/view',
				'media/<action:\w+>'						=>	'media/media/<action>',
				'media/<action:\w+>/<id:\d+>'				=>	'media/media/<action>',
				'media/media/<id:\d+>'						=>	'media/media/view',
				'media/media/<action:\w+>'					=>	'media/media/<action>',
				'media/media/<action:\w+>/<id:\d+>'			=>	'media/media/<action>',
				
				'opportunities/<id:\d+>'							=>	'opportunities/opportunities/view',
				'opportunities/<action:\w+>'						=>	'opportunities/opportunities/<action>',
				'opportunities/<action:\w+>/<id:\d+>'				=>	'opportunities/opportunities/<action>',
				'opportunities/opportunities/<id:\d+>'				=>	'opportunities/opportunities/view',
				'opportunities/opportunities/<action:\w+>'			=>	'opportunities/opportunities/<action>',
				'opportunities/opportunities/<action:\w+>/<id:\d+>'	=>	'opportunities/opportunities/<action>',
				
				'products/<id:\d+>'							=>	'products/products/view',
				'products/<action:\w+>'						=>	'products/products/<action>',
				'products/<action:\w+>/<id:\d+>'			=>	'products/products/<action>',
				'products/products/<id:\d+>'				=>	'products/products/view',
				'products/products/<action:\w+>'			=>	'products/products/<action>',
				'products/products/<action:\w+>/<id:\d+>'	=>	'products/products/<action>',
				
				'quotes/<id:\d+>'							=>	'quotes/quotes/view',
				'quotes/<action:\w+>'						=>	'quotes/quotes/<action>',
				'quotes/<action:\w+>/<id:\d+>'				=>	'quotes/quotes/<action>',
				'quotes/quotes/<id:\d+>'					=>	'quotes/quotes/view',
				'quotes/quotes/<action:\w+>'				=>	'quotes/quotes/<action>',
				'quotes/quotes/<action:\w+>/<id:\d+>'		=>	'quotes/quotes/<action>',
				
				'users/<id:\d+>'							=>	'users/users/view',
				'users/<action:\w+>'						=>	'users/users/<action>',
				'users/<action:\w+>/<id:\d+>'				=>	'users/users/<action>',
				'users/users/<id:\d+>'						=>	'users/users/view',
				'users/users/<action:\w+>'					=>	'users/users/<action>',
				'users/users/<action:\w+>/<id:\d+>'			=>	'users/users/<action>',
				
				'workflow/<id:\d+>'							=>	'workflow/workflow/view',
				'workflow/<action:\w+>'						=>	'workflow/workflow/<action>',
				'workflow/<action:\w+>/<id:\d+>'			=>	'workflow/workflow/<action>',
				'workflow/workflow/<id:\d+>'				=>	'workflow/workflow/view',
				'workflow/workflow/<action:\w+>'			=>	'workflow/workflow/<action>',
				'workflow/workflow/<action:\w+>/<id:\d+>'	=>	'workflow/workflow/<action>',
                
                'reports/<id:\d+>'							=>	'reports/reports/view',
				'reports/<action:\w+>'						=>	'reports/reports/<action>',
				'reports/<action:\w+>/<id:\d+>'             =>	'reports/reports/<action>',
				'reports/reports/<id:\d+>'                  =>	'reports/reports/view',
				'reports/reports/<action:\w+>'              =>	'reports/reports/<action>',
				'reports/reports/<action:\w+>/<id:\d+>'     =>	'reports/reports/<action>',
				
				// 'mobile/<id:\d+>'							=>	'mobile/workflow/view',
				// 'mobile/<action:\w+>'						=>	'mobile/workflow/<action>',
				// 'mobile/<action:\w+>/<id:\d+>'			=>	'mobile/workflow/<action>',
				// 'mobile/workflow/<id:\d+>'				=>	'mobile/workflow/view',
				// 'mobile/workflow/<action:\w+>'			=>	'mobile/workflow/<action>',
				// 'mobile/workflow/<action:\w+>/<id:\d+>'	=>	'mobile/workflow/<action>',
				
				// module/action -> assume DefaultController (module/default/action) unless there are 3 tokens (module/controller/action)
				
				// old type
				// '<controller:\w+>/<id:\d+>'=>'<controller>/view',
				// '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				// '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				*/
			),
		),
		'zip'=>array(
			'class'=>'application.extensions.EZip',
		),
		'session' => array (
			'timeout' => 3600,
		),
		// 'db'=>array(
			// 'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		// ),
		'db'=>array(
			'connectionString' => "mysql:host=$host;dbname=$dbname",
			'emulatePrepare' => true,
			'username' => $user,
			'password' => $pass,
			'charset' => 'utf8',
			//'enableProfiling'=>true,
            //'enableParamLogging' => true,
			'schemaCachingDuration'=>84600
		),
		'authManager'=>array(
			'class' => 'CDbAuthManager',
			'connectionID' => 'db',
			'defaultRoles' => array('guest', 'authenticated', 'admin'),
			'itemTable' => 'x2_auth_item',
			'itemChildTable' => 'x2_auth_item_child',
			'assignmentTable' => 'x2_auth_assignment',
		),
		// 'clientScript'=>array(
			// 'class' => 'X2ClientScript',
		// ),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				// array(
					// 'class'=>'ext.yii-debug-toolbar.YiiDebugToolbarRoute',
					// 'ipFilters'=>array('127.0.0.1'),
				// ),
//				 array(
//					 'class'=>'application.extensions.DbProfileLogRoute',
//					 'countLimit' => 1, // How many times the same query should be executed to be considered inefficient
//					 'slowQueryMin' => 0.01, // Minimum time for the query to be slow
//				 ),
				// array(
					// 'class'=>'CWebLogRoute',
					// 'categories' => 'translations',
					// 'levels' => 'missing',
				// ),
			),
		),
		// 'messages'=>array(
			// 'forceTranslation'=>true,
			// 'onMissingTranslation'=>create_function('$event', 'Yii::log("[".$event->category."] ".$event->message,"missing","translations");'),
		// ),
		
		'cache'=>array(
			'class'=>'system.caching.CFileCache',
		),
		'authCache'=>array(
			'class'=>'application.components.X2AuthCache',
			'connectionID'=>'db',
			'tableName'=>'x2_auth_cache',
			// 'autoCreateCacheTable'=>false,
		),
	),
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>$email,
		'adminModel'=>null,
		'profile'=>null,
		'roles'=>array(),
		'groups'=>array(),
		'isAdmin'=>false,
		'sessionStatus'=>0,
		'logo'=>"uploads/logos/yourlogohere.png",
		'webRoot'=>__DIR__.DIRECTORY_SEPARATOR.'..',
		'trueWebRoot'=>substr(__DIR__,0,-17), 
		'registeredWidgets'=>array(
			'OnlineUsers'=>'Active Users',
			'TimeZone' => 'Time Zone',
			'GoogleMaps'=>'Google Map',
			'ChatBox'=>'Activity Feed',
			'TagCloud'=>'Tag Cloud',
			'ActionMenu'=>'My Actions',
			'MessageBox'=>'Message Board',
			'QuickContact'=>'Quick Contact',
			//'TwitterFeed'=>'Twitter Feed',
			'NoteBox'=>'Note Pad',
			'MediaBox' => 'Media',
			'DocViewer' => 'Doc Viewer',
			'TopSites' => 'Top Sites',
			'HelpfulTips' => 'Helpful Tips'
		),
		'currency'=>'',
		'version'=>$version,
		'edition'=>'',
		'buildDate'=>$buildDate,
		'noSession' => false,
		
	),
);
