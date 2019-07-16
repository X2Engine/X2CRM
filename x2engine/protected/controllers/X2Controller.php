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
 * Base generic controller class
 * 
 * @package application.controllers
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
	

    public function actions () {
        return $this->getBehaviorActions ();
    }
	
	
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

    public function badRequest ($message=null) {
        throw $this->badRequestException ($message);
    }

    public function redirectToLogin () {
        if (Yii::app()->params->isMobileApp) {
            $this->redirect($this->createUrl('/mobile/login'));
        } else {
            $this->redirect($this->createUrl('/site/login'));
        }
    }

    /**
     * Set fields of model using uploaded files in super global
     * @param bool $merge if true, files will be merged with existing values
     */
    public function setFileFields ($model, $merge=false) {
        if (isset ($_FILES[get_class ($model)])) {
            $files = $_FILES[get_class ($model)]; 
            $attributes = array_keys ($files['name']);
            foreach ($attributes as $attr) {
                if ($merge) {
                    $model->$attr = array_merge (
                        is_array ($model->$attr) ? $model->$attr : array (),
                        CUploadedFile::getInstances ($model, $attr));
                } else {
                    $model->$attr = CUploadedFile::getInstance ($model, $attr);
                }
            }
        }
    }

    /**
     * @return CHttpException 
     */
    protected function badRequestException ($message=null) {
        if ($message === null) $message = Yii::t('app', 'Bad request.');
        return new CHttpException (400, $message);
    }


    /**
     * More reliable alternative to CHttpRequest::getIsAjaxRequest in cases where 'x2ajax' or
     * 'ajax' parameters are being used.
     * See http://www.yiiframework.com/forum/index.php?/topic/4945-yiiapp-request-isajaxrequest/
     */
    public function isAjaxRequest () {
        return 
            Yii::app()->request->getIsAjaxRequest () ||
            isset ($_POST['x2ajax']) && $_POST['x2ajax'] || 
            isset ($_POST['ajax']) && $_POST['ajax'] || 
            isset ($_GET['x2ajax']) && $_GET['x2ajax'] || 
            isset ($_GET['ajax']) && $_GET['ajax'];
    }

    /**
     * Rejects ajax requests to non-mobile actions from X2Touch.
     */
    protected function validateMobileRequest ($action) {
        if (!Yii::app()->isMobileApp () || !$this->isAjaxRequest ()) return;

        $whitelist = array ('getItems', 'error');
        if (!in_array ($action->getId (), $whitelist) &&
            !($this instanceof MobileController) &&
            (!$this->asa ('MobileControllerBehavior') ||
             !$this->asa ('MobileControllerBehavior')->hasMobileAction ($action->getId ()))) {

             throw new CHttpException (400, Yii::t('app', 'Bad request.'));
        }
    }

    protected function beforeAction ($action) {
        $this->validateMobileRequest ($action);
        $run = $this->runBehaviorBeforeActionHandlers ($action);
        return $run;
    }

    protected function runBehaviorBeforeActionHandlers ($action) {
        $run = true;
        foreach ($this->behaviors () as $name => $config) {
            if ($this->asa ($name) && $this->asa ($name)->getEnabled () && 
                $this->asa ($name) instanceof ControllerBehavior) {

                $run &= $this->asa ($name)->beforeAction ($action);
            }
            if (!$run) break;
        }
        return $run;
    }

    protected function getBehaviorActions () {
        $actions = array ();
        foreach ($this->behaviors () as $name => $config) {
            if ($this->asa ($name) && $this->asa ($name)->getEnabled () && 
                $this->asa ($name) instanceof ControllerBehavior) {

                $actions = array_merge ($this->asa ($name)->actions (), $actions);
            }
        }
        return $actions;
    }



}
