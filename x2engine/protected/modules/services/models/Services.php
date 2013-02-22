<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

Yii::import('application.models.X2Model');

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
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/services',
		//		'icon'=>'accounts_icon.png',
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		);
	}


	/**
	 * @return array relational rules.
	 */
/*	public function relations() {
		return array();
	}
*/

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
