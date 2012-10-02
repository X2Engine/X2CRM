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
 * @package X2CRM.modules.media.controllers 
 */
class MediaController extends x2base {

	public $modelClass = "Media";

	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}
	
	/**
	 * Forces download of specified media file
	 */
	public function actionDownload($id) {
		$model = $this->loadModel($id);
		$file = Yii::app()->file->set($model->getPath());
		if($file->exists)
			$file->send();
			 //Yii::app()->getRequest()->sendFile($model->fileName,@file_get_contents($fileName));
		$this->redirect(array('view','id'=>$id));
	}

	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpload() {
		$model=new Media;

		if(isset($_POST['Media'])) {
		
			$temp = TempFile::model()->findByPk($_POST['TempFileId']);
						
			$userFolder = Yii::app()->user->name; // place uploaded files in a folder named with the username of the user that uploaded the file
			$userFolderPath = 'uploads/media/'. $userFolder;
			// if user folder doesn't exit, try to create it
			if( !(file_exists($userFolderPath) && is_dir($userFolderPath)) ) {
			    if(!@mkdir('uploads/media/'. $userFolder, 0777, true)) { // make dir with edit permission
			    	// ERROR: Couldn't create user folder
			    	var_dump($userFolder);
			    	exit();
			    }
			}
			
			rename($temp->fullpath(), $userFolderPath .'/'. $temp->name);
			
			// save media info
			$model->fileName = $temp->name;
			$model->createDate = time();
			$model->lastUpdated = time();
			$model->uploadedBy = Yii::app()->user->name;
			$model->associationType = $_POST['Media']['associationType'];
			$model->associationId = $_POST['Media']['associationId'];
			$model->private = $_POST['Media']['private'];
			if($_POST['Media']['description'])
				$model->description = $_POST['Media']['description'];
			
			if($model->save()) {
				$this->redirect(array('view','id'=>$model->id));
			}
		
		}

		$this->render('upload',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);

		if(isset($_POST['Media'])) {
			// save media info
			$model->lastUpdated = time();
			$model->associationType = $_POST['Media']['associationType'];
			$model->associationId = $_POST['Media']['associationId'];
			$model->private = $_POST['Media']['private'];
			if($_POST['Media']['description'])
				$model->description = $_POST['Media']['description'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$model=$this->loadModel($id);
			if(file_exists("uploads/{$model->uploadedBy}/{$model->fileName}"))
				unlink("uploads/{$model->uploadedBy}/{$model->fileName}");
			else if(file_exists("uploads/{$model->fileName}")) 
				unlink("uploads/{$model->fileName}");
			$model->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		}
		else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Media('search');
		if(isset($_GET['Media']))
			$model->attributes=$_GET['Media'];
		$this->render('index',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Media::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='media-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	
	public function actionToggleUserMediaVisible($user) {
		$widgetSettings = json_decode(Yii::app()->params->profile->widgetSettings, true);
		$mediaSettings = $widgetSettings['MediaBox'];
		$hideUsers = $mediaSettings['hideUsers'];
		$ret = '';
		
		if(($key = array_search($user, $hideUsers)) !== false) { // user is not visible, make them visible
			unset($hideUsers[$key]);
			$hideUsers = array_values($hideUsers); // reindex array so json is consistent
			$ret = 1;
		} else { // user is visible, make them not visible
			$hideUsers[] = $user;
			$ret = 0;
		}
						
		$mediaSettings['hideUsers'] = $hideUsers;
		$widgetSettings['MediaBox'] = $mediaSettings;
		Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
		Yii::app()->params->profile->update();
		
		echo $ret;
	}
}
