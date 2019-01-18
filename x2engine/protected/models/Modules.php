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
     * Update visibility and order of top bar module links 
     * @param array $idsOfVisibleModules 
     * @param array $idsOfHiddenModules
     */
    public static function updateTopBarLinks (
        array $idsOfVisibleModules, array $idsOfHiddenModules) {

        $transaction = Yii::app()->db->beginTransaction ();
        try {
            $count = count ($idsOfVisibleModules);
            for ($i = 0; $i < $count; $i++) {
                $id = $idsOfVisibleModules[$i];
                Yii::app()->db->createCommand ("
                    update x2_modules
                    set visible=1, menuPosition=$i
                    where id=:id
                ")->execute (array (':id' => $id));
            }
            $count = count ($idsOfHiddenModules);
            for ($i = 0; $i < $count; $i++) {
                $id = $idsOfHiddenModules[$i];
                Yii::app()->db->createCommand ("
                    update x2_modules
                    set visible=0, menuPosition=-1
                    where id=:id
                ")->execute (array (':id' => $id));
            }
            $transaction->commit ();
        } catch (Exception $e) {
            $transaction->rollback ();
            return false;
        }
        return true;
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Modules the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

    public static function getModuleNames ($custom=null) {
        static $cache;
        if (!isset ($cache)) {
            $cache = Yii::app()->db->createCommand ()
                ->select ('name, custom')
                ->from ('x2_modules')
                ->where('moduleType = "module"')
                ->queryAll ();
        }
        if ($custom) {
            $records = array_filter ($cache, function ($record) {
                return (int) $record['custom'] === 1;
            });
        } else {
            $records = $cache;
        }
        return array_map (function ($record) {
            return $record['name'];
        }, $records);
    }

    public static function getDropdownOptions ($keyType='name', $filter=null) {
        $filter = $filter === null ? function () { return true; } : $filter;
        if ($keyType === 'name') {
            $moduleNames = array_filter (self::getModuleNames (), $filter);
            $options = array ();
            foreach ($moduleNames as $name) {
                $options[$name] = self::displayName (true, $name);
            }
        } elseif ($keyType === 'id') {
            $modules = array_filter (Yii::app()->db->createCommand ()
                ->select ('id, name')
                ->from ('x2_modules')
                ->where('moduleType = "module"')
                ->queryAll (), $filter);
            $options = array ();
            foreach ($modules as $record) {
                $options[$record['id']] = self::displayName (true, $record['name']);
            }
        } else {
            throw new CException ('invalid key type');
        }
        asort ($options);
        return $options;
    }


    public static function dropDownList ($name, $selected='', $htmlOptions=array ()) {
        return CHtml::dropDownList (
            $name, $selected, self::getDropdownOptions (), $htmlOptions);
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
            array(
                'moduleType', 'in', 
                'range' => array ('module', 'link', 'recordLink', 'pseudoModule')
            ),
            array(
                'linkHref,linkRecordType,linkRecordId', 'safe', 
            ),
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
        $skipModules = array(
            'Calendar', 'Charts', 'Groups', 'Reports', 'Media', 'Users', 'Workflow');
        
        $skipModules[] = 'EmailInboxes';

        foreach($modules as $module){

        
            $name = ucfirst($module->name);
            
            if (in_array($name, $skipModules)) {
                continue;
            }

            if($name != 'Document'){
                $controllerName = $name.'Controller';
                if(file_exists(
                    'protected/modules/'.$module->name.'/controllers/'.$controllerName.'.php')){
                    
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

    public function getDisplayName ($plural = true) {
        return self::displayName ($plural, $this->name);
    }

    /**
     * Retrieves the title of a given module or the current module
     * if no module is specified
     * @param bool Retrieve the plural form of the module name
     * @param string Module to retrieve name for
     * @return string|false Current module title, false if none could be found
     */
    public static function displayName($plural = true, $module = null, $refresh = false) {
        $moduleTitle = null;
        if (is_null($module) && isset(Yii::app()->controller->module))
            $module = Yii::app()->controller->module->name;

        // return a cached value
        if (!$refresh && !is_null($module) && isset(self::$_displayNames[$module][$plural]))
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
            $isVowelTrue = false;
            if (preg_match('/y$/', $moduleTitle)) {
                $moduleTitleExploded = explode('y', $moduleTitle);
                // Get 'a' in 'days'
                $isVowel = substr($moduleTitleExploded[count($moduleTitleExploded) - 2], -1);
                if ($isVowel === 'a' || $isVowel === 'e' ||
                        $isVowel === 'i' || $isVowel === 'o' ||
                        $isVowel === 'u') {
                    $isVowelTrue = true;
                }
            }
            if ($plural === false) {
                if (preg_match('/ies$/', $moduleTitle) && !$isVowelTrue) {
                    $moduleTitle = preg_replace('/ies$/', 'y', $moduleTitle);
                } else if (preg_match('/ses$/', $moduleTitle)) {
                    $moduleTitle = preg_replace('/es$/', '', $moduleTitle);
                } else if ($moduleTitle !== 'Process') {
                    // Otherwise chop the trailing s
                    $moduleTitle = trim($moduleTitle, 's');
                }
            } elseif ($plural === 'optional') {
                if (preg_match('/y$/', $moduleTitle) && !$isVowelTrue) {
                    $moduleTitle = preg_replace('/y$/', '(ies)', $moduleTitle);
                } else if (preg_match('/ss$/', $moduleTitle)) {
                    $moduleTitle .= '(es)';
                } else if (in_array ($moduleTitle, array ('Service'))) {
                    $moduleTitle .= '(s)';
                } elseif (preg_match ('/s$/', $moduleTitle)) {
                    $moduleTitle = preg_replace('/s$/', '(s)', $moduleTitle);
                }
            } else {
                if (preg_match('/y$/', $moduleTitle) && !$isVowelTrue) {
                    $moduleTitle = preg_replace('/y$/', 'ies', $moduleTitle);
                } else if (preg_match('/ss$/', $moduleTitle)) {
                    $moduleTitle .= 'es';
                } else if (in_array ($moduleTitle, array ('Service'))) {
                    $moduleTitle .= 's';
                }
            }
        }else{
            $moduleTitle = Yii::t('app', $moduleTitle);
        }
        self::$_displayNames[$module][$plural] = $moduleTitle;
        return $moduleTitle;
    }

    /**
     * Retrieves the item name for the specified Module
     * @param string $module Module to retrieve item name for, or the current module if null
     */
    public static function itemDisplayName($moduleName = null) {
        if (is_null($moduleName) && isset(Yii::app()->controller->module))
            $moduleName = Yii::app()->controller->module->name;
        $module = X2Model::model('Modules')->findByAttributes(array('name' => $moduleName));
        if(!$module){
            return $moduleName;
        }
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
            // Clear cached display names
            self::$_displayNames = array();
            return true;
        } else {
            return false;
        }
    }

    public function getLinkedRecord () {
        if ($this->moduleType !== 'recordLink') {
            throw new CException ('invalid module type');
        }
        $model = X2Model::model2 ($this->linkRecordType);
        if ($model && ($record = $model->findByPk ($this->linkRecordId))) {
            return $record;
        }
    }

    public function getTitle () {
        switch ($this->moduleType) {
            case 'module':
            case 'pseudoModule':
            case 'link':
                return $this->title;
            case 'recordLink':
                $linkedRecord = $this->getLinkedRecord ();
                if ($linkedRecord && isset ($linkedRecord->name)) {
                    return $linkedRecord->name;
                }
                break;
            default:
                break;
        }
    }


}
