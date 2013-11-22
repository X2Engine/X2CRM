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
 * X2FlowTrigger 
 * 
 * @package X2CRM.components.x2flow.actions
 */
class ActionOverdueTrigger extends X2FlowTrigger {

    public $requiresCron = true;
	public $title = 'Action Overdue';
	public $info = 'Triggers when an action becomes overdue. Cronjob must be configured to trigger reliably.';
	
	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelClass' => 'Actions',
			'options' => array(
				array('name'=>'duration','label'=>Yii::t('studio','Time Overdue (s)'),'type'=>'numeric','optional'=>1),
			));
	}
	
	public static function checkCondition($condition,&$params) {
		if(isset($condition['name']) && $condition['name'] === 'duration') {
			if(empty($condition['value']))
				return array ($params['model']->dueDate < time(), '');
			return array ($params['model']->dueDate < time() - (int)$condition['value'], '');
		}
		return parent::checkCondition($condition,$params);
	}
}
