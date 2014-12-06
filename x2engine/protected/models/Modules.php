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
     * Cached Module titles
     */
    private static $_displayNames;

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
     * Clean up after custom modules when they are deleted. Note, this shouldn't be applicable to default modules,
     * they cannot be deleted, only disabled
     */
    protected function afterDelete() {
        parent::afterDelete();
        if (!$this->custom)
            return;

        // remove associated Events, Actions, changelog entries, linked records, and Relationships
        $events = X2Model::model('Events')->findAllByAttributes(array(
            'associationType' => $this->name,
        ));
        $actions = X2Model::model('Actions')->findAllByAttributes(array(
            'associationType' => $this->name,
        ));
        $models = array_merge ($events, $actions);
        foreach ($models as $model)
            $model->delete();
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
     * Retrieves the title of a given module or the current module
     * if no module is specified
     * @param bool Retrieve the plural form of the module name
     * @param string Module to retrieve name for
     * @return string|false Current module title, false if none could be found
     */
    public static function displayName($plural = true, $module = null) {
        $moduleTitle = null;
        if (!isset($module))
            $module = Yii::app()->controller->module->name;

        // return a cached value
        if (isset(self::$_displayNames[$module][$plural]))
            return self::$_displayNames[$module][$plural];

        $moduleTitle = Yii::app()->db->createCommand()
            ->select('title')
            ->from('x2_modules')
            ->where("name = :name")
            ->bindValue(':name', $module)
            ->limit(1)
            ->queryScalar();

        if (!$moduleTitle) return false;

        if (Yii::app()->locale->id === 'en') {
            // Handle silly English pluralization
            if (!$plural) {
                if (preg_match('/ies$/', $moduleTitle)) {
                    $moduleTitle = preg_replace('/ies$/', 'y', $moduleTitle);
                } else if (preg_match('/ses$/', $moduleTitle)) {
                    $moduleTitle = preg_replace('/es$/', '', $moduleTitle);
                } else if ($moduleTitle !== 'Process') {
                    // Otherwise chop the trailing s
                    $moduleTitle = trim($moduleTitle, 's');
                }
            } else {
                if (preg_match('/y$/', $moduleTitle)) {
                    $moduleTitle = preg_replace('/y$/', 'ies', $moduleTitle);
                } else if (preg_match('/ss$/', $moduleTitle)) {
                    $moduleTitle .= 'es';
                }
            }
        }
        self::$_displayNames[$module][$plural] = $moduleTitle;
        return $moduleTitle;
    }

    /**
     * Retrieves the item name for the specified Module
     * @param string $module Module to retrieve item name for, or the current module if null
     */
    public static function itemDisplayName($moduleName = null) {
        if (is_null($moduleName))
            $moduleName = Yii::app()->controller->module->name;
        $module = X2Model::model('Modules')->findByAttributes(array('name' => $moduleName));
        $itemName = $moduleName;
        if (!empty($module->itemName)) {
            $itemName = $module->itemName;
        } else {
            // Attempt to load item name from legacy module options file
            $moduleDir = implode(DIRECTORY_SEPARATOR, array(
                'protected',
                'modules',
                $moduleName
            ));
            $configFile = implode(DIRECTORY_SEPARATOR, array(
                $moduleDir,
                lcfirst($moduleName)."Config.php"
            ));

            if (is_dir($moduleDir) && file_exists($configFile)) {
                $file = Yii::app()->file->set($configFile);
                $contents = $file->getContents();
                if (preg_match("/.*'recordName'.*/", $contents, $matches)) {
                    $itemNameLine = $matches[0];
                    $itemNameRegex = "/.*'recordName'=>'([\w\s]*?)'.*/";
                    $itemName = preg_replace($itemNameRegex, "$1", $itemNameLine);
                    if (!empty($itemName)) {
                        // Save this name in the database for the future
                        $module->itemName = $itemName;
                        $module->save();
                    }
                }
            }
        }

        return $itemName;
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
