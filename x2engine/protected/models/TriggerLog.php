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
 * This is the model class for table "x2_trigger_logs".
 * @package X2CRM.models
 */
class TriggerLog extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Imports the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_trigger_logs';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('flowId', 'required'),
            array('flowId', 'length', 'max' => 100)
        );
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'triggeredAt' => Yii::t('studio','Triggered At'),
			'triggerLog' => Yii::t('studio','Log Output')
		);
	}

    /**
     * Appends flow trace to trigger log. This is called after a flow exectution is initiated
     * by a cron action.
     * @param Integer $triggerLogId the primary key of the trigger log model associated with the
     *  flow execution
     * @param Array $flowTrace the return value of executeFlow 
     */
    public static function appendTriggerLog ($triggerLogId, $flowTrace) {
        $model = self::model ('TriggerLog')->findByPk ($triggerLogId);
        if (!$model) {
            //AuxLib::debugLog ('appendTriggerLog: model is null');; 
            return;
        }
        //AuxLib::debugLog ('appendTriggerLog: model is not null');; 

        //AuxLib::debugLog ('appendTriggerLog: triggerlog old = '.$model->triggerLog);
        $oldLog = CJSON::decode ($model->triggerLog); 
        $model->triggerLog = CJSON::encode (array_merge ($oldLog, $flowTrace));
        $model->save ();
        //AuxLib::debugLog ('new trigger log ='. $model->triggerLog); 
    }

}

