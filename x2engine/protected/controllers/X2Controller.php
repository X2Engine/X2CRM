<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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