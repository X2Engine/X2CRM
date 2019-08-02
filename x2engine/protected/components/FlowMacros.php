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
 * Widget class for Flow Macros.
 *
 * @package application.components
 */
class FlowMacros extends X2Widget {

    public $visibility;

    public function init() {
        parent::init();
    }

    public function run() {
        $modelType = null;
        Yii::app()->controller->redirectOnNullModel = false;
        try {
            $model = Yii::app()->controller->getModel();
        } catch (Exception $e) {
            $model = null;
        }
        Yii::app()->controller->redirectOnNullModel = true;
        if(isset(Yii::app()->controller->module)){
            $modelType = X2Model::getModelName(Yii::app()->controller->module->getName());
        }
        if ($modelType === 'Contacts' && Yii::app()->controller->action->id === 'list') {
            // When viewing a list, instead use the X2List model
            $modelType = 'X2List';
        }
        $flows = $this->getFlows($modelType);
        // Only show Flow Macros on records that have flows
        if (empty($flows) || !($model instanceof X2Model)) {
            return;
        }
        $flowNames = array();
        $flowDescriptions = array();
        foreach ($flows as $id => $flowData) {
            $flowNames[$id] = $flowData['name'];
            $flowDescriptions[$id] = nl2br($flowData['description']);
        }
        $jsonConstants = JSON_HEX_TAG + JSON_HEX_AMP + JSON_HEX_APOS + JSON_HEX_QUOT;
        $this->render('flowMacros', array(
            'flows' => $flowNames,
            'flowDescriptions' => json_encode($flowDescriptions, $jsonConstants),
            'modelType' => $modelType,
            'modelId' => $model->id
        ));
    }

    private function getFlows($modelType) {
        $ret = array();

        $flows = Yii::app()->db->createCommand()
                ->select('id, name, description')
                ->from('x2_flows')
                ->where('triggerType = :type AND modelClass = :model',array(':type'=>'MacroTrigger', ':model'=>$modelType))
                ->queryAll();
        foreach($flows as $row){
            $ret[$row['id']] = array('name' => $row['name'], 'description' => $row['description']);
        }
        return $ret;
    }

}

?>
