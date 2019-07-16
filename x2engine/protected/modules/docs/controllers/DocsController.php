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
 * @package application.modules.docs.controllers
 */
class DocsController extends x2base {

    public $modelClass = 'Docs';

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    // public $layout='//layouts/column2';

    /**
     * @return array action filters
     */

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'createEmail', 'update', 'exportToHtml', 'delete', 'getItems', 'getItem'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionGetItems($term) {
        LinkableBehavior::getItems($term);
    }

    public function actionGetItem($id) {
        $model = $this->loadModel($id);
        if ((($model->visibility == 1 || ($model->visibility == 0 && $model->createdBy == Yii::app()->user->getName())) || Yii::app()->params->isAdmin)) {
            echo $model->text;
        }
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $model = $this->loadModel($id);
        if (!$this->checkPermissions($model, 'view')) {
            $this->denied();
        }
        

        // add doc to user's recent item list
        User::addRecentItem('d', $id, Yii::app()->user->getId());
        X2Flow::trigger('RecordViewTrigger', array('model' => $model));
        $this->render('view', array(
            'model' => $model,
        ));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionFullView($id, $json = 0, $replace = 0) {
        $model = $this->loadModel($id);
        $response = array(
            'body' => $model->text,
            'subject' => $model->subject,
            'to' => $model->emailTo
        );
        if ($replace) {
            foreach (array_keys($response) as $key) {
                $response[$key] = str_replace('{signature}', Yii::app()->params->profile->signature, $response[$key]);
            }
        }
        if ($json) {
            header('Content-type: application/json');
            echo json_encode($response);
        } else {
            echo $response['body'];
        }
    }

    /**
     * Creates a new doc.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate($duplicate = false) {
        $model = new Docs;

        if ($duplicate) {
            $copiedModel = Docs::model()->findByPk($duplicate);
            if (!empty($copiedModel)) {
                foreach ($copiedModel->attributes as $name => $value) {
                    if ($name !== 'id') {
                        $model->$name = $value;
                    }
                }
            }
            $model->name .= ' (' . Yii::t('docs', 'copy') . ')';
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Docs'])) {
            $model->setX2Fields($_POST['Docs']);
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    /**
     * Creates an email template.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreateEmail() {
        $model = new Docs;
        $model->type = 'email';
        $model->associationType = 'Contacts';

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Docs'])) {
            $model->setX2Fields($_POST['Docs']);
            $model->subject = Formatter::restoreInsertableAttributes($model->subject);
            $model->text = Formatter::restoreInsertableAttributes($model->text);
            if ($model->save()) {
                if (isset($_GET['ajax']) && $_GET['ajax']) {
                    echo CJSON::encode($model->attributes);
                    return;
                }
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function actionCreateQuote() {
        $model = new Docs;
        $model->type = 'quote';

        if (isset($_POST['Docs'])) {
            $model->setX2Fields($_POST['Docs']);
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function actionExportToHtml($id) {
        $model = $this->loadModel($id);
        $file = $this->safePath(($uid = uniqid()) . '-doc.html');
        $fp = fopen($file, 'w+');
        $data = "<style>
				#wrap{
					width:6.5in;
					height:9in;
					margin-top:auto;
					margin-left:auto;
					margin-bottom:auto;
					margin-right:auto;
				}
				</style>
				<div id='wrap'>
			" . $model->text . "</div>";
        fwrite($fp, $data);
        fclose($fp);
        $link = CHtml::link(Yii::t('app', 'Download') . '!', array('downloadExport', 'uid' => $uid, 'id' => $id));
        $this->render('export', array(
            'model' => $model,
            'link' => $link,
        ));
    }

    /**
     * Download an exported doc file.
     * @param type $uid Unique ID associated with the file
     * @param type $id ID of the doc exported
     */
    public function actionDownloadExport($uid, $id) {
        if (file_exists($this->safePath($filename = $uid . '-doc.html'))) {
            $this->sendFile($filename, false);
        } else {
            $this->redirect(array('exportToHtml', 'id' => $id));
        }
    }

    public function titleUpdate($old_title, $new_title) {
        if ((sizeof(Modules::model()->findAllByAttributes(array('name' => $new_title))) == 0) && ($old_title != $new_title)) {
            Yii::app()->db->createCommand()->update('x2_modules', array('title' => $new_title,), 'title=:old_title', array(':old_title' => $old_title));
        }
    }

    public function actionGetFolderSelector($id = null, array $selectedFolders = array()) {
        if (!$id) {
            $id = 'root';
        }
        if (is_numeric($id)) {
            $folder = DocFolders::model()->findByPk($id);
            if (!$folder) {
                throw new CHttpException(
                404, Yii::t('app', 'The requested page does not exist.'));
            }
        } elseif ($id === 'root') {
            $folder = $id;
        } else {
            throw new CHttpException(
            400, Yii::t('app', 'Bad request'));
        }
        $children = DocFolders::model()->findChildren($folder, array(
            'folder'
                ), array(
            DocFolders::TEMPLATES_FOLDER_ID,
            $id
        ));
        $dataProvider = new CArrayDataProvider($children, array(
            'id' => 'folder-selector',
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        $this->renderPartial('_folderSelector', array(
            'dataProvider' => $dataProvider,
            'folder' => $folder,
            'selectedFolders' => $selectedFolders,
                ), false, true);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        if ($model->type == null) {
            $model->scenario = 'menu';
        }
        $old_title = $model->name;
        $new_title = $old_title;

        if (isset($_POST['Docs'])) {
            $new_title = $_POST['Docs']['name'];
        }
        if (isset($_POST['Docs'])) {
            $model->attributes = $_POST['Docs'];
            $model->visibility = $_POST['Docs']['visibility'];
            if ($model->save()) {
                $this->titleUpdate($old_title, $new_title);
                $event = new Events;
                $event->associationType = 'Docs';
                $event->associationId = $model->id;
                $event->type = 'doc_update';
                $event->user = Yii::app()->user->getName();
                $event->visibility = $model->visibility;
                $event->save();
                $this->redirect(
                        array('update', 'id' => $model->id, 'saved' => true, 'time' => time())
                );
            }
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
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $model = $this->loadModel($id);
            $this->cleanUpTags($model);
            $model->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect 
            // the browser
            if (!isset($_GET['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
            }
        } else {
            throw new CHttpException(
            400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     * @param integer $id the ID of the folder to display
     */
    public function actionIndex($id = null) {
        $model = new DocFolders;
        $model->parentFolder = $id;
        if (isset($_GET['code']) && isset($_GET['state'])) {
            Yii::app()->session['dropbox_code'] = $_GET['code'];
            Yii::app()->session['dropbox_status'] = $_GET['state'];
        }
        if (Yii::app()->request->isAjaxRequest && isset($_POST['DocFolders'])) {
            $model->setAttributes($_POST['DocFolders']);
            if ($model->parentFolder == 0) {
                $model->parentFolder = null;
            }
            if ($model->save()) {
                echo CJSON::encode(array(
                    'success' => 1
                ));
            } else {
                $form = $this->renderPartial('_folderCreate', array(
                    'model' => $model
                        ), true, true);
                echo CJSON::encode(array(
                    'form' => $form
                ));
                Yii::app()->end();
            }
        } else {
            if (empty($id)) {
                $folderDataProvider = DocFolders::model()->getRootFolderContents();
            } elseif ($id == -1) {
                $folderDataProvider = DocFolders::model()->getTemplatesFolderContents();
            } else {
                $folder = DocFolders::model()->findByPk($id);
                if (!$this->checkPermissions($folder, 'view')) {
                    $this->denied();
                }
                if (isset($folder)) {
                    $folderDataProvider = $folder->getContents();
                } else {
                    throw new CHttpException(
                    404, Yii::t('app', 'The requested page does not exist.'));
                }
            }
            $attachments = new CActiveDataProvider('Media', array(
                'criteria' => array(
                    'order' => 'createDate DESC',
                    'condition' => 'associationType="docs"'
            )));

            $this->render('index', array(
                'currentFolder' => $id,
                'model' => $model,
                'folderDataProvider' => $folderDataProvider,
                'attachments' => $attachments,
            ));
        }
    }

    public function actionMoveFolder($type, $objId, $destId = null) {
        if ($destId == -1) {
            $destination = null;
        } else {
            $destination = DocFolders::model()->findByPk($destId);
        }

        if ($type === 'doc') {
            $model = Docs::model()->findByPk($objId);
        } elseif ($type === 'folder') {
            $model = DocFolders::model()->findByPk($objId);
        }
        if (!isset($model)) {
            throw new CHttpException(404, Yii::t('docs', 'Object or destination not found.'));
        }
        if (!$this->checkPermissions($model, 'edit') ||
                ($destination instanceof DocFolders) &&
                !$this->checkPermissions($destination, 'edit')) {

            $this->denied();
        }
        if ($model->moveTo($destination)) {
            echo 1;
        }
    }

    public function actionDeleteFileFolder() {
        if (Yii::app()->request->isAjaxRequest && isset($_POST['type'], $_POST['id'])) {
            if ($_POST['type'] === 'folder') {
                $model = DocFolders::model()->findByPk($_POST['id']);
                if (is_null($model)) {
                    throw new CHttpException(404, 'Folder not found.');
                }
                if (!$model->checkRecursiveDeletePermissions()) {
                    $this->denied();
                }
            } elseif ($_POST['type'] === 'doc') {
                $model = Docs::model()->findByPk($_POST['id']);
                if (is_null($model)) {
                    throw new CHttpException(404, 'File not found.');
                }
                if (!$this->checkPermissions($model, 'delete')) {
                    $this->denied();
                }
            } else {
                throw new CHttpException(400, 'Bad request.');
            }
            $model->delete();
        } else {
            throw new CHttpException(400, 'Bad request.');
        }
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'docs-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionAutosave($id) {
        $model = $this->loadModel($id);

        $old_title = $model->name;
        $new_title = $old_title;
        if (isset($_POST['Docs'])) {
            $new_title = $_POST['Docs']['name'];
        }

        if (isset($_POST['Docs'])) {
            $model->attributes = $_POST['Docs'];
            // $model = $this->updateChangeLog($model,'Edited');

            if ($model->save()) {
                if ($old_title != $new_title) {
                    $this->titleUpdate($old_title, $new_title);
                }
                echo Yii::t('docs', 'Saved at') . ' ' . Yii::app()->dateFormatter->format(Yii::app()->locale->getTimeFormat('medium'), time());
            };
        }
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'ImportExportBehavior' => array('class' => 'ImportExportBehavior'),
        ));
    }

    /**
     * Create a menu for docs
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Docs = Modules::displayName();
        $Doc = Modules::displayName(false);
        $user = Yii::app()->user->name;
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'createEmail', 'createQuote', 'view', 'edit', 'delete',
         *     'permissions', 'exportToHtml', 'import', 'export',
         * );
         */
        $menuItems = array(
            array(
                'name' => 'index',
                'label' => Yii::t('docs', 'List {module}', array(
                    '{module}' => $Docs,
                )),
                'url' => array('index')
            ),
            array(
                'name' => 'create',
                'label' => Yii::t('docs', 'Create {module}', array(
                    '{module}' => $Doc,
                )),
                'url' => array('create')
            ),
            array(
                'name' => 'createEmail',
                'label' => Yii::t('docs', 'Create Email'),
                'url' => array('createEmail')
            ),
            array(
                'name' => 'createQuote',
                'label' => Yii::t('docs', 'Create {quote}', array(
                    '{quote}' => Modules::displayName(false, "Quotes"),
                )),
                'url' => array('createQuote')
            ),
            array(
                'name' => 'view',
                'label' => Yii::t('docs', 'View'),
                'url' => array('view', 'id' => $modelId)
            ),
            array(
                'name' => 'edit',
                'label' => Yii::t('docs', 'Edit {doc}', array(
                    '{doc}' => $Doc,
                )),
                'url' => array('update', 'id' => $modelId)
            ),
            array(
                'name' => 'delete',
                'label' => Yii::t('docs', 'Delete {doc}', array(
                    '{doc}' => $Doc,
                )),
                'url' => 'javascript:void(0);',
                'linkOptions' => array(
                    'submit' => array('delete', 'id' => $modelId),
                    'confirm' => Yii::t('docs', 'Are you sure you want to delete this item?')
                ),
            ),
            array(
                'name' => 'exportToHtml',
                'label' => Yii::t('docs', 'Export {doc}', array(
                    '{doc}' => $Doc,
                )),
                'url' => array('exportToHtml', 'id' => $modelId)
            ),
            array(
                'name' => 'import',
                'label' => Yii::t('docs', 'Import {module}', array(
                    '{module}' => $Docs,
                )),
                'url' => array('admin/importModels', 'model' => 'Docs'),
            ),
            array(
                'name' => 'export',
                'label' => Yii::t('docs', 'Export {module}', array(
                    '{module}' => $Docs,
                )),
                'url' => array('admin/exportModels', 'model' => 'Docs'),
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
