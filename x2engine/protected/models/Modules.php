<?php

/**
 * This is the model class for table "x2_modules".
 *
 * @package application.models
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property integer $visible
 * @property integer $menuPosition
 * @property integer $searchable
 * @property integer $editable
 * @property integer $adminOnly
 * @property integer $custom
 * @property integer $toggleable
 */
class Modules extends CActiveRecord {
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Modules the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_modules';
    }

    public function scopes () {
        return array (
            'titleSorted' => array (
                'order' => 'title ASC'
            ),
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('visible, menuPosition, searchable, editable, adminOnly, custom, toggleable', 'numerical', 'integerOnly'=>true),
            array('name, title', 'length', 'max'=>250),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable', 'safe', 'on'=>'search'),
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
            'name' => 'Name',
            'title' => 'Title',
            'visible' => 'Visible',
            'menuPosition' => 'Menu Position',
            'searchable' => 'Searchable',
            'editable' => 'Editable',
            'adminOnly' => 'Admin Only',
            'custom' => 'Custom',
            'toggleable' => 'Toggleable',
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('title',$this->title,true);
        $criteria->compare('visible',$this->visible);
        $criteria->compare('menuPosition',$this->menuPosition);
        $criteria->compare('searchable',$this->searchable);
        $criteria->compare('editable',$this->editable);
        $criteria->compare('adminOnly',$this->adminOnly);
        $criteria->compare('custom',$this->custom);
        $criteria->compare('toggleable',$this->toggleable);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    public static function moduleLabel($model) {
        
    }
    public static function recordLabel($model) {
        
    }

    /**
     * Populate a list of available modules to import/export
     */
    public static function getExportableModules() {
        $modules = Modules::model()->findAll();
        $moduleList = array();
        $skipModules = array('Calendar', 'Charts', 'Groups', 'Reports', 'Media', 'Users', 'Workflow');
        foreach($modules as $module){
            $name = ucfirst($module->name);
            if (in_array($name, $skipModules)) {
                continue;
            }
            if($name != 'Document'){
                $controllerName = $name.'Controller';
                if(file_exists('protected/modules/'.$module->name.'/controllers/'.$controllerName.'.php')){
                    Yii::import("application.modules.$module->name.controllers.$controllerName");
                    $controller = new $controllerName($controllerName);
                    $model = $controller->modelClass;
                    if(class_exists($model)){
                        $moduleList[$model] = Yii::t('app', $module->title);
                    }
                }
            }
        }
        return $moduleList;
    }

    /**
     * @return array names of models associated with each module 
     */
    public static function getNamesOfModelsOfModules () {
        $moduleNames = array_map (function ($record) {
            return $record->name; 
        }, Modules::model ()->findAll (array ('select' => 'name')));

        $models = array ();
        foreach ($moduleNames as $name) {
            $modelName = X2Model::getModelName ($name);
            if ($modelName && is_subclass_of ($modelName, 'X2Model')) {
                $models[] = $modelName;
            }
        }
        return $models;
    }

    /**
     * Returns array of custom modules
     * @param bool $visible
     * @return <array of Modules> 
     */
    public function getCustomModules ($visible=false) { 
        $attributes = array ('custom' => 1);
        if ($visible) $attributes['visible'] = 1;
        return $this->findAllByAttributes ($attributes);
    }


    /**
     * Renames module
     * @param string $newTitle 
     * @return bool true for success, false for failure
     */
    public function retitle ($newTitle) {
        $oldTitle = $this->title;
        $this->title = $newTitle;
        if($this->save()){
            // if it's a static page, rename the doc too
            if ($this->name === 'document') { 
                $doc = Docs::model ()->findByAttributes (array ('name' => $oldTitle));
                $doc->name = $this->title;
                $doc->save ();
            }
            return true;
        } else {
            return false;
        }
    }
}
