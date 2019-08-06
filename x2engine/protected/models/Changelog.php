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
 * This is the model class for table "x2_changelog".
 *
 * The followings are the available columns in table 'x2_changelog':
 * @property integer $id
 * @property string $type
 * @property integer $itemId
 * @property string $changedBy
 * @property string $changed
 * @property string $fieldName
 * @property string $oldValue
 * @property string $newValue
 * @property boolean $diff
 * @property integer $timestamp
 */
class Changelog extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Changelog the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_changelog';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array();
			// array('type, itemId, changedBy', 'required'),
			// array('itemId, timestamp', 'numerical', 'integerOnly'=>true),
			// array('type, changedBy', 'length', 'max'=>50),
			// array('fieldName', 'length', 'max'=>255),
			// array('diff', 'boolean'),
			// array('changed, oldValue, newValue', 'safe'),
			// array('id, type, itemId, changedBy, changed, fieldName, oldValue, newValue, timestamp', 'safe', 'on'=>'search'),
		// );
	}

	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'type' => Yii::t('admin','Type'),
			'itemId' => Yii::t('admin','Item'),
			'changedBy' => Yii::t('admin','Changed By'),
			'changed' => Yii::t('admin','Changed'),
			'fieldName' => Yii::t('admin','Field Name'),
			'oldValue' => Yii::t('admin','Old Value'),
			'newValue' => Yii::t('admin','New Value'),
			'diff' => Yii::t('admin','Diff'),
			'timestamp' => Yii::t('admin','Timestamp'),
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

		$parameters = array('limit'=>ceil(Profile::getResultsPerPage()));
		$criteria->scopes = array('findAll'=>array($parameters));

		$criteria->compare('id',$this->id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('itemId',$this->itemId);
		$criteria->compare('changedBy',$this->changedBy,true);
		$criteria->compare('recordName',$this->recordName,true);
		$criteria->compare('fieldName',$this->fieldName,true);
		$criteria->compare('oldValue',$this->oldValue,true);
		$criteria->compare('newValue',$this->newValue,true);
		$criteria->compare('diff',$this->diff,true);
		$criteria->compare('timestamp',$this->timestamp);

		return new SmartActiveDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'timestamp DESC',
			),
			'pagination'=>array(
				'pageSize'=>Profile::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}
}