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

/**
 * X2WebApplication class file.
 * 
 * X2WebApplication extends CWebApplication to provide additional functionality.
 * @property string $absoluteBaseUrl (read-only) the base URL of the web
 *  application, independent of whether there is a web request.
 * @property string $externalAbsoluteBaseUrl (read-only) The absolute base URL
 *  of the application to use when creating URLs to be viewed publicly, from
 *  the internet (i.e. the web lead capture form, email tracking links, etc.)
 * @property string $externalWebRoot (read-only) The web root of public-facing
 *  URLs.
 * @property integer|bool $locked Integer (timestamp) if the application is
 *  locked; false otherwise.
 * @property string $lockFile Path to the lock file
 * @package X2CRM.modules.contacts
 *
 */
class X2WebApplication extends CWebApplication {


    private $_externalAbsoluteBaseUrl;
    private $_absoluteBaseUrl;
    
    /**
     * If the application is locked, this will be an integer corresponding to
     * the date that the application was locked. Otherwise, it will be false.
     * @var mixed
     */
    private $_locked;

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

    /**
     * Creates an URL specific to 
     * @param type $url
     */
    public function createExternalUrl($route,$params=array()) {
        return $this->externalWebRoot.$this->controller->createUrl($route,$params);
    }

    /**
     * Magic getter for {@link absoluteBaseUrl}; in the case that web request data
     * isn't available, it uses a config file.
     *
     * @return type
     */
    public function getAbsoluteBaseUrl(){
        if(!isset($this->_absoluteBaseUrl)){
            if($this->params->noSession){
                $this->_absoluteBaseUrl = '';
                // Use the web API config file to construct the URL
                $file = realpath($this->basePath.'/../webLeadConfig.php');
                if($file){
                    include($file);
                    if(isset($url))
                        $this->_absoluteBaseUrl = $url;
                }
                if(!isset($this->_absoluteBaseUrl)){
                    $this->_absoluteBaseUrl = ''; // Default
                    if($this->hasProperty('request')){
                        // If this is an API request, there is still hope yet to resolve it
                        try{
                            $this->_absoluteBaseUrl = $this->request->getBaseUrl(1);
                        }catch(Exception $e){

                        }
                    }
                }
            }else{
                $this->_absoluteBaseUrl = $this->baseUrl;
            }
        }
        return $this->_absoluteBaseUrl;
    }

    /**
     * Resolves the public-facing absolute base url.
     * 
     * @return type
     */
    public function getExternalWebRoot() {
        if(!isset($this->_externalAbsoluteBaseUrl)) {
            $eabu = $this->params->admin->externalBaseUrl;
            $this->_externalAbsoluteBaseUrl = $eabu ? $eabu : $this->request->getHostInfo();
        }
        return $this->_externalAbsoluteBaseUrl;
    }

    public function getExternalAbsoluteBaseUrl() {
        return $this->externalWebRoot.$this->baseUrl;
    }

    /**
     * Returns the lock status of the application.
     * @return boolean
     */
    public function getLocked() {
        if(!isset($this->_locked)){
            $file = $this->lockFile;
            if(!file_exists($file))
                return false;
            $this->_locked = (int) trim(file_get_contents($file));
        }
        return $this->_locked;
    }

    /**
     * Returns the path to the application lock file
     * @return type
     */
    public function getLockFile() {
        return implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'runtime','x2crm.lock'));
    }

    /**
     * Lock the application (non-administrative users cannot use it).
     *
     * If the value evaluates to false, the application will be unlocked.
     * 
     * @param type $value
     */
    public function setLocked($value) {
        $this->_locked = $value;
        $file = $this->lockFile;
        if($value == false && file_exists($file)) {
            unlink($file);
        } elseif($value !== false) {
            file_put_contents($this->lockFile,$value);
        }

    }
}
