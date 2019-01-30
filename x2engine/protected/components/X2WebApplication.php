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




/**
 * X2WebApplication class file.
 * 
 * X2WebApplication extends CWebApplication to provide additional functionality.
 * 
 * @package application.modules.contacts
 *
 */
class X2WebApplication extends CWebApplication {

	/**
	 * Processes the current request.
	 * It first resolves the request into controller and action,
	 * and then creates the controller to perform the action.
	 */
	public function processRequest()
	{
		if(is_array($this->catchAllRequest) && isset($this->catchAllRequest[0]))
		{
			$route=$this->catchAllRequest[0];
			foreach(array_splice($this->catchAllRequest,1) as $name=>$value)
				$_GET[$name]=$value;
		}
		else
			$route=$this->getUrlManager()->parseUrl($this->getRequest());
		$this->runController(Fields::getPurifier()->purify($route));
	}
    
	/**
	 * Checks whether the named component has been created.
	 * @param string $id application component ID
	 * @return boolean whether the named application component has been created
	 */
	public function componentCreated($id)
	{
        $components = $this->getComponents (true);
		return isset($components[$id]);
	}

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
        
    public function onEndRequest($event) {
        if (!empty(Yii::$translationLog)) {
            $username = '';
            if (isset(Yii::$systemuser)) {
                $username = Yii::$systemuser;
            }
            if (!is_dir(Yii::app()->basePath . '/messages/log_' . $username)) {
                mkdir(Yii::app()->basePath . '/messages/log_' . $username);
            }
            foreach (Yii::$translationLog as $category => $messages) {
                $fileName = Yii::app()->basePath . '/messages/log_' . $username . '/' . $category . '.php';
                if (!is_file($fileName)) {
                    file_put_contents($fileName,
                            '<?php return ' . var_export(array(), true) . ";\n");
                }
                $messages = array_merge(require $fileName, $messages);
                file_put_contents($fileName,
                        '<?php return ' . var_export($messages, true) . ";\n");
            }
        }
        parent::onEndRequest($event);
    }

}
