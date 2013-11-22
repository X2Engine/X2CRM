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
 * This is the model class for table "x2_sessions".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $user
 * @property integer $lastUpdated
 * @property string $IP
 * @property integer $status
 */
class SessionLog extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Session the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_session_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('timestamp', 'numerical', 'integerOnly'=>true),
			array('user', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user, timestamp', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','Sesesion ID'),
			'user' => Yii::t('admin','User'),
			'timestamp' => Yii::t('admin','Timestamp'),
			'status' => Yii::t('admin','Session Event'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
        $criteria->compare('sessionId',$this->id);
		$criteria->compare('user',$this->user,true);
		$criteria->compare('timestamp',$this->lastUpdated);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
    }

    public static function logSession($user, $sessionId, $status){
        $sessionLog=Yii::app()->db->createCommand()
                ->select('sessionLog')
                ->from('x2_admin')
                ->where('id=1')
                ->queryScalar();
        if($sessionLog){
            $model=new SessionLog;
            $model->user=$user;
            $model->sessionId=$sessionId;
            $model->status=$status;
            $model->timestamp=time();
            $model->save();
        }
    }

    public static function parseStatus($status){
        $ret=$status;
        switch($status){
            case 'login':
                $ret='Logged In';
                break;
            case 'invisible':
                $ret="Went Invisible";
                break;
            case 'visible':
                $ret="Went Visible";
                break;
            case 'passiveTimeout':
                $ret='Timeout On Session Cleanup';
                break;
            case 'activeTimeout':
                $ret='Timeout On User Activity';
                break;
            case 'logout':
                $ret="Logged Out";
                break;
        }
        return $ret;
    }
}