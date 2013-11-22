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
class RecordInactiveTrigger extends X2FlowTrigger {
	public $title = 'Record Inactivity';
	public $info = 'Cronjob must be configured for this to work reliably.';
	
	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelClass' => 'modelClass',
			'options' => array(
				array('name'=>'modelClass','label'=>Yii::t('studio','Record Type'),'type'=>'dropdown','options'=>X2Model::getModelTypes(true)),
				array('name'=>'duration','type'=>'numeric','label'=>Yii::t('studio','Duration (s)')),
			));
	}
	
	public static function checkCondition($condition,&$params) {
		if(isset($condition['name']) && $condition['name'] === 'duration') {
			if($params['model']->hasAttribute('lastActivity'))
				return array ($params['model']->lastActivity < time() - (int)$condition->value,
                    '');
			elseif($params['model']->hasAttribute('lastUpdated'))
				return array ($params['model']->lastUpdated < time() - (int)$condition->value,
                    '');
			else
				return array (false, '');
		} else {
			return parent::checkCondition($condition,$params);
		}
	}
}
