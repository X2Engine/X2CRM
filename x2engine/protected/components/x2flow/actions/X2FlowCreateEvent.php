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
 * X2FlowAction that creates an event
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCreateEvent extends X2FlowAction {

    public $title = 'Post to Activity Feed';
    public $info = 'Creates an activity feed event.'; // You can write your own message, or X2CRM will automatically choose one based on what triggered this flow.';

    public function paramRules(){
        // $eventTypes = array('auto'=>Yii::t('app','Auto')) + Dropdowns::getItems(113,'app');
        $eventTypes = Dropdowns::getItems(113, 'studio');

        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array('name' => 'type', 'label' => Yii::t('studio', 'Post Type'), 'type' => 'dropdown', 'options' => $eventTypes),
                array('name' => 'text', 'label' => Yii::t('studio', 'Text'), 'type' => 'text'),
                array('name' => 'user', 'optional' => 1, 'label' => 'User (optional)', 'type' => 'dropdown', 'options' => array('' => '----------', 'auto' => 'Auto') + X2Model::getAssignmentOptions(false, false)),
                array('name' => 'createNotif', 'label' => Yii::t('studio', 'Create Notification?'), 'type' => 'boolean', 'defaultVal' => true),
                ));
    }

    public function execute(&$params){
        $options = &$this->config['options'];

        $event = new Events;
        $notif = new Notification;

        $user = $this->parseOption('user', $params);

        $type = $this->parseOption('type', $params);

        if($type === 'auto'){
            if(!isset($params['model']))
                return false;
            $notif->modelType = get_class($params['model']);
            $notif->modelId = $params['model']->id;
            $notif->type = $this->getNotifType();

            $event->associationType = get_class($params['model']);
            $event->associationId = $params['model']->id;
            $event->type = $this->getEventType();
            if($params['model']->hasAttribute('visibility'))
                $event->visibility = $params['model']->visibility;
            // $event->user = $this->parseOption('user',$params);
        } else{
            $text = $this->parseOption('text', $params);

            $notif->type = 'custom';
            $notif->text = $text;

            $event->type = 'feed';
            $event->subtype = $type;
            $event->text = $text;
            if($user == 'auto' && isset($params['model']) && $params['model']->hasAttribute('assignedTo') && !empty($params['model']->assignedTo)){
                $event->user = $params['model']->assignedTo;
            }elseif(!empty($user)){
                $event->user = $user;
            }else{
                $event->user = 'admin';
            }
        }
        if(!$this->parseOption('createNotif', $params)) {
            if (!$notif->save()) {
                return array(false, array_shift($notif->getErrors()));
            }
        }

        if ($event->save()) {
            return array (true, "");
        } else {
            return array(false, array_shift($event->getErrors()));
        }

    }

}
