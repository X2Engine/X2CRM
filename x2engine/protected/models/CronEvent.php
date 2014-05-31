<?php

/**
 * This is the model class for table "x2_crontab".
 *
 * The followings are the available columns in table 'x2_crontab':
 * @property integer $id
 * @property string $type
 * @property boolean $recurring
 * @property integer $priority
 * @property integer $time the timestamp to run this cron at, or for recurring crons, the interval to execute at
 * @property string $interval 
 * @property string $data
 * @property integer $createDate
 * @property integer $lastExecution
 * @property integer $executionCount
 * 
 * 
 * Cron event types:
 *    email
 *    x2flow
 *    
 *    
 * 
 * 
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

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, data, createDate', 'required'),
			array('type, interval', 'length', 'max'=>20),
			array('recurring', 'boolean'),
			array('id, time, priority, createDate, lastExecution, executionCount', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, time, data, createDate, lastExecution, executionCount', 'safe', 'on'=>'search'),
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