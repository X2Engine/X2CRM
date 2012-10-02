<?php

/* * *******************************************************************************
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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * ****************************************************************************** */

/**
 * This is the model class for table "x2_lead_routing".
 *
 * @package X2CRM.models
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
	    'id' => 'ID',
	    'criteria' => 'Criteria',
	    'users' => 'Users',
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