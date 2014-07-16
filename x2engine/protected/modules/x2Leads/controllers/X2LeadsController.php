<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * @package application.modules.x2Leads.controllers
 */
class X2LeadsController extends x2base {

    public $modelClass = 'X2Leads';

    public function accessRules() {
        return array(
            array('allow',
                'actions'=>array('getItems'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index','view','create','update','search',
                    'saveChanges','delete','shareX2Leads','inlineEmail'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','testScalability'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
                'attributesOfNewRecordToUpdate' => array (
                    'Contacts' => array (
                        'accountName' => 'company',
                    ),
                )
            ),
        ));
    }

    public function actions() {
        return array_merge(parent::actions(), array(
        ));
    }

    public function actionGetItems(){
        $sql = 'SELECT id, name as value FROM x2_x2leads WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        Yii::app()->end();
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     * @param null|Opportunity Set by actionConvertLead in the case that conversion fails
     */
    public function actionView($id, $opportunity=null) {
        $type = 'x2Leads';
        $model = $this->loadModel($id);
        if($this->checkPermissions($model,'view')){

            // add opportunity to user's recent item list
            User::addRecentItem('l', $id, Yii::app()->user->getId()); 

            parent::view($model, $type, array (
                'conversionIncompatibilityWarnings' => 
                    $model->getConversionIncompatibilityWarnings (),
                'opportunity' => 
                    $opportunity,
            ));
        }else{
            $this->redirect('index');
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new X2Leads;
        $users = User::getNames();
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id]=$group->name;
        }
        unset($users['admin']);
        unset($users['']);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['X2Leads'])) {
            $temp=$model->attributes;

            $model->setX2Fields($_POST['X2Leads']);

            if(isset($_POST['x2ajax'])) {
                $ajaxErrors = $this->quickCreate ($model);
            } else {
                if($model->save())
                    $this->redirect(array('view','id'=>$model->id));
            }
        }

        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create',array(
                'model'=>$model,
                'users'=>$users,
            ));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model=$this->loadModel($id);

        if(isset($_POST['X2Leads'])) {
            $model->setX2Fields($_POST['X2Leads']);
            if(!empty($model->associatedContacts))
                $model->associatedContacts=implode(', ',$model->associatedContacts);

            // $this->update($model,$temp);
            $model->save();
            $this->redirect(array('view','id'=>$model->id));
        }
        /* Set assignedTo back into an array only before re-rendering the input box with assignees 
           selected */
        $model->assignedTo = array_map(function($n){
            return trim($n,',');
        },explode(' ',$model->assignedTo));

        $this->render('update',array(
            'model'=>$model,
        ));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $model=new X2Leads('search');
        $this->render('index', array('model'=>$model));
    }

    public function delete($id) {
        $model = $this->loadModel($id);

        CActiveDataProvider::model('Actions')->deleteAllByAttributes(
            array('associationType'=>'X2Leads','associationId'=>$id));

        $this->cleanUpTags($model);
        $model->delete();
    }

    public function actionDelete($id) {
        $model=$this->loadModel($id);

        if(Yii::app()->request->isPostRequest) {
            $event=new Events;
            $event->type='record_deleted';
            $event->associationType=$this->modelClass;
            $event->associationId=$model->id;
            $event->text=$model->name;
            $event->user=Yii::app()->user->getName();
            $event->save();
            Actions::model()->deleteAll('associationId='.$id.' AND associationType=\'x2Leads\'');
            $this->cleanUpTags($model);
            $model->delete();
        } else
            throw new CHttpException(
                400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect 
            // the browser

        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    public function actionGetTerms(){
        $sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result); exit;
    }

    /**
     * Converts a lead to an opportunity 
     * @param int $id id of the lead
     * @param bool $force If true, lead conversion will be attempted even if there are compatibility
     *  issues.
     */
    public function actionConvertLead ($id, $force=false) {
        $model = $this->loadModel ($id);
        $newOpportunity = $model->convertToOpportunity ($force);
        if ($newOpportunity instanceof Opportunity && !$newOpportunity->hasErrors ()) {
            $this->redirect($this->createUrl (
                    '/opportunities/opportunities/view', array ('id' => $newOpportunity->id)));
        }
        $this->actionView ($id, $newOpportunity);
    }
}
