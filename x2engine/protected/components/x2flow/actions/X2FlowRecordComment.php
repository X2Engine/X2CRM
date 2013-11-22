<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * X2FlowAction that adds a comment to a record
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowRecordComment extends X2FlowAction {

    public $title = 'Add Comment';
    public $info = '';

    public function paramRules(){
        $assignmentOptions = array('{assignedTo}' => '{'.Yii::t('studio', 'Owner of Record').'}') + X2Model::getAssignmentOptions(false, true);
        return array(
            'title' => Yii::t('studio', $this->title),
            'model' => 'required',
            'options' => array(
                array('name' => 'assignedTo', 'label' => Yii::t('actions', 'Assigned To'), 'type' => 'dropdown', 'options' => $assignmentOptions),
                array('name' => 'comment', 'label' => Yii::t('studio', 'Comment'), 'type' => 'text'),
            )
        );
    }

    public function execute(&$params){
        $model = new Actions;
        $model->type = 'note';
        $model->complete = 'Yes';
        $model->associationId = $params['model']->id;
        $model->associationType = $params['model']->module;
        $model->actionDescription = $this->parseOption('comment', $params);
        $model->assignedTo = $this->parseOption('assignedTo', $params);
        $model->completedBy = $this->parseOption('assignedTo', $params);

        if(empty($model->assignedTo) && $params['model']->hasAttribute('assignedTo')){
            $model->assignedTo = $params['model']->assignedTo;
            $model->completedBy = $params['model']->assignedTo;
        }

        if($params['model']->hasAttribute('visibility'))
            $model->visibility = $params['model']->visibility;
        $model->createDate = time();
        $model->completeDate = time();

        if($model->save()){
            return array(
                true,
                Yii::t('studio', 'View created action: ').$model->getLink());
        }else{
            return array(false, array_shift($model->getErrors()));
        }
    }

}
