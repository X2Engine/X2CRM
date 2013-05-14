<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/

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
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Services'));
                foreach($fields as $field){
                    $fieldName=$field->fieldName;
                    switch($field->type){
                        case 'boolean':
                            $criteria->compare($field->fieldName,$this->compareBoolean($this->$fieldName), true);
                            break;
                        case 'link':
                            $criteria->compare($field->fieldName,$this->compareLookup($field->linkType, $this->$fieldName), true);
                            break;
                        case 'assignment':
                            $criteria->compare($field->fieldName,$this->compareAssignment($this->$fieldName), true);
                            break;
                        default:
                            $criteria->compare($field->fieldName,$this->$fieldName,true);
                    }

                }


		$dataProvider=new SmartDataProvider(get_class($this), array(
			'sort'=>array('defaultOrder'=>'id ASC'),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
		$arr=$dataProvider->getData();
		foreach($arr as $service){
			$service->assignedTo=User::getUserLinks($service->assignedTo);
		}
		$dataProvider->setData($arr);

		return $dataProvider;
	}

	/**
	 *  Like search but filters by status based on the user's profile
	 *
	 */
	public function searchWithStatusFilter() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		// $criteria->compare('status', '<>Program Manager investigation');

		foreach($this->getFields(true) as $fieldName => $field) {

			if($fieldName == 'status') { // if status exists
				// filter statuses based on user's profile
				$hideStatus = CJSON::decode(Yii::app()->params->profile->hideCasesWithStatus); // get a list of statuses the user wants to hide
				if(!$hideStatus) {
					$hideStatus = array();
				}
				foreach($hideStatus as $hide) {
					$criteria->compare('status', '<>'.$hide);
				}
			}

			switch($field->type){
				case 'boolean':
					$criteria->compare($fieldName,$this->compareBoolean($this->$fieldName), true);
					break;
				case 'link':
					$criteria->compare($fieldName,$this->compareLookup($field->linkType, $this->$fieldName), true);
					break;
				case 'assignment':
					$criteria->compare($fieldName,$this->compareAssignment($this->$fieldName), true);
					break;
				default:
					$criteria->compare($fieldName,$this->$fieldName,true);
			}

		}


		$criteria->together = true;
		// $criteria->with = array('contactId.company');
		// field 'account' is not in x2_services table,
		// it is declared at the top of this class and is used
		// by X2GridView to search the account name associated
		// with the contact associated with this service case.
		// Adding the field 'account' to the table x2_services will
		// cause an SQL error in this function
		if(isset($_GET['Services']['account'])) {
			// $criteria->compare('company.name', $_GET['Services']['account'], true);
		}

		$dataProvider=new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'t.assignedTo ASC', // `t` is an SQL placeholder for x2_services, this prevents an SQL error caused by 3 ambiguous 'id' fields in the SQL query: one each from x2_services, x2_contacts, and x2_accounts
		/*		'attributes'=>array(
					'account'=>array( // let's us sort by account name
						'asc'=>'accounts.name',
						'desc'=>'accounts.name DESC',
					),
				), */
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
		$arr=$dataProvider->getData();
		foreach($arr as $service){
			$service->assignedTo=User::getUserLinks($service->assignedTo);
		}
		$dataProvider->setData($arr);

		return $dataProvider;
	}



}
