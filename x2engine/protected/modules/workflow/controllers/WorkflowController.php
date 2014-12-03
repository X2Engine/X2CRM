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
 * @package application.modules.workflow.controllers 
 */
class WorkflowController extends x2base {
    public $modelClass="Workflow";

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array(
                    'index','view','getWorkflow','getStageDetails','updateStageDetails',
                    'startStage','completeStage','revertStage','getStageMembers','getStages',
                    'changeUi', 'addADeal', 'getStageNameItems', 'getStageNames'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','create','update','delete'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }
    
    // Lists all workflows
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('Workflow');
        $this->render('index',array(
            'dataProvider'=>$dataProvider,
        ));
    }
    
    public function actionAdmin(){
        $this->redirect('index');
    }

    /**
     * Displays workflow table/funnel diagram or the pipeline
     * @param int $id id of the workflow record
     */
    public function actionView($id) {

        // check for optional GET param, if it's not set, use the profile settings
        if (!isset ($_GET['perStageWorkflowView'])) {
            $perStageWorkflowView = 
                Yii::app()->params->profile->miscLayoutSettings['perStageWorkflowView'];
        } else {
            $perStageWorkflowView = $_GET['perStageWorkflowView']; 
            if ($perStageWorkflowView !== 
                Yii::app()->params->profile->miscLayoutSettings['perStageWorkflowView']) {

                $perStageWorkflowView = $perStageWorkflowView === 'true' ? true : false;
                Profile::setMiscLayoutSetting (
                    'perStageWorkflowView', $perStageWorkflowView, true);
            }
        }

        $users=isset($_GET['users'])?$_GET['users']:''; 

        if(isset($_GET['stage']) && is_numeric($_GET['stage']))
            $viewStage = $_GET['stage'];
        else
            $viewStage = null;

        // add workflow to user's recent item list
        User::addRecentItem('w', $id, Yii::app()->user->getId()); 

        //AuxLib::debugLogR ('actionView');
        //$params = ($this->getPerStageViewParams ($id, $viewStage, $users));
        //AuxLib::debugLogR ($params['expectedCloseDateDateRange']);

		$workflows = Workflow::getList(false);	// no "none" options

        $this->render('view',
            array_merge (
                array (
                    'perStageWorkflowView' => $perStageWorkflowView,
                    'workflows' => $workflows,
                ),
                ($perStageWorkflowView ? 
                    $this->getPerStageViewParams ($id, $viewStage, $users) :
                    $this->getDragAndDropViewParams ($id, $users)
                )
            )
        );
    }
    
    // Creates a new Workflow model
    // Creates 1 or more associated WorkflowStage models
    // If creation is successful, the browser will be redirected to the 'view' page.
    public function actionCreate() {
        $workflowModel = new Workflow;
        $workflowModel->lastUpdated = time();

        $stages = array();
        
        if(isset($_POST['Workflow'], $_POST['WorkflowStages'])) {
        
        
            $workflowModel->attributes = $_POST['Workflow'];
            if (isset ($_POST['colors']) && is_array ($_POST['colors'])) {
                // remove empty strings
                $_POST['colors'] = 
                    array_filter ($_POST['colors'], function ($a) { return $a !== ''; });

                $workflowModel->colors = $_POST['colors'];
            }

            if($workflowModel->save()) {
                $validStages = true;
                for($i=0; $i<count($_POST['WorkflowStages']); $i++) {
                    
                    $stages[$i] = new WorkflowStage;
                    $stages[$i]->workflowId = $workflowModel->id;
                    $stages[$i]->attributes = $_POST['WorkflowStages'][$i+1];
                    $stages[$i]->stageNumber = $i+1;
                    $stages[$i]->roles = $_POST['WorkflowStages'][$i+1]['roles'];
                    if(empty($stages[$i]->roles) || in_array('',$stages[$i]->roles))
                        $stages[$i]->roles = array();

                    if(!$stages[$i]->validate())
                        $validStages = false;
                }

                if($validStages) {

                    foreach($stages as &$stage) {
                        $stage->save();
                        foreach($stage->roles as $roleId)
                            Yii::app()->db->createCommand()->insert('x2_role_to_workflow',array(
                                'stageId'=>$stage->id,
                                'roleId'=>$roleId,
                                'workflowId'=>$workflowModel->id,
                            ));
                    }
                    if($workflowModel->save())
                        $this->redirect(array('view','id'=>$workflowModel->id));
                }
            }
        }

        $this->render('create',array(
            'model'=>$workflowModel,
        ));
    }

    // Updates a particular model
    // Deletes and recreates all associated WorkflowStage models
    // If update is successful, the browser will be redirected to the 'view' page.
    public function actionUpdate($id) {
        $workflowModel = $this->loadModel($id);

        $stages = array();
        // echo '<html><body>'.var_dump($_POST['WorkflowStages']).'</body></html>';

        // die(var_dump($_POST['WorkflowStages']));
        if(isset($_POST['Workflow'], $_POST['WorkflowStages'])) {

            $validStages = true;
            for($i=0; $i<count($_POST['WorkflowStages']); $i++) {
                
                $stages[$i] = new WorkflowStage;
                $stages[$i]->workflowId = $id;
                $stages[$i]->attributes = $_POST['WorkflowStages'][$i+1];
                $stages[$i]->stageNumber = $i+1;
                $stages[$i]->roles = $_POST['WorkflowStages'][$i+1]['roles'];
                if(empty($stages[$i]->roles) || in_array('',$stages[$i]->roles))
                    $stages[$i]->roles = array();

                if(!$stages[$i]->validate())
                    $validStages = false;
            }

            $workflowModel->attributes = $_POST['Workflow'];
            $workflowModel->lastUpdated = time();

            if (isset ($_POST['colors']) && 
                is_array ($_POST['colors'])) {
                
                // remove empty strings
                $_POST['colors'] = 
                    array_filter ($_POST['colors'], function ($a) { return $a !== ''; });

                $workflowModel->colors = $_POST['colors'];
            }
            
            if($validStages && $workflowModel->validate()) {
            
                Yii::app()->db->createCommand()->delete(
                    'x2_workflow_stages','workflowId='.$workflowModel->id); // delete old stages
                
                // delete role stuff too
                Yii::app()->db->createCommand()->delete('x2_role_to_workflow','workflowId='.$id);    
                foreach($stages as &$stage) {
                    $stage->save();
                    foreach($stage->roles as $roleId)
                        Yii::app()->db->createCommand()->insert('x2_role_to_workflow',array(
                            'stageId'=>$stage->id,
                            'roleId'=>$roleId,
                            'workflowId'=>$id,
                        ));
                }
                if ($workflowModel->save())
                    $this->redirect(array('view','id'=>$workflowModel->id));
            }
        }
        $this->render('update',array(
            'model'=>$workflowModel,
        ));
    }

    // Deletes Workflow model
    // Associated WorkflowStage models will automatically be deleted by the DB
    public function actionDelete($id) {
        if(Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            /* if AJAX request (triggered by deletion via admin grid view), we should not 
               redirect the browser */
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }
        else
            throw new CHttpException(
                400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Render funnel for inline workflow widget
     */
    public function actionGetWorkflow($workflowId,$modelId,$type) {
        assert (is_numeric($workflowId));
        assert (is_numeric($modelId));
    
        if(is_numeric($workflowId) && is_numeric($modelId)) {
            //$workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
            // echo var_dump($workflowStatus);
            //echo Workflow::renderWorkflow($workflowStatus);

            $workflowStatus = Workflow::getWorkflowStatus($workflowId, $modelId, $type);
            if (sizeof ($workflowStatus['stages']) < 1) return;

            $model = $this->loadModel($workflowId);
            $colors = $model->getWorkflowStageColors (sizeof ($workflowStatus['stages']));

            $this->renderPartial ('_inlineFunnel', array (
                'workflowStatus' => $workflowStatus,
                'stageCount' => sizeof ($workflowStatus['stages']),
                'colors' => $colors,
            ), false, true);
        }
    }

    /**
     * Render funnel for workflow view 
     */
    public function renderFunnelView (
        $workflowId, $dateRange, $expectedCloseDateDateRange, $users='', $modelId=0, 
        $modelType='') {

        $workflowStatus = Workflow::getWorkflowStatus($workflowId, $modelId, $modelType);
        $stageCounts = Workflow::getStageCounts (
            $workflowStatus, $dateRange, $expectedCloseDateDateRange, $users, $modelType);

        $model = $this->loadModel($workflowId);
        $colors = $model->getWorkflowStageColors (sizeof ($stageCounts));
        $stageValues = Workflow::getStageValues (
            $model, $users, $modelType, $dateRange, $expectedCloseDateDateRange);

        $stageNameLinks = Workflow::getStageNameLinks (
            $workflowStatus, $dateRange, $expectedCloseDateDateRange, $users);

        $this->renderPartial ('_funnel', array (
            'workflowStatus' => $workflowStatus,
            'recordsPerStage' => $stageCounts,
            'stageCount' => sizeof ($stageCounts),
            'stageValues' => array_map (
                function ($a) { return Formatter::formatCurrency ($a[1]); }, $stageValues),
            'totalValue' => Formatter::formatCurrency (array_reduce (
                $stageValues, 
                function ($a, $b) { 
                    return $a + $b[1]; 
                }, 0)),
            'stageNameLinks' => $stageNameLinks,
            'colors' => $colors,
        ));
    
    }

    public function renderInlineFunnelView ($workflowId, $modelId=0, $type='') {
        $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
    }
    
    
    public function actionGetStageDetails($workflowId,$stage,$modelId,$type) {
        if(is_numeric($workflowId) && is_numeric($stage) && is_numeric($modelId)) {

            $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
        
            if(isset($workflowStatus['stages'][$stage])) {
                $model = X2Model::model('Actions')->findByAttributes(array(
                    'associationId'=>$modelId,
                    'associationType'=>$type,
                    'type'=>'workflow',
                    'workflowId'=>$workflowId,
                    'stageNumber'=>$stage
                ));
                
                if($model->complete != 'Yes')
                    $model->completedBy = Yii::app()->user->name;
                
                $editable = true;    // default is full permission for everybody
                
                // if roles are specified, check if user has any of them
                if(!empty($workflowStatus['stages'][$stage]['roles'])) {    
                    $editable = count(array_intersect(Yii::app()->params->roles, 
                        $workflowStatus['stages'][$stage]['roles'])) > 0;
                }

                // if the workflow backdate window isn't unlimited, check if the window has passed
                if(Yii::app()->settings->workflowBackdateWindow > 0 && 
                   (time() - $model->completeDate) > 
                        Yii::app()->settings->workflowBackdateWindow) {
                    $editable = false;
                }
                    
                if(Yii::app()->user->checkAccess('WorkflowAdmin') || Yii::app()->params->isAdmin)
                    $editable = true;
                    
                $minDate = Yii::app()->settings->workflowBackdateRange;
                if($minDate < 0)
                    $minDate = null;    // if workflowBackdateRange = -1, no limit on backdating
                else
                    $minDate = '-'.$minDate;    // otherwise, we can only go back this far
                    
                if(Yii::app()->user->checkAccess('WorkflowAdmin') || Yii::app()->params->isAdmin)
                    $minDate = null;

                $this->renderPartialAjax('_workflowDetail',array(
                    'model'=>$model,
                    'editable'=>$editable,
                    'minDate'=>$minDate,
                    'allowReassignment'=>
                        Yii::app()->settings->workflowBackdateReassignment || 
                            Yii::app()->params->isAdmin,
                ),false);
            }
        }
    }
    
    public function actionUpdateStageDetails($id) {

        $action = X2Model::model('Actions')->findByPk($id);
        $previouslyComplete = $action->complete === 'Yes';
        
        $model = X2Model::getModelOfTypeWithId(
            $action->associationType,$action->associationId, true);
        
        if(isset($model,$action,$_POST['Actions'])) {
            $action->setScenario('workflow');

            $action->createDate = Formatter::parseDate($_POST['Actions']['createDate']);
            $action->completeDate = Formatter::parseDate($_POST['Actions']['completeDate']);
            $action->actionDescription = $_POST['Actions']['actionDescription'];

            if(isset($_POST['Actions']['completedBy']) && 
               (Yii::app()->params->isAdmin || 
                Yii::app()->settings->workflowBackdateReassignment)) {
                $action->completedBy = $_POST['Actions']['completedBy'];
            }

            // don't save if createDate isn't valid
            if($action->createDate === false)
                return;
            
            if($action->completeDate === false) {
                $action->complete = 'No';
                $action->completedBy = null;
                
                $model->updateLastActivity();
                
                if($previouslyComplete)    // we're uncompleting this thing
                    Workflow::updateWorkflowChangelog($action,'revert',$model);
                
                unset ($action->completeDate); // remove invalid value
            } else {
                if($action->completeDate < $action->createDate) {
                    // we can't have the completeDate before the createDate now can we
                    $action->completeDate = $action->createDate;    
                }
                $action->complete = 'Yes';
                
                if(!$previouslyComplete)    // we're completing it
                    Workflow::updateWorkflowChangelog($action,'complete',$model);
            }
            if ($action->save()) {
                if ($action->complete === 'Yes') {
                    echo 'complete';
                } else {
                    echo 'success';
                }
            }
        }

    }

    /**
     * Moves a record up or down through a workflow. Called via AJAX.
     */
    public function actionMoveFromStageAToStageB () {
        if(!(isset($_POST['workflowId']) && isset ($_POST['stageA']) && 
           isset ($_POST['stageB']) && isset ($_POST['modelId']) && isset ($_POST['type']) && 
           (!isset ($_POST['comments']) || is_array ($_POST['comments'])))) {

            throw new CHttpException(
                400, 'Invalid request. Please do not repeat this request again.');
        }

        $workflowId = (int) $_POST['workflowId'];
        $stageA = (int) $_POST['stageA'];
        $stageB = (int) $_POST['stageB'];
        $modelId = (int) $_POST['modelId'];
        $comments = isset ($_POST['comments']) ? $_POST['comments'] : array ();
        $type = $_POST['type'];
        $model = X2Model::getModelOfTypeWithId($type,$modelId, true);

        if ($stageA === $stageB || $model === null) {
            echo 'failure';
            return;
        }

        $retVal = Workflow::moveFromStageAToStageB (
            $workflowId, $stageA, $stageB, $model, $comments);
        echo CJSON::encode (array (
            'workflowStatus' => Workflow::getWorkflowStatus ($workflowId, $modelId, $type),
            'flashes' => array ('error' => isset ($retVal[1]) ? array ($retVal[1]) : array ())
        ));
    }

    public function actionStartStage(
        $workflowId,$stageNumber,$modelId,$type) {

        $model = $this->validateParams ($workflowId, $stageNumber, $modelId, $type);

        $workflowStatus = Workflow::getWorkflowStatus($workflowId,$model->id,$type);
        $message = '';
        if (Workflow::validateAction (
            'start', $workflowStatus, $stageNumber, '', $message)) {

            list ($started, $workflowStatus) = 
                Workflow::startStage (
                    $workflowId, $stageNumber, $model, $workflowStatus);
            assert ($started);
        }
        echo CJSON::encode (array (
            'workflowStatus' => $workflowStatus,
            'flashes' => array (
                'error' => !empty ($message) ? array ($message) : array (),
                'success' => empty ($message) ? 
                    array (Yii::t('workflow', 'Stage started')) : array (),
            )
        ));
    }

    /**
     * Helper method for action<workflow action> actions to validate get parameters and to 
     * retrieve the associated model.
     * @param int $workflowId the id of the workflow 
     * @param int $stageNumber the number of the stage
     * @param int $modelId the id of the associated model
     * @param string $type the association type of the associated model
     * @return object model with specified id and associationType
     */
    private function validateParams ($workflowId,$stageNumber,$modelId,$type,$recordName=null) {
        if(!is_numeric($workflowId) || !is_numeric($stageNumber) || 
           (!is_numeric($modelId) && $recordName === null)) {
            throw new CHttpException (400, 'Bad Request');
        }

        if (!is_numeric ($modelId)) {
            $model = X2Model::getModelOfTypeWithName($type,$recordName);
        } else {
            $model = X2Model::getModelOfTypeWithId($type,$modelId, true);
        }

        if ($model === null) throw new CHttpException (400, 'Bad Request');
        return $model;
    }

    /**
     * Called via ajax to add a deal to the pipeline
     * @param int $workflowId the id of the workflow 
     * @param int $stageNumber the number of the stage
     * @param int $modelId the id of the associated model
     * @param string $type the association type of the associated model
     */
    public function actionAjaxAddADeal (
        $workflowId,$stageNumber,$modelId=null,$type,$recordName=null) {

        $model = $this->validateParams ($workflowId, $stageNumber, $modelId, $type,$recordName);
        $workflowStatus = Workflow::getWorkflowStatus($workflowId,$model->id,$type);
        $message = '';
        if (Workflow::validateAction (
            'start', $workflowStatus, $stageNumber, '', $message)) {

            list ($started, $workflowStatus) = 
                Workflow::startStage (
                    $workflowId, $stageNumber, $model, $workflowStatus);
            assert ($started);
        }
        $value = Yii::app()->locale->numberFormatter->formatCurrency (
            Workflow::getProjectedValue ($type, $model->getAttributes ()),
            Yii::app()->params->currency);
        echo CJSON::encode (array (
            'workflowStatus' => $workflowStatus,
            'dealValue' => $value,
            'flashes' => array (
                'error' => !empty ($message) ? array ($message) : array (),
                'success' => empty ($message) ? 
                    array (Yii::t('workflow', 'Stage started')) : array (),
            )
        ));
    }
    
    public function actionCompleteStage(
        $workflowId,$stageNumber,$modelId,$type,$comment='') {

        $model = $this->validateParams ($workflowId, $stageNumber, $modelId, $type);

        $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
        $message = '';
        if (Workflow::validateAction (
            'complete', $workflowStatus, $stageNumber, $comment, $message)) {

            list ($completed, $workflowStatus) = Workflow::completeStage (
                $workflowId, $stageNumber, $model, $comment, true);
        }

        // $record=X2Model::model(ucfirst($type))->findByPk($modelId);
        // if($record->hasAttribute('lastActivity')){
            // $record->lastActivity=time();
            // $record->save();
        // }

        echo CJSON::encode (array (
            'workflowStatus' => $workflowStatus,
            'flashes' => array (
                'error' => !empty ($message) ? array ($message) : array (),
                'success' => empty ($message) ? 
                    array (Yii::t('workflow', 'Stage completed')) : array (),
            )
        ));
    }

    public function actionRevertStage($workflowId,$stageNumber,$modelId,$type) {
        $model = $this->validateParams ($workflowId, $stageNumber, $modelId, $type);

        if ($model === null) throw new CHttpException (400, 'Bad Request');
        $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
        $message = '';
        if (Workflow::validateAction (
            'revert', $workflowStatus, $stageNumber, '', $message)) {

            list ($completed, $workflowStatus) = Workflow::revertStage (
                $workflowId, $stageNumber, $model);
        }

        echo CJSON::encode (array (
            'workflowStatus' => $workflowStatus,
            'flashes' => array (
                'error' => !empty ($message) ? array ($message) : array (),
                'success' => empty ($message) ? 
                    array (Yii::t('workflow', 'Stage reverted')) : array (),
            )
        ));
    }


    /**
     * Get a data provider with contacts, opportunities, and accounts 
     */
    public function getStageMemberDataProviderMixed (
        $workflowId, $dateRange, $expectedCloseDateDateRange, $stage, $users, $modelType='') {
            
        $dateRange=self::getDateRange();
        $expectedCloseDateDateRange=self::getDateRange(
            'expectedCloseDateStart', 'expectedCloseDateEnd', 'expectedCloseDateRange');

        
        if(!is_numeric($workflowId) || !is_numeric($stage))
            return new CActiveDataProvider();

        $records = array ();
        $attrs = array ();

//        AuxLib::debugLogR ('$modelType = ');
//        AuxLib::debugLogR ($modelType);
//        AuxLib::debugLogR (gettype ( $modelType));
//        AuxLib::debugLogR (strlen ($modelType));
//        AuxLib::debugLogR ($modelType[0]);
//        AuxLib::debugLogR ($modelType[1]);
        if (!empty ($modelType)) {
            AuxLib::coerceToArray ($modelType);
        }
//        AuxLib::debugLogR ('$modelType = ');
//        AuxLib::debugLogR ($modelType);


        // CActiveDataProviders are used for the purposes of reusing code.
        if (empty ($modelType) || in_array ('contacts', $modelType)) {
            $contactsDataProvider = $this->getStageMemberDataProvider (
                'contacts', $workflowId, $dateRange, $expectedCloseDateDateRange, $stage, $users, 
                false);
            $records['contacts'] = $contactsDataProvider->data;
            $attrs = array_merge ($attrs, Contacts::model ()->attributeNames ());
        }
        if (empty ($modelType) || in_array ('opportunities', $modelType)) {
            $opportunitiesDataProvider = $this->getStageMemberDataProvider (
                'opportunities', $workflowId, $dateRange, $expectedCloseDateDateRange, $stage,
                $users, false);
            $records['opportunities'] = $opportunitiesDataProvider->data;
            $attrs = array_merge ($attrs, Opportunity::model ()->attributeNames ());
        }
        if (empty ($modelType) || in_array ('accounts', $modelType)) {
            $accountsDataProvider = $this->getStageMemberDataProvider (
                'accounts', $workflowId, $dateRange, $expectedCloseDateDateRange, $stage,
                $users, false);
            $records['accounts'] = $accountsDataProvider->data;
            $attrs = array_merge ($attrs, Accounts::model ()->attributeNames ());
        }

        // get union of attribute names
        $attrUnion = array_unique ($attrs);
        $attrUnion[] = 'actionLastUpdated'; // used to sort records

        $combinedRecords = Record::mergeMixedRecords ($records, $attrUnion);

        /* 
        Sort records by the in descending order by the last time their associated workflow action 
        was updated. This allows Workflow stage listviews to be updated without reloading the 
        entire page. Every time a user drags a record from one stage to the next, placing that 
        record at the top of the target stage's list maintains the correct record ordering.
        */
        usort ($combinedRecords, function ($a, $b) {
            if ($a['actionLastUpdated'] > $b['actionLastUpdated']) {
                return -1;
            } else if ($a['actionLastUpdated'] < $b['actionLastUpdated']) {
                return 1;
            } else {
                return 0;
            }
        });

        $dataProvider = new CArrayDataProvider (
            $combinedRecords,
            array (
                'pagination' => array (
                    'pageSize' => 20
                )
            )
        );

        $dataProvider->pagination->route = '/workflow/workflow/view';
        $dataProvider->pagination->params = $_GET;
        unset ($dataProvider->pagination->params['ajax']);

        return $dataProvider;
    }

    /**
     * Get a data provider for members of a given type in a given stage
     * @param string $type {'contacts', 'opportunities', 'accounts'}
     */
    public function getStageMemberDataProvider (
        $type, $workflowId, $dateRange, $expectedCloseDateDateRange, $stage, $user, 
        $perStageWorkflowView=true) {

        $modelName = X2Model::getModelName ($type);
        $model = X2Model::model ($modelName);
        $tableName = $model->tableName ();

        $params = array (
            ':workflowId' => $workflowId,
            ':stage' => $stage,
            ':start' => $dateRange['start'],
            ':end' => $dateRange['end'],
            ':type' => $type,
        );
        if ($expectedCloseDateDateRange['range'] !== 'all') {
            $params = array_merge ($params, array (
                ':expectedCloseDateStart' => $expectedCloseDateDateRange['end'],
                ':expectedCloseDateEnd' => $expectedCloseDateDateRange['end'],
            ));
        }

        if(!empty($user)){
            $userString=" AND x2_actions.assignedTo=:user ";
            $params[':user'] = $user;
        }else{
            $userString="";
        }

        list ($accessCondition, $accessConditionParams) = 
            $modelName::model ()->getAccessSQLCondition ($tableName);
        $params = array_merge ($params, $accessConditionParams);
        $stageMemberSql = Yii::app()->db->createCommand()
            ->select("$tableName.*, x2_actions.lastUpdated as actionLastUpdated")
            ->from($tableName)
            ->join('x2_actions',"$tableName.id = x2_actions.associationId")
            ->where(
                ($expectedCloseDateDateRange['range'] !== 'all' ? 
                ($tableName . '.expectedCloseDate 
                    BETWEEN :expectedCloseDateStart AND :expectedCloseDateEnd AND ') : '').
                "x2_actions.workflowId=:workflowId AND x2_actions.stageNumber=:stage AND
                x2_actions.associationType=:type AND complete!='Yes' AND 
                (completeDate IS NULL OR completeDate=0) AND 
                x2_actions.createDate BETWEEN :start AND :end ".$userString." AND ".$accessCondition
                )
            ->getText();

        $memberCount = Yii::app()->db->createCommand()
            ->select("COUNT(*)")
            ->from($tableName)
            ->join('x2_actions',"$tableName.id = x2_actions.associationId")
            ->where(
                ($expectedCloseDateDateRange['range'] !== 'all' ? 
                ($tableName . '.expectedCloseDate 
                    BETWEEN :expectedCloseDateStart AND :expectedCloseDateEnd AND ') : '').
                "x2_actions.workflowId=:workflowId AND x2_actions.stageNumber=:stage AND
                x2_actions.associationType=:type AND complete!='Yes' AND 
                (completeDate IS NULL OR completeDate=0) AND 
                x2_actions.createDate BETWEEN :start AND :end ".$userString.' AND '.$accessCondition,
                $params
                )
            ->queryScalar();

        $membersDataProvider = new CSqlDataProvider($stageMemberSql,array(
            'totalItemCount'=>$memberCount,
            'sort'=>array(
                'defaultOrder'=>'lastUpdated DESC',
            ),
            'pagination'=> (!$perStageWorkflowView ? false : array('pageSize'=>20)),
            'params' => $params
        ));

        return $membersDataProvider;
    }

    public function actionGetStageMembers(
        $id,$stage,$start,$end,$range,$expectedCloseDateStart, $expectedCloseDateEnd,
        $expectedCloseDateRange,$users,$modelType) {

        $modelType = self::parseModelType ($modelType);
        $this->getStageMembers (
            $id, $stage, $start, $end, $range, $expectedCloseDateStart, $expectedCloseDateEnd,
            $expectedCloseDateRange, $users, $modelType);
    }

    public function getStageMembers($workflowId,$stage,$start,$end,$range,$expectedCloseDateStart,
        $expectedCloseDateEnd, $expectedCloseDateRange, $users,$modelType='') {

        $params = array ();
        
        $dateRange=self::getDateRange();
        $expectedCloseDateDateRange=self::getDateRange(
            'expectedCloseDateStart', 'expectedCloseDateEnd', 'expectedCloseDateRange');
        
        if(!is_numeric($workflowId) || !is_numeric($stage))
            return new CActiveDataProvider();

        if (!empty ($modelType)) AuxLib::coerceToArray ($modelType);

        if (empty ($modelType) || in_array ('contacts', $modelType))
            $contactsDataProvider = $this->getStageMemberDataProvider (
                'contacts', $workflowId, $dateRange, $expectedCloseDateDateRange, $stage, $users);
        if (empty ($modelType) || in_array ('opportunities', $modelType))
            $opportunitiesDataProvider = $this->getStageMemberDataProvider (
                'opportunities', $workflowId, $dateRange, $expectedCloseDateDateRange, $stage, 
                $users);
        if (empty ($modelType) || in_array ('accounts', $modelType))
            $accountsDataProvider = $this->getStageMemberDataProvider (
                'accounts', $workflowId, $dateRange, $expectedCloseDateDateRange, $stage, $users);

        $gridConfig = array (
            'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
            'template'=> 
                '<div class="page-title">{title}{buttons}'.
                '</div>{items}{pager}',
            'fixedHeader' => false,
            'fullscreen' => false,
	        'buttons'=>array('columnSelector','autoResize'),
        );
        
        if(isset($contactsDataProvider) && !empty ($contactsDataProvider) && 
            // only render the grid that's requested if an X2GridView update request is being made
           (!isset ($_GET['ajax']) || $_GET['ajax'] === 'contacts-grid')) {

        $this->renderPartial ('_ajaxRequestedStageMembers', 
            array ('gridConfig' => array_merge (
                $gridConfig,
                array (
                    'gvSettingsName' => 'contactsStageMembers',
                    'viewName' => 'workflowViewContacts',
                    'defaultGvSettings'=>array(
                        'name' => 125,
                        'dealvalue' => 165,
                        'dealstatus' => 80,
                        'lastUpdated' => 80,
                        'assignedTo' => 80,
                    ),
                    'dataProvider' => $contactsDataProvider,
                    'id'=>'contacts-grid',
                    'ajaxUpdate'=>'contacts-grid',
                    'modelName' => 'Contacts',
                    'title' => Yii::t('contacts','Contacts'),
                    'specialColumns'=>array(
                        'name' => array(
                            'name'=>'name',
                            'header'=>Yii::t('contacts','Name'),
                            'value'=>'CHtml::link('.
                                '$data["name"],array("/contacts/contacts/view",'.'
                                    "id"=>$data["id"]))',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'30%')
                        ),
                        'lastUpdated' => array(
                            'name'=>'lastUpdated',
                            'header'=>Yii::t('contacts','Expected Close Date'),
                            'value'=>'Formatter::formatDate($data["lastUpdated"])',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'15%')
                        ),
                        'assignedTo' => array(
                            'header'=>X2Model::model('Contacts')->getAttributeLabel('assignedTo'),
                            'name'=>'assignedTo',
                            'value'=>'empty($data["assignedTo"])?'.
                                'Yii::t("app","Anyone"):User::getUserLinks($data["assignedTo"])',
                            'type'=>'raw',
                        ),
                    ),
                )
            )), false, true);
        
        }
        
        if(isset ($opportunitiesDataProvider) && !empty($opportunitiesDataProvider) &&
           (!isset ($_GET['ajax']) || $_GET['ajax'] === 'opportunities-grid')) {

        $this->renderPartial ('_ajaxRequestedStageMembers', 
            array ('gridConfig' => array_merge (
                $gridConfig,
                array (
                    'defaultGvSettings'=>array(
                        'name' => 125,
                        'quoteAmount' => 165,
                        'salesStage' => 80,
                        'expectedCloseDate' => 80,
                        'assignedTo' => 80,
                    ),
                    'dataProvider' => $opportunitiesDataProvider,
                    'id'=>'opportunities-grid',
                    'ajaxUpdate'=>'opportunities-grid',
                    'modelName' => 'Opportunity',
                    'viewName' => 'workflowViewOpportunities',
                    'gvSettingsName' => 'opportunityStageMembers',
                    'title' => Yii::t('opportunitites','Opportunities'),
                    'specialColumns'=>array(
                        'name' => array(
                            'header'=>X2Model::model('Opportunity')->getAttributeLabel('name'),
                            'name'=>'name',
                            'value'=>'CHtml::link('.
                                    '$data["name"],'.
                                    'array("/opportunities/opportunities/view",'.
                                        '"id"=>$data["id"]))',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'40%'),
                        ),
                        'expectedCloseDate' => array(
                            'header'=>X2Model::model('Opportunity')->getAttributeLabel(
                                'expectedCloseDate'),
                            'name'=>'expectedCloseDate',
                            'value'=>'Formatter::formatDate($data["expectedCloseDate"])',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'13%'),
                        ),
                        'assignedTo' => array(
                            'header'=>X2Model::model('Opportunity')->getAttributeLabel(
                                'assignedTo'),
                            'name'=>'assignedTo',
                            'value'=>'empty($data["assignedTo"])?'.
                                'Yii::t("app","Anyone"):User::getUserLinks($data["assignedTo"])',
                            'type'=>'raw',
                        ),
                    ),
                )
            )), false, true);
        }

        if(isset ($accountsDataProvider) && !empty($accountsDataProvider) && 
           (!isset ($_GET['ajax']) || $_GET['ajax'] === 'accounts-grid')) {

        $this->renderPartial ('_ajaxRequestedStageMembers', 
            array ('gridConfig' => array_merge (
                $gridConfig,
                array (
                    'gvSettingsName' => 'accountsStageMembers',
                    'viewName' => 'workflowViewAccounts',
                    'dataProvider' => $accountsDataProvider,
                    'id'=>'accounts-grid',
                    'ajaxUpdate'=>'accounts-grid',
                    'modelName' => 'Accounts',
                    'title' => Yii::t('accounts','Accounts'),
                    'defaultGvSettings'=>array(
                        'name' => 125,
                        'dealvalue' => 165,
                        'dealstatus' => 80,
                        'lastUpdated' => 80,
                        'assignedTo' => 80,
                    ),
                    'specialColumns'=>array(
                        'name' => array(
                            'name'=>'name',
                            'header'=>Yii::t('accounts','Name'),
                            'value'=>'CHtml::link('.
                                '$data["name"],array("/accounts/accounts/view",'.'
                                    "id"=>$data["id"]))',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'30%')
                        ),
                        'lastUpdated' => array(
                            'name'=>'lastUpdated',
                            'header'=>Yii::t('accounts','Expected Close Date'),
                            'value'=>'Formatter::formatDate($data["lastUpdated"])',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'15%')
                        ),
                        'assignedTo' => array(
                            'header'=>X2Model::model('Accounts')->getAttributeLabel('assignedTo'),
                            'name'=>'assignedTo',
                            'value'=>'empty($data["assignedTo"])?'.
                                'Yii::t("app","Anyone"):User::getUserLinks($data["assignedTo"])',
                            'type'=>'raw',
                        ),
                    ),
                )
            )), false, true);
        }
    }

    public static function parseModelType ($modelType) {
        //AuxLib::debugLogR ($modelType);
        if (isset ($_GET['workflowAjax']) && $_GET['workflowAjax']) 
            $modelType = CJSON::decode ($modelType);
        if (empty ($modelType) || (is_array ($modelType) && sizeof ($modelType) === 1 &&
            empty ($modelType[0]))) {

            return '';
        }
        AuxLib::coerceToArray ($modelType);
        return $modelType;
    }

    public function actionGetStageValue($id,$stage,$users,$modelType,$start,$end){
        $modelType = self::parseModelType ($modelType);
        $dateRange = self::getDateRange ();
        $expectedCloseDateDateRange=self::getDateRange(
            'expectedCloseDateStart', 'expectedCloseDateEnd', 'expectedCloseDateRange');
        $workflow = Workflow::model ()->findByPk ($id);
        if ($workflow !== null) {
            list ($totalValue, $projectedValue, $currentAmount, $count) = Workflow::getStageValue (
                $id, $stage, $users, $modelType, $dateRange, $expectedCloseDateDateRange);
            $this->renderPartial ('_dataSummary', array (
                'stageName' => $workflow->getStageName ($stage),
                'totalValue' => Formatter::formatCurrency ($totalValue),
                'projectedValue' => Formatter::formatCurrency ($projectedValue),
                'currentAmount' => Formatter::formatCurrency ($currentAmount),
                'count' => $count,
            ));
        }
    }

    
    public function actionGetStages() {
        if(isset($_GET['id'])) {
            $stages = Yii::app()->db->createCommand()
                ->select('name')
                ->from('x2_workflow_stages')
                ->where('workflowId=:id',array(':id'=>$_GET['id']))
                ->order('id ASC')
                ->queryColumn();

            echo CJSON::encode($stages);
        }
    }
    
   /**
    * Called via AJAX. Returns partial view for specified view
    */
    public function actionChangeUI () {
        if (!isset ($_GET['perStageWorkflowView']) ||
            !isset ($_GET['id'])) {
            return;
        }

        $perStageWorkflowView = $_GET['perStageWorkflowView'] === 'true' ? true : false;
        $id = (int) $_GET['id'];
        $users = isset ($_GET['users']) ? $_GET['users'] : '';
        
        Profile::setMiscLayoutSetting ('perStageWorkflowView', $perStageWorkflowView, true);

        if ($perStageWorkflowView) {
            $this->renderPartial (
                '_perStageView', $this->getPerStageViewParams ($id, null, $users), false, true);
        } else {
            $this->renderPartial (
                '_dragAndDropView',$this->getDragAndDropViewParams ($id, $users), false, true);
        }
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='workflow-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    private function getDragAndDropViewParams ($id, $users='') {
        $model = $this->loadModel($id);
        $modelType = isset ($_GET['modelType']) ? self::parseModelType ($_GET['modelType']) : '';

        $dateRange=self::getDateRange();
        $expectedCloseDateDateRange=self::getDateRange(
            'expectedCloseDateStart', 'expectedCloseDateEnd', 'expectedCloseDateRange');

        $memberListContainerSelectors = array ();
        $stageValues = Workflow::getStageValues (
            $model, $users, $modelType, $dateRange, $expectedCloseDateDateRange);
        $stageCount = count ($model->stages);
        for ($i = 1; $i <= $stageCount; $i++) {
            $memberListContainerSelectors[] = '#workflow-stage-'.$i.' .items';
        }
        $workflowStatus = Workflow::getWorkflowStatus ($id);
        $stagePermissions = Workflow::getStagePermissions ($workflowStatus);
        $stagesWhichRequireComments = Workflow::getStageCommentRequirements ($workflowStatus);
        $stageNames = Workflow::getStageNames ($workflowStatus);
        $colors = $model->getWorkflowStageColors ($stageCount, true);
        return array (
            'model'=>$model,
            'modelType'=>$modelType,
            'dateRange'=>$dateRange,
            'expectedCloseDateDateRange'=>$expectedCloseDateDateRange,
            'users'=>$users,
            'colors'=>$colors,
            'listItemColors'=>Workflow::getPipelineListItemColors ($colors),
            'memberListContainerSelectors'=>$memberListContainerSelectors,
            'stagePermissions'=>$stagePermissions,
            'stagesWhichRequireComments'=>$stagesWhichRequireComments,
            'stageNames'=>$stageNames,
            'stageValues' => $stageValues
        );
    }

    private function getPerStageViewParams ($id, $viewStage=null, $users='') {
        $dateRange=self::getDateRange();
        $expectedCloseDateDateRange=self::getDateRange(
            'expectedCloseDateStart', 'expectedCloseDateEnd', 'expectedCloseDateRange');
        $modelType = isset ($_GET['modelType']) ? self::parseModelType ($_GET['modelType']) : '';
        $model = $this->loadModel($id);
        $stageCount = count ($model->stages);
        return array (
            'model'=>$model,
            'modelType'=>$modelType,
            'viewStage'=>$viewStage,
            'colors'=>$model->getWorkflowStageColors ($stageCount, true),
            'dateRange'=>$dateRange,
            'expectedCloseDateDateRange'=>$expectedCloseDateDateRange,
            'users'=>$users,
        );
    }

    /**
     * Helper function for actionGetStageMembers 
     */
    /*private function getStageMemberCondition (
        $workflowId, $stage, $dateRange, $userString, $modelClass, $suppressContactsCond=true) {
        $condition = "x2_actions.workflowId=:workflowId AND x2_actions.stageNumber=:stage AND 
            x2_actions.associationType=:modelClass AND complete!='Yes' AND 
            (completeDate IS NULL OR completeDate=0) AND 
            x2_actions.createDate BETWEEN :start AND :end". 
            " $userString".
                (!$suppressContactsCond ? 
            " AND (x2_contacts.visibility=1 OR x2_contacts.assignedTo=:username)" : '');
        $params = array ();
        $params[':workflowId'] = $workflowId;
        $params[':stage'] = $stage;
        $params[':start'] = $dateRange['start'];
        $params[':end'] = $dateRange['end'];
        if (!$suppressContactsCond)
            $params[':username'] = Yii::app()->user->getName();
        $params[':modelClass'] = $modelClass;

        return array ($condition, $params);
    }*/


    /**
     *  Used for auto-complete methods.
     */
    public function actionGetItems($term){
        X2LinkableBehavior::getItems ($term);
    }

    /**
     * Used to populate options of stage name dropdowns
     */
    public function actionGetStageNames($workflowId, $optional='true'){
		$stages = Workflow::getStagesByNumber($workflowId);
        if ($optional === 'true') 
            echo CJSON::encode (
                AuxLib::dropdownForJson (array ('any' => Yii::t('app', 'Any')) + $stages));
        else
            echo CJSON::encode (AuxLib::dropdownForJson ($stages));
    }

    /**
     * Used for stage name autocomplete inputs 
     */
    public function actionGetStageNameItems($workflowId, $term){
        $stageNames = Yii::app()->db->createCommand()
            ->select('name')
            ->from('x2_workflow_stages')
            ->where(
                'workflowId=:id and name like :qterm',
                array(':id'=>$workflowId, ':qterm' => $term.'%'))
            ->order('stageNumber ASC')
            ->queryColumn();
        echo CJSON::encode ($stageNames);
    }
    
    /**
     * Calls getDateRange and sets to default range to 'all' if the date range is the 
     * expected close date date range
     */
    public static function getDateRange ( 
        $startKey='start',$endKey='end',$rangeKey='range') {

        if ($startKey === 'expectedCloseDateStart') {
            $range = X2DateUtil::getDateRange ($startKey, $endKey, $rangeKey, 'all');

            // for expected close date, override the default behavior of getDateRange.
            // 'all time' should not end at 'today' since expected close dates can be in 
            // the future.
            if ($range['range'] === 'all') {
                $range['end'] = 0;
            }
            return $range;
        } else {
            return X2DateUtil::getDateRange ($startKey, $endKey, $rangeKey, 'custom');
        }
    }

    /**
     * Create a menu for Process
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Processes = Modules::displayName();
        $Process = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'edit', 'view', 'funnel', 'pipeline', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('workflow','All {processes}', array(
                    '{processes}' => $Processes,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('app','Create'),
                'url'=>array('create'),
                'visible'=>Yii::app()->params->isAdmin
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('workflow','Edit {process}', array(
                    '{process}' => $Process,
                )), 
                'url'=>array('update', 'id'=>$modelId), 
                'visible'=>Yii::app()->params->isAdmin
            ),
            array(
                'name'=>'funnel',
                'label'=>Yii::t('app','Funnel View'),
                'url'=>array('view', 'id'=>$modelId, 'perStageWorkflowView'=>'true'),
                'linkOptions' => array ('id' => 'funnel-view-menu-item'),
            ),
            array(
                'name'=>'pipeline',
                'label'=>Yii::t('app','Pipeline View'),
                'url'=>array('view', 'id'=>$modelId, 'perStageWorkflowView'=>'false'),
                'linkOptions' => array ('id' => 'pipeline-view-menu-item'),
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('workflow','Delete {process}', array(
                    '{process}' => $Process,
                )), 
                'url'=>'#', 
                'linkOptions'=>array('submit'=>array('delete','id'=>$modelId),
                'confirm'=>Yii::t('app','Are you sure you want to delete this item?')), 
                'visible'=>Yii::app()->params->isAdmin
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}

