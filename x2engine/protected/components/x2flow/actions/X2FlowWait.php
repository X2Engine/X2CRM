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
 * X2FlowAction that creates a notification
 *
 * @package X2CRM.components.x2flow.actions
 */
class X2FlowWait extends X2FlowAction {
	public $title = 'Wait';
    public $requiresCron = true;
	public $info = 'Delay execution of the remaining steps until the specified time.';

	public $flowId = null;
	public $flowPath = null;

	public function paramRules() {

		$units = array(
			'mins'=>Yii::t('studio','minutes'),
			'hours'=>Yii::t('studio','hours'),
			'days'=>Yii::t('studio','days'),
			'months'=>Yii::t('studio','months'),
            'secs'=>Yii::t('studio','seconds (recommended for formulas only)'),
		);
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
            'requiresCron' => $this->requiresCron,
			'options' => array(
				// array('name'=>'user','label'=>'User','type'=>'assignment','options'=>$assignmentOptions),	// just users, no groups or 'anyone'
				// array('name'=>'type','label'=>'Type','type'=>'dropdown','options'=>$notifTypes),
				array('name'=>'delay','label'=>Yii::t('studio','For')),
				array('name'=>'unit','label'=>Yii::t('studio','Type'),'type'=>'dropdown','options'=>$units),
				// array('name'=>'timeOfDay','type'=>'time','label'=>'Time of Day','optional'=>1),
			));
	}

	public function execute(&$params, $triggerLogId=null) {
		$options = &$this->config['options'];
        $options['delay']['value']=$this->parseOption('delay',$params);
		if(!is_array($this->flowPath) || !is_numeric($options['delay']['value']))
			return array (false, "");

		$time = X2FlowItem::calculateTimeOffset(
            (int)$options['delay']['value'],$options['unit']['value']);

		if($time === false) {
			return array (false, "");
        }
		$time += time();

        // add 1 to the branch position in the flow path, to skip this action
		$this->flowPath[count($this->flowPath)-1]++;	

		$cron = new CronEvent;
		$cron->type = 'x2flow';
		$cron->createDate = time();
		$cronData = array(
			'flowId'=>$this->flowId,
			'flowPath'=>$this->flowPath,
            'triggerLogId'=>$triggerLogId
		);
		$cron->time = $time;

		if(isset($params['model'])) {
			$cronData['modelId'] = $params['model']->id;
			$cronData['modelClass'] = get_class($params['model']);
		}
		foreach(array_keys($params) as $param) {

            // remove any models so the JSON doesn't get crazy long
			if(is_object($params[$param]) && $params[$param] instanceof CActiveRecord){	
				$tmpModel = $params['model'];
                unset($params['model']);
            }
		}

		$cronData['params'] = $params;

		$cron->data = CJSON::encode($cronData);
		// $cron->validate();
		// die(var_dump($cron->getErrors()));
        if(isset($tmpModel)){
            $params['model']=$tmpModel;
        }
		if ($cron->save()) {
			return array (true, "");
        } else {
			return array (false, "");
        }
		// $notif->user = $this->parseOption('user',$params);

	}
}











