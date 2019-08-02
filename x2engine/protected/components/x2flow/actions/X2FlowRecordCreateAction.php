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
 * X2FlowAction that creates a new action
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRecordCreateAction extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Create Action for Record';
    public $info = 'Creates a new action for associated record.';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelRequired' => 1,
            'options' => array(
                // array('name'=>'attributes'),
                array('name' => 'subject', 'label' => Yii::t('actions', 'Subject'), 'optional' => 1),
                array('name' => 'description', 'label' => Yii::t('actions', 'Description'), 'type' => 'text')
        )));
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $action = new Actions;
        $action->associationType = X2Model::getAssociationType(get_class($params['model']));
        $action->associationId = $params['model']->id;
        $action->subject = $this->parseOption('subject', $params);
        $action->actionDescription = $this->parseOption('description', $params);
        if ($params['model']->hasAttribute('assignedTo')) {
            $action->assignedTo = $params['model']->assignedTo;
        }
        if ($params['model']->hasAttribute('priority')) {
            $action->priority = $params['model']->priority;
        }
        if ($params['model']->hasAttribute('visibility')) {
            $action->visibility = $params['model']->visibility;
        }

        if ($action->save()) {
            return array(
                true,
                Yii::t('studio', "View created action: ") . $action->getLink()
            );
        } else {
            $errors = $action->getErrors();
            return array(false, array_shift($errors));
        }
    }

}
