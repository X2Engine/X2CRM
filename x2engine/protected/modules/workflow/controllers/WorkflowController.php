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
        $model = new Workflow('search');
        $this->render('index',array(
            'model'=>$model,
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
        
        $workflowAttr = filter_input(INPUT_POST,'Workflow', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $workflowStages = filter_input(INPUT_POST,'WorkflowStages', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if(!empty($workflowAttr) && !empty($workflowStages)) {
        
        
            $workflowModel->attributes = $workflowAttr;
            $colors = filter_input(INPUT_POST, 'colors', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($colors)) {
                $colors =  array_filter ($colors, function ($a) { return $a !== ''; });
                $workflowModel->colors = $colors;
            }

            if($workflowModel->save()) {
                $validStages = true;
                for($i=0; $i<count($workflowStages); $i++) {
                    
                    $stages[$i] = new WorkflowStage;
                    $stages[$i]->workflowId = $workflowModel->id;
                    $stages[$i]->attributes = $workflowStages[$i+1];
                    $stages[$i]->stageNumber = $i+1;
                    $stages[$i]->roles = $workflowStages[$i+1]['roles'];
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

        $workflowAttr = filter_input(INPUT_POST,'Workflow', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $workflowStages = filter_input(INPUT_POST,'WorkflowStages', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if(!empty($workflowAttr) && !empty($workflowStages)) {

            list($validStages, $newStages, $forDeletion) = $workflowModel->updateStages($workflowStages);

            $workflowModel->attributes = $workflowAttr;
            $workflowModel->lastUpdated = time();
            $colors = filter_input(INPUT_POST, 'colors', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($colors)) {
                $colors =  array_filter ($colors, function ($a) { return $a !== ''; });
                $workflowModel->colors = $colors;
            }
            
            if($validStages && $workflowModel->validate()) {
            
                WorkflowStage::model()->deleteByPk($forDeletion);
                
                // delete role stuff too
                Yii::app()->db->createCommand()->delete('x2_role_to_workflow','workflowId='.$id);    
                foreach($newStages as &$stage) {
                    $stage->save();
                    foreach($stage->roles as $roleId){
                        Yii::app()->db->createCommand()->insert('x2_role_to_workflow',array(
                            'stageId'=>$stage->id,
                            'roleId'=>$roleId,
                            'workflowId'=>$id,
                        ));
                    }
                }
                if ($workflowModel->save()){
                    $this->redirect(array('view','id'=>$workflowModel->id));
                }
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
        $workflowId, $dateRange, $users='', $modelId=0, 
        $modelType='') {

        $workflowStatus = Workflow::getWorkflowStatus($workflowId, $modelId, $modelType);
        $stageCounts = Workflow::getStageCounts (
            $workflowStatus, $dateRange, $users, $modelType);
        $stageValues = Workflow::getStageValues(
            $workflowStatus, $dateRange, $users, $modelType);

        $model = $this->loadModel($workflowId);
        $colors = $model->getWorkflowStageColors (sizeof ($stageCounts));

        $stageNameLinks = Workflow::getStageNameLinks (
            $workflowStatus, $dateRange, $users);

        $this->renderPartial ('_funnel', array (
            'workflowStatus' => $workflowStatus,
            'recordsPerStage' => $stageCounts,
            'stageCount' => sizeof ($stageCounts),
            'stageValues' => array_map (
                function ($a) { return (is_null($a) ? '' : Formatter::formatCurrency ($a)); }, $stageValues),
            'totalValue' => Formatter::formatCurrency (array_sum ($stageValues)),
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
            $stageArr = $workflowStatus['stages'][$stage];
            if(isset($stageArr)) {
                $model = X2Model::model('Actions')->findByAttributes(array(
                    'associationId'=>$modelId,
                    'associationType'=>$type,
                    'type'=>'workflow',
                    'workflowId'=>$workflowId,
                    'stageNumber'=>$stageArr['id']
                ));
                
                if($model->complete != 'Yes')
                    $model->completedBy = Yii::app()->user->name;
                
                $editable = true;    // default is full permission for everybody
                
                // if roles are specified, check if user has any of them
                if(!empty($stageArr['roles'])) {    
                    $editable = count(array_intersect(Yii::app()->params->roles, 
                        $stageArr['roles'])) > 0;
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
        echo CJSON::encode (array (
            'workflowStatus' => $workflowStatus,
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
     * Get a data provider for members of a given type in a given stage
     * @param string $type {'contacts', 'opportunities', 'accounts'}
     */
    public function getStageMemberDataProvider (
        $type, $workflowId, $dateRange, $stage, $user) {

        $modelName = X2Model::getModelName ($type);
        if(!$modelName){
            $type = 'contacts';
            $modelName = 'Contacts';
        }
        $model = X2Model::model ($modelName);
        $tableName = $model->tableName ();
        $stageModel = WorkflowStage::model()->findByAttributes(array(
            'workflowId' => $workflowId,
            'stageNumber' => $stage,
        ));
        $params = array (
            ':workflowId' => $workflowId,
            ':stage' => $stageModel->id,
            ':start' => $dateRange['start'],
            ':end' => $dateRange['end'],
            ':type' => $type,
        );

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
            ->where("x2_actions.workflowId=:workflowId AND x2_actions.stageNumber=:stage AND
                x2_actions.associationType=:type AND complete!='Yes' AND 
                (completeDate IS NULL OR completeDate=0) AND 
                x2_actions.createDate BETWEEN :start AND :end ".$userString." AND ".$accessCondition
                )
            ->getText();

        $memberCount = Yii::app()->db->createCommand()
            ->select("COUNT(*)")
            ->from($tableName)
            ->join('x2_actions',"$tableName.id = x2_actions.associationId")
            ->where("x2_actions.workflowId=:workflowId AND x2_actions.stageNumber=:stage AND
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
            'pagination'=> array('pageSize'=>20),
            'params' => $params
        ));

        return $membersDataProvider;
    }

    public function actionGetStageMembers(
        $id,$stage,$start,$end,$range, $users,$modelType) {
        
        $this->getStageMembers (
            $id, $stage, $start, $end, $range, $users, $modelType);
    }

    public function getStageMembers($workflowId,$stage,$start,$end,$range,
        $users, $modelType='') {

        $params = array ();
        
        $dateRange=self::getDateRange();
        
        if(!is_numeric($workflowId) || !is_numeric($stage))
            return new CActiveDataProvider();

        $dataProvider = $this->getStageMemberDataProvider (
                $modelType, $workflowId, $dateRange, $stage, $users);

        $gridConfig = array (
            'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
            'template'=> 
                '<div class="page-title">{title}{buttons}'.
                '</div>{items}{pager}',
            'fixedHeader' => false,
            'fullscreen' => false,
	        'buttons'=>array('columnSelector','autoResize'),
        );
        $modelName = X2Model::getModelName($modelType);
        $this->renderPartial ('_ajaxRequestedStageMembers', 
            array ('gridConfig' => array_merge (
                $gridConfig,
                array (
                    'gvSettingsName' => $modelType.'-stageMembers',
                    'viewName' => $modelType.'-workflowView',
                    'defaultGvSettings'=>array(
                        'name' => 125,
                        'lastUpdated' => 80,
                        'assignedTo' => 80,
                    ),
                    'dataProvider' => $dataProvider,
                    'id'=>'workflow-stage-grid',
                    'ajaxUpdate'=>'workflow-stage-grid',
                    'modelName' => $modelName,
                    'title' => Modules::displayName(true, $modelType),
                    'specialColumns'=>array(
                        'name' => array(
                            'name'=>'name',
                            'header'=>Yii::t('app','Name'),
                            'value'=>'X2Model::getModelLinkMock (
                                "'.$modelName.'",
                                $data[\'nameId\'],
                                array (
                                    \'data-qtip-title\' => $data[\'name\']
                                )
                            )',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'30%')
                        ),
                        'lastUpdated' => array(
                            'name'=>'lastUpdated',
                            'header'=>X2Model::model($modelName)->getAttributeLabel('lastUpdated'),
                            'value'=>'Formatter::formatDate($data["lastUpdated"])',
                            'type'=>'raw',
                            'htmlOptions'=>array('width'=>'15%')
                        ),
                        'assignedTo' => array(
                            'header'=>X2Model::model($modelName)->getAttributeLabel('assignedTo'),
                            'name'=>'assignedTo',
                            'value'=>'empty($data["assignedTo"])?'.
                                'Yii::t("app","Anyone"):User::getUserLinks($data["assignedTo"])',
                            'type'=>'raw',
                        ),
                    ),
                )
            )), false, true);
        
    }

    public function actionGetStageValue($id,$stage,$users,$modelType,$start,$end){
        $dateRange = self::getDateRange ();
        $workflow = Workflow::model ()->findByPk ($id);
        if ($workflow !== null) {
            $workflowStatus = Workflow::getWorkflowStatus ($id);
            $counts = Workflow::getStageCounts($workflowStatus, $dateRange, $users, $modelType);
            $this->renderPartial ('_dataSummary', array (
                'stageName' => $workflow->getStageName ($stage),
                'count' => $counts[$stage - 1],
            ));
        }
    }

    
    public function actionGetStages() {
        if(isset($_GET['id'])) {
            $stages = Yii::app()->db->createCommand()
                ->select('id, name')
                ->from('x2_workflow_stages')
                ->where('workflowId=:id',array(':id'=>$_GET['id']))
                ->order('id ASC')
                ->queryAll();
            $ret = array();
            foreach($stages as $stage){
                $ret[$stage['id']] = $stage['name'];
            }
            echo CJSON::encode($ret);
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
        if(isset($_GET['modelType'])){
            $modelType = $_GET['modelType'];
        }elseif(!empty($model->financialModel)){
            if(X2Model::getModelName($model->financialModel)){
                $modelType = $model->financialModel;
            }else{
                $modelType = 'contacts';
            }
        }else{
            $modelType = 'contacts';
        }
        $dateRange=self::getDateRange();

        $memberListContainerSelectors = array ();
        $stageCount = count ($model->stages);
        for ($i = 1; $i <= $stageCount; $i++) {
            $memberListContainerSelectors[] = '#workflow-stage-'.$i.' .items';
        }
        $workflowStatus = Workflow::getWorkflowStatus ($id);
        $stagePermissions = Workflow::getStagePermissions ($workflowStatus);
        $stagesWhichRequireComments = Workflow::getStageCommentRequirements ($workflowStatus);
        $stageNames = Workflow::getStageNames ($workflowStatus);
        $colors = $model->getWorkflowStageColors ($stageCount, true);
        $stageCounts = Workflow::getStageCounts (
            $workflowStatus, $dateRange, $users, $modelType);
        $stageValues = Workflow::getStageValues(
            $workflowStatus, $dateRange, $users, $modelType);
        return array (
            'model'=>$model,
            'modelType'=>$modelType,
            'dateRange'=>$dateRange,
            'users'=>$users,
            'colors'=>$colors,
            'listItemColors'=>Workflow::getPipelineListItemColors ($colors),
            'memberListContainerSelectors'=>$memberListContainerSelectors,
            'stagePermissions'=>$stagePermissions,
            'stagesWhichRequireComments'=>$stagesWhichRequireComments,
            'stageNames'=>$stageNames,
            'stageCounts' => $stageCounts,
            'stageValues' => $stageValues,
        );
    }

    private function getPerStageViewParams ($id, $viewStage=null, $users='') {
        $dateRange=self::getDateRange();
        $model = $this->loadModel($id);
        $modelType = isset ($_GET['modelType']) ? $_GET['modelType'] : (!empty($model->financialModel)?$model->financialModel:'contacts');
        $stageCount = count ($model->stages);
        return array (
            'model'=>$model,
            'modelType'=>$modelType,
            'viewStage'=>$viewStage,
            'colors'=>$model->getWorkflowStageColors ($stageCount, true),
            'dateRange'=>$dateRange,
            'users'=>$users,
        );
    }

    /**
     *  Used for auto-complete methods.
     */
    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
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
    
    public function actionGetFinancialFields($modelType){
        $ret = array('' => 'Select a field');
        $ret = array_merge($ret, Workflow::getCurrencyFields($modelType));
        echo CJSON::encode($ret);
    }
    
    /**
     * Calls getDateRange and sets to default range to 'all' if the date range is the 
     * expected close date date range
     */
    public static function getDateRange ( 
        $startKey='start',$endKey='end',$rangeKey='range') {
        return X2DateUtil::getDateRange ($startKey, $endKey, $rangeKey, 'custom');
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
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('workflow','Edit {process}', array(
                    '{process}' => $Process,
                )), 
                'url'=>array('update', 'id'=>$modelId), 
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
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}

