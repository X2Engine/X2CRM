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
 * X2FlowAction that creates a new record
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRecordCreate extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Create Record';
    public $info = 'Creates a new record.';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        return array_merge(parent::paramRules(), array(
            'title' => $this->title,
            'info' => Yii::t('studio', $this->info),
            'modelClass' => 'modelClass',
            'options' => array(
                array('name' => 'attributes'),
                array(
                    'name' => 'modelClass', 'label' => Yii::t('studio', 'Record Type'),
                    'type' => 'dropdown',
                    'options' => X2Flow::getModelTypes(true)
                ),
                array(
                    'name' => 'createRelationship',
                    'label' =>
                    Yii::t('studio', 'Create Relationship') .
                    '&nbsp;' .
                    X2Html::hint2(
                            Yii::t('app', 'Check this box if you want a new relationship to be ' .
                                    'established between the record created by this action and the ' .
                                    'record that triggered the flow.')),
                    'type' => 'boolean',
                    'defaultVal' => false,
                ),
            ),
        ));
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        // make sure this is a valid model type
        if (!is_subclass_of($this->config['modelClass'], 'X2Model')) {
            return array(false, "");
        }
        if (!isset($this->config['attributes']) || empty($this->config['attributes'])) {
            return array(false, "");
        }

        // verify that if create relationship option was set, that a relationship can be made
        if ($this->parseOption('createRelationship', $params)) {
            $acceptedModelTypes = X2Model::getModelTypesWhichSupportRelationships();

            if (!in_array($this->config['modelClass'], $acceptedModelTypes)) {
                return array(false, Yii::t('admin', 'Relationships cannot be made with records ' .
                            'of type {type}.', array('{type}' => $this->config['modelClass'])));
            }
            if (!isset($params['model'])) { // no model passed to trigger
                return array(false, '');
            }
            if (!in_array(get_class($params['model']), $acceptedModelTypes)) {
                return array(false, Yii::t('admin', 'Relationships cannot be made with records ' .
                            'of type {type}.', array('{type}' => get_class($params['model']))));
            }
        }

        $model = new $this->config['modelClass'];
        $model->setScenario('X2FlowCreateAction');
        if ($this->setModelAttributes($model, $this->config['attributes'], $params) &&
                $model->save()) {

            if ($this->parseOption('createRelationship', $params)) {
                $params['model']->createRelationship($model);
            }
            return array(
                true,
                Yii::t('studio', 'View created record: ') . $model->getLink());
        } else {
            $errors = $model->getErrors();
            return array(false, array_shift($errors));
        }
    }

}
