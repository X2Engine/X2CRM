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
 * X2FlowAction that reassigns a record
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowRecordReassign extends X2FlowAction {

    public $title = 'Reassign Record';
    public $info = 'Assign the record to a user or group, or automatically using lead routing.';

    public function __construct(){
        $this->attachBehavior('LeadRoutingBehavior', array('class' => 'LeadRoutingBehavior'));
    }

    public function paramRules(){
        $leadRoutingModes = array(
            '' => 'Free For All',
            'roundRobin' => 'Round Robin Distribution',
            'roundRobin' => 'Sequential Distribution',
            'singleUser' => 'Direct User Assignment'
        );
        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelRequired' => 1,
            'options' => array(
                // array('name'=>'routeMode','label'=>'Routing Method','type'=>'dropdown','options'=>$leadRoutingModes),
                array('name' => 'user', 'label' => 'User', 'type' => 'dropdown', 'multiple' => 1, 'options' => array('auto' => Yii::t('studio', 'Use Lead Routing')) + X2Model::getAssignmentOptions(true, true)),
            // array('name'=>'onlineOnly','label'=>'Online Only?','optional'=>1,'type'=>'boolean','defaultVal'=>false),
                ));
    }

    public function execute(&$params){
        $model = $params['model'];
        if(!$model->hasAttribute('assignedTo')){
            return array(
                false,
                Yii::t('studio', get_class($model).' records have no attribute "assignedTo"')
            );
        }

        $user = $this->parseOption('user', $params);
        if($user === 'auto'){
            $assignedTo = $this->getNextAssignee();
        }elseif(CActiveRecord::model('User')->exists('username=?', array($user)) || CActiveRecord::model('Groups')->exists('id=?', array($user))){ // make sure the user exists
            $assignedTo = $user;
        }else{
            return array(false, Yii::t('studio', 'User '.$user.' does not exist'));
        }

        if($model->updateByPk(
                        $model->id, array('assignedTo' => $assignedTo))){

            if(is_subclass_of($model, 'X2Model')){
                return array(
                    true,
                    Yii::t('studio', 'View updated record: ').$model->getLink()
                );
            }else{
                return array(true, "");
            }
        }else{
            return array(false, "");
        }
    }

}
