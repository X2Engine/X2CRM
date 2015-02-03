<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * @package application.modules.media.controllers
 */
class MediaController extends x2base {

    public $modelClass = "Media";

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            /*
            uncomment when media module supports custom forms
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),*/
        ));
    }

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){

        // add media object to user's recent item list
        User::addRecentItem('m', $id, Yii::app()->user->getId()); 

        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Forces download of specified media file
     */
    public function actionDownload($id){
        $model = $this->loadModel($id);
        $filePath = $model->getPath();
        if ($filePath != null)
            $file = Yii::app()->file->set($filePath);
        else
            throw new CHttpException(404);
        if($file->exists)
            $file->send();
        //Yii::app()->getRequest()->sendFile($model->fileName,@file_get_contents($fileName));
        $this->redirect(array('view', 'id' => $id));
    }

    /**
     * Alias for actionUpload
     */
    public function actionCreate () {
        $this->actionUpload ();
    }

    private function createAttachmentAction ($model) {
        if(!empty($model->associationType) && !empty($model->associationId) && 
            is_numeric($model->associationId)){

            $note = new Actions;
            $note->createDate = time();
            $note->dueDate = time();
            $note->completeDate = time();
            $note->complete = 'Yes';
            $note->visibility = '1';
            $note->completedBy = Yii::app()->user->getName();
            if($model->private){
                $note->assignedTo = Yii::app()->user->getName();
                $note->visibility = '0';
            }else{
                $note->assignedTo = 'Anyone';
            }
            $note->type = 'attachment';
            $note->associationId = $model->associationId;
            $note->associationType = $model->associationType;
            if($modelName = X2Model::getModelName($model->associationType)){
                $association = X2Model::model($modelName)->findByPk($model->associationId);
                if($association != null){
                    $note->associationName = $association->name;
                }
            }
            $note->actionDescription = $model->fileName.':'.$model->id;
            return $note->save();
        }
        return false;
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionUpload(){
        $model = new Media;

        if(isset($_POST['Media'])){

            $temp = TempFile::model()->findByPk($_POST['TempFileId']);

            $userFolder = Yii::app()->user->name; // place uploaded files in a folder named with the username of the user that uploaded the file
            $userFolderPath = 'uploads/media/'.$userFolder;
            // if user folder doesn't exit, try to create it
            if(!(file_exists($userFolderPath) && is_dir($userFolderPath))){
                if(!@mkdir('uploads/media/'.$userFolder, 0777, true)){ // make dir with edit permission
                    // ERROR: Couldn't create user folder
                    var_dump($userFolder);
                    exit();
                }
            }

            rename($temp->fullpath(), $userFolderPath.'/'.$temp->name);

            // save media info
            $model->fileName = $temp->name;
            $model->createDate = time();
            $model->lastUpdated = time();
            $model->uploadedBy = Yii::app()->user->name;
            $model->associationType = $_POST['Media']['associationType'];
            $model->associationId = $_POST['Media']['associationId'];
            $model->private = $_POST['Media']['private'];
            $model->path; // File type setter is embedded in the magic getter for path
            $model->name = $_POST['Media']['name'];
            if (empty($model->name))
                $model->name = $model->fileName;
            if($_POST['Media']['description'])
                $model->description = $_POST['Media']['description'];

            /*     
            uncomment when media module supports custom forms
            if(isset($_POST['x2ajax'])){
                $ajaxErrors = $this->quickCreate ($model);
                if (!$ajaxErrors) {
                    $this->createAttachmentAction ($model);
                }
            }else{*/
                if($model->save()){
                    $this->createAttachmentAction ($model);
                    $this->redirect(array('view', 'id' => $model->id));
                }
            //}
        }

        /*
        uncomment when media module supports custom forms
        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        } else {*/
            $this->render('upload', array(
                'model' => $model,
            ));
        //}
    }

    public function actionQtip($id){
        $model = Media::model()->findByPk($id);
        $this->renderPartial('qtip', array('model' => $model));
    }

    /**
     * Creates a new media object via an ajax upload
     *
     */
    public function actionAjaxUpload(){

        $fileUrl = '';

        try{
            if(Yii::app()->user->isGuest)
                throw new Exception('You are not logged in.');

            if(!isset($_FILES['upload'], $_GET['CKEditorFuncNum'])) //,$_GET['Media']
                throw new Exception('Invalid request.');

            $upload = CUploadedFile::getInstanceByName('upload');

            if($upload == null)
                throw new Exception('Invalid file.');

            $fileName = $upload->getName();
            $fileName = str_replace(' ', '_', $fileName);

            $userFolder = Yii::app()->user->name; // place uploaded files in a folder named with the username of the user that uploaded the file
            $userFolderPath = 'uploads/media/'.$userFolder;
            // if user folder doesn't exit, try to create it
            if(!(file_exists($userFolderPath) && is_dir($userFolderPath))){
                if(!@mkdir('uploads/media/'.$userFolder, 0777, true)){ // make dir with edit permission
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
            $model->associationType = 'none';
            // $model->associationType = $_GET['Media']['associationType'];
            // $model->associationId = $_GET['Media']['associationId'];
            $model->private = true; //$_GET['Media']['private'];
            // if($_POST['GET']['description'])
            // $model->description = $_POST['Media']['description'];

            if(!$model->save()){
                throw new Exception('Error saving Media entry');
            }


            $fileUrl = $model->getFullUrl();
        }catch(Exception $e){
            echo '<html><body><script type="text/javascript">',
            'window.parent.CKEDITOR.tools.callFunction(', json_encode($_GET['CKEditorFuncNum']), ',"","', $e->getMessage(), '");',
            '</script></body></html>';
            return;
        }
        echo '<html><body><script type="text/javascript">',
        'window.parent.CKEDITOR.tools.callFunction(', json_encode($_GET['CKEditorFuncNum']), ',"', json_encode($fileUrl), '","");',
        '</script></body></html>';
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id){
        $model = $this->loadModel($id);

        if(isset($_POST['Media'])){
            // save media info
            $model->lastUpdated = time();
            $model->associationType = $_POST['Media']['associationType'];
            $model->associationId = $_POST['Media']['associationId'];
            $model->private = $_POST['Media']['private'];
            if($_POST['Media']['description'])
                $model->description = $_POST['Media']['description'];
            if (! $model->drive) {
                // Handle setting the name if the Media isn't stored on Drive
                $model->name = $_POST['Media']['name'];
                if (empty($model->name))
                    $model->name = $model->fileName;
            }
            if($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id){
        if(Yii::app()->request->isPostRequest){
            // we only allow deletion via POST request
            $model = $this->loadModel($id);
            $model->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }
        else
            throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex(){
        $model = new Media('search');
        if(isset($_GET['Media'])){
            foreach($_GET['Media'] as $key => $value){
                if($model->hasAttribute($key))
                    $model->$key = $value;
            }
        }
        $this->render('index', array(
            'model' => $model,
        ));
    }

//    public function actionTestDrive(){
//        $admin = Yii::app()->settings;
//        if(isset($_REQUEST['logout'])){
//            unset($_SESSION['access_token']);
//        }
//        require_once('protected/components/GoogleAuthenticator.php');
//        $auth = new GoogleAuthenticator();
//        if($auth->getAccessToken()){
//            $service = $auth->getDriveService();
//        }
//        $createdFile = null;
//        if(isset($service, $_SESSION['access_token'], $_FILES['upload'])){
//            $file = new Google_DriveFile();
//            $file->setTitle($_FILES['upload']['name']);
//            $file->setDescription('Uploaded by X2Engine');
//            $file->setMimeType($_FILES['upload']['type']);
//
//            $data = file_get_contents($_FILES['upload']['tmp_name']);
//            try{
//                $createdFile = $service->files->insert($file, array(
//                    'data' => $data,
//                    'mimeType' => $_FILES['upload']['type'],
//                        ));
//                if(is_array($createdFile)){
//                    $media = new Media;
//                    $media->fileName = $createdFile['id'];
//                    $media->name = $createdFile['title'];
//                    $media->associationType = 'Contacts';
//                    $media->associationId = 955;
//                    $media->uploadedBy = Yii::app()->user->getName();
//                    $media->mimetype = $createdFile['mimeType'];
//                    $media->filesize = $createdFile['fileSize'];
//                    $media->drive = 1;
//                    $media->save();
//                }
//            }catch(Google_AuthException $e){
//                unset($_SESSION['access_token']);
//                $auth->setErrors($e->getMessage());
//                $service = null;
//                $createdFile = null;
//            }
//        }
//
//        $this->render('testDrive', array(
//            'auth' => $auth,
//            'createdFile' => $createdFile,
//            'service' => isset($service) ? $service : null,
//            'baseFolder' => isset($service) ? $this->printFolder('root', $auth) : null
//        ));
//    }

    public function actionRecursiveDriveFiles($folderId){
        $ret = $this->printFolder($folderId);
        echo $ret;
    }

    public function printFolder($folderId, $auth = null){
        if(is_null($auth)){
            $auth = new GoogleAuthenticator();
        }
        $service = $auth->getDriveService();
        try{
            if($service){
                $ret = "";
                $files = $service->files;
                $fileList = $files->listFiles(array('q' => 'trashed=false and "'.$folderId.'" in parents'));
                $folderList = array();
                $fileArray = array();
                foreach($fileList['items'] as $file){
                    if($file['mimeType'] == 'application/vnd.google-apps.folder'){
                        $folderList[] = $file;
                    }else{
                        $fileArray[] = $file;
                    }
                }
                $fileList = array_merge($folderList, $fileArray);
                foreach($fileList as $file){
                    if($file['mimeType'] == 'application/vnd.google-apps.folder'){
                        $ret .= "<div class='drive-wrapper'><div class='drive-item'><div class='drive-icon' style='background:url(\"".$file['iconLink']."\") no-repeat'></div><a href='#' class='toggle-file-system drive-link' data-id='{$file['id']}'> ".$file['title']."</a></div></div>";
                        $ret .= "<div class='drive' id='{$file['id']}' style='display:none;'>";
                        $ret .= "</div>";
                    }else{
                        $ret .= "<div class='drive-wrapper'><div class='drive-item'><div class='drive-icon' style='background:url(\"".$file['iconLink']."\") no-repeat'></div> <a class='x2-link drive-link media' href='".$file['alternateLink']."' target='_blank'>".$file['title']."</a></div></div>";
                    }
                }
                return $ret;
            }else{
                return false;
            }
        }catch(Google_AuthException $e){
            if(isset($_SESSION['access_token']) || isset($_SESSION['token'])){ // If these are set it's possible the token expired and there is a refresh token available
                $auth->flushCredentials(false); // Only flush the recently received information
                return $this->printFolder($folderId); // Try again, it will use a refresh token if available this time, otherwise it will fail.
            }else{
                $auth->flushCredentials();
                $auth->setErrors($e->getMessage());
                return false;
            }
        }catch(Google_ServiceException $e){
            $auth->setErrors($e->getMessage());
            return false;
        }
    }

    public function actionRefreshDriveCache(){
        $auth = new GoogleAuthenticator();
        if($auth->getAccessToken()){
            if(isset($_SESSION['driveFiles'])){
                unset($_SESSION['driveFiles']);
            }
            echo $_SESSION['driveFiles'] = $this->printFolder('root');
        }
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model){
        if(isset($_POST['ajax']) && $_POST['ajax'] === 'media-form'){
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionToggleUserMediaVisible($user){
        $widgetSettings = json_decode(Yii::app()->params->profile->widgetSettings, true);
        $mediaSettings = $widgetSettings['MediaBox'];
        $hideUsers = $mediaSettings['hideUsers'];
        $ret = '';

        if(($key = array_search($user, $hideUsers)) !== false){ // user is not visible, make them visible
            unset($hideUsers[$key]);
            $hideUsers = array_values($hideUsers); // reindex array so json is consistent
            $ret = 1;
        }else{ // user is visible, make them not visible
            $hideUsers[] = $user;
            $ret = 0;
        }

        $mediaSettings['hideUsers'] = $hideUsers;
        $widgetSettings['MediaBox'] = $mediaSettings;
        Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
        Yii::app()->params->profile->update();

        echo $ret;
    }

    public function actionGetItems(){
        $model = X2Model::model ($this->modelClass);
        if (isset ($model)) {
            $tableName = $model->tableName ();
            $sql = 
                'SELECT id, fileName as value
                 FROM '.$tableName.' 
                 WHERE associationType!="theme" and fileName LIKE :qterm 
                 ORDER BY fileName ASC';
            $command = Yii::app()->db->createCommand($sql);
            $qterm = $_GET['term'].'%';
            $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
            $result = $command->queryAll();
            echo CJSON::encode($result);
        }
        Yii::app()->end();
    }

    /**
     * Create a menu for Media
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Media = Modules::displayName();
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'upload', 'view', 'edit', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('media', 'All {media}', array(
                    '{media}' => $Media,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'upload',
                'label'=>Yii::t('media', 'Upload'),
                'url'=>array('upload')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('media', 'Update'),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('media', 'Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('media','Are you sure you want to delete this item?'))
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
