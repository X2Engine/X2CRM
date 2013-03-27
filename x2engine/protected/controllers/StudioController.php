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
 
/*********************************************************************************
 * Portions created by X2Engine are Copyright (C) X2Engine, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

/**
 * 
 * 
 * @package X2CRM.controllers
 */
class StudioController extends x2base {
	// Declares class-based actions.
	

	public $layout = '//layouts/column1';
	public function filters() {
		return array(
			'setPortlets',
			'accessControl',
		);
	}

	
	public function actionGetParams($name,$type) {
		if($type === 'action') {
			Yii::import('application.components.x2flow.actions.*');
			echo CJSON::encode(X2FlowItem::getActionParamRules($name));
		} else {
			Yii::import('application.components.x2flow.triggers.*');
			echo CJSON::encode(X2FlowItem::getTriggerParamRules($name));
		}
	}
	
	public function actionGetFields($model) {
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
			if($field->type === 'assignment' && $field->linkType === 'multiple')
				$data['type'] = 'assignment_multiple';
			if($field->type === 'dropdown' || $field->type === 'link')
				$data['linkType'] = $field->linkType;
			
			$fields[] = $data;
		}
		echo CJSON::encode($fields);
	}
	
	
		
	public function actionFlowDesigner() {
		$t0 = microtime(true);
		if(Yii::app()->user->getName() !== 'admin')
			throw new CHttpException(403, 'You are not authorized to perform this action.');
			
		Yii::import('application.components.x2flow.actions.*');
		
		$actionTypes = array();
		foreach(scandir(Yii::getPathOfAlias('application.components.x2flow.actions')) as $file) {
			if($file === '.' || $file === '..' || $file === 'X2FlowAction.php')
				continue;
			
			$class = X2FlowAction::create(substr($file,0,-4));	// remove file extension and create instance
			if($class !== null)
				$actionTypes[get_class($class)] = $class->title;
		}
		// var_dump(microtime(true)-$t0);
		// die(var_dump(array_keys($actionTypes)));
		
		
		$this->render('flowEditor',array('actionTypes'=>$actionTypes));
	
	}
	
	
}
