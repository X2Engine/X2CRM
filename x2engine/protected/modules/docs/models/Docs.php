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

// Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_docs".
 * 
 * @package X2CRM.modules.docs.models
 */
class Docs extends X2Model {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Docs the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_docs';
	}

	public function behaviors() {
		return array_merge(parent::behaviors(), array(
					'X2LinkableBehavior' => array(
						'class' => 'X2LinkableBehavior',
						'module' => 'docs',
					),
					'ERememberFiltersBehavior' => array(
						'class' => 'application.components.ERememberFiltersBehavior',
						'defaults' => array(),
						'defaultStickOnClear' => false
					)
				));
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, text, createdBy', 'required'),
			array('createDate, lastUpdated', 'numerical', 'integerOnly' => true),
			array('name, editPermissions, subject', 'length', 'max' => 100),
			array('createdBy', 'length', 'max' => 60),
			array('updatedBy', 'length', 'max' => 40),
			array('type', 'length', 'max' => 10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, text, createdBy, createDate, updatedBy, lastUpdated, editPermissions, type', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	public function parseType() {
		if (!isset($this->type))
			$this->type = '';
		switch ($this->type) {
			case 'email':
				return Yii::t('docs', 'Template');
			default:
				return Yii::t('docs', 'Document');
		}
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		// $criteria->compare('id',$this->id);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('subject', $this->subject, true);
		// $criteria->compare('text',$this->text,true);
		$criteria->compare('createdBy', $this->createdBy, true);
		$criteria->compare('createDate', $this->createDate);
		$criteria->compare('updatedBy', $this->updatedBy, true);
		$criteria->compare('lastUpdated', $this->lastUpdated);
		$criteria->compare('type', $this->type);

		if (!Yii::app()->user->checkAccess('AdminIndex')) {
			$condition = 'visibility="1" OR createdBy="Anyone"  OR createdBy="' . Yii::app()->user->getName() . '" OR editPermissions LIKE "%' . Yii::app()->user->getName() . '%"';
			/* x2temp */
			$groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId=' . Yii::app()->user->getId())->queryColumn();
			if (!empty($groupLinks))
				$condition .= ' OR createdBy IN (' . implode(',', $groupLinks) . ')';

			$condition .= 'OR (visibility=2 AND createdBy IN 
				(SELECT username FROM x2_group_to_user WHERE groupId IN
					(SELECT groupId FROM x2_group_to_user WHERE userId=' . Yii::app()->user->getId() . ')))';
			$criteria->addCondition($condition);
		}
		// $criteria->compare('editPermissions',$this->editPermissions,true);

		$dateRange = Yii::app()->controller->partialDateRange($this->createDate);
		if ($dateRange !== false)
			$criteria->addCondition('createDate BETWEEN ' . $dateRange[0] . ' AND ' . $dateRange[1]);

		$dateRange = Yii::app()->controller->partialDateRange($this->lastUpdated);
		if ($dateRange !== false)
			$criteria->addCondition('lastUpdated BETWEEN ' . $dateRange[0] . ' AND ' . $dateRange[1]);

		return new SmartDataProvider('Docs', array(
					'pagination' => array(
						'pageSize' => ProfileChild::getResultsPerPage(),
					),
					'sort' => array(
						'defaultOrder' => 'lastUpdated DESC, id DESC',
					),
					'criteria' => $criteria,
				));
	}

	/**
	 * Replace tokens with model attribute values.
	 * 
	 * @param type $str Input text
	 * @param X2Model $model Model to use for replacement
	 * @param array $vars List of extra variables to replace
	 * @param bool $encode Encode replacement values if true; use renderAttribute otherwise.
	 * @return string
	 */
	public static function replaceVariables($str,&$model,$vars = array(),$encode = false) {
		if($encode) {
			foreach(array_keys($vars) as $key)
				$vars[$key] = CHtml::encode($vars[$key]);
		}
		$str = strtr($str,$vars);	// replace any manually set variables
		
		if($model instanceof X2Model) {
			if(get_class($model) !== 'Quote') {
				$matches = array();
				preg_match_all('/{\w+}/',$str,$matches);
				
				if(isset($matches[0])) {
					$attributes = array();
					foreach($matches[0] as &$match) {	// loop through the things (email body)
						$attribute = substr($match, 1, -1); // remove { and }
						if($model->hasAttribute($attribute))
							$attributes[$match] = $model->renderAttribute($attribute,false,true); // get the correctly formatted attribute (which is already in HTML)
					}
					$str = strtr($str,$attributes);	// replace any attributes that were found
				}
			} else {
				// Specialized, separate method for quotes that can use details from 
				// either accounts or quotes.
				// There may still be some stray quotes with 2+ contacts on it, so
				// explode and pick the first to be on the safe side. The most 
				// common use case by far is to have only one contact on the quote.
				$contactIds = explode(' ', $model->associatedContacts);
				$contactId = $contactIds[0];
				$accountId = $model->accountName;
				$staticModels = array('Contact' => Contacts::model(), 'Account' => Accounts::model(), 'Quote' => Quote::model());
				$models = array(
					'Contact' => $model->contact,
					'Account' => empty($accountId) ? null : $staticModels['Account']->findByPk($model->accountName),
					'Quote' => $model
				);
				$attributes = array();
				foreach($models as $name => $modelObj) {
					if(empty($modelObj)) {
						// Model will be blank
						foreach ($staticModels[$name]->fields as $field) {
							$attributes['{' . $name . '.' . $field->fieldName . '}'] = '';
						}
					} else {
						// Insert attributes
						foreach($modelObj->attributes as $fieldName => $value) {
							$attributes['{' . $name . '.' . $fieldName . '}'] = $encode ? CHtml::encode($value) : $modelObj->renderAttribute($fieldName);
						}
					}
				}
				$quoteParams = array(
					'{Quote.lineItems}' => $model->productTable(true),
					'{Quote.dateNow}' => date("F d, Y", time()),
					'{Quote.quoteOrInvoice}' => Yii::t('quotes',$model->type=='invoice' ? 'Invoice' : 'Quote'),
				);
				// Run the replacement:
				$str = strtr($str,array_merge($attributes,$quoteParams));
				return $str;
			}
		}
		return $str;
	}

	public static function getEmailTemplates($type = 'email') {
		$templateLinks = array();
		// $criteria = new CDbCriteria(array('order'=>'lastUpdated DESC'));
		$condition = 'TRUE';
		if (!Yii::app()->user->checkAccess('AdminIndex')) {
			$condition = 'visibility="1" OR createdBy="Anyone"  OR createdBy="' . Yii::app()->user->getName() . '"';
			/* x2temp */
			$uid = self::model()->suID;
			if(empty($uid)){
				if(Yii::app()->params->noSession)
					$uid = 1;
				else
					$uid = Yii::app()->user->id;
			}
			$groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId=' .$uid)->queryColumn();
			if (!empty($groupLinks))
				$condition .= ' OR createdBy IN (' . implode(',', $groupLinks) . ')';

			$condition .= 'OR (visibility=2 AND createdBy IN 
				(SELECT username FROM x2_group_to_user WHERE groupId IN
					(SELECT groupId FROM x2_group_to_user WHERE userId=' . $uid . ')))';
			// $criteria->addCondition($condition);
		}
		// $templates = X2Model::model('Docs')->findAllByAttributes(array('type'=>'email'),$criteria);

		$templateData = Yii::app()->db->createCommand()
			->select('id,name')
			->from('x2_docs')
			->where('type="'.$type.'" AND (' . $condition . ')')
			->order('name ASC')
			// ->andWhere($condition)
			->queryAll(false);
		foreach($templateData as &$row)
			$templateLinks[$row[0]] = $row[1];
		return $templateLinks;
	}

}