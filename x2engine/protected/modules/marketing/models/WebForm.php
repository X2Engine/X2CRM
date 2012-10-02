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
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/**
 * This is the model class for table "x2_web_forms".
 *
 * @package X2CRM.modules.marketing.models
 */
class WebForm extends CActiveRecord {
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'x2_web_forms';
	}

	public function rules() {
		return array(
			array('name, type, visibility, assignedTo, createdBy, updatedBy, createDate, lastUpdated', 'required'),
			array('description, modelName, fields', 'safe'),
			array('id, visibility, createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('name, type, modelName', 'length', 'max'=>100),
			array('description', 'length', 'max'=>255),
			array('assignedTo, createdBy, updatedBy', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, type, description, modelName, fields, params, css, visibility, assignedTo, createdBy, updatedBy, createDate, lastUpdated', 'safe', 'on'=>'search'),
		);
	}

	public function attributeLabels() {
		return array(
			'id'=>Yii::t('marketing', 'ID'),
			'name'=>Yii::t('marketing', 'Name'),
			'type'=>Yii::t('marketing', 'Type'),
			'description'=>Yii::t('marketing', 'Description'),
			'modelName'=>Yii::t('marketing', 'Model Name'),
			'fields'=>Yii::t('marketing', 'Fields'),
			'params'=>Yii::t('marketing', 'Parameters'),
			'css'=>Yii::t('marketing', 'CSS'),
			'visibility'=>Yii::t('marketing', 'Visibility'),
			'assignedTo'=>Yii::t('marketing', 'Assigned To'),
			'createdBy'=>Yii::t('marketing', 'Created By'),
			'updatedBy'=>Yii::t('marketing', 'Updated By'),
			'createDate'=>Yii::t('marketing', 'Create Date'),
			'lastUpdated'=>Yii::t('marketing', 'Last Updated'),
		);
	}

	protected function beforeSave() {
		if (!empty($this->params)) {
			$this->params = json_encode($this->params);
		}
		return parent::beforeSave();
	}

	protected function afterFind() {
		if (!empty($this->params)) {
			$this->params = json_decode($this->params);
		}
		parent::afterFind();
	}
}
?>
