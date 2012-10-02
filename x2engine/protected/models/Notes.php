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
 * 
 * @package X2CRM.models
 * @property integer $id
 * @property string $type
 * @property integer $associationId
 * @property string $note
 * @property string $createdBy
 * @property string $createDate
 */
class Notes extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Notes the static model class
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
	return 'x2_notes';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
	// NOTE: you should only define rules for those attributes that
	// will receive user inputs.
	return array(
	    array('type, associationId, note, createdBy', 'required'),
	    array('associationId', 'numerical', 'integerOnly' => true),
	    array('type', 'length', 'max' => 20),
	    array('createdBy', 'numerical', 'integerOnly' => true),
	    array('createDate', 'safe'),
	    // The following rule is used by search().
	    // Please remove those attributes that should not be searched.
	    array('id, type, associationId, note, createdBy, createDate', 'safe', 'on' => 'search'),
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
	    'type' => 'Type',
	    'associationId' => 'Association',
	    'note' => 'Note',
	    'createdBy' => 'Created By',
	    'createDate' => 'Create Date',
	);
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
	$criteria->compare('type', $this->type, true);
	$criteria->compare('associationId', $this->associationId);
	$criteria->compare('note', $this->note, true);
	$criteria->compare('createdBy', $this->createdBy, true);
	$criteria->compare('createDate', $this->createDate, true);

	return new CActiveDataProvider(get_class($this), array(
		    'criteria' => $criteria,
		));
    }

}