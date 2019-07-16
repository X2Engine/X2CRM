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




// Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_workflows".
 * @package application.modules.workflow.models
 */
class Workflow extends X2ActiveRecord {

    const DEFAULT_ALL_MODULES = '-1';

    /**
     * Returns the static model of the specified AR class.
     * @return Workflow the static model class
     */
    public static function model($className=__CLASS__) { return parent::model($className); }

    /**
     * @return string the associated database table name
     */
    public function tableName() { return 'x2_workflows'; }

    private static $_workflowOptions;
    private $_stageNameAutoCompleteSource;

    public function behaviors() {
        return array_merge(parent::behaviors(),array(
            'LinkableBehavior'=>array(
                'class'=>'LinkableBehavior',
                'module'=>'workflow'
            ),
            'JSONFieldsDefaultValuesBehavior' => array(
                'class' => 'application.components.behaviors.JSONFieldsDefaultValuesBehavior',
                'transformAttributes' => array(
                    'colors' => array(
                        'first'=>'c4f455', // color of the first stage
                        'last'=>'f18c1c', // color of the last stage
                    ),
                ),
                'maintainCurrentFieldsOrder' => true
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
        ));
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'required'),
            array('lastUpdated', 'numerical', 'integerOnly'=>true),
            array('name, financialModel, financialField', 'length', 'max'=>250),
            array('isDefault, financial', 'boolean'),
            array('isDefaultFor', 'validateIsDefaultFor'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, lastUpdated, financial, financialModel, financialField', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'stages'=>array(self::HAS_MANY, 'WorkflowStage', 'workflowId', 'order'=>'stageNumber ASC'),
        );
    }
    
    /**
     * @return array behaviors.
     */
    // public function behaviors(){
        // return array('CSaveRelationsBehavior' => array('class' => 'application.components.behaviors.CSaveRelationsBehavior'));
    // }

    /**
     * isDefault should either be a boolean value or an array of module ids 
     */
    public function validateIsDefaultFor ($attr) {
        $val = $this->$attr;
        if (is_array ($val)) {
            $moduleIds = Yii::app()->db->createCommand ()
                ->select ('id')
                ->from ('x2_modules')
                ->queryColumn ();
            if (array_diff ($val, array_merge (array (self::DEFAULT_ALL_MODULES), $moduleIds))) {
                $this->addError ($attr, Yii::t('workflow', 'Invalid module'));
            }
        }
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'name' => Yii::t('workflow','Process Name'),
            'isDefault' => Yii::t('workflow','Default Process'),
            'isDefaultFor' => Yii::t('workflow','Default Process'),
            'lastUpdated' => Yii::t('workflow','Last Updated'),
            'financial' => Yii::t('workflow','Show Financial Data'),
            'financialModel' => Yii::t('workflow','Financial Data Model'),
            'financialField' => Yii::t('workflow','Financial Data Field'),
        );
    }

    private $_isDefaultFor;
    public function setIsDefaultFor ($isDefaultFor) {
        if (!is_array ($isDefaultFor)) $isDefaultFor = array ();
        $this->_isDefaultFor = $isDefaultFor;
        if (in_array (self::DEFAULT_ALL_MODULES, $this->_isDefaultFor)) {
            $this->isDefault = true;
            $this->_isDefaultFor = array (self::DEFAULT_ALL_MODULES);
        } else {
            $this->isDefault = false;
        }
    }

    public function getIsDefaultFor () {
        if (!isset ($this->_isDefaultFor)) {
            if ($this->isDefault) {
                $this->_isDefaultFor = array (self::DEFAULT_ALL_MODULES);
            } else {
                $this->_isDefaultFor = Yii::app()->db->createCommand ("
                    select id
                    from x2_modules
                    where defaultWorkflow=:id
                ")->queryColumn (array (':id' => $this->id));
            }
        }
        return $this->_isDefaultFor;
    }

    public function renderAttribute (
            $fieldName, $makeLinks = true, $textOnly = true, $encode = true) {
        switch ($fieldName) {
            case 'isDefaultFor':
                $isDefaultFor = $this->getIsDefaultFor ();
                if (in_array (self::DEFAULT_ALL_MODULES, $isDefaultFor)) {
                    return Yii::t('workflow', 'All modules');
                } elseif ($isDefaultFor) {
                    $qpg = new QueryParamGenerator;
                    $moduleNames = Yii::app()->db->createCommand ()
                        ->select ('name')
                        ->from ('x2_modules')
                        ->where ('id in '.$qpg->bindArray ($isDefaultFor, true))
                        ->queryColumn ($qpg->getParams ());
                    return implode (', ', ArrayUtil::asorti (array_map (function ($name) {
                        return Modules::displayName (true, $name);
                    }, $moduleNames)));
                }
                break;
            default:
                return $this->$fieldName;
        }
    }

    /**
     * If this workflow is the default, unset isDefault flag on all other workflows
     */
    public function afterSave() {
        if (in_array (self::DEFAULT_ALL_MODULES, $this->isDefaultFor)) {
            // this workflow is default for all modules, so remove all defaults
            Yii::app()->db->createCommand("
                update x2_modules
                set defaultWorkflow=NULL
                where true
            ")->execute (array (':id' => $this->id));
            // remove old global default
            Yii::app()->db->createCommand("
                update x2_workflows
                set isDefault=0
                where id!=:id
            ")->execute (array (':id' => $this->id));
        } else {
            // set default on a per-module basis

            // add new values
            if ($this->isDefaultFor) {
                $qpg = new QueryParamGenerator;
                Yii::app()->db->createCommand("
                    update x2_modules
                    set defaultWorkflow=:id
                    where id in ".$qpg->bindArray ($this->isDefaultFor, true)."
                ")->execute ($qpg->mergeParams (array (':id' => $this->id))->getParams ());
            }

            // clear old values
            if ($this->isDefaultFor) {
                $qpg = new QueryParamGenerator;
                Yii::app()->db->createCommand("
                    update x2_modules
                    set defaultWorkflow=NULL
                    where id not in ".$qpg->bindArray ($this->isDefaultFor, true)." and
                        defaultWorkflow=:id
                ")->execute ($qpg->mergeParams (array (':id' => $this->id))->getParams ());
            } else {
                Yii::app()->db->createCommand("
                    update x2_modules
                    set defaultWorkflow=NULL
                    where defaultWorkflow=:id
                ")->execute (array (':id' => $this->id));
            }

            // if there's a global default, remove it
            Yii::app()->db->createCommand("
                update x2_workflows
                set isDefault=0
                where true
            ")->execute ();
        }
        
        parent::afterSave();
    }

    public function beforeDelete() {
        // Explicitly delete associated actions for installations who have updated and failed
        // to receive the CASCADE on the constraint
        Actions::model()->deleteAllByAttributes(array(
            'workflowId' => $this->id,
        ));

        return parent::beforeDelete ();
    }

    /**
     * @return array workflow names indexed by id 
     */
    public static function getList($enableNone=true) {
        $workflows = X2Model::model('Workflow')->findAll();
        $list = array();
        if($enableNone)
            $list[0] = Yii::t('app','None');
        foreach ($workflows as $model)
            $list[$model->id] = $model->name;
        return $list;
    }

    public static function getWorkflowOptions () {
        if (!isset (self::$_workflowOptions)) {
            self::$_workflowOptions = self::getList (false);
        }
        return self::$_workflowOptions;
    }

    /**
     * @param array $workflowStatus
     * @param int $stage
     * @return bool true if stage can be uncompleted, false otherwise
     */
    private static function canUncomplete ($workflowStatus, $stage) {
        /* can only uncomplete if there is no restriction on backdating, or we're 
           still within the edit time window */
        return Yii::app()->params->isAdmin ||
            Yii::app()->settings->workflowBackdateWindow < 0 ||
            $workflowStatus['stages'][$stage]['completeDate'] == 0 ||
            (time() - $workflowStatus['stages'][$stage]['completeDate']) < 
                 Yii::app()->settings->workflowBackdateWindow;
    }

    public static function getStageUncompletionPermissions ($workflowStatus) {
        $uncompletionPermissions = array ();
        $stageCount = sizeof ($workflowStatus['stages']);
        for ($stageNum = 1; $stageNum <= $stageCount; $stageNum++) {
            $uncompletionPermissions[] = self::canUncomplete ($workflowStatus, $stageNum);
        }
        return $uncompletionPermissions;
    }

    /**
     * This method is equivalent to the JS _checkPermissions method of DragAndDropViewManager
     * @param int $stageA
     * @param int $stageB
     * @param array $workflowStatus
     * @return bool true if user has permissions for all stages in range [$stageA, $stageB].
     */
    private static function checkPermissions ($stageA, $stageB=null, $workflowStatus) {
        $stagePermissions = Workflow::getStagePermissions ($workflowStatus);

        $hasPermission = true;
        if ($stageB === null) {
            return $stagePermissions[$stageA - 1];
        }

        $stageRange = array ($stageA, $stageB);
        sort ($stageRange);

        $hasPermission = array_reduce (array_slice (
            $stagePermissions, $stageRange[0] - 1, ($stageRange[1] - $stageRange[0]) + 1), 
            function ($a, $b) { return $a & $b; }, true);

        return $hasPermission;
    }

    /**
     * @param int $stageA
     * @param int $stageB
     * @param array $workflowStatus
     * @param array $comments Comments indexed by stage number
     * @return bool true if comments array has a comment for each stage which requires a comment
     *  in the range [$stageA, $stageB]
     */
    private static function checkCommentRequirements (
        $stageA, $stageB=null, $workflowStatus, $comments) {

        $stagesWhichRequireComments = Workflow::getStageCommentRequirements ($workflowStatus);
        $commentRequirementsMet = true;

        if ($stageB === null) {
            return !$stagesWhichRequireComments[$stageA - 1] || 
                (isset ($comments[$stageA]) && !empty ($comments[$stageA]));
        }

        for ($i = $stageA - 1; $i < $stageB - 1; ++$i) {
            $commentRequirementsMet &= 
                !$stagesWhichRequireComments[$i] || 
                (isset ($comments[$i + 1]) && !empty ($comments[$i + 1]));
        }

        return $commentRequirementsMet;
    }

    /**
     * @return bool true if stages in range [a, b) can be completed in order such that at each
     *  stage completion, all stage requirements for that stage are met
     */
    private static function checkAllStageRequirements ($stageA, $stageB=null, $workflowStatus) {
        $stageRequirementsMet = true;
        //AuxLib::debugLogR ('checkAllStageRequirements: ' .$stageA.' '.$stageB);

        if ($stageB === null) {
            return self::checkStageRequirement ($stageA, $workflowStatus); 
        }

        $tmpWorfklowStatus = $workflowStatus; // clone array 

        for ($i = $stageA; $i < $stageB; ++$i) {
            //AuxLib::debugLogR ('checking requirements for stage ' . $i);
            $stageRequirementsMet &= 
                self::checkStageRequirement ($i, $tmpWorfklowStatus);
            //AuxLib::debugLogR ((int) $stageRequirementsMet);
            if ($stageRequirementsMet) {
                // mock stage completion since stages will be completed in order from a to b
                $tmpWorfklowStatus['stages'][$i]['complete'] = true;
            } else {
                break;
            }
        }

       //AuxLib::debugLogR ('$requirementMet = ');
       //AuxLib::debugLogR ($stageRequirementsMet);

        return $stageRequirementsMet;
    }


    /**
     * Checks if all required stages are complete
     * @param int $stageNumber
     * @param object $workflowStatus
     * @return bool true if stage dependencies are met, false otherwise
     */
    private static function checkStageRequirement ($stageNumber, $workflowStatus) {
        $requirementMet = true;
        //AuxLib::debugLogR ('checkStageRequirement');
        //AuxLib::debugLogR ($workflowStatus['stages'][$stageNumber]['requirePrevious']);

        // check if all stages before this one are complete
        if($workflowStatus['stages'][$stageNumber]['requirePrevious'] == 
           WorkflowStage::REQUIRE_ALL) {    

            for($i=1; $i<$stageNumber; $i++) {
                if(empty($workflowStatus['stages'][$i]['complete'])) {
                    $requirementMet = false;
                    break;
                }
            }
        } else if($workflowStatus['stages'][$stageNumber]['requirePrevious'] < 0) { 
            // or just check if the specified stage is complete

            if(empty($workflowStatus['stages'][ -1*$workflowStatus['stages'][$stageNumber]
                ['requirePrevious'] ]['complete'])) {

                $requirementMet = false;
            }
        }
        return $requirementMet;
    }

    /**
     * Used to determine if the user has permission to move record from stage a to b, subject to
     * the backdate window restraint.
     * @return bool true if user has permission to revert all stages which will be reverted in
     *  the range [$stageA, $stageB], false otherwise
     */
    private static function checkAllBackdateWindows ($stageA, $stageB=null, $workflowStatus) {
        if (Yii::app()->params->isAdmin) return true;
        //AuxLib::debugLogR ('checkAllBackdateWindows');

        if ($stageB === null) $stageB = $stageA + 1;

        $noBackdateWindowViolations = true;
        $stageRange = array ($stageA, $stageB);
        sort ($stageRange);

        //AuxLib::debugLogR ($workflowStatus);
        //AuxLib::debugLogR ($stageRange);

        for ($i = $stageRange[0]; $i < $stageRange[1]; ++$i) {
            // valid if either stage will not be uncompleted or stage can be completed
            $noBackdateWindowViolations &= 
                !(isset ($workflowStatus['stages'][$i]['complete']) && 
                  $workflowStatus['stages'][$i]['complete']) || 
                self::canUncomplete ($workflowStatus, $i);
            if (!$noBackdateWindowViolations) break;
        }
        return $noBackdateWindowViolations;
    }

    /**
     * @param array $workflowStatus 
     * @param int $stageNumber
     * @return bool true if stage is started, false otherwise
     */
    public static function isStarted ($workflowStatus, $stageNumber) {
        return (self::isCompleted ($workflowStatus, $stageNumber) ||
            $workflowStatus['stages'][$stageNumber]['createDate']);
    }

    /**
     * @param array $workflowStatus 
     * @param int $stageNumber
     * @return bool true if stage is completed, false otherwise
     */
    public static function isCompleted ($workflowStatus, $stageNumber) {
        return $workflowStatus['stages'][$stageNumber]['complete'];
    }

    public static function isInProgress ($workflowStatus, $stageNumber) {
        return self::isStarted ($workflowStatus, $stageNumber) && 
            !self::isCompleted ($workflowStatus, $stageNumber);

    }

    /**
     * Validates a single workflow action. Like validateStageChange () except that only one
     * stage change is validated.
     * @param bool $strict If true, validation will fail in the case that the specified action
     *  cannot be taken because it has already been taken before.
     */
    public static function validateAction (
        $action, $workflowStatus, $stage, $comment='', &$message='') {

        assert (in_array ($action, array ('complete', 'start', 'revert')));

        if (!isset ($workflowStatus['stages'][$stage])) {
            $message = Yii::t(
                'workflow', 'Stage {stage} does not exist', 
                array ('{stage}' => $stage));
            return false;
        }

        // ensure that the stage is in a valid state
        switch ($action) {
            case 'complete':
                if (self::isCompleted ($workflowStatus, $stage)) {
                    $message = Yii::t(
                        'workflow', 'Stage {stage} has already been completed', 
                        array ('{stage}' => $stage));
                    return false;
                }
                break;
            case 'start':
                if (self::isStarted ($workflowStatus, $stage)) {
                    $message = Yii::t(
                        'workflow', 'Stage {stage} has already been started',
                        array ('{stage}' => $stage));
                    return false;
                }
                break;
            case 'revert':
                if (!self::isStarted ($workflowStatus, $stage)) {
                    $message = Yii::t(
                        'workflow', 'Stage {stage} has not been started.',
                        array ('{stage}' => $stage));
                    return false;
                }
                break;
        }


        if (!self::checkPermissions (
            $stage, null, $workflowStatus)) {

            $message = Yii::t('workflow', 'You do not have permission to perform that action.');
            return false;
        }
        if ($action === 'complete' || $action === 'start') {
            if (!self::checkStageRequirement ($stage, $workflowStatus)) {
                $message = Yii::t('workflow', 'Stage requirements were not met.');
                return false;
            }
        } 
        if ($action === 'complete') {
            if (!self::checkCommentRequirements (
                $stage, null, $workflowStatus, array ($stage => $comment))) {

                $message = Yii::t('workflow', 'Stage required a comment but was given none.');
                return false;
            }
        } else if ($action === 'revert') {
            if (!self::checkAllBackdateWindows ($stage, null, $workflowStatus)) {
                $message = Yii::t('workflow', 'Stage could not be reverted because its '.
                    'backdate window has expired.');
                return false;
            } 
        }
        return true;
    }

    /**
     * A helper method for moveFromStageAToStageB. Unlike validateAction, this method does
     * not check whether or not intermediate stages are in valid states.
     * Ensure that current user can move record from stage a to b with given comments. In
     * addition to returning true/false, error flashes are added using X2Flashes.
     * @param int $workflowId
     * @param int $stageA Start stage (indexed by 1) 
     * @param int $stageB End stage (indexed by 1) 
     * @param array $comments comment strings indexed by workflow stage number
     * @return bool true if the change from stage a to b is valid for the given workflow, false
     *  otherwise
     */
    private static function validateStageChange (
        $workflowId, $stageA, $stageB, $modelId, $modelType, $comments=array()) {

        $workflowStatus = Workflow::getWorkflowStatus ($workflowId, $modelId, $modelType);

        $errors = array ();

        // ensure that the record is at the stage that the user thinks it is. It's possible that
        // the date displayed in their interface has become out-of-date as the result of 
        // users simultaenously updating workflow stages. 
        if (!self::isInProgress ($workflowStatus, $stageA)) {
            return array (
                false, 
                Yii::t('workflow', 
                    'Stage change failed. This could be because your interface is displaying '.
                    'out-of-date information. Please try refreshing the page.'));
        }

        if ($stageA < $stageB) {
            if (!self::checkAllStageRequirements ($stageA, $stageB, $workflowStatus)) {
                return array (false, Yii::t('workflow', 'Stage requirements were not met.'));
            } else if (!self::checkCommentRequirements (
                $stageA, $stageB, $workflowStatus, $comments)) {
                // comments only get added when stages are completed

                return array (false,
                    Yii::t('workflow', 'A stage required a comment but was given none.'));
            }
        } else {
            if (!self::checkAllStageRequirements ($stageB, null, $workflowStatus)) {
                // only stage b is started, all other stages in range are reverted

                return array (false, Yii::t('workflow', 'Stage requirements were not met.'));
            } else if (!self::checkAllBackdateWindows ($stageA - 1, $stageB, $workflowStatus)) {
                // check backdate window of all but the first stage, since the first stage
                // never gets uncompleted 

                return array (false,
                    Yii::t('workflow', 'At least one stage could not be reverted because its '.
                    'backdate window has expired.'));
            } 
        }

        if (!self::checkPermissions (
            $stageA, $stageB, $workflowStatus)) {

            return array (false,
                Yii::t('workflow', 'You do not have permission to perform that stage change.'));
        }

        return array (true);
    }

    /**
     * Moves a record up or down a workflow. Assumes that stageA is started but not completed.
     * Intermediate stages and stageB can be in any state.
     * @param int $workflowId
     * @param int $stageA Start stage (indexed by 1) 
     * @param int $stageB End stage (indexed by 1) 
     * @param object $model model associated with workflow
     * @param array $comments comment strings indexed by workflow stage number
     * Precondition: $stageA !== $stageB
     * @return array first element is success, the second is an optional message
     */
    public static function moveFromStageAToStageB (
        $workflowId, $stageA, $stageB, $model, $comments=array()) {

        if ($stageA === $stageB && YII_DEBUG) {
            throw new CException ('Precondition violation: $stageA === $stageB');
        }
        $modelId = $model->id;
        $type = lcfirst (X2Model::getModuleName (get_class ($model)));

        $retVal = self::validateStageChange (
            $workflowId, $stageA, $stageB, $modelId, $type, $comments);

        if (!$retVal[0]) {
            return $retVal;
        }

        // enact stage change
        if ($stageA < $stageB) {
            // complete first stage
            list ($success, $status) = Workflow::completeStage (
                $workflowId, $stageA, $model, 
                isset ($comments[$stageA]) ? $comments[$stageA] : '', false);
            for ($i = $stageA + 1; $i < $stageB; ++$i) {
                // start and complete intermediate stages
                list ($success, $status) = 
                    Workflow::startStage ($workflowId, $i, $model, $status);
                list ($success, $status) = Workflow::completeStage (
                    $workflowId, $i, $model, 
                    isset ($comments[$i]) ? $comments[$i] : '', false, $status);
            }
            list ($success, $status) = 
                Workflow::startStage ($workflowId, $stageB, $model, $status);
            // uncomplete a completed final stage
            list ($success, $status) = 
                Workflow::revertStage ($workflowId, $stageB, $model, false, $status);
        } else { // $stageA > $stageB
            // unstart first stage
            list ($success, $status) = 
                Workflow::revertStage ($workflowId, $stageA, $model);
            for ($i = $stageA - 1; $i > $stageB; --$i) {
                // uncomplete and unstart intermediate stages
                list ($success, $status) = 
                    Workflow::revertStage ($workflowId, $i, $model, $status);
                list ($success, $status)  = 
                    Workflow::revertStage ($workflowId, $i, $model, $status);
            }
            // uncomplete a completed final stage
            list ($success, $status) = 
                Workflow::revertStage ($workflowId, $stageB, $model, false, $status);
            list ($success, $status) = 
                Workflow::startStage ($workflowId, $stageB, $model, false, $status);
        }

        return array (true);
    }

    /**
     * Retrieves information on all stages (their complete state, their stage dependencies,
     * their stage permissions, and their comment requirements) and on the workflow itself
     * (its complete and started state and its id)
     * @param int $workflowId id of workflow
     * @param int $modelId id of model to which the workflow is related (optional)
     * @param mixed $modelType type of model to which the workflow is related. 
     * @return array Contains information about the workflow and its stages
     */
    public static function getWorkflowStatus($workflowId,$modelId=0,$modelType='') {

        $workflowStatus = array(
            'id'=>$workflowId,
            'stages'=>array(),
            'started'=>false,
            'completed'=>true
        );
        
        $workflow = Workflow::model()->findByPk($workflowId);
        if($workflow){
            $workflowStatus['financial'] = $workflow->financial;
            $workflowStatus['financialModel'] = $workflow->financialModel;
            $workflowStatus['financialField'] = $workflow->financialField;
        }
        
        $workflowStages = X2Model::model('WorkflowStage')
            ->findAllByAttributes(
                array('workflowId'=>$workflowId),
                new CDbCriteria(array('order'=>'id ASC')));
        
        // load all WorkflowStage names into workflowStatus
        foreach($workflowStages as &$stage) {    
            $workflowStatus['stages'][$stage->stageNumber] = array(
                'id'=>$stage->id,
                'name'=>$stage->name,
                'requirePrevious'=>$stage->requirePrevious,
                'roles'=>$stage->roles,
                'complete' => false,
                'createDate' => null,
                'completeDate' => null,
                'requireComment'=>$stage->requireComment,
            );
        }
        unset($stage);

        $workflowActions = array();

        
        if($modelId !== 0) {
            $workflowActions = X2Model::model('Actions')->findAllByAttributes(
                array(
                    'associationId'=>$modelId,
                    'associationType'=>$modelType,
                    'type'=>'workflow',
                    'workflowId'=>$workflowId
                ),
                new CDbCriteria(array('order'=>'createDate ASC'))
            );
        }
        
        foreach($workflowActions as &$action) {
            
            
            $workflowStatus['started'] = true; // clearly there's at least one stage up in here
            if(isset($action->workflowStage)){
                $stage = $action->workflowStage->stageNumber;

                // decode workflowActions into a funnel list
                // Note: multiple actions with the same stage will overwrite each other
                $workflowStatus['stages'][$stage]['createDate'] = $action->createDate;        
                $workflowStatus['stages'][$stage]['completeDate'] = $action->completeDate;

                 /* A stage is considered complete if either its complete attribute is true or if it 
                 has a valid complete date. */
                $workflowStatus['stages'][$stage]['complete'] = 
                    ($action->complete == 'Yes') || 
                    (!empty($action->completeDate) && $action->completeDate < time());    

                $workflowStatus['stages'][$stage]['description'] = $action->actionDescription; 
            }
        }
        
        // now scan through and see if there are any incomplete stages
        foreach($workflowStatus['stages'] as &$stage) { 
            if(!isset($stage['completeDate'])) {
                $workflowStatus['completed'] = false;
                break;
            }
        }
        return $workflowStatus;
    }
    
    /**
     * @param int id workflow record id
     * @return array all stage records associated with the workflow
     */
    public static function getStages($id) {
        return Yii::app()->db->createCommand()
            ->select('name')
            ->from('x2_workflow_stages')
            ->where('workflowId=:id',array(':id'=>$id))
            ->order('stageNumber ASC')
            ->queryColumn();
    }

    /**
     * @return array names of stage records associated with the workflow indexed by stage number
     */
    public static function getStagesByNumber ($id) {
        $stages = Yii::app()->db->createCommand()
            ->select('name,stageNumber')
            ->from('x2_workflow_stages')
            ->where('workflowId=:id',array(':id'=>$id))
            ->order('stageNumber ASC')
            ->queryAll();

        $stageNamesIndexedByNumber = array ();
        for ($i = 0; $i < sizeof ($stages); $i++) {
            $stageNamesIndexedByNumber[$stages[$i]['stageNumber']] = $stages[$i]['name'];
        }
        return $stageNamesIndexedByNumber;
    }

    /**
     * @return string Name of stage with given stage number 
     */
    public function getStageName ($stageNumber) {
        $stageName = Yii::app()->db->createCommand()
            ->select('name')
            ->from('x2_workflow_stages')
            ->where('workflowId=:id AND stageNumber=:stageNumber',
                array(
                    ':id'=>$this->id,
                    ':stageNumber'=>$stageNumber,
                ))
            ->queryScalar();
        return $stageName;
    }

    /**
     * @param array return value of getWorkflowStatus 
     * @return <array of strings> one for each stage
     */
    public static function getStageNames ($workflowStatus) {
        $stageCount = count($workflowStatus['stages']);

        $stageNames = array ();
        for($stage=1; $stage<=$stageCount;$stage++) {
            $stageNames[] = $workflowStatus['stages'][$stage]['name'];
        }

        return $stageNames;
    }

    /**
     * @param array return value of getWorkflowStatus 
     * @return <array of bools> One bool for each stage, true if the stage requires a comment,
     *  false otherwise
     */
    public static function getStageCommentRequirements ($workflowStatus) {
        $stageCount = count($workflowStatus['stages']);

        $commentRequirements = array ();

        for($stage=1; $stage<=$stageCount;$stage++) {
            $commentRequirements[] = $workflowStatus['stages'][$stage]['requireComment'];
        }

        return $commentRequirements;
    }

    /**
     * @param array return value of getWorkflowStatus 
     * @return <array of bools> One bool for each stage, true if the current user has permission
     *  for the stage, false otherwise
     */
    public static function getStagePermissions ($workflowStatus) {
        $stageCount = count($workflowStatus['stages']);

        $editPermissions = array ();

        for($stage=1; $stage<=$stageCount;$stage++) {

            // if roles are specified, check if user has any of them
            if(!empty($workflowStatus['stages'][$stage]['roles'])) {
                $editPermissions[] = count(array_intersect(
                    Yii::app()->params->roles,$workflowStatus['stages'][$stage]['roles'])) > 0;
            } else {
                $editPermissions[] = true; // default is full permission for everybody
            }

            if(Yii::app()->params->isAdmin)    // admin override
                $editPermissions[$stage - 1] = true;
            
        }

        return $editPermissions;
    }

    /**
      * get hex codes for each stage
      * @param int $stageCount number of stages
      * @return array array of hex codes, one for each stage
      */
    public function getWorkflowStageColors ($stageCount, $getShaded=false) {

        $color1 = $this->colors['first'];
        $color2 = $this->colors['last'];

        $startingRgb = X2Color::hex2rgb2($color1);
        $endingRgb = X2Color::hex2rgb2($color2);

        $rgbDifference = array(
            $endingRgb[0] - $startingRgb[0],
            $endingRgb[1] - $startingRgb[1],
            $endingRgb[2] - $startingRgb[2],
        );
        
        if ($stageCount === 1) {
            $rgbSteps = array (0, 0, 0);
        } else {
            $steps = $stageCount - 1;
            // 1 step for each stage other than the first
            $rgbSteps = array(
                $rgbDifference[0] / $steps,
                $rgbDifference[1] / $steps,
                $rgbDifference[2] / $steps,
            );
        }

        $colors = array ();
        for($i=0; $i<$stageCount;$i++) {
            $colors[] = X2Color::rgb2hex2(
                 $startingRgb[0] + ($rgbSteps[0]*$i),
                 $startingRgb[1] + ($rgbSteps[1]*$i),
                 $startingRgb[2] + ($rgbSteps[2]*$i)
            );
            if ($getShaded) {
                $colors[$i] = array ($colors[$i]);
                $colors[$i][] = X2Color::rgb2hex2 (array_map (function ($a) {
                    return $a > 255 ? 255 : $a;
                }, array (
                     0.93 * ($startingRgb[0] + ($rgbSteps[0]*$i)),
                     0.93 * ($startingRgb[1] + ($rgbSteps[1]*$i)),
                     0.93 * ($startingRgb[2] + ($rgbSteps[2]*$i))
                )));
            }
        }

        return $colors;
    }

     
    /**
     * Get number of records at each stage
     * @param array $workflowStatus
     * @param array $dateRange return value of WorkflowController::getDateRange ()
     * @param string $users users filter
     * @param string $modelType model type filter
     * @return array number of records at each stage subject to specified filters
     */
    public static function getStageCounts (
        &$workflowStatus, $dateRange, $users='', $modelType='contacts') {

        $stageCount = count($workflowStatus['stages']);

        if ($users !== '') {
            $userString = " AND x2_actions.assignedTo='$users' ";
        } else {
            $userString = "";
        }

        $params = array (
            ':start' => $dateRange['start'],
            ':end' => $dateRange['end'],
            ':workflowId' => $workflowStatus['id'],
        );

        $stageCounts = array ();
        $modelName = X2Model::getModelName ($modelType);
        if(!$modelName){
            $modelType = 'contacts';
            $modelName = 'Contacts';
        }
        $model = X2Model::model($modelName);
        $tableName = $model->tableName();
        list ($accessCondition, $accessConditionParams) = 
            $modelName::model ()->getAccessSQLCondition ($tableName);

        $allParams = array_merge ($params, $accessConditionParams);
        $recordsAtStages = Yii::app()->db->createCommand()
            ->select("stageNumber, COUNT(*)")
            ->from($tableName)
            ->join(
                'x2_actions',
                'x2_actions.associationId='.$tableName.'.id')
            ->where(
                "x2_actions.complete != 'Yes' $userString AND 
                (x2_actions.completeDate IS NULL OR x2_actions.completeDate = 0) AND 
                x2_actions.createDate BETWEEN :start AND :end AND
                x2_actions.type='workflow' AND workflowId=:workflowId AND 
                 associationType='".$modelType."' AND ".$accessCondition,
                $allParams)
            ->group('stageNumber')
            ->queryAll();
        foreach($recordsAtStages as $row){
            $stage = WorkflowStage::model()->findByPk($row['stageNumber']);
            if($stage){
                $stageCounts[$stage->stageNumber - 1] = $row['COUNT(*)'];
            }
        }
        for($i = 0; $i < $stageCount; $i++){
            if(!isset($stageCounts[$i])){
                $stageCounts[$i] = 0;
            }
        }
        ksort($stageCounts);
        return $stageCounts;
    }
    
    public static function getStageValues(
    &$workflowStatus, $dateRange, $users = '', $modelType = 'contacts') {
        $stageValues = array();
        $stageCount = count($workflowStatus['stages']);
        if ($workflowStatus['financial'] && $modelType === $workflowStatus['financialModel']
                && !empty($workflowStatus['financialField'])) {
            $financialField = Fields::model()->findByAttributes(array(
                'modelName' => X2Model::getModelName($workflowStatus['financialModel']),
                'fieldName' => $workflowStatus['financialField']
            ));
            if($financialField){
                $params = array(
                    ':start' => $dateRange['start'],
                    ':end' => $dateRange['end'],
                    ':workflowId' => $workflowStatus['id'],
                );
                if (!empty($users)) {
                    $userString = " AND x2_actions.assignedTo=:user ";
                    $params[':user'] = $users;
                } else {
                    $userString = "";
                }
                $modelName = X2Model::getModelName($modelType);
                $model = X2Model::model($modelName);
                $tableName = $model->tableName();
                list ($accessCondition, $accessConditionParams) = $modelName::model()->getAccessSQLCondition($tableName);

                $allParams = array_merge($params, $accessConditionParams);
                $vals = Yii::app()->db->createCommand()
                    ->select('stageNumber, SUM(' . $workflowStatus['financialField'] . ') as total')
                    ->from($tableName)
                    ->join(
                        'x2_actions',
                        'x2_actions.associationId='.$tableName.'.id')
                    ->where(
                        "x2_actions.complete != 'Yes' $userString AND 
                        (x2_actions.completeDate IS NULL OR x2_actions.completeDate = 0) AND 
                        x2_actions.createDate BETWEEN :start AND :end AND
                        x2_actions.type='workflow' AND workflowId=:workflowId AND 
                         associationType='".$modelType."' AND ".$accessCondition,
                        $allParams)
                    ->group('stageNumber')
                    ->queryAll();
                foreach($vals as $row){
                    $stage = WorkflowStage::model()->findByPk($row['stageNumber']);
                    if($stage){
                        $stageValues[$stage->stageNumber - 1] = $row['total'];
                    }
                }
                for($i = 0; $i < $stageCount; $i++){
                    if(!isset($stageValues[$i])){
                        $stageValues[$i] = 0;
                    }
                }
            }
        }
        for($i = 0; $i < $stageCount; $i++){
            if(!isset($stageValues[$i])){
                $stageValues[$i] = null;
            }
        }
        
        ksort($stageValues);
        return $stageValues;
    }

    /**
     * Helper method for the workflow view.  
     * @return array links for each of the workflow stages. When clicked, the details of a 
     *  particular stage will be shown
     */
    public static function getStageNameLinks (
        &$workflowStatus, $dateRange, $users) {

        $links = array ();
        $stageCount = count($workflowStatus['stages']);

        for($i=1; $i<=$stageCount;$i++) {
            $links[] = CHtml::link(
                $workflowStatus['stages'][$i]['name'],
                array(
                    '/workflow/workflow/view',
                    'id'=>$workflowStatus['id'],
                    'stage'=>$i,
                    'start'=>Formatter::formatDate($dateRange['start']),
                    'end'=>Formatter::formatDate($dateRange['end']),
                    'range'=>$dateRange['range'],
                    $users
                ),
                array(
                    'onclick'=>'x2.WorkflowViewManager.getStageMembers('.$i.'); return false;',
                    'title' => addslashes ($workflowStatus['stages'][$i]['name']),
                )
            );
        }
        return $links;
    }
    
    public function updateStages($newStages){
        $oldIds = array();
        foreach($this->stages as $stage){
            $oldIds[] = $stage->id;
        }
        $newIds = array();
        for ($i = 1; $i <= count($newStages); $i++) {
            if(isset($newStages[$i]['stageId'])){
                $newIds[$i] = $newStages[$i]['stageId'];
            }else{
                $newIds[$i] = '';
            }
        }
        $forDeletion = array_diff($oldIds, $newIds);
        $validStages = true;
        $returnStages = array();
        foreach($newIds as $number => $id){
            $stage = WorkflowStage::model()->findByPk($id);
            if(!$stage){
                $stage = new WorkflowStage;
            }
            $stage->workflowId = $this->id;
            $stage->stageNumber = $number;
            $stage->attributes = $newStages[$number];
            $stage->roles = $newStages[$number]['roles'];
            if(empty($stage->roles) || in_array('',$stage->roles)){
                $stage->roles = array();
            }
            $returnStages[] = $stage;
            if(!$stage->validate()){
                $validStages = false;
            }
        }
        return array($validStages, $returnStages, $forDeletion);
    }
    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('isDefault',$this->isDefault,true);
        $criteria->compare('lastUpdated',$this->lastUpdated);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns stage requirements for each stage in the workflow
     * @param array return value of getWorkflowStatus 
     */
    public static function getStageRequirements ($workflowStatus) {
        $stageCount = count($workflowStatus['stages']);

        $stageRequirements = array ();

        for($stage=1; $stage<=$stageCount;$stage++) {
            $stageRequirements[] = $workflowStatus['stages'][$stage]['requirePrevious'];
        }

        return $stageRequirements;
    }

    /**
     * Completes a workflow stage 
     * @param int $workflowId
     * @param int $stageNumber
     * @param object $model model associated with workflow
     * @param string $comment comment to complete the stage with
     * @param bool $autoStart if true, unless this action completes the workflow, an attempt will
     *  be made to start the next unstarted stage in the case that no other stages have been
     *  started
     * @return array 
     *  (<bool, true if the stage was completed and false otherwise>, <array, the workflow status>)
     */
    public static function completeStage (
        $workflowId,$stageNumber,$model, $comment, $autoStart=true, $workflowStatus=null) {
        //AuxLib::debugLogR ('completing stage '.$stageNumber.'with comment'.$comment);
        $comment = trim($comment);
        
        $modelId = $model->id;
        $type = lcfirst (X2Model::getModuleName (get_class ($model)));

        if (!$workflowStatus)
            $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);

        $stageCount = count($workflowStatus['stages']);
        
        $stage = &$workflowStatus['stages'][$stageNumber];

        $completed = false;
        
        // if stage has been started but not completed. 
        // TODO: verify the assumption that a set createDate indicates a started stage
        if($model !== null && 
            self::isStarted ($workflowStatus, $stageNumber) &&
            !self::isCompleted($workflowStatus, $stageNumber)) {
        
            // is this stage OK to complete? if a comment is required, then is $comment empty?
            if(self::checkStageRequirement ($stageNumber, $workflowStatus) && 
               (!$stage['requireComment'] || ($stage['requireComment'] && !empty($comment)))) {
            
                /*
                Find the action associated with the stage and complete it
                */
            
                
                $action = X2Model::model('Actions')->findByAttributes(
                    array(
                        'associationId'=>$modelId,'associationType'=>$type,'type'=>'workflow',
                        'workflowId'=>$workflowId,'stageNumber'=>$stage['id']
                    )
                );

                $action->setScenario('workflow');
                
                // don't genererate normal action changelog/triggers/events
                $action->disableBehavior('changelog');    
                $action->disableBehavior('TagBehavior'); // no tags
                $action->completeDate = time(); // set completeDate and save model
                $action->dueDate=null;
                $action->complete = 'Yes';
                $action->completedBy = Yii::app()->user->getName();
                $action->actionDescription = $comment;
                $action->save();
                
                $model->updateLastActivity();
                
                self::updateWorkflowChangelog($action,'complete',$model);

                if ($autoStart) {
                
                   /*
                   Find the first stage which hasn't been started and start it
                   */
                   for($i=1; $i<=$stageCount; $i++) {
                       // skip started but not completed stages
                       if($i != $stageNumber && 
                          empty($workflowStatus['stages'][$i]['completeDate']) && 
                          !empty($workflowStatus['stages'][$i]['createDate'])) {

                           break;
                       }
                   
                       // start the next one (unless there is already one)
                       if(empty($workflowStatus['stages'][$i]['createDate'])) {
                           $nextAction = new Actions('workflow');
                           
                           // don't genererate normal action changelog/triggers/events
                           $nextAction->disableBehavior('changelog');    
                           $nextAction->disableBehavior('TagBehavior'); // no tags
                           $nextAction->associationId = $modelId;
                           $nextAction->associationType = $type;
                           $nextAction->assignedTo = Yii::app()->user->getName();
                           $nextAction->type = 'workflow';
                           $nextAction->complete = 'No';
                           $nextAction->visibility = 1;
                           $nextAction->createDate = time();
                           $nextAction->workflowId = $workflowId;
                           $nextAction->stageNumber = $workflowStatus['stages'][$i]['id'];
                           // $nextAction->actionDescription = $comment;
                           $nextAction->save();
   
                           X2Flow::trigger('WorkflowStartStageTrigger',array(
                               'workflow'=>$nextAction->workflow,
                               'model'=>$model,
                               'workflowId'=>$nextAction->workflow->id,
                               'stageNumber'=>$i,
                           ));
                           
                           self::updateWorkflowChangelog($nextAction,'start',$model);
                           
                           // $changes=$this->calculateChanges($oldAttributes, $model->attributes, 
                           //   $model);
                           // $this->updateChangelog($model,$changes);
                           break;
                       }
                   }
               
                }

                // if($stageNumber < $stageCount && empty($workflowStatus[$stageNumber+1]['createDate'])) {    // if this isn't the final stage,
                    
                // }
                
                // refresh the workflow status
                $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);    
                $completed = true;

                X2Flow::trigger('WorkflowCompleteStageTrigger',array(
                    'workflow'=>$action->workflow,
                    'model'=>$model,
                    'workflowId'=>$action->workflow->id,
                    'stageNumber'=>$stageNumber,
                ));
                
                
                if($workflowStatus['completed'])
                    X2Flow::trigger('WorkflowCompleteTrigger',array(
                        'workflow'=>$action->workflow,
                        'model'=>$model,
                        'workflowId'=>$action->workflow->id
                    ));

            }
        }
        //AuxLib::debugLogR ((int) $completed);

        return array ($completed, $workflowStatus);

    }

    /**
     * Starts a workflow stage 
     * @param int $workflowId
     * @param int $stageNumber the stage to start
     * @param object $model model associated with workflow
     */
    public static function startStage (
        $workflowId,$stageNumber,$model,$workflowStatus=null) {

        //AuxLib::debugLogR ('starting stage '.$stageNumber);
        $modelId = $model->id;
        $type = lcfirst (X2Model::getModuleName (get_class ($model)));

        if (!$workflowStatus) 
            $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);

        $stage = $workflowStatus['stages'][$stageNumber];
        //AuxLib::debugLogR ($workflowStatus);
        //assert ($model !== null);

        $started = false;
        
        // if stage has not yet been started or completed
        if($model !== null && 
            self::checkStageRequirement ($stageNumber, $workflowStatus) && 
           !self::isStarted ($workflowStatus, $stageNumber)) {
            
            $action = new Actions('workflow');

            // don't genererate normal action changelog/triggers/events
            $action->disableBehavior('changelog');    
            $action->disableBehavior('TagBehavior'); // no tags up in here
            $action->associationId = $modelId;
            $action->associationType = $type;
            $action->assignedTo = Yii::app()->user->getName();
            $action->updatedBy = Yii::app()->user->getName();
            $action->complete = 'No';
            $action->type = 'workflow';
            $action->visibility = 1;
            $action->createDate = time();
            $action->lastUpdated = time();
            $action->workflowId = (int)$workflowId;
            $action->stageNumber = (int)$stage['id'];
            $action->save();
            
            $model->updateLastActivity();

            X2Flow::trigger('WorkflowStartStageTrigger',array(
                'workflow'=>$action->workflow,
                'model'=>$model,
                'workflowId'=>$action->workflow->id,
                'stageNumber'=>$stageNumber,
            ));
            
            if(!$workflowStatus['started'])
                X2Flow::trigger('WorkflowStartTrigger',array(
                    'workflow'=>$action->workflow,
                    'model'=>$model,
                    'workflowId'=>$action->workflow->id,
                ));
            
            self::updateWorkflowChangelog($action,'start',$model);
            $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
            $started = true;
        }

        //AuxLib::debugLogR ((int) $started);
        return array ($started, $workflowStatus);
    }

    /**
     * Uncompletes a stage (if completed) or unstarts it (if started).
     * @param $unstarts bool If false, will not attempt to unstart an ongoing stage
     */
    public static function revertStage (
        $workflowId,$stageNumber,$model,$unstart=true,$workflowStatus=null) {

        //AuxLib::debugLogR ('reverting stage '.$stageNumber);

        $modelId = $model->id;
        $type = lcfirst (X2Model::getModuleName (get_class ($model)));
        
        if (!$workflowStatus)
            $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
        
        $stage = $workflowStatus['stages'][$stageNumber];
        $reverted = false;
        
        // if stage has been started or completed
        if($model !== null &&
            self::isStarted ($workflowStatus, $stageNumber)) {

            $action = X2Model::model('Actions')->findByAttributes(
                array(
                    'associationId'=>$modelId,'associationType'=>$type,'type'=>'workflow',
                    'workflowId'=>$workflowId,'stageNumber'=>$stage['id']
                )
            );

            // the stage is complete, so just set it to 'started'
            if(self::isCompleted ($workflowStatus, $stageNumber) && 
               self::canUncomplete ($workflowStatus, $stageNumber)) {

                //AuxLib::debugLogR ('uncompleting stage '.$stageNumber);
                $action->setScenario('workflow');
                
                // don't genererate normal action changelog/triggers/events
                $action->disableBehavior('changelog');    
                $action->disableBehavior('TagBehavior'); // no tags up in here
                $action->complete = 'No';
                $action->completeDate = null;
                $action->completedBy = '';

                // original completion note no longer applies
                $action->actionDescription = '';    
                $action->save();
                
                self::updateWorkflowChangelog($action,'revert',$model);

                X2Flow::trigger('WorkflowRevertStageTrigger',array(
                    'workflow'=>$action->workflow,
                    'model'=>$model,
                    'workflowId'=>$action->workflow->id,
                    'stageNumber'=>$stageNumber,
                ));
                
                // delete all incomplete stages after this one
                // X2Model::model('Actions')->deleteAll(new CDbCriteria(
                    // array('condition'=>"associationId=$modelId AND associationType='$type' AND type='workflow' AND workflowId=$workflowId AND stageNumber > $stageNumber AND (completeDate IS NULL OR completeDate=0)")
                // ));
                
                
            } else if ($unstart) { 
                // the stage is already incomplete, so delete it and all subsequent stages

                $subsequentActions = X2Model::model('Actions')->findAll(new CDbCriteria(
                    array(
                        'condition' => 
                            "associationId=$modelId AND associationType='$type' ".
                                "AND type='workflow' AND workflowId=$workflowId ".
                                "AND stageNumber >= {$stage['id']}"
                    )
                ));
                foreach($subsequentActions as &$action) {
                    self::updateWorkflowChangelog($action,'revert',$model);
                    X2Flow::trigger('WorkflowRevertStageTrigger',array(
                        'workflow'=>$action->workflow,
                        'model'=>$model,
                        'workflowId'=>$action->workflow->id,
                        'stageNumber'=>$action->stageNumber,
                    ));
                    $action->delete();
                }
            }
            $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
            $reverted = true;
        }
        //AuxLib::debugLogR ((int) $reverted);
        return array ($reverted, $workflowStatus);
    }

    public static function updateWorkflowChangelog(&$action,$changeType,&$model) {
        $changelog = new Changelog;
        // $type = $action->associationType=='opportunities'?"Opportunity":ucfirst($action->associationType);
        $changelog->type = get_class($model);
        $changelog->itemId = $action->associationId;
        // $record=X2Model::model(ucfirst($type))->findByPk($action->associationId);
        // if(isset($record) && $record->hasAttribute('name')){
            // $changelog->recordName=$record->name;
        // }else{
            // $changelog->recordName=$type;
        // }X2Flow::trigger('WorkflowStageCompleteTrigger',array('workflow'=>'model'=>$model));
        $changelog->recordName = $model->name;
        $changelog->changedBy = Yii::app()->user->getName();
        $changelog->timestamp = time();
        $changelog->oldValue = '';
        
        $workflowName = $action->workflow->name;
        // $workflowName = Yii::app()->db->createCommand()->select('name')->from('x2_workflows')->where('id=:id',array(':id'=>$action->workflowId))->queryScalar();
        $stageName = Yii::app()->db->createCommand()
            ->select('name')
            ->from('x2_workflow_stages')
            ->where(
                'workflowId=:id AND stageNumber=:sn',
                array(
                    ':sn'=>$action->stageNumber,
                    ':id'=>$action->workflowId))
                ->queryScalar();
        
        $event = new Events;
        $event->associationType = 'Actions';
        $event->associationId = $action->id;
        $event->user = Yii::app()->user->getName();
        
        if($changeType === 'start') {
            //$trigger = 'WorkflowStartStageTrigger';
            $event->type = 'workflow_start';
            $changelog->newValue='Workflow Stage Started: '.$stageName;
            
        } elseif($changeType === 'complete') {
            //$trigger = 'WorkflowCompleteStageTrigger';
            $event->type = 'workflow_complete';
            $changelog->newValue = 'Workflow Stage Completed: '.$stageName;
            
        } elseif($changeType === 'revert') {
            //$trigger = 'WorkflowRevertStageTrigger';
            $event->type = 'workflow_revert';
            $changelog->newValue = 'Workflow Stage Reverted: '.$stageName;
            
        } else {
            return;
        }
        
        /*X2Flow::trigger($trigger,array(
            'workflow'=>$action->workflow,
            'model'=>$model,
            'stageNumber'=>$action->stageNumber,
            'stageName'=>$stageName,
        ));*/
        
        $event->save();
        $changelog->save();
    }

    public function getStageNameAutoCompleteSource() {
        if (!isset ($this->_stageNameAutoCompleteSource)) {
            $this->_stageNameAutoCompleteSource = Yii::app()->controller->createUrl (
                '/workflow/workflow/getStageNameItems');
        }
        return $this->_stageNameAutoCompleteSource;
    }


    /**
     * @param array $colors an array of color hex values, 1 for each stage 
     * @return array css color strings to be used for pipeline list item backgrounds
     */
    public static function getPipelineListItemColors ($colors) {
        $listItemColors = array ();
        for ($i = 1; $i <= count ($colors); ++$i) {
            list($r,$g,$b) = X2Color::hex2rgb2 ($colors[$i-1][0]);
            $listItemColors[$i - 1][] = "rgba($r, $g, $b, 0.20)";
            $listItemColors[$i - 1][] = "rgba($r, $g, $b, 0.12)";
        }
        return $listItemColors;
    }

    public function getDisplayName ($plural=true, $ofModule=true) {
        return Yii::t('workflow', '{process}', array(
            '{process}' => Modules::displayName($plural, 'Process'),
        ));
    }
    
    public static function getCurrencyFields($model='contacts'){
        $ret = array();
        if(X2Model::getModelName($model)){
            $financialFields = Fields::model()->findAllByAttributes(array(
                'modelName'=>X2Model::getModelName($model),
                'type' => 'currency'
            ));
            foreach($financialFields as $field){
                $ret[$field->fieldName] = $field->attributeLabel;
            }
            asort($ret);
        }
        return $ret;
    }

}
