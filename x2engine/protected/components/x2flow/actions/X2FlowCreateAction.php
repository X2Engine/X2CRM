<?php
/*********************************************************************************
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
 ********************************************************************************/

/**
 * X2FlowAction that creates a new action
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowCreateAction extends X2FlowAction {
	public $title = 'Create Action';
	public $info = 'Creates a new action for the specified user.';

	public function paramRules() {
		$visOptions = array(
			1 => Yii::t('actions','Public'),
			0 => Yii::t('actions','Private'),
		);
		$priorityOptions = array(
			'1' => Yii::t('actions','Low'),
			'2' => Yii::t('actions','Medium'),
			'3' => Yii::t('actions','High')
		);
		// $assignmentOptions = array('{assignedTo}'=>'{'.Yii::t('studio','Owner of Record').'}') + X2Model::getAssignmentOptions(false,true);	// '{assignedTo}', groups, no 'anyone'
		$assignmentOptions = array('{assignedTo}' => '{'.Yii::t('studio', 'Owner of Record').'}') + X2Model::getAssignmentOptions(false, true);	// '{assignedTo}', groups, no 'anyone'

		return array(
			'title' => Yii::t('studio',$this->title),
			'options' => array(
				// array('name'=>'attributes'),
                array('name'=>'dueDate','label'=>Yii::t('actions','Due Date'),'type'=>'dateTime', 'optional'=>1),
				array('name'=>'subject','label'=>Yii::t('actions','Subject'),'optional'=>1),
				array('name'=>'description','label'=>Yii::t('actions','Description'),'type'=>'text'),
				array('name'=>'assignedTo','label'=>Yii::t('actions','Assigned To'),'type'=>'dropdown','options'=>$assignmentOptions),
				array('name'=>'priority','label'=>Yii::t('actions','Priority'),'type'=>'dropdown','options'=>$priorityOptions),
				array('name'=>'visibility','label'=>Yii::t('actions','Visibility'),'type'=>'dropdown','options'=>$visOptions),
				// array('name'=>'reminder','label'=>Yii::t('actions','Remind Me'),'type'=>'checkbox','default'=>false),
			));
	}

	public function execute(&$params) {
		$options = $this->config['options'];

		$action = new Actions;

		$action->subject = $this->parseOption('subject',$params);
        $action->dueDate = $this->parseOption('dueDate',$params);
		$action->actionDescription = $this->parseOption('description',$params);
		$action->priority = $this->parseOption('priority',$params);
		$action->visibility = $this->parseOption('visibility',$params);
		// $action->

		if(isset($params['model']))
			$action->assignedTo = $this->parseOption('assignedTo',$params);

		// if(isset($this->config['attributes']))
			// $this->setModelAttributes($action,$this->config['attributes'],$params);

        if ($action->save()) {
            return array (
                true,
                Yii::t('studio', "View created action: ").$action->getLink ());
        } else {
            return array(false, array_shift($action->getErrors()));
        }



		// if($this->parseOption('reminder',$params)) {
			// $notif=new Notification;
			// $notif->modelType='Actions';
			// $notif->createdBy=Yii::app()->user->getName();
			// $notif->modelId=$model->id;
			// if($_POST['notificationUsers']=='me'){
				// $notif->user=Yii::app()->user->getName();
			// }else{
				// $notif->user=$model->assignedTo;
			// }
			// $notif->createDate=$model->dueDate-($_POST['notificationTime']*60);
			// $notif->type='action_reminder';
			// $notif->save();
			// if($_POST['notificationUsers']=='both' && Yii::app()->user->getName()!=$model->assignedTo){
				// $notif2=new Notification;
				// $notif2->modelType='Actions';
				// $notif2->createdBy=Yii::app()->user->getName();
				// $notif2->modelId=$model->id;
				// $notif2->user=Yii::app()->user->getName();
				// $notif2->createDate=$model->dueDate-($_POST['notificationTime']*60);
				// $notif2->type='action_reminder';
				// $notif2->save();
			// }
		// }
	}
}
