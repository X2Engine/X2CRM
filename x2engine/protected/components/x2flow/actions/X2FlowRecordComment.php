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
 * X2FlowAction that adds a comment to a record
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRecordComment extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Add Comment';
    public $info = 'Adds a comment to associated record.';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        $assignmentOptions = array('{assignedTo}' => '{' . Yii::t('studio', 'Owner of Record') . '}') +
                X2Model::getAssignmentOptions(false, true);
        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelRequired' => 1,
            'options' => array(
                array(
                    'name' => 'assignedTo',
                    'label' => Yii::t('actions', 'Assigned To'),
                    'type' => 'dropdown', 'options' => $assignmentOptions,
                ),
                array(
                    'name' => 'comment',
                    'label' => Yii::t('studio', 'Comment'),
                    'type' => 'text'
                ),
            )
        ));
    }
    
    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $model = new Actions;
        $model->type = 'note';
        $model->complete = 'Yes';
        $model->associationId = $params['model']->id;
        $model->associationType = $params['model']->module;
        $model->actionDescription = $this->parseOption('comment', $params);
        $model->assignedTo = $this->parseOption('assignedTo', $params);
        $model->completedBy = $this->parseOption('assignedTo', $params);

        if (empty($model->assignedTo) && $params['model']->hasAttribute('assignedTo')) {
            $model->assignedTo = $params['model']->assignedTo;
            $model->completedBy = $params['model']->assignedTo;
        }

        if ($params['model']->hasAttribute('visibility')) {
            $model->visibility = $params['model']->visibility;
        }
        $model->createDate = time();
        $model->completeDate = time();

        if ($model->save()) {
            return array(
                true,
                Yii::t('studio', 'View created action: ') . $model->getLink());
        } else {
            $errors = $model->getErrors();
            return array(false, array_shift($errors));
        }
    }

}
