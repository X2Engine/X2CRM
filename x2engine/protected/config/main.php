<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
require_once "dbConfig.php";
require_once "emailConfig.php";


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
		'application.models.*',
		'application.components.*',
		'application.components.ERememberFiltersBehavior',
		'application.components.EButtonColumnWithClearFilters',
	),

	'modules'=>array(
		'gii'=>$gii,
		'mobile',
	),

	'behaviors' => array('ApplicationConfigBehavior'),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'file'=>array(
			'class'=>'application.extensions.CFile',
		),
		// uncomment the following to enable URLs in path-format
		
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
                                array('api/list', 'pattern'=>'api/<model:\w+>', 'verb'=>'GET'),
                                array('api/view', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
                                array('api/update', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'POST'),
                                array('api/delete', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'DELETE'),
                                array('api/create', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'),
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

		'db'=>$db,
		/*array(
			'connectionString' => 'mysql:host=localhost;dbname=test',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => ' ',
			'charset' => 'utf8',
		),*/
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				/*
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'trace, info, error, warning',
					'categories' => 'system.db.*',
					
				),
				// uncomment the following to show log messages on web pages
				
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>$email,
		'adminModel'=>null,
		'profile'=>null,
		'logo'=>"uploads/logos/yourlogohere.png",
		'webRoot'=>__DIR__.DIRECTORY_SEPARATOR.'..',
		'registeredWidgets'=>array(
			'MessageBox'=>'Message Board',
			'QuickContact'=>'Quick Contact',
			'GoogleMaps'=>'Google Map',
			'TwitterFeed'=>'Twitter Feed',
			'ChatBox'=>'Chat',
			'NoteBox'=>'Note Pad',
			'ActionMenu'=>'My Actions',
			'TagCloud'=>'Tag Cloud',
                        'OnlineUsers'=>'Active Users',
		),
		'currency'=>'',
		'version'=>$version,
	),
);