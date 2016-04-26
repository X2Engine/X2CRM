<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/



/**
 * X2FlowAction that creates a notification
 *
 * @package application.components.x2flow.actions
 */
class X2FlowWait extends X2FlowAction {
	public $title = 'Wait';
    public $requiresCron = true;
	public $info = 'Delay execution of the remaining steps until the specified time.';

	public $flowId = null;

    public function getFormattedUnit ($unit) {
        switch ($unit) {
            case 'mins':
                return Yii::t('studio', 'minute');
            case 'hours':
                return Yii::t('studio', 'hour');
            case 'days':
                return Yii::t('studio', 'day');
            case 'months':
                return Yii::t('studio', 'month');
            case 'secs':
                return Yii::t('studio', 'second');
            default:
                return '';
        }
    }

	public function paramRules() {
		$units = array(
			'mins'=>Yii::t('studio','minutes'),
			'hours'=>Yii::t('studio','hours'),
			'days'=>Yii::t('studio','days'),
			'months'=>Yii::t('studio','months'),
            'secs'=>Yii::t('studio','seconds (recommended for formulas only)'),
		);
		return array_merge (parent::paramRules (), array (
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
            'requiresCron' => $this->requiresCron,
			'options' => array(
				array('name'=>'delay','label'=>Yii::t('studio','For')),
				array(
                    'name'=>'unit',
                    'label'=>Yii::t('studio','Type'),
                    'type'=>'dropdown',
                    'options'=>$units
                ),
				// array('name'=>'user','label'=>'User','type'=>'assignment','options'=>$assignmentOptions),	// just users, no groups or 'anyone'
				// array('name'=>'type','label'=>'Type','type'=>'dropdown','options'=>$notifTypes),
				// array('name'=>'timeOfDay','type'=>'time','label'=>'Time of Day','optional'=>1),
			)));
	}

	public function execute(&$params, $triggerLogId=null) {
		$options = &$this->config['options'];
        $options['delay']['value']=$this->parseOption('delay',$params);
		if(!is_numeric($options['delay']['value']))
			return array (false, "");

		$time = X2FlowItem::calculateTimeOffset(
            (int) $options['delay']['value'], $options['unit']['value']);

		if($time === false) {
			return array (false, "");
        }
        $timeOffset = $time + time ();

		$cron = new CronEvent;
		$cron->type = 'x2flow';
		$cronData = array(
			'flowId'=>$this->flowId,
            // called flowPath since it referred to a path to the wait action before 5.2
            'flowPath' => $this->config['id'],
            'triggerLogId'=>$triggerLogId
		);
		$cron->time = $timeOffset;

		if(isset($params['model'])) {
            // stored in two places for legacy reasons. CronBehavior expects model id and type
            // to be stored in JSON data. Eventually duplication should be removed and only
            // association columns should be used.
			$cronData['modelId'] = $params['model']->id;
			$cronData['modelClass'] = get_class($params['model']);
            $cron->associationType = get_class ($params['model']);
            $cron->associationId = $params['model']->id;
		}
		foreach(array_keys($params) as $param) {

            // remove any models so the JSON doesn't get crazy long
			if(is_object($params[$param]) && $params[$param] instanceof CActiveRecord){	
				$tmpModel = $params[$param];
                unset($params[$param]);
            }
		}

		$cronData['params'] = $params;

		$cron->data = CJSON::encode($cronData);
		// $cron->validate();
        if(isset($tmpModel)){
            $params['model']=$tmpModel;
        }
		if ($cron->save()) {
			return array (
                true, "Waiting for " . $options['delay']['value'] . ' ' . 
                $this->getFormattedUnit ($options['unit']['value']) . '(s)');
        } else {
			return array (false, "");
        }
	}
}
