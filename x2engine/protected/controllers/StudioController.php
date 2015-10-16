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
        $this->render('flowIndex');
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
    public function actionGetFields($model) {
        if(!class_exists($model)) {
            echo 'false';
            return;
        }
        $fieldModels = X2Model::model($model)->getFields();
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
                if(array_key_exists('X2LinkableBehavior', $staticLinkModel->behaviors())) {
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
    
    

     

}
