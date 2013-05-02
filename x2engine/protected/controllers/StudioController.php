<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');

/**
 * @package X2CRM.controllers
 */
class StudioController extends x2base {
	// Declares class-based actions.


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

	public function actionDeleteFlow($id) {
		$model = $this->loadModel($id);
		$model->delete();
		$this->redirect(array('flowIndex'));
	}

	public function actionTest() {
		$a = array();
		
		$act = new X2FlowEmail;
		$act->config = array (
			'type' => 'X2FlowEmail',
			'options' => array (
				'to' => 'me@x2engine.com',
				'from' => 'mpearson@x2engine.com',
				'template' => '',
				'subject' => 'Hey you!',
				'cc' =>'',
				'bcc' =>'',
				'body' => 'test test test test'
			)
		);
		var_dump($act->execute($a));
		
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

		if($type === 'action')
			$paramRules = X2FlowAction::getParamRules($name);	// X2Flow Actions
		elseif($type === 'trigger')
			$paramRules = X2FlowTrigger::getParamRules($name);	// X2Flow Triggers
		elseif($type === 'condition')
			$paramRules = X2FlowTrigger::getGenericCondition($name); // generic conditions (for triggers and switches)
		else
			$paramRules = false;

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
				$data['options'] = X2FlowAction::dropdownForJson(User::getNames());
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

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 *
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 */
	public function loadModel($id) {
		if(null === $model = CActiveRecord::model('X2Flow')->findByPk((int)$id))
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}
}
