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

Yii::import('application.models.X2Model');
Yii::import('application.modules.user.models.*');

/**
 * This is the model class for table "x2_services".
 *
 * @package X2CRM.modules.services.models
 */
class Services extends X2Model {

	public $account;

	/**
	 * Returns the static model of the specified AR class.
	 * @return Services the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_services';
	}

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'services',
		//		'icon'=>'accounts_icon.png',
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		));
	}

    public function rules () {
        $parentRules = parent::rules ();
        /*$parentRules[]= array (
            'firstName,lastName', 'required', 'on' => 'webForm');*/
        return $parentRules;
    }

    public function afterFind(){
        if($this->name != $this->id) {
			$this->name = $this->id;
			$this->update(array('name'));
		}
        return parent::afterFind();
    }

	/**
	 *
	 * @return boolean whether or not to save
	 */
	public function afterSave() {
		$model = $this->getOwner();

		$oldAttributes = $model->getOldAttributes();

		if($model->escalatedTo != '' && (!isset($oldAttributes['escalatedTo']) || $model->escalatedTo != $oldAttributes['escalatedTo'])) {
			$event=new Events;
			$event->type='case_escalated';
			$event->user=$this->updatedBy;
			$event->associationType='Services';
			$event->associationId=$model->id;
			if($event->save()){
				$notif = new Notification;
				$notif->user = $model->escalatedTo;
				$notif->createDate = time();
				$notif->type = 'escalateCase';
				$notif->modelType = 'Services';
				$notif->modelId = $model->id;
				$notif->save();
			}
		}

		parent::afterSave();
	}

	public function search() {
		$criteria=new CDbCriteria;
		return $this->searchBase($criteria);
	}

	/**
	 *  Like search but filters by status based on the user's profile
	 *
	 */
	public function searchWithStatusFilter() {
		$criteria=new CDbCriteria;
		foreach($this->getFields(true) as $fieldName => $field) {

			if($fieldName == 'status') { // if status exists
				// filter statuses based on user's profile
				$hideStatus = CJSON::decode(Yii::app()->params->profile->hideCasesWithStatus); // get a list of statuses the user wants to hide
				if(!$hideStatus) {
					$hideStatus = array();
				}
				foreach($hideStatus as $hide) {
					$criteria->compare('t.status', '<>'.$hide);
				}
			}
		}
		$criteria->together = true;
		return $this->searchBase($criteria);
	}



}
