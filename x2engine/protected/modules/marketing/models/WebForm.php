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
 * This is the model class for table "x2_web_forms".
 *
 * @package application.modules.marketing.models
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
            array('generateLead', 'boolean'),
            array('redirectUrl', 'url', 'defaultScheme' => 'http'),
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

    public function getDisplayName ($plural=true, $ofModule=true) {
        return Yii::t('marketing', 'Web Form');
    }

}
?>
