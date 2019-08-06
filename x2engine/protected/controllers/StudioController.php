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




Yii::import('application.components.x2flow.X2FlowItem');
Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');

/**
 * @package application.controllers
 */
class StudioController extends x2base {

    public $modelClass = 'X2Flow';

    // public $layout = '//layouts/column1';
    public function filters() {
        return array(
            'setPortlets',
            //'accessControl',
        );
    }


    public function behaviors(){
        return array_merge (parent::behaviors () , 
            array(
                'ImportExportBehavior' => array('class' => 'ImportExportBehavior'),
            )
        );
    }


    public function actions() {
        if(file_exists(Yii::app()->getBasePath().'/components/FlowDesignerAction.php')) {
            return array(
                'flowDesigner'=>array(
                    'class'=>'FlowDesignerAction'
                ),
            );
        }
        return array();
    }


    public function actionFlowIndex() {
        $model = new X2Flow('search');
        $this->render('flowIndex',array(
            'model'=>$model,
        ));
    }

    public function actionTriggerLogs($pageSize=null) {
        $triggerLogsDataProvider = new CActiveDataProvider('TriggerLog', array(
            'criteria' => array(
                'order' => 'triggeredAt DESC'
            ),
            'pagination'=>array(
                'pageSize' => !empty($pageSize) ?
                    $pageSize :
                    Profile::getResultsPerPage()
            ),
        ));
        $viewParams['triggerLogsDataProvider'] = $triggerLogsDataProvider;
        $this->render('triggerLogs', array (
            'triggerLogsDataProvider' => $triggerLogsDataProvider
            )
        );
    }

    public function actionDeleteFlow($id) {
        $model = $this->loadModel($id);
        $model->delete();
        $this->redirect(array('flowIndex'));
    }
    
    

    public function actionGetParams($name,$type) {
        if($type === 'action') {
            $paramRules = X2FlowAction::getParamRules($name);    // X2Flow Actions
        } elseif($type === 'trigger') {
            $paramRules = X2FlowTrigger::getParamRules($name);    // X2Flow Triggers
        } elseif($type === 'condition') {
            // generic conditions (for triggers and switches)
            $paramRules = X2FlowTrigger::getGenericCondition($name); 
        } else {
            $paramRules = false;
        }

        if($paramRules !== false) {
            if($type === 'condition') {
                if(isset($paramRules['options']))
                    $paramRules['options'] = AuxLib::dropdownForJson($paramRules['options']);
            } else {
                // find any dropdowns and reformat them
                foreach($paramRules['options'] as &$option) { 
                    if(isset($option['options'])) // so the item order is preserved in JSON
                        $option['options'] = AuxLib::dropdownForJson($option['options']);
                }
                // do the same for suboptions, if they're present
                if (isset ($paramRules['suboptions'])) {
                    foreach($paramRules['suboptions'] as &$subOption) {
                        if(isset($subOption['options']))        
                            $subOption['options'] = AuxLib::dropdownForJson(
                                $subOption['options']);
                    }
                }
            }
        }
        echo CJSON::encode($paramRules);
    }

    // reports TODO
    public function actionGetFields($model, $type = 'all') {
        if(!class_exists($model)) {
            echo 'false';
            return;
        }
        $filterFn = function($field){ return true; };
        if($type !== 'all'){
            $filterFn = function($field) use ($type) { return $field->type === $type; };
        }
        $fieldModels = X2Model::model($model)->getFields(false, $filterFn);
        $fields = array();

        foreach($fieldModels as &$field) {
            if($field->isVirtual)
                continue;
            $data = array(
                'name' => $field->fieldName,
                'label' => $field->attributeLabel,
                'type' => $field->type,
            );

            if($field->required)
                $data['required'] = 1;
            if($field->readOnly)
                $data['readOnly'] = 1;
            if($field->type === 'assignment' || $field->type === 'optionalAssignment' ) {
                $data['options'] = AuxLib::dropdownForJson(
                    X2Model::getAssignmentOptions(true, true));
                if ($field->type === 'assignment')
                    $data['multiple'] = $field->linkType === 'multiple' ? 1 : 0;
            } elseif($field->type === 'dropdown') {
                $data['linkType'] = $field->linkType;
                $dropdown = Dropdowns::model ()->findByPk ($field->linkType);
                if (!$dropdown) continue;
                $data['options'] = AuxLib::dropdownForJson(Dropdowns::getItems($field->linkType));
                $data['multiple'] = $dropdown->multi ? 1 : 0;
            }

            if($field->type === 'link') {
                $staticLinkModel = X2Model::model($field->linkType);
                if(array_key_exists('LinkableBehavior', $staticLinkModel->behaviors())) {
                    $data['linkType'] = $field->linkType;
                    $data['linkSource'] = Yii::app()->controller->createUrl(
                        $staticLinkModel->autoCompleteSource);
                }
            }


            $fields[] = $data;
        }
        usort ($fields, function ($a, $b) {
            return strcmp ($a['label'], $b['label']);
        });
        echo CJSON::encode($fields);
    }

    public function actionDeleteAllTriggerLogs ($flowId) {
        if (isset ($flowId)) {
            $triggerLogs = TriggerLog::model()->findAllByAttributes (array (
                'flowId' => $flowId
            ));
            foreach ($triggerLogs as $log) {
                $log->delete ();
            }
            echo "success";
        } else {
            echo "failure";
        }
    }

    public function actionDeleteAllTriggerLogsForAllFlows () {
        $triggerLogs = TriggerLog::model()->findAll ();
        foreach ($triggerLogs as $log) {
            $log->delete ();
        }
        echo "success";
    }

    public function actionDeleteTriggerLog ($id) {
        if (isset ($id)) {
            $triggerLog = TriggerLog::model()->findByAttributes (array (
                'id' => $id
            ));
            if (!empty ($triggerLog)) {
                $triggerLog->delete ();
                echo "success";
                return;
            }
        }
        echo "failure";
    }
    
    
    public function actionExecuteMacro() {
        if (Yii::app()->request->isAjaxRequest) {
            $id = filter_input(INPUT_POST, 'flowId', FILTER_SANITIZE_NUMBER_INT);
            $modelType = filter_input(INPUT_POST, 'modelType', FILTER_DEFAULT);
            $modelId = filter_input(INPUT_POST, 'modelId', FILTER_DEFAULT);

            if (empty($modelType) || empty($modelId)) {
                throw new CHttpException(400, 'Your request is invalid.');
            }
            $flow = X2Flow::model()->findByPk($id);
            if (empty($id) || !isset($flow) || $flow->modelClass !== $modelType) {
                throw new CHttpException(400, 'Please select a valid workflow.');
            }
            if ($flow->triggerType !== 'MacroTrigger') {
                throw new CHttpException(400, 'Selected flow must use the Macro Trigger.');
            }
            $model = X2Model::model($modelType)->findByPk($modelId);
            if (!isset($model)) {
                throw new CHttpException(400, 'Macros must be executed on an existing model.');
            }
            if (!$this->X2PermissionsBehavior->checkPermissions($model, 'view')) {
                throw new CHttpException(403, 'You are not authorized to view this record.');
            }
            $params['modelClass'] = $modelType;
            $params['model'] = $model;
            X2Flow::executeFlow($flow, $params, null);
        }
    }
    

     
    /**
     * Simple validation function for imported flow. A more sophisticated validation method
     * is needed but this, at least, ensures that the flow can be saved.
     * @param string $flow Decoded imported flow file contents
     * @return bool
     */
    public function validateImportedFlow ($flow) {
        if (!is_array ($flow) ||
            !isset ($flow['flowName']) ||
            !isset ($flow['trigger']) ||
            !is_array ($flow['trigger']) ||
            !isset ($flow['trigger']['type']))  {

            return false;
        }
        return true;
    }

    /**
     * Import a flow which was exported with the flow export tool 
     */
    public function actionImportFlow () {
        $model = null;
        if (isset ($_FILES['flowImport'])) {
            if (AuxLib::checkFileUploadError ('flowImport')) {
                throw new CException (
                    AuxLib::getFileUploadErrorMessage ($_FILES['flowImport']['error']));
            }
            $fileName = $_FILES['flowImport']['name'];
            $ext = pathinfo ($fileName, PATHINFO_EXTENSION);
            if ($ext !== 'json') {
                throw new CException (Yii::t('studio', 'Invalid file type'));
            }
            $data = file_get_contents($_FILES['flowImport']['tmp_name']);
           
            $flow = CJSON::decode ($data);
            if ($this->validateImportedFlow ($flow)) {
                $model = new X2Flow;
                $model->name = $flow['flowName'];
                $model->description = $flow['flowDesc'];
                $model->triggerType = $flow['trigger']['type'];
                $model->flow = CJSON::encode ($flow);
                $model->active = false;
                if ($model->save ()) {
                    $this->redirect(
                        $this->createUrl ('/studio/flowDesigner', array ('id' => $model->id)));
                } 
            }
            Yii::app()->user->setFlash ('error', Yii::t('studio', 'Invalid file contents'));
        }

        $this->render ('importFlow', array (
            'model' => $model,
        ));
    }

    /**
     * Exports flow json as .json file and provides a download link 
     */
    public function actionExportFlow ($flowId) {
        $flowId = $_GET['flowId'];
        $flow = X2Flow::model()->findByPk ($flowId);
        $download = false;
        $_SESSION['flowExportFile'] = '';
        if (isset ($_GET['export'])) {
            $flowJSON = $flow->flow; 
            $file = 'flow.json'; 
            $filePath = $this->safePath($file);
            file_put_contents ($filePath, $flowJSON);
            $_SESSION['flowExportFile'] = $file;
            $download = true;
        } 
        $this->render ('exportFlow', array (
            'flow' => $flow,
            'download' => $download
        ));
    }
     

}
