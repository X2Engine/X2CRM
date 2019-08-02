<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * This is the model class for table "x2_trigger_logs".
 * @package application.models
 */
class TriggerLog extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Imports the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

    public function behaviors() {
        return array_merge (parent::behaviors (), array(
            'RecordLimitBehavior' => array(
                'class' => 'RecordLimitBehavior',
                'limit' => Yii::app()->settings->triggerLogMax,
                'timestampField' => 'triggeredAt',
            ),
        ));
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
            return;
        }

        $oldLog = is_array(CJSON::decode ($model->triggerLog)) ? CJSON::decode ($model->triggerLog) : array(); 
        $model->triggerLog = CJSON::encode (array_merge ($oldLog, $flowTrace));
        $model->save ();
    }

}

