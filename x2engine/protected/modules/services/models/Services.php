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




Yii::import('application.models.X2Model');
Yii::import('application.modules.user.models.*');

/**
 * This is the model class for table "x2_services".
 *
 * @package application.modules.services.models
 */
class Services extends X2Model {

	public $account;

    public $verifyCode; // CAPTCHA for Service case form

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
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'services',
		//		'icon'=>'accounts_icon.png',
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		));
	}

    public function rules () {
        $rules = array_merge(parent::rules (), array(
            array(
                'verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements(),
                    'on' => 'webFormWithCaptcha', 'captchaAction' => 'site/webleadCaptcha')
        ));
        return $rules;
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
	public function searchWithStatusFilter($pageSize=null, $uniqueId=null) {
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
		return $this->searchBase($criteria, $pageSize);
	}



}
