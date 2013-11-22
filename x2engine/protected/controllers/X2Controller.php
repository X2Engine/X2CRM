<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/** 
 * Base generic controller class
 * 
 * @package X2CRM.controllers
 */
abstract class X2Controller extends CController {

	/**
	 * Renders a view file.
	 * Overrides {@link CBaseController::renderFile} to check if the requested view 
	 * has a version in /custom, and uses that if it exists.
	 *
	 * @param string $viewFile view file path
	 * @param array $data data to be extracted and made available to the view
	 * @param boolean $return whether the rendering result should be returned instead of being echoed
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @throws CException if the view file does not exist
	 */
	// public function renderFile($viewFile,$data=null,$return=false) {
		// return parent::renderFile(Yii::getCustomPath($viewFile),$data,$return);
	// }
	
	
	
	/**
	 * Finds a view file based on its name.
	 * Overrides {@link CBaseController::resolveViewFile} to check if the requested view 
	 * has a version in /custom, and uses that if it exists.
	 *
	 * @param string $viewName the view name
	 * @param string $viewPath the directory that is used to search for a relative view name
	 * @param string $basePath the directory that is used to search for an absolute view name under the application
	 * @param string $moduleViewPath the directory that is used to search for an absolute view name under the current module.
	 * If this is not set, the application base view path will be used.
	 * @return mixed the view file path. False if the view file does not exist.
	 */
	
	
	public function resolveViewFile($viewName,$viewPath,$basePath,$moduleViewPath=null) {
		if(empty($viewName))
			return false;

		if($moduleViewPath===null)
			$moduleViewPath=$basePath;

		if(($renderer=Yii::app()->getViewRenderer())!==null)
			$extension=$renderer->fileExtension;
		else
			$extension='.php';
		if($viewName[0]==='/')
		{
			if(strncmp($viewName,'//',2)===0)
				$viewFile=$basePath.$viewName;
			else
				$viewFile=$moduleViewPath.$viewName;
		}
		else if(strpos($viewName,'.'))
			$viewFile=Yii::getPathOfAlias($viewName);
		else
			$viewFile=$viewPath.DIRECTORY_SEPARATOR.$viewName;

		// custom part
		$fileName = Yii::getCustomPath($viewFile.$extension);
		if(is_file($fileName)) {
			return Yii::app()->findLocalizedFile($fileName);
		} else if($extension!=='.php') {
			$fileName = Yii::getCustomPath($viewFile.'.php');
			if(is_file($fileName))
				return Yii::app()->findLocalizedFile($fileName);
		}
		return false;
	}
}