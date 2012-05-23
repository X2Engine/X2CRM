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
 ********************************************************************************/

/**
 * This is the model class for table "x2_fields".
 *
 * The followings are the available columns in table 'x2_fields':
 * @property integer $id
 * @property string $modelName
 * @property string $fieldName
 * @property string $attributeLabel
 * @property integer $show
 * @property integer $custom
 */
class Fields extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Fields the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_fields';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('modelName, fieldName, attributeLabel', 'length', 'max'=>250),
			array('modelName, fieldName, attributeLabel','required'),
			array('custom, modified, readOnly', 'boolean'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, modelName, fieldName, attributeLabel, custom, modified, readOnly', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'modelName' => 'Model Name',
			'fieldName' => 'Field Name',
			'attributeLabel' => 'Attribute Label',
			'custom' => 'Custom',
			'modified' => 'Modified',
			'readOnly' => 'Read Only',
                        'required' => "Required",
                        'searchable' => "Searchable",
                        'relevance' => 'Search Relevance',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('modelName',$this->modelName,true);
		$criteria->compare('fieldName',$this->fieldName,true);
		$criteria->compare('attributeLabel',$this->attributeLabel,true);
		$criteria->compare('custom',$this->custom);
		$criteria->compare('modified',$this->modified);
		$criteria->compare('readOnly',$this->readOnly);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	public static function getLinkId($type,$name) {
		// die($name);
		if(strtolower($type) == 'contacts')
			$model = CActiveRecord::model('Contacts')->find('CONCAT(firstName," ",lastName)=:name',array(':name'=>$name));
		else
			$model = CActiveRecord::model(ucfirst($type))->findByAttributes(array('name'=>$name));
		// die(var_dump($model));
		if(isset($model))
			return $model->name;
		else
			return null;
	}
}