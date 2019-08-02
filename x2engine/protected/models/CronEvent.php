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
 * This is the model class for table "x2_crontab".
 *
 * The followings are the available columns in table 'x2_crontab':
 * @property integer $id
 * @property string $type (email, x2flow, periodicTrigger)
 * @property boolean $recurring
 * @property integer $priority
 * @property integer $time the timestamp to run this cron at, or for recurring crons, the interval to execute at
 * @property string $interval 
 * @property string $data
 * @property integer $createDate
 * @property integer $lastExecution
 * @property integer $executionCount
 */
class CronEvent extends CActiveRecord {

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CronEvent the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_cron_events';
	}

    public function afterSave () {
        return parent::afterSave ();
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type', 'required'),
			array('type, interval', 'length', 'max'=>20),
			array('recurring', 'boolean'),
			array('id, time, priority, createDate, lastExecution, executionCount', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, time, data, createDate, lastExecution, executionCount', 'safe', 'on'=>'search'),
		);
	}

    public function behaviors(){
        return array(
			'JSONEmbeddedModelFieldsBehavior' => array(
				'class' => 'application.components.behaviors.JSONEmbeddedModelFieldsBehavior',
				'transformAttributes' => array ('schedule'),
				'fixedModelFields' => array ('schedule' => 'CronSchedule'),
				'encryptedFlagAttr' => false,
			),
        );
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'type' => 'Type',
			'recurring' => 'Recurring',
			'priority' => 'Priority',
			'time' => 'Time',
			'interval' => 'Interval',
			'data' => 'Data',
			'createDate' => 'Create Date',
			'lastExecution' => 'Last Execution',
			'executionCount' => 'Execution Count',
		);
	}

    /**
     * Sets/updates the next execution time of a recurring cron event.
     *
     * This method should be called on all recurring cron events, after the
     * action of said events has been taken. It is expected that the "time"
     * attribute never be in the future when this method runs.
     * 
     * @return type
     */
    public function recur($update = true) {
        if((integer) $this->interval <= 0)
            return;
        $this->lastExecution = time();
        $interval = (integer) max($this->interval, 1);
        // If the last execution time is more than one interval's
        // length into the past, add more intervals' worth of time
        $intervals = (integer) max(floor(($this->lastExecution - $this->time) / $interval)+1, 1);
        $this->time = $this->time + $this->interval * $intervals;
        if($update)
            $this->update(array('lastExecution', 'time'));
    }

    protected function beforeValidate () {
        $this->createDate = time ();
        return parent::beforeValidate ();
    }

    public function getPastDueCronEvents () {

        // get recurring scheduled events occurring now
        $recurringScheduleEvents = $this->findAllByAttributes (array (
            'type' => 'recurringSchedule'
        ));
        $now = time ();
        $timeNow = array (
            'minutes' => (int) date ('i', $now),
            'hours' => (int) date ('H', $now),
            'dayOfMonth' => (int) date ('d', $now),
            'month' => (int) date ('m', $now),
            'dayOfWeek' => (int) date ('w', $now),
        );
        $events = array ();
        foreach ($recurringScheduleEvents as $event) {
            $schedule = $event->schedule;
            $scheduled = true;
            foreach ($schedule as $field => $val) {
                if (!in_array ('*', $val) && !in_array ($timeNow[$field], $val)) {
                    $scheduled = false;
                    break;
                }
            }
            if ($scheduled) $events[] = $event;
        }

        // get past due offset-schedule events
        $events = array_merge ($events, CActiveRecord::model('CronEvent')->findAllBySql(
            'SELECT * from x2_cron_events ' .
            'WHERE type != "recurringSchedule" AND time < ' . time()));

        // sort by priority descending, id ascending
        usort ($events, function ($a, $b) {
            if ($b->priority === $a->priority) {
                return $a->id - $b->id;
            } else {
                return $b->priority - $a->priority;
            }
        });
        return $events;
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
/* 	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('type',$this->type,true);
		$criteria->compare('time',$this->time,true);
		$criteria->compare('data',$this->data,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('lastExecution',$this->lastExecution,true);
		$criteria->compare('executionCount',$this->executionCount,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	} */
}
