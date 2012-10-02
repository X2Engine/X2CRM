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

/**
 * Custom URL parsing class
 * 
 * @package X2CRM.components 
 */
class X2UrlManager extends CBaseUrlRule {

	public $connectionID = 'db';
 
	public function createUrl($manager,$route,$params,$ampersand) {
	
		// $url = 
	
	
	
		if ($route==='car/index') {
			if (isset($params['manufacturer'], $params['model']))
				return $params['manufacturer'] . '/' . $params['model'];
			else if (isset($params['manufacturer']))
				return $params['manufacturer'];
		}
		return false;  // this rule does not apply
	}
 
	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo) {
	
	return false;
		$module = '';
		$controller = '';
		$action = '';

		$path = explode('/',$pathInfo);

		if(empty($path[0]))
			return '';
			
		$path[0] = lcfirst($path[0]);
		
		// scan top-level controllers for first term
		$exclude = array('.','..','x2base.php','X2Controller.php');
		foreach(scandir(Yii::app()->controllerPath) as $file) {
			if(in_array($file,$exclude,true))
				continue;

			if(lcfirst(str_replace('Controller.php','',$file)) === $path[0])
				$controller = $path[0];
		}
		// not a top level controller
		if(empty($controller)) {
			// scan modules folder
			if(in_array($path[0],array_keys(Yii::app()->modules),true))
				$module = $path[0];
			// well then...this URL must be stupid
			else
				return false;
		}
		
		if(count($path) == 1)
			return '/'.$path[0];	// just module or just top level controller are both valid routes
		
		
		
		/* URL Formats (order of testing)
		
			/controller			->		
			/module				->		

			
			
			
		 */
		//   '<module:\w+>/<id:\d+>'=>'<module>/<module>/view',
		//   '<module:\w+>/<action:\w+>'=>'<module>/<module>/<action>',
		//   '<module:\w+>/<action:\w+>/<id:\d+>'=>'<module>/<module>/<action>',
		//   '<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>',
		//   '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>'=>'<module>/<controller>/<action>',
		
		
		// if(ctype_digit($path[1])) {
		
		
		// if(!empty($module))
		
		// Yii::app()->modulePath.DIRECTORY_SEPARATOR.$module
		
		
		
		
		
		
		
		// var_dump((Yii::app()->controllerMap));
		// var_dump((Yii::app()->modules));
		// var_dump($module);
		die();
			
			
			
		switch(count($path)) {
		
			case 1:		// just a top-level controller or module root
				

			case 2:		// controller/action, module/controller, module/action, or module/id

		}
		
		
		// die(var_dump($pieces));
		
		
		// if(empty($pieces))
			// return '';
		
		
		
		
	
	
		// if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
			// check $matches[1] and $matches[3] to see
			// if they match a manufacturer and a model in the database
			// If so, set $_GET['manufacturer'] and/or $_GET['model']
			// and return 'car/index'
		// }
		return false;  // this rule does not apply
	}
}














