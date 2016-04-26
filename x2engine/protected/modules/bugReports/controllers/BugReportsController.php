<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

class BugReportsController extends x2base {
    public $modelClass = 'BugReports';

    public function behaviors () {
         return array_merge (parent::behaviors (), array (
            'MobileControllerBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
            ),
            'MobileActionHistoryBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.MobileActionHistoryBehavior'
            ),
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),
         ));
    }

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        User::addRecentItem ('BugReports', $id);
        $type='BugReports';
        $model=$this->loadModel($id);
        if($this->checkPermissions($model,'view')) {
            parent::view($model, $type);
        }else{
            $this->redirect('index');
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new BugReports;
        $users=User::getNames();

        if(isset($_POST['BugReports'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['BugReports']);
            if (isset($_POST['x2ajax'])) {
                $ajaxErrors = $this->quickCreate($model);
            } else {
                parent::create($model, $temp, 0);
            }
        }

        if (isset($_POST['x2ajax'])) {
            $this->renderInlineCreateForm($model, isset($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create', array(
                'model' => $model,
                'users' => $users,
            ));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users = User::getNames();

        if(isset($_POST['BugReports'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['BugReports']);
            parent::update($model,$temp,'0');
        }

        $this->render('update',array(
            'model'=>$model,
            'users'=>$users,
        ));
    }
    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $model=$this->loadModel($id);
            $this->cleanUpTags($model);
            $model->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $model=new BugReports('search');
        $this->render('index', array('model'=>$model));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new BugReports('search');
        $this->render('admin', array('model'=>$model));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model=BugReports::model()->findByPk((int)$id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='bugReports-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     *  Show or hide a certain status in the gridview
     *
     *  Called through ajax with a status and if that status should be shown or hidden.
     *  Saves the result in the user's profile.
     *
     */
    public function actionStatusFilter() {

        if(isset($_POST['all'])) {    // show all the things!!
            Yii::app()->params->profile->hideBugsWithStatus = CJSON::encode(array());    // hide none
            Yii::app()->params->profile->update(array('hideBugsWithStatus'));

        } elseif(isset($_POST['none'])) {    // hide all the things!!!!11
            $statuses = array();

            $dropdownId = Yii::app()->db->createCommand()    // get the ID of the statuses dropdown via fields table
                ->select('linkType')
                ->from('x2_fields')
                ->where('modelName="BugReports" AND fieldName="status" AND type="dropdown"')
                ->queryScalar();
            if($dropdownId !== null)
                $statuses = Dropdowns::getItems($dropdownId);    // get the actual statuses

            Yii::app()->params->profile->hideBugsWithStatus = CJSON::encode($statuses);
            Yii::app()->params->profile->update(array('hideBugsWithStatus'));

        } elseif(isset($_POST['checked'])) {

            $checked = CJSON::decode($_POST['checked']);
            $status = isset($_POST['status'])? $_POST['status'] : false;

            // var_dump($checked);
            // var_dump($status);

            $hideStatuses = CJSON::decode(Yii::app()->params->profile->hideBugsWithStatus); // get a list of statuses the user wants to hide
            if($hideStatuses === null || !is_array($hideStatuses))
                $hideStatuses = array();

            // var_dump($checked);
            // var_dump(in_array($status, $hideStatuses));
            if($checked && ($key = array_search($status, $hideStatuses)) !== false) { // if we want to show the status, and it's not being shown
                unset($hideStatuses[$key]); // show status
            } else if(!$checked && !in_array($status, $hideStatuses)) { // if we want to hide the status, and it's not being hidden
                $hideStatuses[] = $status;
            }

            Yii::app()->params->profile->hideBugsWithStatus = CJSON::encode($hideStatuses);
            Yii::app()->params->profile->update(array('hideBugsWithStatus'));
        }
    }
}
