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
 * X2FlowAction that creates a notification
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCreateNotif extends X2FlowAction {

    public $title = 'Create Popup Notification';

    // public $info = 'You can type a custom message, or X2CRM will automatically choose one based on the event that triggered this flow.';

    public function paramRules(){
        $notifTypes = array('auto' => 'Auto', 'custom' => 'Custom');
        $assignmentOptions = array('{assignedTo}' => '{'.Yii::t('studio', 'Owner of Record').'}') + X2Model::getAssignmentOptions(false, false); // '{assignedTo}', no groups, no 'anyone'

        return array(
            'title' => Yii::t('studio', $this->title),
            // 'info' => Yii::t('studio',$this->info),
            'options' => array(
                array('name' => 'user', 'label' => Yii::t('studio', 'User'), 'type' => 'assignment', 'options' => $assignmentOptions), // just users, no groups or 'anyone'
                // array('name'=>'type','label'=>'Type','type'=>'dropdown','options'=>$notifTypes),
                array('name' => 'text', 'label' => Yii::t('studio', 'Message'), 'optional' => 1),
                ));
    }

    public function execute(&$params){
        $options = &$this->config['options'];

        $notif = new Notification;
        $notif->user = $this->parseOption('user', $params);
        $notif->createdBy = 'API';
        $notif->createDate = time();
        // file_put_contents('triggerLog.txt',"\n".$notif->user,FILE_APPEND);
        // if($this->parseOption('type',$params) == 'auto') {
        // if(!isset($params['model']))
        // return false;
        // $notif->modelType = get_class($params['model']);
        // $notif->modelId = $params['model']->id;
        // $notif->type = $this->getNotifType();
        // } else {
        $notif->type = 'custom';
        $notif->text = $this->parseOption('text', $params);
        // }

        if ($notif->save()) {
            return array (true, "");
        } else {
            return array(false, array_shift($notif->getErrors()));
        }

    }

}
