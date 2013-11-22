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
 * X2FlowAction that creates a new action
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowRecordCreateAction extends X2FlowAction {

    public $title = 'Create Action for Record';
    public $info = 'Creates a new action associated with the record that triggered this flow.';

    public function paramRules(){
        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelRequired' => 1,
            'options' => array(
                // array('name'=>'attributes'),
                array('name' => 'subject', 'label' => Yii::t('actions', 'Subject'), 'optional' => 1),
                array('name' => 'description', 'label' => Yii::t('actions', 'Description'), 'type' => 'text')
                ));
    }

    public function execute(&$params){
        $action = new Actions;
        $action->associationType = lcfirst(get_class($params['model']));
        $action->associationId = $params['model']->id;
        $action->subject = $this->parseOption('subject', $params);
        $action->actionDescription = $this->parseOption('description', $params);
        if($params['model']->hasAttribute('assignedTo'))
            $action->assignedTo = $params['model']->assignedTo;
        if($params['model']->hasAttribute('priority'))
            $action->priority = $params['model']->priority;
        if($params['model']->hasAttribute('visibility'))
            $action->visibility = $params['model']->visibility;

        if ($action->save()) {
            return array (
                true,
                Yii::t('studio', "View created action: ").$action->getLink ()
            );
        } else {
            return array(false, array_shift($action->getErrors()));
        }

    }

}

