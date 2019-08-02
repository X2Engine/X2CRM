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
 * This is the model class for table "x2_form_versions".
 *
 * @package application.models
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

        $layoutData = json_decode((isset($layout)? $layout->layout : X2Model::getDefaultFormLayout($modelName)),true);

        $editableFieldsInLayout = array ();
	    if(isset($layoutData['sections']) && count($layoutData['sections']) > 0) {
		    foreach($layoutData['sections'] as &$section) {
				foreach($section['rows'] as &$row) {
					if(isset($row['cols'])) {
						foreach($row['cols'] as &$col) {
							if(isset($col['items'])) {
								foreach($col['items'] as &$item) {

                                    if(isset($item['name'],$item['labelType'],$item['readOnly'])) {
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

    /**
     * Helper method to unset all defaultView or defaultForm flags
     * @param string $type Form type, either 'view' or 'form', or both if argument is omitted
     * @param string Model type to unset flags for
     */
    public static function clearDefaultFormLayouts($type = null, $model = null, $scenario = null) {
        // Construct attributes to select form layouts
        $attr = array('model' => $model);
        if ($scenario)
            $attr['scenario'] = $scenario;
        if ($type === 'view')
            $attr['defaultView'] = 1;
        else if ($type === 'form')
            $attr['defaultForm'] = 1;
        $layouts = FormLayout::model()->findAllByAttributes ($attr);

        foreach ($layouts as &$layout) {
            if ($type === 'view')
                $layout->defaultView = false;
            else if ($type === 'form')
                $layout->defaultForm = false;
            else
                $layout->defaultView = $layout->defaultForm = false;
            $layout->save();
        }
    }
}
