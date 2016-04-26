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
                'attributesOfNewRecordToUpdate' => array (
                    'Contacts' => array (
                        'accountName' => 'company',
                    ),
                )
            ),
        ));
    }


    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
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

            if ($model->save())
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
     * Create a menu for Leads
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Leads = Modules::displayName();
        $Lead = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete', 'attach', 'quotes',
         *     'convert', 'print', 'import', 'export',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('x2Leads','{leads} List', array(
                    '{leads}' => $Leads,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('x2Leads','Create {lead}', array(
                    '{lead}' => $Lead,
                )),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('x2Leads','Edit {lead}', array(
                    '{lead}' => $Lead,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('x2Leads','Delete {lead}', array(
                    '{lead}' => $Lead,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?')
            ),
            ModelFileUploader::menuLink(),
            array(
                'name'=>'quotes',
                'label' => Yii::t('quotes', 'Quotes/Invoices'),
                'url' => 'javascript:void(0)',
                'linkOptions' => array(
                    'onclick' => 'x2.inlineQuotes.toggle(); return false;')
            ),
            array(
                'name'=>'convertToContact',
                'label' => Yii::t('x2Leads', 'Convert to {contact}', array(
                    '{contact}' => Modules::displayName(false, "Contacts"),
                )),
                'url' => '#',
                'linkOptions' => array ('id' => 'convert-lead-to-contact-button'),
            ),
            array(
                'name'=>'convert',
                'label' => Yii::t('x2Leads', 'Convert to {opportunity}', array(
                    '{opportunity}' => Modules::displayName(false, "Opportunities"),
                )),
                'url' => '#',
                'linkOptions' => array ('id' => 'convert-lead-button'),
            ),
            array(
                'name'=>'print',
                'label' => Yii::t('app', 'Print Record'),
                'url' => '#',
                'linkOptions' => array (
                    'onClick'=>"window.open('".
                        Yii::app()->createUrl('/site/printRecord', array (
                            'modelClass' => 'X2Leads',
                            'id' => $modelId,
                            'pageTitle' => Yii::t('app', 'Leads').': '.(isset($model) ?
                                $model->name : "")
                        ))."');"
	            )
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('x2Leads', 'Import {leads}', array(
                    '{leads}' => $Leads,
                )),
                'url'=>array('admin/importModels', 'model'=>'X2Leads'),
                'visible'=>Yii::app()->params->isAdmin
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('x2Leads', 'Export {leads}', array(
                    '{leads}' => $Leads,
                )),
                'url'=>array('admin/exportModels', 'model'=>'X2Leads'),
                'visible'=>Yii::app()->params->isAdmin
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
