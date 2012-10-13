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
 * ****************************************************************************** */

/**
 * X2WebApplication class file.
 * 
 * X2WebApplication extends CWebApplication to provide additional functionality.
 * 
 * @package X2CRM.modules.contacts 
 */
class X2WebApplication extends CWebApplication {
	/**
	 * Creates a controller instance based on a route.
	 * Modified to check in /custom for controller files.
	 * See {@link CWebApplication::createController()} for details.
	 *
	 * @param string $route the route of the request.
	 * @param CWebModule $owner the module that the new controller will belong to. Defaults to null, meaning the application
	 * instance is the owner.
	 * @return array the controller instance and the action ID. Null if the controller class does not exist or the route is invalid.
	 */
	public function createController($route,$owner=null)
	{
		if($owner===null)
			$owner=$this;
		if(($route=trim($route,'/'))==='')
			$route=$owner->defaultController;
		$caseSensitive=$this->getUrlManager()->caseSensitive;

		$route.='/';
		while(($pos=strpos($route,'/'))!==false)
		{
			$id=substr($route,0,$pos);
			if(!preg_match('/^\w+$/',$id))
				return null;
			if(!$caseSensitive)
				$id=strtolower($id);
			$route=(string)substr($route,$pos+1);
			if(!isset($basePath))  // first segment
			{
				if(isset($owner->controllerMap[$id]))
				{
					return array(
						Yii::createComponent($owner->controllerMap[$id],$id,$owner===$this?null:$owner),
						$this->parseActionParams($route),
					);
				}

				if(($module=$owner->getModule($id))!==null) {
				
					// fix module's base paths in case module was loaded from /custom
					$module->basePath = Yii::resetCustomPath($module->basePath);
					$module->viewPath = Yii::resetCustomPath($module->viewPath);
					Yii::setPathOfAlias($module->getId(),$module->basePath);
				
					return $this->createController($route,$module);
				}
				$basePath=$owner->getControllerPath();
				$controllerID='';
			}
			else
				$controllerID.='/';
			$className=ucfirst($id).'Controller';

			$classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';
			
			$extendedClassFile = Yii::getCustomPath($basePath.DIRECTORY_SEPARATOR.'My'.$className.'.php');
			
			if(is_file($extendedClassFile)) {					// see if there's an extended controller in /custom
				if(!class_exists($className,false))
					require(Yii::getCustomPath($classFile));	// import the 'real' controller
				$className = 'My'.$className;					// add "My" to the class name
				$classFile = $extendedClassFile;
			} else {
				$classFile = Yii::getCustomPath($classFile);	// look in /custom for controller file
			}

			if(is_file($classFile)) {
				if(!class_exists($className,false))
					require($classFile);
				if(class_exists($className,false) && is_subclass_of($className,'CController'))
				{
					$id[0]=strtolower($id[0]);
					return array(
						new $className($controllerID.$id,$owner===$this?null:$owner),
						$this->parseActionParams($route),
					);
				}
				return null;
			}
			$controllerID.=$id;
			$basePath.=DIRECTORY_SEPARATOR.$id;
		}
	}
}
