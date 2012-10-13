<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
require_once "X2Config.php";

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
		// 'gii'=>$gii,
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
                
                
                '<controller:(site|admin|profile|api|search|notifications)>/<id:\d+>'=>'<controller>/view',
				'<controller:(site|admin|profile|api|search|notifications)>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:(site|admin|profile|api|search|notifications)>/<action:\w+>'=>'<controller>/<action>',
                
                '<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow)>/<id:\d+>'=>'<module>/<module>/view',
                '<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow)>/<action:\w+>'=>'<module>/<module>/<action>',
                '<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow)>/<action:\w+>/<id:\d+>'=>'<module>/<module>/<action>',
                '<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow)>/<controller:\w+>/<id:\d+>'=>'<module>/<module>/view',
                '<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow)>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
                '<module:(accounts|actions|calendar|charts|contacts|dashboard|docs|groups|marketing|media|opportunities|products|quotes|reports|users|workflow)>/<controller:\w+>/<action:\w+>/<id:\d+>'=>'<module>/<controller>/<action>',
                
                '<module:\w+>/<id:\d+>'=>'<module>/default/view',
				'<module:\w+>/<action:\w+>'=>'<module>/default/<action>',
				'<module:\w+>/<action:\w+>/<id:\d+>'=>'<module>/default/<action>',
				'<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
				'<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>'=>'<module>/<controller>/<action>',
			
				
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
				

				'x2touch'=>'mobile/site/home', 
			),
		),
		'zip'=>array(
			'class'=>'application.extensions.EZip',
		),
		'session' => array (
			'timeout' => 3600,
		),
		/*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		*/
		// uncomment the following to use a MySQL database

		'db'=>array_merge(
                array(
                    'connectionString' => "mysql:host=$host;dbname=$dbname",
                    'emulatePrepare' => true,
                    'username' => $user,
                    'password' => $pass,
                    'charset' => 'utf8',
                ),
                array(
                    'schemaCachingDuration'=>84600
                )),
        
        'authManager'=>array(
            'class' => 'CDbAuthManager',
			'connectionID' => 'db',
			'defaultRoles' => array('guest', 'authenticated', 'admin'),
			'itemTable' => 'x2_auth_item',
			'itemChildTable' => 'x2_auth_item_child',
			'assignmentTable' => 'x2_auth_assignment',
		),
		/*array(
			'connectionString' => 'mysql:host=localhost;dbname=test',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => ' ',
			'charset' => 'utf8',
		),*/
		// 'messages'=>array(
			// 'forceTranslation'=>true,
			// 'onMissingTranslation'=>create_function('$event', 'Yii::log("[".$event->category."] ".$event->message,"missing","translations");'),
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
				// array(
					// 'class'=>'CFileLogRoute',
					// 'levels'=>'info',
					// 'categories' => 'translations',
					// 'logFile'=>'translations.log',
					// 'categories' => 'system.db.*',
					
				// ),
				// uncomment the following to show log messages on web pages
				
				 // array(
					// 'class'=>'CWebLogRoute',
						// 'categories' => 'translations',
						// 'levels' => 'missing',
				 // ),
			),
		),
		'cache'=>array(
			'class'=>'system.caching.CFileCache',
			// 'servers'=>array(
				// array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				// array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			// ),
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
		'logo'=>"uploads/logos/yourlogohere.png",
		'webRoot'=>__DIR__.DIRECTORY_SEPARATOR.'..',
		'trueWebRoot'=>substr(__DIR__,0,-17), 
		'registeredWidgets'=>array(
			'TimeZone' => 'Time Zone',
			'MessageBox'=>'Message Board',
			'QuickContact'=>'Quick Contact',
			'GoogleMaps'=>'Google Map',
			//'TwitterFeed'=>'Twitter Feed',
			'ChatBox'=>'Chat',
			'NoteBox'=>'Note Pad',
			'ActionMenu'=>'My Actions',
			'TagCloud'=>'Tag Cloud',
			'OnlineUsers'=>'Active Users',
			'MediaBox' => 'Media',
			'DocViewer' => 'Doc Viewer',
			'TopSites' => 'Top Sites',
		),
		'currency'=>'',
		'version'=>$version,
		'edition'=>'',
		'buildDate'=>$buildDate,
	),
);
