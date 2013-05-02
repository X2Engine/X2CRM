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
			$model->path; // File type setter is embedded in the magic getter for path
			if($_POST['Media']['description'])
				$model->description = $_POST['Media']['description'];
			
			if($model->save()) {
                if(!empty($model->associationType) && !empty($model->associationId) && is_numeric($model->associationId)){
                    $note=new Actions;
					$note->createDate = time();
					$note->dueDate = time();
					$note->completeDate = time();
					$note->complete='Yes';
					$note->visibility='1';
					$note->completedBy=Yii::app()->user->getName();
					if($model->private) {
						$note->assignedTo = Yii::app()->user->getName();
						$note->visibility = '0';
					} else {
						$note->assignedTo='Anyone';
					}
					$note->type='attachment';
					$note->associationId=$model->associationId;
					$note->associationType=$model->associationType;
                    if($modelName=X2Model::getModelName($model->associationType)){
                        $association =X2Model::model($modelName)->findByPk($model->associationId);
                        if($association != null){
                                $note->associationName = $association->name;
                        }
                    }
					$note->actionDescription = $model->fileName . ':' . $model->id;
                    $note->save();
                }
				$this->redirect(array('view','id'=>$model->id));
			}
		
		}

		$this->render('upload',array(
			'model'=>$model,
		));
	}
	
	public function actionQtip($id) {
		$model = Media::model()->findByPk($id);
		$this->renderPartial('qtip',array('model'=>$model));
	}
	/**
	 * Creates a new media object via an ajax upload
	 * 
	 */
	public function actionAjaxUpload() {
	
		$fileUrl = '';
	
		try {
			if(Yii::app()->user->isGuest)
				throw new Exception('You are not logged in.');

			if(!isset($_FILES['upload'],$_GET['CKEditorFuncNum']))	//,$_GET['Media']
				throw new Exception('Invalid request.');

			$upload = CUploadedFile::getInstanceByName('upload');
			
			if($upload == null)
				throw new Exception('Invalid file.');
			
			$fileName = $upload->getName();
			$fileName = str_replace(' ','_',$fileName);

			$userFolder = Yii::app()->user->name; // place uploaded files in a folder named with the username of the user that uploaded the file
			$userFolderPath = 'uploads/media/'. $userFolder;
			// if user folder doesn't exit, try to create it
			if( !(file_exists($userFolderPath) && is_dir($userFolderPath)) ) {
				if(!@mkdir('uploads/media/'. $userFolder, 0777, true)) { // make dir with edit permission
					throw new Exception('Error creating user folder.');
					// ERROR: Couldn't create user folder
					// var_dump($userFolder);
					// exit();
				}
			}
			
			if(!$upload->saveAs($userFolderPath.DIRECTORY_SEPARATOR.$fileName))
				throw new Exception('Error saving file');

			// save media info
			$model = new Media;
			$model->fileName = $fileName;
			$model->createDate = time();
			$model->lastUpdated = time();
			$model->uploadedBy = Yii::app()->user->name;
			// $model->associationType = $_GET['Media']['associationType'];
			// $model->associationId = $_GET['Media']['associationId'];
			$model->private = true; //$_GET['Media']['private'];
			// if($_POST['GET']['description'])
				// $model->description = $_POST['Media']['description'];
			
			if(!$model->save())
				throw new Exception('Error saving Media entry');
			
			
			$fileUrl = $model->getFullUrl();

		} catch(Exception $e) {
			echo '<html><body><script type="text/javascript">',
				'window.parent.CKEDITOR.tools.callFunction(',$_GET['CKEditorFuncNum'],',"","',$e->getMessage(),'");',
				'</script></body></html>';
			return;
		}
		echo '<html><body><script type="text/javascript">',
			'window.parent.CKEDITOR.tools.callFunction(',$_GET['CKEditorFuncNum'],',"',$fileUrl,'","");',
			'</script></body></html>';
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
		if(isset($_GET['Media'])){
            foreach($_GET['Media'] as $key=>$value){
                if($model->hasAttribute($key))
                    $model->$key=$value;
            }
        }
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
