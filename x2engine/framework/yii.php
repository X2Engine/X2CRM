<?php
/**
 * Yii bootstrap file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package system
 * @since 1.0
 */

require(dirname(__FILE__).'/YiiBase.php');

/**
 * Yii is a helper class serving common framework functionalities.
 *
 * It encapsulates {@link YiiBase} which provides the actual implementation.
 * By writing your own Yii class, you can customize some functionalities of YiiBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system
 * @since 1.0
 */
class Yii extends YiiBase
{
    public static $paths = array();
	protected static $rootPath;

	public static function getRootPath() {
        if (YII_DEBUG && YII_UNIT_TESTING) { 
            // resets root path to the webroot so that custom files can be detected
            $path = array ();
            exec ('pwd', $path);
            self::$rootPath = dirname (preg_replace ('/\/tests/', '', $path[0]));
        } elseif (!isset(self::$rootPath)) {
			self::$rootPath = dirname(self::app()->request->scriptFile);
		}

		return self::$rootPath;
	}

	/**
	 * Extends {@link YiiBase::createWebApplication()} to use X2WebApplication
	 * @param mixed $config application configuration.
	 * @return X2WebApplication
	 */
	public static function createWebApplication($config=null) {
		require(implode(DIRECTORY_SEPARATOR,array(
            __DIR__,
            '..',
            'protected',
            'components',
            'X2WebApplication.php'
        )));
		return parent::createApplication('X2WebApplication',$config);
	}

	/**
	 * Checks if a custom version of a file exists
	 *
	 * @param String $path The file path
	 * @return String $path The original file path, or the version in /custom if it exists
	 */
	public static function getCustomPath($path) {
		//calculate equivalent path in /custom, ie. from [root]/[path] to [root]/custom/[path]
		$customPath = str_replace(self::getRootPath(),self::getRootPath().DIRECTORY_SEPARATOR.'custom',$path);

		if(file_exists($customPath))
			$path = $customPath;
		return $path;
	}

	/**
	 * Checks if a custom version of a class file exists
	 *
	 * @param String $path The path to something in /custom
	 * @return String $path The path to the original file or folder
	 */
	public static function resetCustomPath($customPath) {
		return str_replace(self::getRootPath().DIRECTORY_SEPARATOR.'custom',self::getRootPath(),$customPath);
	}

	/**
	 * Imports a class or a directory.
	 * Overrides {@link YiiBase::import()} to check in /custom for all imported classes
	 *
	 * @param string $alias path alias to be imported
	 * @param boolean $forceInclude whether to include the class file immediately. If false, the class file
	 * will be included only when the class is being used. This parameter is used only when
	 * the path alias refers to a class.
	 * @return string the class name or the directory that this alias refers to
	 * @throws CException if the alias is invalid
	 */
	public static function import($alias,$forceInclude=false)
	{
		if(isset(self::$_imports[$alias]))  // previously imported
			return self::$_imports[$alias];

		if(class_exists($alias,false) || interface_exists($alias,false))
			return self::$_imports[$alias]=$alias;

		if(($pos=strrpos($alias,'\\'))!==false) // a class name in PHP 5.3 namespace format
		{
			$namespace=str_replace('\\','.',ltrim(substr($alias,0,$pos),'\\'));
			if(($path=self::getPathOfAlias($namespace))!==false)
			{
				$classFile=$path.DIRECTORY_SEPARATOR.substr($alias,$pos+1).'.php';
				if($forceInclude)
				{
					if(is_file($classFile))
						require(self::getCustomPath($classFile));
					else
						throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing PHP file.',array('{alias}'=>$alias)));
					self::$_imports[$alias]=$alias;
				}
				else
					self::$classMap[$alias]=$classFile;
				return $alias;
			}
			else
				throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory.',
					array('{alias}'=>$namespace)));
		}

		if(($pos=strrpos($alias,'.'))===false)  // a simple class name
		{
			if($forceInclude && self::x2_autoload($alias))
				self::$_imports[$alias]=$alias;
			return $alias;
		}

		$className=(string)substr($alias,$pos+1);
		$isClass=$className!=='*';

		if($isClass && (class_exists($className,false) || interface_exists($className,false)))
			return self::$_imports[$alias]=$className;

		if(($path=self::getPathOfAlias($alias))!==false)
		{
			if($isClass)
			{
				if($forceInclude)
				{
					if(is_file($path.'.php'))
						require(self::getCustomPath($path.'.php'));
					else
						throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing PHP file.',array('{alias}'=>$alias)));
					self::$_imports[$alias]=$className;
				}
				else
					self::$classMap[$className]=$path.'.php';
				return $className;
			}
			else  // a directory
			{
				if(self::$_includePaths===null)
				{
					self::$_includePaths=array_unique(explode(PATH_SEPARATOR,get_include_path()));
					if(($pos=array_search('.',self::$_includePaths,true))!==false)
						unset(self::$_includePaths[$pos]);
				}

				array_unshift(self::$_includePaths,$path);

				if(self::$enableIncludePath && set_include_path('.'.PATH_SEPARATOR.implode(PATH_SEPARATOR,self::$_includePaths))===false)
					self::$enableIncludePath=false;

				return self::$_imports[$alias]=$path;
			}
		}
		else
			throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
				array('{alias}'=>$alias)));
	}

	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 * @param string $className class name
	 * @return boolean whether the class has been loaded successfully
	 */
	public static function x2_autoload($className)
	{
		// use include so that the error PHP file may appear
		if(isset(self::$classMap[$className]))
			include(self::getCustomPath(self::$classMap[$className]));
		else if(isset(self::$_coreClasses[$className]))
			include(YII_PATH.self::$_coreClasses[$className]);
		else
		{
			// include class file relying on include_path
			if(strpos($className,'\\')===false)  // class without namespace
			{
				if(self::$enableIncludePath===false)
				{
					foreach(self::$_includePaths as $path)
					{
						$classFile=$path.DIRECTORY_SEPARATOR.$className.'.php';
						if(is_file($classFile))
						{
							include(self::getCustomPath($classFile));
							break;
						}
					}
				}
				else
					include(self::getCustomPath($className.'.php'));
			}
			else  // class name with namespace in PHP 5.3
			{
				$namespace=str_replace('\\','.',ltrim($className,'\\'));
				if(($path=self::getPathOfAlias($namespace))!==false)
					include(self::getCustomPath($path.'.php'));
				else
					return false;
			}
			return class_exists($className,false) || interface_exists($className,false);
		}
		return true;
	}

	public static function t($category,$message,$params=array(),$source=null,$language=null) {
		if(isset($_GET['t']) && $_GET['t'])
			return '<dt class="yii-t">'
				.CHtml::hiddenField('cat',$category)
				.CHtml::hiddenField('msg',$message)
				.parent::t($category,$message,$params,$source,$language)
				.'</dt>';

		else
			return parent::t($category,$message,$params,$source,$language);
	}
}
