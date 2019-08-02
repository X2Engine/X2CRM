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
 * This is the model class for table "x2_lead_routing".
 *
 * @package application.models
 * @property integer $id
 * @property string $field
 * @property string $value
 * @property string $users
 */
class LeadRouting extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return LeadRouting the static model class
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
	return 'x2_lead_routing';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
	// NOTE: you should only define rules for those attributes that
	// will receive user inputs.
	return array(
	    array('users', 'safe'),
	    // The following rule is used by search().
	    // Please remove those attributes that should not be searched.
	    array('id,  users', 'safe', 'on' => 'search'),
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

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
	return array(
	    'id' => Yii::t('admin','ID'),
	    'criteria' => Yii::t('admin','Criteria'),
	    'users' => Yii::t('admin','Users'),
        'priority' => Yii::t('admin','Priority'),
	);
    }

    /**
     * Obtains an array of criteria from a JSON-encoded list.
     * @param string $str JSON-endoced list of lead routing criteria
     * @return array
     */
    public static function parseCriteria($str) {
	$array = json_decode($str);
	$arr = array();
	foreach ($array as $criteria) {
	    $pieces = explode(',', $criteria);
	    $arr[] = array('field' => $pieces[0], 'comparison' => $pieces[1], 'value' => $pieces[2]);
	}
	return $arr;
    }

    /**
     * Turns a list of lead routing criteria into a human-readable list of rules.
     *
     * @param string $str
     * @return string
     */
    public static function humanizeText($str) {
	$array = json_decode($str);
	$arr = array();
	$return = "If ";
	$tempArr = array();
        if(!is_array($array)){
            return '';
        }
	foreach ($array as $criteria) {
	    $tempStr = "";
	    $pieces = explode(',', $criteria);
	    $arr[] = array('field' => $pieces[0], 'comparison' => $pieces[1], 'value' => $pieces[2]);
	    $field = Fields::model()->findByAttributes(array('modelName' => 'Contacts', 'fieldName' => $pieces[0]))->attributeLabel;
	    $tempStr.=$field;
	    switch ($pieces[1]) {
		case '<':
		    $tempStr.=" is less than or equal to $pieces[2]";
		    break;
		case '>':
		    $tempStr.=" is greater than or equal to $pieces[2]";
		    break;
		case '=':
		    $tempStr.=" is equal to $pieces[2]";
		    break;
		case '!=':
		    $tempStr.=" is not equal to $pieces[2]";
		    break;
		case 'contains':
		    $tempStr.=" contains the the text '$pieces[2]'";
		    break;
	    }
	    $tempArr[] = $tempStr;
	}
	$return.=implode(' and ', $tempArr);
	return $return;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
	// Warning: Please modify the following code to remove attributes that
	// should not be searched.

	$criteria = new CDbCriteria;

	$criteria->compare('id', $this->id);
	$criteria->compare('field', $this->field, true);
	$criteria->compare('value', $this->value, true);
	$criteria->compare('users', $this->users, true);

	return new CActiveDataProvider(get_class($this), array(
		    'criteria' => $criteria,
		));
    }

}