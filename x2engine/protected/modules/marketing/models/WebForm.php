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
			array('id, name, type, description, modelName, fields, params, css, header, visibility, assignedTo, createdBy, updatedBy, createDate, lastUpdated', 'safe', 'on'=>'search'),
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
			'header'=>Yii::t('marketing', 'Header Code'),
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
