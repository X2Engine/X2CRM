<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import('application.components.x2flow.X2FlowItem');
Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');

/**
 * @package X2CRM.controllers
 */
class StudioController extends x2base {
	// Declares class-based actions.

	public $modelClass = 'X2Flow';


	// public $layout = '//layouts/column1';
	public function filters() {
		return array(
			'setPortlets',
			//'accessControl',
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

	public function actionTest() {
		echo CRYPT_SALT_LENGTH ;
		// var_dump($a instanceof X2Model);

		// $a = array();
		// $a = array(''=>Yii::t('studio','Custom')) + Docs::getEmailTemplates();
		// $a = Docs::getEmailTemplates();
		// var_dump($a);

		// $act = new X2FlowEmail;
		// $act->config = array (
			// 'type' => 'X2FlowEmail',
			// 'options' => array (
				// 'to' => 'me@x2engine.com',
				// 'from' => 'mpearson@x2engine.com',
				// 'template' => '',
				// 'subject' => 'Hey you!',
				// 'cc' =>'',
				// 'bcc' =>'',
				// 'body' => 'test test test test'
			// )
		// );
		// var_dump($act->execute($a));

		// $x = X2FlowTrigger::checkCondition(array('type'=>'time_of_day','operator'=>'<','value'=>'11:30'),$a);
		// var_dump($x);

		/* $triggerName = 'RecordDeleteTrigger';
		$params = array('model'=>new Contacts);

		$flowAttributes = array('triggerType'=>$triggerName);

		if(isset($params['model']))
			$flowAttributes['modelClass'] = get_class($params['model']);

		$results = array();

		// find all flows matching this trigger and modelClass
		foreach(CActiveRecord::model('X2Flow')->findAllByAttributes($flowAttributes) as $flow) {
			// file_put_contents('triggerLog.txt',"\n".$triggerName,FILE_APPEND);
			$flowData = CJSON::decode($flow->flow);	// parse JSON flow data


			if($flowData !== false && isset($flowData['trigger']['type'],$flowData['items'][0]['type'])) {

				$trigger = X2FlowTrigger::create($flowData['trigger']);

				if($trigger === null || !$trigger->validateRules($params) || !$trigger->check($params))
					return;
				// var_dump($trigger->check($params));
				$results[] = array($flow->name,$flow->executeBranch($flowData['items'],$params));
			}
		}
		var_dump($results); */
	}

	public function actionGetParams($name,$type) {

		if($type === 'action') {
			$paramRules = X2FlowAction::getParamRules($name);	// X2Flow Actions
		} elseif($type === 'trigger') {
			$paramRules = X2FlowTrigger::getParamRules($name);	// X2Flow Triggers
		} elseif($type === 'condition') {
            // generic conditions (for triggers and switches)
			$paramRules = X2FlowTrigger::getGenericCondition($name); 
		} else {
			$paramRules = false;
        }

		if($paramRules !== false) {
			if($type === 'condition') {
				if(isset($paramRules['options']))
					$paramRules['options'] = X2FlowAction::dropdownForJson($paramRules['options']);
			} else {
				foreach($paramRules['options'] as &$option) {	// find any dropdowns and reformat them
					if(isset($option['options']))				// so the item order is preserved in JSON
						$option['options'] = X2FlowAction::dropdownForJson($option['options']);
				}
			}
		}
		echo CJSON::encode($paramRules);
	}

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
				$data['options'] = X2FlowAction::dropdownForJson(X2Model::getAssignmentOptions(true, true));
			} elseif($field->type === 'dropdown') {
				$data['linkType'] = $field->linkType;
				$data['options'] = X2FlowAction::dropdownForJson(Dropdowns::getItems($field->linkType));
			}

			if($field->type === 'link') {
				$staticLinkModel = X2Model::model($field->linkType);
				if(array_key_exists('X2LinkableBehavior', $staticLinkModel->behaviors())) {
					$data['linkType'] = $field->linkType;
					$data['linkSource'] = Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource);
				}
			}


			$fields[] = $data;
		}
		echo CJSON::encode($fields);
	}

    function actionDeleteAllTriggerLogs ($flowId) {
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

    function actionDeleteAllTriggerLogsForAllFlows () {
        $triggerLogs = TriggerLog::model()->findAll ();
        foreach ($triggerLogs as $log) {
            $log->delete ();
        }
        echo "success";
    }

    function actionDeleteTriggerLog ($id) {
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
