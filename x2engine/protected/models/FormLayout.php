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
 * This is the model class for table "x2_form_versions".
 * 
 * @package X2CRM.models
 * @property integer $id
 * @property string $model
 * @property string $version
 * @property string $layout
 * @property boolean $defaultView
 * @property boolean $defaultForm
 * @property integer $createDate
 * @property integer $lastUpdated
 */
class FormLayout extends CActiveRecord {

	public static $scenarios = array('Default','Inline');

	/**
	 * Returns the static model of the specified AR class.
	 * @return FormVersions the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_form_layouts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('version, model', 'length', 'max'=>250),
			array('createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('defaultView, defaultForm', 'boolean'),
			array('layout', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, version, model, layout, defaultView, defaultForm, createDate, lastUpdated', 'safe', 'on'=>'search'),
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
			'version' => 'Version',
			'model' => 'Model Name',
			'layout' => 'Layout',
			'defualtView' => 'Default View',
			'defualtForm' => 'Default Form',
			'createDate' => 'Create Date',
			'lastUpdated' => 'Last Updated',
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
		$criteria->compare('model',$this->model,true);
		$criteria->compare('version',$this->version,true);
		$criteria->compare('defaultView',$this->defaultView,true);
		$criteria->compare('defaultForm',$this->defaultForm,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    /**
     * Returns fieldName, fieldLabel pairs for all fields for which the user has edit rights and
     * which are present in the layout.
     */
    public function getEditableFieldsInLayout ($modelName) {
        $editableFieldsFieldInfo = X2Model::model ($modelName)->getEditableFieldNames (false);

        // Construct criteria for finding the right form layout.
        $attributes = array('model'=>ucfirst($modelName),'defaultForm'=>1);

        $layout = self::model()->findByAttributes($attributes);
        if (!isset ($layout)) return false;

	    $layoutData = json_decode($layout->layout,true);

        $editableFieldsInLayout = array ();
	    if(isset($layoutData['sections']) && count($layoutData['sections']) > 0) {
		    foreach($layoutData['sections'] as &$section) {
				foreach($section['rows'] as &$row) {
					if(isset($row['cols'])) {
						foreach($row['cols'] as &$col) {
							if(isset($col['items'])) {
								foreach($col['items'] as &$item) {

                                    if(isset($item['name'],$item['labelType'],$item['readOnly'],
                                        $item['height'],$item['width'])) {
        
                                        $fieldName = preg_replace('/^formItem_/u','',$item['name']);
        
                                        if(in_array (
                                            $fieldName, array_keys ($editableFieldsFieldInfo))) {

                                            $editableFieldsInLayout[$fieldName] = 
                                                $editableFieldsFieldInfo[$fieldName];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $editableFieldsInLayout;
    }

}
