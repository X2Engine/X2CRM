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





Yii::import('application.components.behaviors.LinkableBehavior');
Yii::import('application.components.behaviors.ChangeLogBehavior');
Yii::import('application.components.behaviors.FlowTriggerBehavior');
Yii::import('application.components.behaviors.TimestampBehavior');
Yii::import('application.components.behaviors.TagBehavior');

Yii::import('application.components.behaviors.FingerprintBehavior');

Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.docs.models.Docs');
Yii::import('application.modules.users.models.*');
Yii::import('application.models.X2Flow');

/**
 * General model class that uses dynamic fields
 *
 * @property array $fieldPermissions Associative array of field names to
 *  permissions: 0 for no access, 1 for read access, and 2 for read/write
 * @property string $myModelName (read-only) Model name of the instance.
 * @property array $relatedX2Models (read-only) Models associated via the
 *  associations table
 * @property array $readableAttributeNames (read-only) Names of attributes that
 *  can be accessed, per the field-level security settings, by the current user.
 * @property boolean $isExemptFromFieldLevelPermissions True if the user is
 *  admin or has no roles (in which case field-level permissions do not apply)
 * @package application.models
 */
abstract class X2Model extends X2ActiveRecord {

    public $supportsFieldLevelPermissions = true;

    /**
     * @var true if this model can have workflows associated with it, false otherwise 
     */
    public $supportsWorkflow = true;

    /**
     * @var (optional) string Used in the search scenario to uniquely identify this model. Allows 
     *  filters to be saved separately for each grid view.
     */
    public $uid = null;

    /**
     * @var bool If true, grid views displaying models of this type will have their filter and
     *  sort settings saved in the database instead of in the session
     */
    public $dbPersistentGridSettings = false;

    /**
     * @var bool $disablePersistentGridSettings If true, grid settings will not be saved to or 
     *  retrieved from the session/db
     */
    public $disablePersistentGridSettings = false;

    /**
     * Temporary hack to allow importer to skip certain validation rules. This is used in place of
     * scenario because the scenario property isn't used correctly in many places throughout the
     * codebase. Scenario is meant to be used to filter validation rules 
     * (http://www.yiiframework.com/doc/api/1.1/CModel#scenario-detail), and not otherwise. So,
     * for now, changing the scenario can mean introducing unintended side-effects not related
     * to validation. Eventually, all non-validation uses of scenario should be refactored.
     * @var string $subScenario  
     */
    public $subScenario = '';
    protected $_oldAttributes = array();

    /**
     * A flag for disabling the automatic setting of fields in events like find,
     * update, validate (etc) to reduce overhead during queries.
     * @var type
     */
    public static $autoPopulateFields = true;

    /**
     * List of mapping between module names/associationType values and model class names
     */
    public static $associationModels = array(
        'bugreports' => 'BugReports',
        'media' => 'Media',
        'actions' => 'Actions',
        'calendar' => 'X2Calendar',
        'contacts' => 'Contacts',
        'accounts' => 'Accounts',
        'product' => 'Product',
        'products' => 'Product',
        'Campaign' => 'Campaign',
        'x2Leads' => 'X2Leads',
        'marketing' => 'Campaign',
        'quote' => 'Quote',
        'quotes' => 'Quote',
        'opportunities' => 'Opportunity',
        'social' => 'Social',
        'services' => 'Services',
        'users' => 'User',
        'anoncontact' => 'AnonContact',
        '' => ''
    );

    /**
     * 1-1 mapping between model names and the names of the modules they belong to  
     */
    public static $modelNameToModuleName = array(
        'Accounts' => 'Accounts',
        'Actions' => 'Actions',
        'BugReports' => 'BugReports',
        'Campaign' => 'Marketing',
        'Fingerprint' => 'Marketing',
        'AnonContact' => 'Marketing',
        'Contacts' => 'Contacts',
        'X2List' => 'Contacts',
        'Groups' => 'Groups',
        'Product' => 'Products',
        'Media' => 'Media',
        'Opportunity' => 'Opportunities',
        'Quote' => 'Quotes',
        'Reports' => 'Reports',
        'Services' => 'Services',
        'User' => 'Users',
        'WebForm' => 'Marketing',
        'Workflow' => 'Workflow',
        'X2Calendar' => 'Calendar',
        'X2Leads' => 'X2Leads',
    );

    /**
     * Mapping from model name to record name of module associated with that model
     */
    private static $recordNames = array(
        'Actions' => 'action',
        'Contacts' => 'contact',
        'Accounts' => 'account',
        'Product' => 'product',
        'Campaign' => 'campaign',
        'Quote' => 'quote',
        'Opportunity' => 'opportunity',
        'Services' => 'case',
        'Groups' => 'group',
        'Docs' => 'doc',
        'X2Leads' => 'lead',
        'X2List' => 'list item',
    );

    /**
     * Models with insertable attributes
     * 
     * @var type 
     */
    public static $modelsWithInsertableAttributes = array(
        'Accounts', 'Actions', 'Contacts', 'Docs', 'Groups', 'Campaign',
        'Media', 'Opportunity', 'Product', 'Quote', 'Services', 'BugReports'
    );
    public static $translatedModelTitles = array();
    protected static $_editableFieldNames = array();

    /**
     * Stores one copy of fields for all instances of this model
     * @var type
     */
    protected static $_fields;

    /**
     * Stores, for the current user, the permissions of the fields (1 for read,
     * 2 for read/write, 0 for no access)
     * 
     * @var type
     */
    protected static $_fieldPermissions = array();

    /**
     * Stores possible references to models via lookup fields. The structure of
     * this array is:
     *
     * 1st level (array):
     * [model class key] => [array value]
     *
     * 2nd level (array):
     * [table name key] => [array value]
     *
     * So for each model name, there is an array of corresponding tables (and
     * for each table, a list of columns) that need to be updated if the nameId
     * attribute changes.
     * @var type
     */
    protected static $_nameIdRefs;
    // cache for models loaded for link field attributes (used by automation system)
    protected static $_linkedModels;
    protected $_runAfterCreate;   // run afterCreate before afterSave, but only for new records
    protected $fieldFormatterClass = 'FieldFormatter';
    private static $_modelNames;
    private static $_attributeLabels;

    /**
     * Initialize the model.
     *
     * Calls {@link queryFields()} before CActiveRecord::__constructo() is
     * called, and populates the model with default values, if any.
     */
    public function __construct(
    $scenario = 'insert', $uid = null, $dbPersistentGridSettings = false, $disablePersistentGridSettings = false) {

        $this->uid = $uid;
        $this->dbPersistentGridSettings = $dbPersistentGridSettings;
        $this->disablePersistentGridSettings = $disablePersistentGridSettings;
        $this->queryFields();
        parent::__construct($scenario);
        if ($this->getIsNewRecord() && $scenario == 'insert') {
            foreach ($this->getFields() as $field) {
                if ($field->defaultValue != null && !$field->readOnly) {
                    $this->{$field->fieldName} = $field->defaultValue;
                }
            }
        }
    }

    public static function model($className = 'CActiveRecord') {
        $modelName = self::getModelName($className);
        if (class_exists($modelName)) {
            return parent::model($modelName);
        } else {
            throw new CHttpException(500, 'Class: ' . $className . " not found.");
        }
    }

    /**
     * Like {@link model()} except without the exception thrown in case of bad model name 
     */
    public static function model2($className = 'CActiveRecord') {
        $modelName = self::getModelName($className);
        if (class_exists($modelName)) {
            return parent::model($modelName);
        } else {
            return false;
        }
    }

    /**
     * Runs specified function without the specified behavior
     * @param string $behaviorName 
     * @param function $fn 
     */
    public function runWithoutBehavior($behaviorName, $fn) {
        $this->disableBehavior($behaviorName);
        $fn();
        $this->enableBehavior($behaviorName);
    }

    /**
     * Returns name of records associated with model type or $type if none could be found
     * @param string $type class name of subclass of X2Model
     * @param bool $plural if true, the record name will be pluralized
     * @return string 
     */
    public static function getRecordName($type, $plural = false) {
        if (isset(self::$recordNames[$type])) {
            $recordName = self::$recordNames[$type];
            if ($plural) {
                if (preg_match("/y$/", $recordName)) {
                    $recordName = preg_replace("/y$/", 'ies', $recordName);
                } else {
                    $recordName .= 's';
                }
            }
            return $recordName;
        } else {
            return $type;
        }
    }

    public static function getAllRecordNames() {
        return self::$recordNames;
    }

    /**
     * Get association type corresponding to model 
     * @return string
     */
    public static function getAssociationType($modelName) {
        $modelsToTypes = array_flip(X2Model::$associationModels);
        if (isset($modelsToTypes[$modelName])) {
            return $modelsToTypes[$modelName];
        } else {
            return strtolower($modelName);
        }
    }

    /**
     * Gets name of model corresponding to module or association type
     * @return string
     */
    public static function getModelName($typeOrModuleName, $strict = false) {
        if (array_key_exists(strtolower($typeOrModuleName), X2Model::$associationModels)) {
            return X2Model::$associationModels[strtolower($typeOrModuleName)];
        } else if (!$strict) {
            if (class_exists(ucfirst($typeOrModuleName))) {
                return ucfirst($typeOrModuleName);
            } elseif (class_exists($typeOrModuleName)) {
                return $typeOrModuleName;
            } else {
                return false;
            }
        }
    }

    /**
     * @param array $modelNames names of models
     * @return array models with given names
     */
    public static function getModelsFromNames(array $modelNames) {
        return array_map(function ($name) {
            return new $name ();
        }, $modelNames);
    }

    /**
     * @param array $models models
     * @return array names of tables associated with given models
     */
    public static function getTableNames(array $models) {
        return array_map(function ($model) {
            return $model->tableName();
        }, $models);
    }

    /**
     * Retrieves a list of model names.
     *
     * Obtains model names as an associative array with model names as the keys
     * and human-readable model names as their values. This is used in place of
     * {@link getDisplayedModelNamesList()} (formerly Admin::getModelList) where
     * specifying values for {@link modelName}, because the value of that should
     * ALWAYS be the name of the actual class, and {@link X2Model::getModelName()}
     * is guaranteed to return a class name (or false, if the class does not
     * exist).
     *
     * @param null|CDbCriteria $criteria if not null, will be used to query modules table. 
     *  Specifying a CDbCriteria will bypass caching.
     * @return array module titles indexed by associated model class names
     */
    public static function getModelNames($criteria = null) {
        if ($criteria !== null || !isset(self::$_modelNames)) {
            $modelNames = array();
            if ($criteria === null) {
                $modules = self::getModules();
            } else {
                $criteria->addColumnCondition(
                        array('visible' => 1, 'editable' => true), 'AND', 'OR');
                $modules = X2Model::model('Modules')->findAll($criteria);
            }
            foreach ($modules as $module) {
                if ($modelName = X2Model::getModelName($module->name)) {
                    $modelNames[$modelName] = Yii::t('app', $module->title);
                } else { // Shouldn't happen since getModelName uses class_exists
                    $modelNames[ucfirst($module->name)] = Yii::t('app', $module->title);
                }
            }
            asort($modelNames);
            if ($criteria !== null) {
                return $modelNames;
            } else {
                self::$_modelNames = $modelNames;
            }
        }
        return self::$_modelNames;
    }

    /**
     * Tests whether or not model name is the name of a visible, editable module's primary model
     * @param string $modelName 
     * @return true
     */
    public static function isModuleModelName($modelName) {
        $moduleModelsByName = array_flip(self::getModuleModelNames());
        return isset($moduleModelsByName[$modelName]);
    }

    /**
     * magic getter for names of visible, editable modules
     */
    private static $_moduleModelNames;

    public static function getModuleModelNames() {
        if (!isset(self::$_moduleModelNames)) {
            $modules = self::getModules();
            $modelNames = array();
            foreach ($modules as $module) {
                if ($modelName = X2Model::getModelName($module->name))
                    $modelNames[] = $modelName;
                else // Shouldn't happen since getModelName uses class_exists
                    $modelNames[] = ucfirst($module->name);
            }
            self::$_moduleModelNames = $modelNames;
        }
        return self::$_moduleModelNames;
    }

    /**
     * magic getter for visible, editable modules 
     */
    private static $_modules;

    public static function getModules() {
        if (!isset(self::$_modules)) {
            self::$_modules = X2Model::model('Modules')
                    ->findAllByAttributes(array('visible' => 1, 'editable' => true));
        }
        return self::$_modules;
    }

    /**
     * @var array $_moduleModelsByName 
     */
    private static $_moduleModelsByName;

    public static function getModuleModelsByName() {
        if (!isset(self::$_moduleModelsByName)) {
            $modules = self::getModules();
            $modelNames = array();
            foreach ($modules as $module) {
                if ($modelName = X2Model::getModelName($module->name))
                    $modelNames[$modelName] = X2Model::model($modelName);
                else // Custom module most likely
                    $modelNames[ucfirst($module->name)] = X2Model::model($modelName);
            }
            self::$_moduleModelsByName = $modelNames;
        }
        return self::$_moduleModelsByName;
    }

    /**
     * Returns the translated module titles indexed by association type
     * @return array 
     */
    public static function getAssociationTypeOptions() {
        $modelNames = array_keys(self::getModelNames());

        if (Yii::app()->user->checkAccess('MarketingAdminAccess'))
            $modelNames[] = 'AnonContact';


        $associationTypes = array();
        foreach ($modelNames as $modelName) {
            $associationTypes[self::getAssociationType($modelName)] = self::getModelTitle(
                            $modelName);
        }
        return $associationTypes;
    }

    public function getDisplayName($plural = true) {
        $moduleName = X2Model::getModuleName(get_class($this));
        return Modules::displayName($plural, $moduleName);
    }

    /**
     * Returns the title of the model to display in the UI
     */
    public static function getModelTitle($modelClass, $singular = false) {
        if ($modelClass == 'Calendar') // model name is prefixed with X2
            $modelClass = 'X2Calendar';
        if (!isset(self::$translatedModelTitles[$singular][$modelClass])) {
            if (!isset(self::$translatedModelTitles)) {
                self::$translatedModelTitles = array();
            }
            self::$translatedModelTitles[$singular] = array();
            try {
                $model = self::model($modelClass);
            } catch (Exception $e) {
                $model = null;
            }
            if ($model) {
                $title = $model->getDisplayName(!$singular);
            } else {
                $title = $modelClass;
            }
            self::$translatedModelTitles[$singular][$modelClass] = Yii::t(
                            ($model && isset(self::model($modelClass)->module)) ?
                            self::model($modelClass)->module : 'app', $title);
        }
        return self::$translatedModelTitles[$singular][$modelClass];
    }

    public static function getTranslatedModelTitles($singular = false) {
        $modelTitles = array();
        foreach (self::$modelNameToModuleName as $model => $module) {
            $modelTitles[$model] = self::getModelTitle($model, $singular);
        }
        return $modelTitles;
    }

    /**
     * Returns module name for given model name, or $modelName if none could be found 
     * @param string $modelName the name of a model
     * @return string the name of the module associated with the model
     */
    public static function getModuleName($modelName) {
        if (isset(self::$modelNameToModuleName[$modelName])) {
            return self::$modelNameToModuleName[$modelName];
        } else {
            return strtolower($modelName);
        }
    }

    /**
     * Returns model name of module associated with current controller
     * Precondition: model is an instance of X2Model
     * @return string model name
     */
    public static function getModuleModelName() {
        return X2Model::getModelName(Yii::app()->controller->module->name);
    }

    /**
     * Returns model of module associated with current controller
     * Precondition: model is an instance of X2Model
     * @return object model
     */
    public static function getModuleModel() {
        return X2Model::model(X2Model::getModuleModelName());
    }

    /**
     * Updates action timer sum fields in X2Model.
     * 
     * @todo write a proper unit test for this method
     */
    public static function updateTimerTotals($modelId, $modelName = null) {
        Yii::import('application.modules.actions.models.*');
        $modelName = empty($modelName) ? get_called_class() : $modelName;
        $model = self::model($modelName)->findByPk($modelId);
        if (empty($model) || $model->asa('LinkableBehavior') == null)
            return;
        // All fields of type "timerSum":
        $fields = array_filter($model->fields, function($f) {
            return $f->type == 'timerSum';
        });
        foreach ($fields as $field) {
            if ($field->linkType == null) {
                // "all types" specified, so take the shortcut of summing over
                // timeSpent field of all actions, which already itself 
                // contain sums over timer records, all of which are also
                // associated with the current model:
                $model->{$field->fieldName} = Yii::app()->db->createCommand()
                        ->select("SUM(timeSpent)")
                        ->from(Actions::model()->tableName())
                        ->where("associationId=:id 
                             AND associationType=:module
                             AND type IN ('call','time')")
                        ->queryScalar(array(
                    ':module' => $model->module,
                    ':id' => $modelId
                ));
            } else {
                // Sum over all *published* timer records of the given type:
                $model->{$field->fieldName} = Yii::app()->db->createCommand()
                        ->select("SUM(endtime-timestamp)")
                        ->from(ActionTimer::model()->tableName())
                        ->where("associationId=:id
                        AND associationType=:modelName
                        AND actionId IS NOT NULL
                        AND type=:type")
                        ->queryScalar(array(
                    ':id' => $modelId,
                    ':modelName' => $modelName,
                    ':type' => $field->linkType
                ));
            }
        }
        if (count($fields) > 0)
            $model->update(array_map(function($f) {
                        return $f->fieldName;
                    }, $fields));
    }

    public function getMediaLookupFields() {
        $fields = Fields::model()->findAllByAttributes(array(
            'type' => 'link',
            'linkType' => 'Media',
            'modelName' => X2Model::getModelName(get_class($this)),
        ));
        return $fields;
    }

    /**
     * Hides record from all but admin users who have "Show Hidden" turned on
     */
    public function hide() {
        $visibilityAttr = $this->getVisibilityAttr();
        $assignmentAttr = $this->getAssignmentATtr();
        $this->$visibilityAttr = X2PermissionsBehavior::VISIBILITY_PRIVATE;
        $this->$assignmentAttr = 'Anyone';
    }

    /**
     * Use all email addresses of the model for finding a record
     * @param type $email
     */
    public function findByEmail($email) {
        $criteria = new CDbCriteria;
        $paramCount = 0;
        foreach ($this->getFields() as $field) {
            if ($field->type == 'email') {
                $paramCount++;
                $params[$param = ":email$paramCount"] = $email;
                $criteria->addCondition("`{$field->fieldName}`=$param", 'OR');
            }
        }
        $criteria->params = $params;
        if ($this->asa('DuplicateBehavior')) {
            $criteria->addCondition($this->getHiddenCondition(), 'AND');
        }
        return self::model(get_class($this))->find($criteria);
    }

    /**
     * Finds a model via a nameId reference
     * @param type $nameId
     * @return type
     */
    public function findByNameId($nameId) {
        return self::model()->findByAttributes(compact('nameId'));
    }

    public function findByAttributes($attributes, $condition = '', $params = array()) {
        if ($this->asa('DuplicateBehavior')) {
            $hiddenCondition = $this->getHiddenCondition();
            if (empty($condition)) {
                $condition = $hiddenCondition;
            } else {
                if (is_array($condition)) {
                    if (isset($condition['condition'])) {
                        $condition['condition'] .= ' AND ' . $hiddenCondition;
                    } else {
                        $condition['condition'] = $hiddenCondition;
                    }
                } else {
                    $condition .= ' AND ' . $hiddenCondition;
                }
            }
        }
        return parent::findByAttributes($attributes, $condition, $params);
    }

    /**
     * Magic getter for {@link myModelName}
     * @return string
     */
    public function getMyModelName() {
        return self::getModelName(get_class($this));
    }

    public function resetFieldsPropertyCache() {
        $key = $this->tableName();
        self::$_fields[$key] = null;
        $this->queryFields();
    }

    /**
     * Queries and caches Fields objects for the model.
     *
     * This method obtains the fields defined for the model in
     * <tt>x2_fields</tt> and makes them available for later usage to ensure
     * that the query does not need to be performed again. The fields are stored
     * as both static attributes of the model and and as Yii cache objects.
     */
    protected function queryFields() {
        $key = $this->tableName();

        // only look up fields if they haven't already been looked up
        if (!isset(self::$_fields[$key])) {

            // check the app cache for the data
            self::$_fields[$key] = Yii::app()->cache->get('fields_' . $key);
            if (self::$_fields[$key] === false) { // if the cache is empty, look up the fields
                $fieldList = CActiveRecord::model('Fields')->findAllByAttributes(
                        array('modelName' => get_class($this), 'isVirtual' => 0));
                if (!empty($fieldList)) {
                    self::$_fields[$key] = $fieldList;

                    // cache the data
                    Yii::app()->cache->set('fields_' . $key, self::$_fields[$key], 0);
                } else {
                    self::$_fields[$key] = $this->attributeLabels();
                }
            }
        }
    }

    public function relations() {
        $relations = array();
        $myClass = get_class($this);

        // Generate relations from link-type fields.
        foreach (self::$_fields[$this->tableName()] as &$_field) {
            if ($_field->type === 'link' && class_exists($_field->linkType)) {
                $relations[$alias = $_field->fieldName . 'Model'] = array(
                    self::BELONGS_TO,
                    $_field->linkType,
                    array($_field->fieldName => 'nameId'),
                );
            }
        }
        if (Yii::app()->contEd('pro')) {
            $relations['gallery'] = array(
                self::HAS_ONE, 'GalleryToModel',
                'modelId',
                'condition' => 'modelName="' . $myClass . '"');
        }
        return $relations;
    }

    /**
     * Returns a list of behaviors that this model should behave as.
     * @return array the behavior configurations (behavior name=>behavior configuration)
     */
    public function behaviors() {
        $behaviors = array(
            'LinkableBehavior' => array('class' => 'LinkableBehavior'),
            'TimestampBehavior' => array('class' => 'TimestampBehavior'),
            'FlowTriggerBehavior' => array('class' => 'FlowTriggerBehavior'),
            'TagBehavior' => array('class' => 'TagBehavior'),
            'changelog' => array('class' => 'ChangeLogBehavior'),
            'permissions' => array('class' => Yii::app()->params->modelPermissions),
            'MergeableBehavior' => array('class' => 'MergeableBehavior'),
            'relationships' => array('class' => 'RelationshipsBehavior'),
        );
        if (Yii::app()->contEd('pro')) {
            $behaviors['galleryBehavior'] = array(
                'class' => 'application.extensions.gallerymanager.GalleryBehavior',
                'idAttribute' => 'galleryId',
                'versions' => array(
                    'small' => array(
                        'centeredpreview' => array(98, 98),
                    ),
                ),
                'name' => true,
                'description' => true,
            );
        }
        return $behaviors;
    }

    /**
     * Saves attributes on initial model lookup
     */
    public function afterFind() {
        $this->_oldAttributes = $this->getAttributes();
        parent::afterFind();
    }

    /**
     * Remembers if this was a new record before saving.
     * @returns the answer from {@link CActiveRecord::beforeSave()}
     */
    public function beforeSave() {
        if ($this->asa('ContactsNameBehavior')) {
            $this->asa('ContactsNameBehavior')->setName();
        }

        $this->_runAfterCreate = $this->getIsNewRecord();
        if (!$this->_runAfterCreate) {
            $this->updateNameId();
        } else {
            // Safeguard against duplicate entries (violating unique constraint
            // on the nameId column): set uniqueId before submitting to
            // some unique value, and let it be updated to a proper uniqueId
            // value after saving. This is just in case the nameId update after
            // insertion fails, which is easily corrected.
            if ($this->hasAttribute('nameId')) {
                $this->nameId = uniqid();
            }
        }
        return parent::beforeSave();
    }

    public function onAfterCreate($event) {
        $this->raiseEvent('onAfterCreate', $event);
    }

    public function afterCreate() {
        $this->_runAfterCreate = false;
        if ($this->hasEventHandler('onAfterCreate'))
            $this->onAfterCreate(new CEvent($this));
    }

    public function onAfterInsert($event) {
        $this->raiseEvent('onAfterInsert', $event);
    }

    public function onAfterUpdate($event) {
        $this->raiseEvent('onAfterUpdate', $event);
    }

    public function afterUpdate() {

        // Update, as necessary, references to this record via the nameId field.
        /* x2tempstart */
        $this->updateNameIdRefs();
        /* x2tempend */

        if ($this->hasEventHandler('onAfterUpdate')) {
            $this->onAfterUpdate(new CEvent($this));
        }
    }

    /**
     * Runs when a model is deleted.
     * Clears any entries in <tt>x2_phone_numbers</tt>.
     * Fires onAfterDelete event.
     */
    public function afterDelete() {
        // Clear out old tags:
        $class = get_class($this);
        Tags::model()->deleteAllByAttributes(array(
            'type' => $class,
            'itemId' => $this->id
        ));

        // Clear out old phone numbers
        X2Model::model('PhoneNumber')->deleteAllByAttributes(array(
            'modelId' => $this->id,
            'modelType' => $class
        ));

        RecordAliases::model()->deleteAllByAttributes(array(
            'recordId' => $this->id,
            'recordType' => $class,
        ));

        // Change all references to this record so that they retain the name but
        // exclude the ID:
        if ($this->hasAttribute('nameId') && $this->hasAttribute('name')) {
            $this->_oldAttributes = $this->getAttributes();
            $this->nameId = $this->name;
            $this->updateNameIdRefs();
        }

        // clear out associated actions
        Actions::model()->deleteAllByAttributes(
                array(
                    'associationType' => strtolower(self::getAssociationType(get_class($this))),
                    'associationId' => $this->id
        ));

        if ($this->hasEventHandler('onAfterDelete'))
            $this->onAfterDelete(new CEvent($this));
    }

    /**
     * Modified to enable/disable X2Flow record update trigger.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function save($runValidation = true, $attributes = null) {
        if (!$runValidation || $this->validate($attributes)) {
            /* x2modstart */
            if ($this->asa('FlowTriggerBehavior') &&
                    $this->asa('FlowTriggerBehavior')->enabled) {
                $this->enableUpdateTrigger();
            }
            
            $retVal = $this->getIsNewRecord() ?
                    $this->insert($attributes) : $this->update($attributes);
            
            if ($this->asa('FlowTriggerBehavior') &&
                    $this->asa('FlowTriggerBehavior')->enabled) {
           
                $this->disableUpdateTrigger();
            }
            /* x2modend */
     
            return $retVal;
        } else {
            return false;
        }
    }

    /**
     * Runs when a model is saved.
     * Scans attributes for phone numbers and index them in <tt>x2_phone_numbers</tt>.
     * Updates <tt>x2_relationships</tt> table based on link type fields.
     * Fires onAfterSave event.
     */
    public function afterSave() {
        if ($this->_runAfterCreate)
            $this->afterCreate();
        else
            $this->afterUpdate();

        $phoneFields = array();

        // look through fields for phone numbers and relationships
        foreach (self::$_fields[$this->tableName()] as &$_field) {
            if ($_field->type === 'phone') {
                $fieldValue = $this->getAttribute($_field->fieldName);
                // Only update Phone records that have changed
                if (!isset($this->_oldAttributes[$_field->fieldName]) || $fieldValue != $this->_oldAttributes[$_field->fieldName])
                    $phoneFields[$_field->fieldName] = $fieldValue;
            }
        }

        // create new entries in x2_phone_numbers
        $className = get_class($this);
        foreach ($phoneFields as $field => &$number) {
            if (!empty($number)) {
                // eliminate everything other than digits
                $number = preg_replace('/\D/', '', $number);
                $num = PhoneNumber::model()->findByAttributes(array(
                    'modelId' => $this->id,
                    'modelType' => $className,
                    'fieldName' => $field,
                ));
                if (!$num)
                    $num = new PhoneNumber;
                $num->number = $number;
                $num->modelId = $this->id;
                $num->modelType = $className;
                $num->fieldName = $field;
                $num->save();
            }
        }

        parent::afterSave(); // raise onAfterSave event for behaviors, such as ChangeLogBehavior
    }

    /**
     * Generates validation rules for custom fields
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array_merge(
                parent::rules(), self::modelRules(self::$_fields[$this->tableName()], $this));
    }

    public static function modelRules(&$fields, $model) {
        $fieldTypes = array(
            'required',
            'email',
            'unique',
            'int',
            'numerical',
            //'date',
            //'float',
            'boolean',
            'safe',
            'search',
            'link',
            'foreignKey',
            'uniqueIndex',
        );
        $fieldRules = array_fill_keys($fieldTypes, array());
        $validators = Fields::getFieldTypes('validator');

        foreach ($fields as &$_field) {

            $fieldRules['search'][] = $_field->fieldName;
            if (isset($validators[$_field->type]) && $_field->safe) {
                $fieldRules[$validators[$_field->type]][] = $_field->fieldName;
            }

            if ($_field->required) {
                $fieldRules['required'][] = $_field->fieldName;
            }
            /* x2tempstart */
            // see note above subScenario property
            if (!(property_exists($model, 'subScenario') &&
                    $model->subScenario === 'importOverwrite' && $_field->fieldName === 'id') &&
                    $_field->uniqueConstraint) {
                /* x2tempend */
                $fieldRules['unique'][] = $_field->fieldName;
            }

            if ($_field->type == 'link' && $_field->required)
                $fieldRules['link'][] = $_field->fieldName;

            if ($_field->keyType === 'FOR') {
                $fieldRules['foreignKey'][] = $_field->fieldName;
            }
            if ($_field->keyType === 'UNI') {
                $fieldRules['uniqueIndex'][] = $_field->fieldName;
            }
        }

        $rules = array(
            array(implode(',', $fieldRules['foreignKey']),
                'application.components.validators.X2ModelForeignKeyValidator'),
            array(implode(',', $fieldRules['uniqueIndex']),
                'application.components.validators.X2ModelUniqueIndexValidator'),
            array(implode(',', $fieldRules['required']), 'required'),
            array(implode(',', $fieldRules['unique']), 'unique'),
            array(implode(',', $fieldRules['numerical']), 'numerical'),
            array(implode(',', $fieldRules['email']), 'email'),
            array(implode(',', $fieldRules['int']), 'numerical', 'integerOnly' => true),
            array(implode(',', $fieldRules['boolean']), 'boolean'),
            array(implode(',', $fieldRules['link']), 'application.components.ValidLinkValidator'),
            array(implode(',', $fieldRules['safe']), 'safe'),
            array(implode(',', $fieldRules['search']), 'safe', 'on' => 'search'),
        );

        return $rules;
    }

    /**
     * Returns the named attribute value.
     * Recognizes linked attributes and looks them up with {@link getLinkedAttribute()}
     * @param string $name the attribute name
     * @param bool $renderFlag
     * @param bool $makeLinks If the render flag is set, determines whether to render attributes
     *  as links
     * @return mixed the attribute value. Null if the attribute is not set or does not exist.
     * @see hasAttribute
     */
    public function getAttribute($name, $renderFlag = false, $makeLinks = false) {
        // check for a linked attribute (eg. "account.assignedTo")
        $nameParts = explode('.', $name);

        if (count($nameParts) > 1) {
            // We have a complicated link like "account.primaryContact.email"

            $linkField = array_shift($nameParts); // Remove the current model
            $linkModel = $this->getLinkedModel($linkField);

            // Put the name back together e.g. primaryContact.email
            $name = implode('.', $nameParts);

            if (isset($linkModel)) {
                return $linkModel->getAttribute($name, $renderFlag);
            } else {
                // If it's an assignment field, check the Profile model
                $fieldInfo = $this->getField($linkField);
                if ($fieldInfo instanceof Fields && $fieldInfo->type == 'assignment') {
                    $profRecord = X2Model::model('Profile')
                            ->findByAttributes(array('username' => $this->$linkField));

                    if (isset($profRecord)) {
                        return $profRecord->getAttribute($name, $renderFlag);
                    }
                }
            }
        } else {
            if ($renderFlag) {
                return $this->renderAttribute($name, $makeLinks);
            } else {
                return parent::getAttribute($name);
            }
        }
        return null;
    }

    /**
     * Looks up a linked attribute by loading the linked model and calling getAttribute() on it.
     * @param string $linkField the attribute of $this linking to the external model
     * @param string $attribute the attribute of the external model
     * @return mixed the attribute value. Null if the attribute is not set or does not exist.
     */
    public function getLinkedAttribute($linkField, $attribute) {
        if (null !== $model = $this->getLinkedModel($linkField))
            return $model->getAttribute($attribute);
        return null;
    }

    /**
     * Looks up a linked attribute by loading the linked model and calling renderAttribute() on it.
     * @param string $linkField the attribute of $this linking to the external model
     * @param string $attribute the attribute of the external model
     * @return mixed the properly formatted attribute value. Null if the attribute is not set or 
     *  does not exist.
     * @see getLinkedAttribute
     */
    public function renderLinkedAttribute($linkField, $attribute) {
        if (null !== $model = $this->getLinkedModel($linkField))
            return $model->renderAttribute($attribute);
        return null;
    }

    /**
     * Looks up an external model referenced in a link field.
     * Caches loaded models in X2Model::$_linkedModels
     * @param string $linkField the attribute of $this linking to the external model
     * @param bool $lookup Actually look up the model; otherwise (if false) use
     *  the name/ID to populate a dummy model that can be used for just
     *  generating a link.
     * @return mixed the active record. Null if the attribute is not set or does not exist.
     */
    public function getLinkedModel($linkField, $lookup = true) {
        $nameId = $this->getAttribute($linkField);
        list($name, $id) = Fields::nameAndId($nameId);

        if (ctype_digit((string) $id)) {
            $field = $this->getField($linkField);

            if ($field !== null && $field->type === 'link') {
                $modelClass = $field->linkType;

                if (!$lookup) {
                    return self::getLinkedModelMock($modelClass, $name, $id);
                }

                // try to look up the linked model
                if (!isset(self::$_linkedModels[$modelClass][$id])) {
                    self::$_linkedModels[$modelClass][$id] = X2Model::model($modelClass)->findByPk($id);
                    if (self::$_linkedModels[$modelClass][$id] === null)  // if it doesn't exist, set it to false in the cache
                        self::$_linkedModels[$modelClass][$id] = false;  // so isset() returns false and we can skip this next time
                }

                if (self::$_linkedModels[$modelClass][$id] !== false)
                    return self::$_linkedModels[$modelClass][$id];  // success!
            }
        }
        return null;
    }

    /**
     * Creates a mock-up of a linked model with the minimum requirements for
     * generating a link in a view of another model.
     * 
     * @param string $modelClass
     * @param string $name
     * @param integer $id
     * @param bool $allowEmpty Return the model even if $name/$id are empty.
     * @return \modelClass|string
     */
    public static function getLinkedModelMock($modelClass, $name, $id, $allowEmpty = false) {
        if ($id !== null || $allowEmpty) {
            // Take the shortcut for link generation:
            $model = X2Model::model($modelClass);
            if (!$model instanceof X2Model) {
                throw new CException("Error: model $modelClass does not refer to an existing child class of X2Model.");
            }
            if ($model->hasAttribute('id') && $model->hasAttribute('name')) {
                $model->id = $id;
                $model->name = $name;
                return $model;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Wrapper method for generating a link to the view for a model record.
     *
     * @param int $id the route to this model's AutoComplete data source
     * @param string $class the model class
     * @return string a link to the model, or $id if the model is invalid
     */
    public static function getModelLink($id, $class, $requireAbsoluteUrl = false) {
        try {
            $model = X2Model::model($class)->findByPk($id);
        } catch (CHttpException $e) {
            $model = null;
        }
        if (isset($model) && !is_null($model->asa('LinkableBehavior'))) {
            if (isset(Yii::app()->controller) && method_exists(Yii::app()->controller, 'checkPermissions')) {
                if (Yii::app()->controller->checkPermissions($model, 'view')) {
                    if ($requireAbsoluteUrl) {
                        return $model->getUrlLink();
                    } else {
                        return $model->getLink();
                    }
                } else {
                    return $model->renderAttribute('name');
                }
            } else {
                if ($requireAbsoluteUrl) {
                    return $model->getUrlLink();
                } else {
                    return $model->getLink();
                }
            }
            // return CHtml::link($model->name,array($model->getDefaultRoute().'/'.$model->id));
        } elseif (is_numeric($id)) {
            return '';
        } else {
            return $id;
        }
    }

    /**
     * @return array static linked models indexed by link field name
     */
    public function getStaticLinkedModels() {
        $linkFields = array_filter($this->fields, function ($field) {
            return $field->type === 'link';
        });
        $linkedModels = array();
        foreach ($linkFields as $field) {
            $linkedModels[$field->fieldName] = new $field->linkType ();
        }
        return $linkedModels;
    }

    /**
     * Link generation shortcut.
     * @param type $modelClass
     * @param type $nameId
     * @param array $htmlOptions options to be applied to the link element
     * @return type
     */
    public static function getModelLinkMock($modelClass, $nameId, $htmlOptions = array()) {
        list($name, $id) = Fields::nameAndId($nameId);
        $model = self::getLinkedModelMock($modelClass, $name, $id);
        if ($model instanceof X2Model && !is_null($model->asa('LinkableBehavior'))) {
            return $model->getLink($htmlOptions);
        } else {
            return CHtml::encode($name);
        }
    }

    /**
     * Returns all possible models, either as a regular array or associative
     * (key and value are the same)
     * @param boolean $assoc
     * @return array
     */
    public static function getModelTypes($assoc = false, $filter = null) {
        $modelTypes = Yii::app()->db->createCommand()
                ->selectDistinct('modelName')
                ->from('x2_fields')
                ->where('modelName!="Calendar"')
                ->order('modelName ASC')
                ->queryColumn();
        if ($filter) {
            $modelTypes = array_filter($modelTypes, $filter);
        }

        if ($assoc === true) {
            $modelTypes = array_combine($modelTypes, array_map(function($type) {
                        return X2Model::model($type)->getDisplayName(true, false);
                    }, $modelTypes));
            asort($modelTypes);
            return $modelTypes;
        }
        $modelTypes = array_map(function($term) {
            return Yii::t('app', $term);
        }, $modelTypes);
        return $modelTypes;
    }

    /**
     * Returns all possible models that support location, either as a regular array or associative
     * (key and value are the same)
     * @return array
     */
    public static function getModelTypesWhichSupportLocation() {
        $modelTypes = self::getModelTypes();
        $filteredTypes = array();
        foreach ($modelTypes as $type => $title) {
            if (X2Model::Model($type)->asa('users') || X2Model::Model($type)->asa('contacts')) {
                $filteredTypes[] = $type;
            }
        }
        return $filteredTypes;
    }

    /**
     * Like getModelTypes () except that only types of models which support relationships are 
     * returned. 
     * 
     * if $assoc is true, the return array will appear as so:
     *        array (
     *            <Model Name> => <Translated Model Name>, ...
     *        )
     * if $assoc is false:
     *        array (
     *            <index> => <Model Name>, ...
     *        )
     * 
     * @param boolean $assoc to return as an associative array or not
     * @return array of model names as specified above
     */
    private static $_modelsWhichSupportRelationships;

    public static function getModelTypesWhichSupportRelationships($assoc = false, $refresh = false) {
        if (!isset(self::$_modelsWhichSupportRelationships[$assoc]) || $refresh) {
            $modelTypes = self::getModelTypes(true);
            $filteredTypes = array();
            foreach ($modelTypes as $type => $title) {
                if (X2Model::Model($type)->asa('relationships')) {
                    if ($assoc) {
                        $filteredTypes[$type] = $title;
                    } else {
                        $filteredTypes[] = $type;
                    }
                }
            }
            self::$_modelsWhichSupportRelationships[$assoc] = $filteredTypes;
        }

        return self::$_modelsWhichSupportRelationships[$assoc];
    }

    /**
     * Like getModelTypes () except that only types of models which support workflow are 
     * returned
     * @param boolean $assoc
     * @return array 
     */
    public static function getModelTypesWhichSupportWorkflow($assoc = false, $associationTypes = false) {
        $modelTypes = self::getModelTypes($assoc);
        $tmp = $assoc ? array_flip($modelTypes) : $modelTypes;
        $tmp = array_filter($tmp, function ($a) use ($assoc) {
            return X2Model::Model($a)->supportsWorkflow;
        });
        $tmp = $assoc ? array_flip($tmp) : $tmp;
        $tmp = array_intersect($modelTypes, $tmp);
        if ($associationTypes) {
            $arr = array();
            foreach ($tmp as $k => $v) {
                if ($assoc) {
                    $arr[X2Model::getAssociationType($k)] = $v;
                } else {
                    $arr[] = X2Model::getAssociationType($v);
                }
            }
            $tmp = $arr;
        }
        return $tmp;
    }

    /**
     * Returns a translated label using "module" defined in 
     * @param type $label
     * @return type
     */
    public function translatedAttributeLabel($label) {
        return Yii::t((bool) $this->asa('LinkableBehavior') ?
                        (empty($this->module) ? 'app' : $this->module) : 'app', $label);
    }

    /**
     * Returns custom attribute values defined in x2_fields
     * @return array customized attribute labels (name=>label)
     * @see generateAttributeLabel
     */
    public function getAttributeLabels() {
        $tableName = $this->tableName();
        if (!isset(self::$_attributeLabels[$tableName])) {
            $labels = array();

            foreach (self::$_fields[$tableName] as &$_field) {
                $labels[$_field->fieldName] = $this->translatedAttributeLabel($_field->attributeLabel);
            }

            self::$_attributeLabels[$tableName] = $labels;
        }
        return self::$_attributeLabels[$tableName];
    }

    /**
     * Returns custom attribute values defined in x2_fields
     * @return array customized attribute labels (name=>label)
     * @see generateAttributeLabel
     */
    public function attributeLabels() {
        $labels = array();

        foreach (self::$_fields[$this->tableName()] as &$_field) {
            $labels[$_field->fieldName] = $this->translatedAttributeLabel($_field->attributeLabel);
        }

        return $labels;
    }

    /**
     * Returns the text label for the specified attribute.
     * This method overrides the parent implementation by supporting
     * returning the label defined in relational object.
     * In particular, if the attribute name is in the form of "post.author.name",
     * then this method will derive the label from the "author" relation's "name" attribute.
     * @param string $attribute the attribute name
     * @return string the attribute label
     * @see generateAttributeLabel
     * @since 1.1.4
     */
    public function getAttributeLabel($attribute) {
        $attributeLabels = $this->getAttributeLabels();
        if (isset($attributeLabels[$attribute]))
            return $attributeLabels[$attribute];

        if (isset(self::$_fields[$this->tableName()][$attribute])) {
            return self::$_fields[$this->tableName()][$attribute];
        }
        // original Yii code
        if (strpos($attribute, '.') !== false) {
            $segs = explode('.', $attribute);
            $name = array_pop($segs);
            $model = $this;
            foreach ($segs as $seg) {
                $relations = $model->getMetaData()->relations;
                if (isset($relations[$seg]))
                    $model = X2Model::model($relations[$seg]->className);
                else
                    break;
            }
            return $model->getAttributeLabel($name);
        } else
            return $this->generateAttributeLabel($attribute);
    }

    public function getOldAttributes() {
        return $this->_oldAttributes;
    }

    /**
     * Returns all attributes of the current model that the user has permission
     * to view.
     * 
     * @param type $names
     */
    public function getReadableAttributeNames() {
        return array_keys(array_filter($this->getFieldPermissions(), function($p) {
                    return $p >= Fields::READ_PERMISSION;
                }));
    }

    public function getEditableAttributeNames() {
        return array_keys(array_filter($this->getFieldPermissions(), function($p) {
                    return $p >= Fields::WRITE_PERMISSION;
                }));
    }

    /**
     * Filters attributes to those for which the current user has view permission
     * @return array attribute values indexed by name 
     */
    public function getVisibleAttributes() {
        if (!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
            $fieldPermissions = $this->getFieldPermissions();
        } else { // bypass permissions
            return $this->getAttributes();
        }

        $visibleAttributeNames = array();
        foreach ($fieldPermissions as $fieldName => $permission) {
            if ($permission >= Fields::READ_PERMISSION) {
                $visibleAttributeNames[] = $fieldName;
            }
        }
        return $this->getAttributes($visibleAttributeNames);
    }

    /**
     * @param bool $assoc If true, fields in returned array will be indexed by field name
     * @param null|function $filterFn 
     * @param int $requiredPermission Used to filter fields by field-level permissions
     * @return array 
     */
    public function getFields($assoc = false, $filterFn = null, $requiredPermission = Fields::NO_PERMISSION) {

        if ($assoc) {
            $fields = array();
            foreach (self::$_fields[$this->tableName()] as &$field) {
                if ($filterFn !== null) {
                    if ($filterFn($field)) {
                        $fields[$field->fieldName] = $field;
                    }
                } else {
                    $fields[$field->fieldName] = $field;
                }
            }
            return $fields;
        } else {
            if ($filterFn !== null) {
                $fields = array();
                foreach (self::$_fields[$this->tableName()] as &$field) {
                    if ($filterFn($field)) {
                        $fields[] = $field;
                    }
                }
                return $fields;
            } else {
                return self::$_fields[$this->tableName()];
            }
        }

        // remove all fields for which the user lacks sufficient permission
        if ($requiredPermission > Fields::NO_PERMISSION) {
            $permissions = $this->getFieldPermissions();
            foreach ($permissions as $name => $permissionLevel) {
                if ($permissionLevel > $requiredPermission) {
                    unset($fields[$name]);
                }
            }
        }
    }

    /**
     * @return array all standard comparison operators
     */
    public static function getFieldComparisonOptions() {
        return array(
            '=' => Yii::t('app', 'equals'),
            '>' => Yii::t('app', 'greater than'),
            '<' => Yii::t('app', 'less than'),
            '>=' => Yii::t('app', 'greater than or equal to'),
            '<=' => Yii::t('app', 'less than or equal to'),
            '<>' => Yii::t('app', 'not equal to'),
            'list' => Yii::t('app', 'in list'),
            'notList' => Yii::t('app', 'not in list'),
            'empty' => Yii::t('app', 'empty'),
            'notEmpty' => Yii::t('app', 'not empty'),
            'contains' => Yii::t('app', 'contains'),
            'noContains' => Yii::t('app', 'does not contain'),
            'before' => Yii::t('app', 'before'),
            'after' => Yii::t('app', 'after'),
        );
    }

    /**
     * @param bool $includeFieldsOfLinkedRecords if true, add field options for related models
     * @param bool $condList 
     * @param function|null $filterFn if set, will be used to filter results
     * @param string $separator used to separate parent attribute from field name 
     * @return array  
     */
    public function getFieldsForDropdown(
    $includeFieldsOfLinkedRecords = false, $condList = true, $filterFn = null, $separator = '.') {

        if ($includeFieldsOfLinkedRecords) {
            $linkedModels = $this->getStaticLinkedModels();
            $fieldsForDropdown = array();
            $fieldsForDropdown[''] = $this->_getFieldsForDropdown(
                    null, $condList, true, $filterFn, $separator);
            foreach ($linkedModels as $fieldName => $linkedModel) {
                if ($this->getField($fieldName)) {
                    $optGroupHeader = $this->getAttributeLabel($fieldName);
                } else if (self::isModuleModelName($fieldName)) {
                    $optGroupHeader = self::getModelTitle($fieldName);
                } else {
                    throw new CException('invalid field name');
                }
                $fieldsForDropdown[$optGroupHeader] = $linkedModel->_getFieldsForDropdown(
                        $fieldName, $condList, true, $filterFn, $separator);
            }
            return $fieldsForDropdown;
        } else {
            return $this->_getFieldsForDropdown(null, $condList, true, $filterFn, $separator);
        }
    }

    /**
     * @return null|Fields Fields instance if found, null otherwise
     */
    public function getField($fieldName) {
        foreach (self::$_fields[$this->tableName()] as &$_field) {
            if ($_field->fieldName == $fieldName)
                return $_field;
        }
        return null;
    }

    /**
     * Whether to skip applying field level permissions
     *
     * Returns false if the user has any roles and isn't administrator; returns
     * true (meaning, no arbitrary restrictions on field access/editability)
     * 
     * @return boolean
     */
    public function getIsExemptFromFieldLevelPermissions() {
        return Yii::app()->params->isAdmin || empty(Yii::app()->params->roles);
    }

    public function insert($attributes = null) {
        
        $succeeded = parent::insert($attributes);
       // printR( json_encode($attributes),1);
        // Alter and save the nameId field:
        if ($succeeded && self::$autoPopulateFields) {
            $this->updateNameId(true);

            if ($this->hasEventHandler('onAfterInsert'))
                $this->onAfterInsert(new CEvent($this));
        }
        return $succeeded;
    }

    /**
     * @param string $field the name of the field
     * @param string $class the name of the class with which the field is associated
     * @param int $id
     * @param bool $encode Whether to html encode the number
     * @param bool $makeLink Whether return a phone link
     * @param string $default What to use in case phone number lookup failed;
     *  circumvents the need to re-query the model if used.
     */
    public static function getPhoneNumber(
    $field, $class, $id, $encode = false, $makeLink = false, $default = null) {

        $phoneCheck = CActiveRecord::model('PhoneNumber')
                ->findByAttributes(
                array('modelId' => $id, 'modelType' => $class, 'fieldName' => $field));
        if ($phoneCheck instanceof PhoneNumber && strlen($phoneCheck->number) == 10 &&
                strpos($phoneCheck->number, '0') !== 0 && strpos($phoneCheck->number, '1') !== 0) {

            $number = (string) $phoneCheck->number;
            $fmtNumber = "(" . substr($number, 0, 3) . ") " . substr($number, 3, 3) . "-" .
                    substr($number, 6, 4);
        } elseif ($default != null) {
            $number = (string) $default;
            $fmtNumber = $default;
        } else {
            $record = X2Model::model($class)->findByPk($id);
            if (isset($record) && $record->hasAttribute($field)) {
                $number = (string) $record->$field;
                $fmtNumber = $number;
            }
        }
        if ($encode && isset($fmtNumber)) {
            $fmtNumber = CHtml::encode($fmtNumber);
        }
        if (isset($fmtNumber) && $makeLink && !Yii::app()->params->profile->disablePhoneLinks) {
            return '<a href="tel:' . $number . '">' . $fmtNumber . '</a>';
        }
        return isset($fmtNumber) ? $fmtNumber : '';
    }

    public static function renderModelInput(CModel $model, $field, $htmlOptions = array()) {
        if (!$field->asa('CommonFieldsBehavior')) {
            throw new CException('$field must have CommonFieldsBehavior');
        }
        if ($field->required) {
            if (isset($htmlOptions['class'])) {
                $htmlOptions['class'] .= ' x2-required';
            } else {
                $htmlOptions = array_merge(
                        array(
                    'class' => 'x2-required'
                        ), $htmlOptions
                );
            }
        }
        $fieldName = $field->fieldName;
        if (!isset($field))
            return null;
        switch ($field->type) {
            case 'text':
                return CHtml::activeTextArea($model, $field->fieldName, array_merge(
                                        array('title' => $field->attributeLabel), array_merge(array('encode' => false), $htmlOptions)));

            case 'date':
                $oldDateVal = $model->$fieldName;
                $model->$fieldName = Formatter::formatDate($model->$fieldName, 'medium');
                Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                $pickerOptions = array(// jquery options
                    'dateFormat' => Formatter::formatDatePicker(),
                    'changeMonth' => false,
                    'changeYear' => true,
                );
                if (Yii::app()->getLanguage() === 'fr')
                    $pickerOptions['monthNamesShort'] = Formatter::getPlainAbbrMonthNames();
                $input = Yii::app()->controller->widget('CJuiDateTimePicker', array(
                    'model' => $model, //Model object
                    'attribute' => $fieldName, //attribute name
                    'mode' => 'date', //use "time","date" or "datetime" (default)
                    'options' => $pickerOptions,
                    'htmlOptions' => array_merge(array(
                        'title' => $field->attributeLabel,
                            ), $htmlOptions),
                    'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                        ), true);
                $model->$fieldName = $oldDateVal;
                return $input;
            case 'dateTime':
                $oldDateTimeVal = $model->$fieldName;
                $pickerOptions = array(// jquery options
                    'dateFormat' => Formatter::formatDatePicker('medium'),
                    'timeFormat' => Formatter::formatTimePicker(),
                    'ampm' => Formatter::formatAMPM(),
                    'changeMonth' => true,
                    'changeYear' => true,
                );
                if (Yii::app()->getLanguage() === 'fr')
                    $pickerOptions['monthNamesShort'] = Formatter::getPlainAbbrMonthNames();
                $model->$fieldName = Formatter::formatDateTime($model->$fieldName);
                Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                $input = Yii::app()->controller->widget('CJuiDateTimePicker', array(
                    'model' => $model, //Model object
                    'attribute' => $fieldName, //attribute name
                    'mode' => 'datetime', //use "time","date" or "datetime" (default)
                    'options' => $pickerOptions,
                    'htmlOptions' => array_merge(array(
                        'title' => $field->attributeLabel,
                            ), $htmlOptions),
                    'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                        ), true);
                $model->$fieldName = $oldDateTimeVal;
                return $input;
            case 'dropdown':
                // Note: if desired to translate dropdown options, change the seecond argument to 
                // $model->module
                $om = $field->getDropdownOptions();
                $multi = (bool) $om['multi'];
                $dropdowns = $om['options'];
                $curVal = $multi ? CJSON::decode($model->{$field->fieldName}) : $model->{$field->fieldName};

                $ajaxArray = array();
                if ($field instanceof Fields) {
                    $dependencyCount = X2Model::model('Dropdowns')
                            ->countByAttributes(array('parent' => $field->linkType));
                    $fieldDependencyCount = X2Model::model('Fields')
                            ->countByAttributes(array(
                        'modelName' => $field->modelName,
                        'type' => 'dependentDropdown',
                        'linkType' => $field->linkType));
                    if ($dependencyCount > 0 && $fieldDependencyCount > 0) {
                        $ajaxArray = array('ajax' => array(
                                'type' => 'GET', //request type
                                'url' => Yii::app()->controller->createUrl('/site/dynamicDropdown'),
                                'data' => 'js:{
                                "val":$(this).val(),
                                "dropdownId":"' . $field->linkType . '",
                                "field":true, "module":"' . $field->modelName . '"
                            }',
                                'success' => '
                                function(data){
                                    if(data){
                                        data=JSON.parse(data);
                                        if(data[0] && data[1]){
                                            $("#' . $field->modelName . '_"+data[0]).html(data[1]);
                                        }
                                    }
                                }',
                        ));
                    }
                }
                $htmlOptions = array_merge(
                        $htmlOptions, $ajaxArray, array('title' => $field->attributeLabel));
                if ($multi) {
                    $multiSelectOptions = array();
                    if (!is_array($curVal))
                        $curVal = array();
                    foreach ($curVal as $option)
                        $multiSelectOptions[$option] = array('selected' => 'selected');
                    $htmlOptions = array_merge(
                            $htmlOptions, array(
                        'options' => $multiSelectOptions,
                        'multiple' => 'multiple'
                    ));
                } elseif ($field->includeEmpty) {
                    $htmlOptions = array_merge(
                            $htmlOptions, array('empty' => Yii::t('app', "Select an option")));
                }
                return CHtml::activeDropDownList($model, $field->fieldName, $dropdowns, $htmlOptions);

            case 'dependentDropdown':
                return CHtml::activeDropDownList($model, $field->fieldName, array('' => '-'), array_merge(
                                        array(
                            'title' => $field->attributeLabel,
                                        ), $htmlOptions
                ));
            case 'link':
                $linkSource = null;
                $linkId = '';
                $name = '';

                if ($field->linkType === 'Media' && in_array($model->scenario, array('webForm', 'webFormWithCaptcha'))) {
                    // Render an upload form for link type fields to Media
                    $input = CHtml::fileField($field->modelName . '[' . $fieldName . ']');
                } else {
                    if (class_exists($field->linkType)) {
                        // Create a model for autocompletion:
                        if (!empty($model->$fieldName)) {
                            list($name, $linkId) = Fields::nameAndId($model->$fieldName);
                            $linkModel = X2Model::getLinkedModelMock($field->linkType, $name, $linkId, true);
                        } else {
                            $linkModel = X2Model::model($field->linkType);
                        }
                        if ($linkModel instanceof X2Model && $linkModel->asa('LinkableBehavior') instanceof LinkableBehavior) {
                            $linkSource = Yii::app()->controller->createUrl($linkModel->autoCompleteSource);
                            $linkId = $linkModel->id;
                            $oldLinkFieldVal = $model->$fieldName;
                            $model->$fieldName = $name;
                        }
                    }

                    static $linkInputCounter = 0;
                    $hiddenInputId = $field->modelName . '_' . $fieldName . "_id" . $linkInputCounter++;
                    $input = CHtml::hiddenField(
                                    $field->modelName . '[' . $fieldName . '_id]', $linkId, array('id' => $hiddenInputId))
                            . Yii::app()->controller->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                'model' => $model,
                                'attribute' => $fieldName,
                                // 'name'=>'autoselect_'.$fieldName,
                                'source' => $linkSource,
                                'value' => $name,
                                'options' => array(
                                    'minLength' => '1',
                                    'select' => 'js:function( event, ui ) {
                                        $("#' . $hiddenInputId . '").
                                            val(ui.item.id);
                                        $(this).val(ui.item.value);
                                        return false;
                                }',
                                    'create' => $field->linkType == 'Contacts' ?
                                    'js:function(event, ui) {
                                        $(this).data( "uiAutocomplete" )._renderItem = 
                                            function(ul,item) {
                                                return $("<li>").data("item.autocomplete",item).
                                                    append(x2.forms.renderContactLookup(item)).
                                                    appendTo(ul);
                                            };
                                }' : ($field->linkType == 'BugReports' ? 'js:function(event, ui) {
                                    $(this).data( "uiAutocomplete" )._renderItem = 
                                        function( ul, item ) {

                                        var label = "<a style=\"line-height: 1;\">" + item.label;

                                        label += "<span style=\"font-size: 0.6em;\">";

                                        // add email if defined
                                        if(item.subject) {
                                            label += "<br>";
                                            label += item.subject;
                                        }

                                        label += "</span>";
                                        label += "</a>";

                                        return $( "<li>" )
                                            .data( "item.autocomplete", item )
                                            .append( label )
                                            .appendTo( ul );
                                    };
                                }' : ''),
                                ),
                                'htmlOptions' => array_merge(array(
                                    'title' => $field->attributeLabel,
                                        ), $htmlOptions)
                                    ), true);
                    if (isset($oldLinkFieldVal))
                        $model->$fieldName = $oldLinkFieldVal;
                }
                return $input;
            case 'rating':
                return Yii::app()->controller->widget('X2StarRating', array(
                            'model' => $model,
                            'attribute' => $field->fieldName,
                            'readOnly' => isset($htmlOptions['disabled']) && $htmlOptions['disabled'],
                            'minRating' => Fields::RATING_MIN, //minimal value
                            'maxRating' => Fields::RATING_MAX, //max value
                            'starCount' => Fields::RATING_MAX - Fields::RATING_MIN + 1, //number of stars
                            'cssFile' => Yii::app()->theme->getBaseUrl() . '/css/rating/jquery.rating.css',
                            'htmlOptions' => $htmlOptions,
                            'callback' => 'function(value, link){
                        if (typeof x2 !== "undefined" &&
                            typeof x2.InlineEditor !== "undefined" &&
                            typeof x2.InlineEditor.ratingFields !== "undefined") {

                            x2.InlineEditor.ratingFields["' .
                            $field->modelName . '[' . $field->fieldName . ']"] = value;
                        }
                    }',), true);

            case 'boolean':
                $checkbox = CHtml::openTag('div', X2Html::mergeHtmlOptions(
                                        $htmlOptions, array(
                                    'class' => 'checkboxWrapper'
                                        )
                ));
                $checkbox .= CHtml::activeCheckBox($model, $field->fieldName, array_merge(array(
                            'unchecked' => 0,
                            'title' => $field->attributeLabel,
                                        ), $htmlOptions));
                $checkbox .= CHtml::closeTag('div');
                return $checkbox;
            case 'assignment':
                $oldAssignmentVal = $model->$fieldName;
                $model->$fieldName = !empty($model->$fieldName) ?
                        ($field->linkType == 'multiple' && !is_array($model->$fieldName) ?
                        explode(', ', $model->$fieldName) : $model->$fieldName) :
                        X2Model::getDefaultAssignment();
                $dropdownList = CHtml::activeDropDownList(
                                $model, $fieldName, X2Model::getAssignmentOptions(true, true), array_merge(array(
                            // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
                            // 'disabled'=>$item['readOnly']? 'disabled' : null,
                            'title' => $field->attributeLabel,
                            'id' => $field->modelName . '_' . $fieldName . '_assignedToDropdown',
                            'multiple' =>
                            ($field->linkType == 'multiple' ? 'multiple' : null),
                                        ), $htmlOptions)
                );
                $model->$fieldName = $oldAssignmentVal;
                return $dropdownList;
            case 'optionalAssignment': // optional assignment for users (can be left blank)

                $users = User::getNames();
                unset($users['Anyone']);

                return CHtml::activeDropDownList($model, $fieldName, $users, array_merge(array(
                            // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
                            // 'disabled'=>$item['readOnly']? 'disabled' : null,
                            'title' => $field->attributeLabel,
                            'empty' => '',
                                        ), $htmlOptions));

            case 'visibility':
                $permissionsBehavior = Yii::app()->params->modelPermissions;
                return CHtml::activeDropDownList($model, $field->fieldName, $permissionsBehavior::getVisibilityOptions(), array_merge(array(
                            'title' => $field->attributeLabel,
                            'id' => $field->modelName . "_visibility",
                                        ), $htmlOptions));

            // 'varchar', 'email', 'url', 'int', 'float', 'currency', 'phone'
            // case 'int':
            // return CHtml::activeNumberField($model, $field->fieldNamearray_merge(array(
            // 'title' => $field->attributeLabel,
            // ), $htmlOptions));

            case 'percentage':
                $htmlOptions['class'] = empty($htmlOptions['class']) ? 'input-percentage' : $htmlOptions['class'] . ' input-percentage';
                return CHtml::activeTextField($model, $field->fieldName, array_merge(array(
                            'title' => $field->attributeLabel,
                                        ), $htmlOptions));

            case 'currency':
                $fieldName = $field->fieldName;
                $elementId = isset($htmlOptions['id']) ?
                        '#' . $htmlOptions['id'] :
                        '#' . $field->modelName . '_' . $field->fieldName;
                Yii::app()->controller->widget('application.extensions.moneymask.MMask', array(
                    'element' => $elementId,
                    'currency' => Yii::app()->params['currency'],
                    'config' => array(
                        //'showSymbol' => true,
                        'affixStay' => true,
                        'decimal' => Yii::app()->locale->getNumberSymbol('decimal'),
                        'thousands' => Yii::app()->locale->getNumberSymbol('group'),
                    )
                ));

                return CHtml::activeTextField($model, $field->fieldName, array_merge(array(
                            'title' => $field->attributeLabel,
                            'class' => 'currency-field',
                                        ), $htmlOptions));
            case 'credentials':
                $typeAlias = explode(':', $field->linkType);
                $type = $typeAlias[0];
                if (count($typeAlias) > 1) {
                    $uid = Credentials::$sysUseId[$typeAlias[1]];
                } else {
                    $uid = Yii::app()->user->id;
                }
                return Credentials::selectorField(
                    $model,
                    $field->fieldName,
                    $type,
                    $uid,
                    array(),
                    count($typeAlias) >= 3 && $typeAlias[2] == 'bounced',
                    false,
                    count($typeAlias) >= 3 && $typeAlias[2] == 'bounced'
                );
            case 'timerSum':
                // Sorry, no-can-do. This is field derives its value from a sum over timer records.
                return $model->renderAttribute($field->fieldName);
            case 'float':
            case 'int':
                if (isset($model->$fieldName)) {
                    $oldNumVal = $model->$fieldName;
                    $model->$fieldName = Yii::app()->locale->numberFormatter->formatDecimal($model->$fieldName);
                }
                $input = CHtml::activeTextField($model, $field->fieldName, array_merge(array(
                            'title' => $field->attributeLabel,
                                        ), $htmlOptions));
                if (isset($oldNumVal)) {
                    $model->$fieldName = $oldNumVal;
                }
                return $input;
            default:
                return CHtml::activeTextField($model, $field->fieldName, array_merge(array(
                            'title' => $field->attributeLabel,
                                        ), $htmlOptions));

            // array(
            // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
            // 'disabled'=>$item['readOnly']? 'disabled' : null,
            // 'title'=>$field->attributeLabel,
            // 'style'=>$default?'color:#aaa;':null,
            // ));
        }
    }

    public static function renderMergeInput($modelType, $idArray, $field) {
        $options = array();
        $equalFlag = true;
        $selected = $idArray[0];
        $lastValue = null;
        foreach ($idArray as $id) {
            $tmpModel = X2Model::model($modelType)->findByPk($id);
            $equalFlag = $equalFlag && (is_null($lastValue) || $lastValue == $tmpModel->{$field->fieldName});
            $lastValue = is_null($tmpModel->{$field->fieldName}) ? "" : $tmpModel->{$field->fieldName};
            $options[$id] = strlen($tmpModel->{$field->fieldName}) == 0 ? '' : $tmpModel->renderAttribute($field->fieldName, false, true, false, false);
        }
        if ($field->type !== 'text') {
            $valid = array_filter($options, 'strlen');
            if (key($valid) != $selected) {
                $selected = key($valid);
            }
            foreach ($options as &$option) {
                if (strlen($option) == 0) {
                    $option = '-';
                }
            }
            return CHtml::dropDownList(X2Model::getModelName($modelType) . '[' . $field->fieldName . ']', $selected, $options, array(
                        'disabled' => $equalFlag ? 'disabled' : '',
                    )) . ($equalFlag ? CHtml::hiddenField(X2Model::getModelName($modelType) . '[' . $field->fieldName . ']', $selected) : "");
        } else {
            $str = str_replace("<br />", "\n", implode("\n--\n", $options));
            return CHtml::textArea(X2Model::getModelName($modelType) . '[' . $field->fieldName . ']', $str, array(
                        'style' => 'height:100px;',
            ));
        }
    }

    /**
     * Renders an attribute of the model based on its field type
     * @param string $fieldName the name of the attribute to be rendered
     * @param array $htmlOptions htmlOptions to be used on the input
     * @return string the HTML or text for the formatted attribute
     */
    public function renderInput($fieldName, $htmlOptions = array()) {
        $field = $this->getField($fieldName);

        if (!$field) {
            return $this->renderErroneousField();
        }

        if ($this->inputRenderer) {
            // check if there's a renderer for this field type
            if ($input = $this->inputRenderer->renderInput($field, $htmlOptions)) {
                return $input;
            }
        }

        return self::renderModelInput($this, $field, $htmlOptions);
    }

    /**
     * Renders an error when a field cannot be found
     */
    public function renderErroneousField() {
        $html = '<span class="erroneous-field">';
        $html .= Yii::t('app', 'Field could not be found');
        $html .= '</span>';
        return $html;
    }

    /**
     * Sets attributes using X2Fields
     * @param array &$data array of attributes to be set (eg. $_POST['Contacts'])
     * @param bool $filter encode all HTML special characters in input
     * @param bool $bypassPermissions (optional)
     */
    public function setX2Fields(&$data, $filter = false, $bypassPermissions = false) {
        $editableFieldsFieldNames = $this->getEditableFieldNames();

        // loop through fields to deal with special types
        foreach (self::$_fields[$this->tableName()] as &$field) {
            $fieldName = $field->fieldName;

            // skip fields that are read-only or haven't been set
            if (!isset($data[$fieldName]) ||
                    (!$bypassPermissions && !in_array($fieldName, $editableFieldsFieldNames))) {

                if (isset($data[$fieldName]) &&
                        !in_array($fieldName, $editableFieldsFieldNames)) {

                    //if (YII_DEBUG)
                    //printR('setX2Fields: Warning: ' . $fieldName . ' set');
                }
                continue;
            }

            // eliminate placeholder values
            if ($data[$fieldName] === $this->getAttributeLabel($fieldName) &&
                    $field->type !== 'dropdown') {

                $data[$fieldName] = null;
            }

            if ($field->type === 'currency') {
                $defaultCurrency = Yii::app()->settings->currency;
                $curSym = Yii::app()->locale->getCurrencySymbol($defaultCurrency);
                if (is_null($curSym))
                    $curSym = $defaultCurrency;
                $data[$fieldName] = Fields::strToNumeric($data[$fieldName], 'currency', $curSym);
            }

            if ($field->type === 'link') {
                // Do a preliminary lookup for linkId in case there are
                // duplicates (similar name) and the user selects one of them,
                // in which case there is an ID from the form that was populated
                // via the auto-complete input widget:
                $linkId = null;
                if (isset($data[$fieldName . '_id'])) {
                    // get the linked model's ID from the hidden autocomplete field
                    $linkId = $data[$fieldName . '_id'];
                }

                if (ctype_digit((string) $linkId)) {
                    $link = Yii::app()->db->createCommand()
                            ->select('name,nameId')
                            ->from(X2Model::model($field->linkType)->tableName())
                            ->where('id=?', array($linkId))
                            ->queryRow(true);
                    // Make sure the linked model exists and that the name matches:
                    if (isset($link['name']) && $link['name'] === $data[$fieldName]) {
                        $data[$fieldName] = $link['nameId'];
                    }
                }
            }
            $this->$fieldName = $field->parseValue($data[$fieldName], $filter);
        }

        // Set default values.
        //
        // This should only happen in the case that the field was not included
        // in the form submission data (with the exception of assignment fields), and the field is 
        // empty, and the record is new.
        if ($this->getIsNewRecord() && $this->scenario == 'insert') {
            // Set default values
            foreach ($this->getFields(true) as $fieldName => $field) {
                if (!isset($data[$fieldName]) && $this->$fieldName == '' &&
                        $field->defaultValue != null && !$field->readOnly) {

                    $this->$fieldName = $field->defaultValue;
                } else if ($this->$fieldName === null && $field->defaultValue === null &&
                        $field->type === 'assignment') {

                    $this->$fieldName = self::getDefaultAssignment();
                }
            }
        }
    }

    /**
     * Base search function, includes Retrieves a list of models based on the current 
     *  search/filter conditions.
     * @param CDbCriteria $criteria the attribute name
     * @param integer $pageSize If set, will override property of profile model
     * @return CActiveDataProvider the data provider that can return the models based on the 
     *  search/filter conditions.
     */
    public function searchBase($criteria, $pageSize = null, $showHidden = false) {
        if (isset($_GET['showHidden']) && $_GET['showHidden'] &&
                Yii::app()->user->checkAccess(self::getModuleName(get_class($this)) . 'Admin')) {

            $showHidden = true;
        }

        if ($criteria === null) {
            $criteria = $this->getAccessCriteria(
                    't', Yii::app()->params->modelPermissions, $showHidden);
        } else {
            $criteria->mergeWith(
                    $this->getAccessCriteria('t', Yii::app()->params->modelPermissions, $showHidden));
        }

        $filterCriteria = new CDbCriteria;
        $this->compareAttributes($filterCriteria);
        $criteria->mergeWith($filterCriteria);
        $criteria->with = array(); // No joins necessary!
        $sort = new SmartSort(
                get_class($this), isset($this->uid) ? $this->uid : get_class($this));
        $sort->multiSort = false;
        $sort->attributes = $this->getSort();
        $sort->defaultOrder = 't.lastUpdated DESC, t.id DESC';

        if ($pageSize === null) {
            if (!Yii::app()->user->isGuest) {
                $pageSize = Profile::getResultsPerPage();
            } else {
                $pageSize = 20;
            }
        }

        $dataProvider = new SmartActiveDataProvider(get_class($this), array(
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
            'criteria' => $criteria,
            'uid' => $this->uid,
            'dbPersistentGridSettings' => $this->dbPersistentGridSettings,
            'disablePersistentGridSettings' => $this->disablePersistentGridSettings,
        ));
        $sort->applyOrder($criteria);
        return $dataProvider;
    }

    public function getSort() {
        $attributes = array();
        foreach (self::$_fields[$this->tableName()] as &$field) {
            $fieldName = $field->fieldName;
            switch ($field->type) {
                default:
                    $attributes[$fieldName] = array(
                        'asc' => 't.' . $fieldName . ' ASC',
                        'desc' => 't.' . $fieldName . ' DESC',
                    );
            }
        }
        return $attributes;
    }

    /**
     * Unshifts valid operators of the front of the string.
     * @return array (<operator>, <remaining string>)
     */
    public function unshiftOperator($string) {
        $retArr = array('', $string);
        if (strlen($string) > 1) {
            if (strlen($string) > 2 && preg_match("/^(<>|>=).*/", $string)) {
                $retArr = array(substr($string, 0, 2), substr($string, 2));
            } else if (preg_match("/^(<|>|=).*/", $string)) {
                $retArr = array($string[0], substr($string, 1));
            }
        }

        return $retArr;
    }

    /**
     * Helper method for compareAttributes 
     */
    protected function compareAttribute(&$criteria, $field) {
        $fieldName = $field->fieldName;
        switch ($field->type) {
            case 'boolean':
                $criteria->compare(
                        't.' . $fieldName, $this->compareBoolean($this->$fieldName), true);
                break;
            case 'assignment':
                $assignmentCriteria = new CDbCriteria;
                $assignmentVal = $this->compareAssignment($this->$fieldName);

                if ($field->linkType === 'multiple' && $this->$fieldName) {
                    if (!is_array($assignmentVal))
                        $assignmentVal = array();
                    $assignmentVal = array_map(function ($val) {
                        return preg_quote($val);
                    }, $assignmentVal);
                    if (strlen($this->$fieldName) && strncmp(
                                    "Anyone", ucfirst($this->$fieldName), strlen($this->$fieldName)) === 0) {

                        $assignmentVal[] = 'Anyone';
                    }
                    $assignmentRegex = '(^|, )(' . implode('|', $assignmentVal) . ')' .
                            (in_array('Anyone', $assignmentVal) ? '?' : '') . '(, |$)';

                    $assignmentParamName = CDbCriteria::PARAM_PREFIX . CDbCriteria::$paramCount;
                    $criteria->params[$assignmentParamName] = $assignmentRegex;
                    CDbCriteria::$paramCount++;
                    $criteria->addCondition(
                            't.' . $fieldName . ' REGEXP BINARY ' . $assignmentParamName);
                } else {
                    $assignmentCriteria->compare(
                            't.' . $fieldName, $assignmentVal, true);
                    if (strlen($this->$fieldName) && strncmp(
                                    "Anyone", ucfirst($this->$fieldName), strlen($this->$fieldName)) === 0) {

                        $assignmentCriteria->compare('t.' . $fieldName, 'Anyone', false, 'OR');
                        $assignmentCriteria->addCondition('t.' . $fieldName . ' = ""', 'OR');
                    }
                }
                $criteria->mergeWith($assignmentCriteria);
                break;
            case 'dropdown':
                $dropdownVal = $this->compareDropdown($field->linkType, $this->$fieldName);
                if (is_array($dropdownVal)) {
                    foreach ($dropdownVal as $val) {
                        $dropdownRegex = '(^|((\\[|,)"))' . preg_quote($val) . '(("(,|\\]))|$)';
                        $dropdownParamName = CDbCriteria::PARAM_PREFIX . CDbCriteria::$paramCount;
                        $criteria->params[$dropdownParamName] = $dropdownRegex;
                        CDbCriteria::$paramCount++;
                        $criteria->addCondition(
                                't.' . $fieldName . ' REGEXP BINARY ' . $dropdownParamName);
                    }
                } else {
                    $criteria->compare('t.' . $fieldName, $dropdownVal, false);
                }
                break;
            case 'date':
            case 'dateTime':
                if (!empty($this->$fieldName)) {
                    // get operator and convert date string to timestamp
                    $retArr = $this->unshiftOperator($this->$fieldName);

                    $operator = $retArr[0];
                    $timestamp = Formatter::parseDate($retArr[1]);
                    if (!$timestamp) {
                        // if date string couldn't be parsed, it's better to display no results
                        // than non-empty incorrect results (which could result in bad mass updates
                        // or deletes)
                        $criteria->addCondition('FALSE');
                    } else if ($operator === '=' || $operator === '') {
                        $criteria->addBetweenCondition(
                                't.' . $fieldName, $timestamp, $timestamp + 60 * 60 * 24);
                    } else {
                        $value = $operator . $timestamp;
                        $criteria->compare('t.' . $fieldName, $value);
                    }
                }
                break;
            case 'phone':
            // $criteria->join .= ' RIGHT JOIN x2_phone_numbers ON (x2_phone_numbers.itemId=t.id AND x2_tags.type="Contacts" AND ('.$tagConditions.'))';
            default:
                $criteria->compare('t.' . $fieldName, $this->$fieldName, true);
        }
    }

    public function compareAttributes(&$criteria) {
        if ($this->asa('TagBehavior') && $this->asa('TagBehavior')->getEnabled() &&
                $this->tags) {

            $tagCriteria = new CDbCriteria;
            $this->compareTags($tagCriteria);
            $criteria->mergeWith($tagCriteria);
        }

        foreach (self::$_fields[$this->tableName()] as &$field) {
            $this->compareAttribute($criteria, $field);
        }
    }

    protected function compareBoolean($data) {
        if (is_null($data) || $data == '')
            return null;

        // default to true unless recognized as false
        return in_array(
                        mb_strtolower(
                                trim($data)), array(0, 'f', 'false', Yii::t('actions', 'No')), true) ? 0 : 1;
    }

    public function compareAssignment($data) {
        if (is_null($data) || $data == '')
            return null;
        $userNames = Yii::app()->db->createCommand()
                ->select('username')
                ->from('x2_users')
                ->where(array('like', 'CONCAT(firstName," ",lastName)', "%$data%"))
                ->queryColumn();
        $groupIds = Yii::app()->db->createCommand()
                ->select('id')
                ->from('x2_groups')
                ->where(array('like', 'name', "%$data%"))
                ->queryColumn();

        return (count($groupIds) + count($userNames) == 0) ? -1 : $userNames + $groupIds;
    }

    protected function compareDropdown($ddId, $value) {
        if (is_null($value) || $value == '') {
            return null;
        }
        $dropdown = X2Model::model('Dropdowns')->findByPk($ddId);
        $multi = $dropdown->multi;
        if (isset($dropdown)) {
            $index = $dropdown->getDropdownIndex($ddId, $value, $multi);
            if (!is_null($index)) {
                return $index;
            } else {
                return -1;
            }
        }
        return -1;
    }

    /**
     * Attempts to load the model with the given ID, if the current
     * user passes authentication checks. Throws an exception if not.
     * @param Integer $d The ID of the model to load
     * @return mixed The model object
     */
    /*     public static function load($modelName,$id) {
      $model = X2Model::model($modelName)->findByPk($id);
      if($model === null)
      throw new CHttpException(404, Yii::t('app', 'Sorry, this record doesn\'t seem to exist.'));



      $authItem = ucfirst(Yii::app()->controller->id).ucfirst(Yii::app()->controller->action->id);

      // $authItem = ucfirst(Yii::app()->controller->id).'ViewPrivate';

      $result = Yii::app()->user->checkAccess($authItem);

      if($model->hasAttribute('visibility') && $model->hasAttribute('assignedTo')) {
      throw new CHttpException(403, 'You are not authorized to perform this action.');
      }

      return $model;
      } */

    /**
     * Returns a model of the appropriate type with a particular record loaded.
     *
     * @param String $type The type of the model to load
     * @param Integer $id The id of the record to load
     * @return CActiveRecord A database record with the requested type and id
     */
    public static function getAssociationModel($type, $id) {
        if ($id != 0 && $modelName = X2Model::getModelName($type))
            return X2Model::model($modelName)->findByPk($id);
        else
            return null;
    }

    /**
     * Picks the primary key attribute out of an associative aray and finds the record
     * @param array $params
     * @return type
     */
    public function findByPkInArray(array $params) {
        $pkc = $this->tableSchema->primaryKey;
        $pk = null;
        if (is_array($pkc)) { // Composite primary key
            $pk = array();
            foreach ($pkc as $colName) {
                if (array_key_exists($colName, $params))
                    $pk[$colName] = $params[$colName];
                else // Primary key column missing
                    return null;
            }
        } elseif (array_key_exists($pkc, $params)) { // Single-column primary key
            $pk = $params[$pkc];
        } else { // Can't do anything; primary key not found in array.
            return null;
        }
        return $this->findByPk($pk);
    }

    /**
     * Sets the nameId field appropriately.
     *
     * The model must be inserted already, so that its primary key can
     * be used.
     * @param bool $save If true, update the model when done.
     */
    public function updateNameId($save = false) {
        if (!$this->hasAttribute('nameId')) {
            return;
        }
        $this->_oldAttributes['nameId'] = $this->nameId;
        $this->nameId = Fields::nameId($this->name, $this->id);
        if ($save) {
            $that = $this;
            $this->runWithoutBehavior('FlowTriggerBehavior', function () use ($that) {
                $that->updateByPk($that->id, array('nameId' => $that->nameId));
            });
        }
    }

    /**
     * Populates the nameId field in multiple records (or all) with one query.
     *
     * Note, if the method {@link Fields::nameId()} is ever dramatically changed,
     * this method too will need to be changed accordingly. The unit test,
     * {X2ModelTest::testMassUpdateNameId()}, is designed to fail when this
     * happens in order to draw attention to it.
     *
     * @param string $modelName
     * @param mixed $ids
     */
    public static function massUpdateNameId($modelName, $ids = array()) {
        $param = array();
        if ($modelName === 'Actions') {
            // Actions don't have a nameId; instead set the associationName
            $idField = 'associationId';
            $nameField = 'associationName';
            $nameIdField = 'associationName';
        } else {
            $idField = 'id';
            $nameField = 'name';
            $nameIdField = 'nameId';
        }
        $sql = "UPDATE `" . self::model($modelName)->tableName() . "` "
                . "SET `" . $nameIdField . "`=CONCAT(`" . $nameField . "`,:delim,`" . $idField . "`)";
        if (is_array($ids) && count($ids) > 1) {
            // Multiple records with IDs specified by $ids parameters
            $count = 0;
            foreach ($ids as $id) {
                $param[":id" . $count] = $id;
                $count++;
            }
            $sql .= ' WHERE `id` IN (' . implode(',', array_keys($param)) . ')';
        } else if (!empty($ids)) {
            // One ID specified:
            $param[':id'] = is_array($ids) ? reset($ids) : $ids;
            $sql .= ' WHERE `id`=:id';
        } else {
            // All records to be udpated:
            $sql .= ' WHERE 1';
        }
        $param[':delim'] = Fields::NAMEID_DELIM;
        $result = Yii::app()->db->createCommand($sql)->execute($param);
        if ($modelName === 'Actions') {
            // clean up association nameIds where the model could not be found
            $sql = 'UPDATE x2_actions ' .
                    'SET associationName = LEFT(`associationName`, INSTR(`associationName`, "_0")-1)' .
                    ' WHERE associationName like "%_0"';
            Yii::app()->db->createCommand($sql)->execute();
        }
        return $result;
    }

    /**
     * Updates references to this model record in other tables.
     *
     * This is a temporary solution to be used until a future version, wherein
     * all tables have been migrated to InnoDB and foreign key constraints can
     * be used to maintain referential integrity. However, this function should
     * still be kept in place for handling the special case of deletion, which
     * cannot be adequately handled by foreign key constraints.
     *
     * It may be a while before we can safely migrate everything to InnoDB,
     * because one or more of our big-ticket customers have FULLTEXT indexes on
     * their contacts table to make searching faster, and FULLTEXT indexes are
     * not supported in InnoDB.
     *
     * @todo Also in said future version would be a suite of unit-tested
     *  methods in Fields (or this model) for creating, deleting and altering
     *  indexes and foreign key constraints, so that new custom fields can
     *  be created properly.
     */
    public function updateNameIdRefs() {

        // Update all references to this model via the nameId field.
        //
        // The name field and thus nameId field may have changed. Update all
        // references to it so that searching/sorting works properly.
        if (!$this->hasAttribute('nameId')) {
            return;
        }
        $nameId = $this->nameId;
        $oldNameId = isset($this->_oldAttributes['nameId']) ?
                $this->_oldAttributes['nameId'] : null;
        if ($nameId !== $oldNameId) {
            // First, however, we need to get references:
            $modelName = get_class($this);
            if (!isset(self::$_nameIdRefs[$modelName])) {
                // Attempt to get cached values:
                $cacheIndex = 'nameIdRefs_' . $modelName;
                self::$_nameIdRefs[$modelName] = Yii::app()->cache->get($cacheIndex);
                // Generate and store an index of references if not available:
                if (self::$_nameIdRefs[$modelName] == false) {
                    $fields = Fields::model()->findAllByAttributes(array(
                        'type' => 'link',
                        'linkType' => $modelName
                    ));
                    self::$_nameIdRefs[$modelName] = array();
                    foreach ($fields as $field) {
                        $table = X2Model::model($field->modelName)->tableName();
                        self::$_nameIdRefs[$modelName][$table][] = $field->fieldName;
                    }
                    // cache the data
                    Yii::app()->cache->set($cacheIndex, self::$_nameIdRefs[$modelName], 0);
                }
            }

            // Go through references and run updates:
            foreach (self::$_nameIdRefs[$modelName] as $table => $columns) {
                foreach ($columns as $column) {
                    Yii::app()->db->createCommand()->update(
                            $table, array($column => $nameId), "$column=:nid", array(':nid' => $oldNameId)
                    );
                }
            }
        }
    }

    /**
     * @return bool true if attribute changed after being saved, false otherwise 
     */
    public function attributeChanged($attr) {
        $oldAttributes = $this->getOldAttributes();
        return (!isset($oldAttributes[$attr]) && $this->isNewRecord) ||
                (in_array($attr, array_keys($oldAttributes)) &&
                $this->getAttribute($attr) != $oldAttributes[$attr]);
    }

    /**
     * Helper method for renderModelInput () and setX2Fields () used to retrieve the application
     * default for the assignment field. This gets superceded by user define default values.
     */
    public static function getDefaultAssignment() {
        return Yii::app()->user->isGuest ? 'Anyone' : Yii::app()->user->getName();
    }

    /**
     * Returns assignment selection options
     * @param type $anyone
     * @param type $showGroups
     * @param type $showSeparator
     * @return type
     */
    public static function getAssignmentOptions(
    $anyone = true, $showGroups = true, $showSeparator = true) {

        $users = User::getNames();
        if ($anyone !== true) {
            unset($users['Anyone']);
        }

        if ($showGroups === true) {
            $groups = Groups::getNames();
            if (count($groups) > 0) {
                if ($showSeparator) {
                    $users = $users + array('' => '--------------------') + $groups;
                } else {
                    $users = $users + $groups;
                }
            }
        }
        return $users;
    }

    /**
     * Returns an array of field names that the user has permission to edit
     * @param boolean if false, get attribute labels as well as field names
     * @return mixed if $suppressAttributeLabels is true, an array of field names is returned,
     *    otherwise an associative array is returned (fieldName => attributeLabel)
     */
    public function getEditableFieldNames($suppressAttributeLabels = true) {
        $class = get_class($this);
        if (!isset(self::$_editableFieldNames[$class])) {
            $editableFields = array_keys(
                    array_filter($this->fieldPermissions, function($p) {
                        return $p >= 2;
                    }));
            if (sizeof($editableFields)) {
                $params = AuxLib::bindArray($editableFields);
                $in = AuxLib::arrToStrList(array_keys($params));
                self::$_editableFieldNames[$class] = Yii::app()->db->createCommand()
                        ->select('fieldName, attributeLabel')
                        ->from('x2_fields')
                        ->where('readOnly!=1 AND modelName="' . get_class($this) . '" '
                                . 'AND fieldName IN ' . $in, $params)
                        ->queryAll();
            } else {
                self::$_editableFieldNames[$class] = array();
            }
        }

        $editableFieldNames = array();
        if (!$suppressAttributeLabels) {
            foreach (self::$_editableFieldNames[$class] as $fieldInfo) {
                $editableFieldNames[$fieldInfo['fieldName']] = $fieldInfo['attributeLabel'];
            }
        } else {
            foreach (self::$_editableFieldNames[$class] as $fieldInfo) {
                $editableFieldNames[] = $fieldInfo['fieldName'];
            }
        }

        return $editableFieldNames;
    }

    /**
     * Getter for {@link fieldPermissions}
     * @return type
     */
    public function getFieldPermissions() {
        $class = get_class($this);

        if (!isset(self::$_fieldPermissions[$class])) {
            $roles = Roles::getUserRoles(Yii::app()->getSuId());
            if (!$this->isExemptFromFieldLevelPermissions) {
                $permRecords = Yii::app()->db->createCommand()
                        ->select("f.fieldName,MAX(rtp.permission),f.readOnly")
                        ->from(RoleToPermission::model()->tableName() . ' rtp')
                        ->join(Fields::model()->tableName() . ' f', 'rtp.fieldId=f.id '
                                . 'AND rtp.roleId IN ' . AuxLib::arrToStrList($roles) . ' '
                                . 'AND f.modelName=:class', array(':class' => $class))
                        ->group('f.fieldName')
                        ->queryAll(false);
            } else {
                $permRecords = Yii::app()->db->createCommand()
                        ->select("fieldName,CAST(2 AS UNSIGNED INTEGER),readOnly")
                        ->from(Fields::model()->tableName() . ' f')
                        ->where('modelName=:class', array(':class' => $class))
                        ->queryAll(false);
            }
            $fieldPerms = array();
            foreach ($permRecords as $record) {
                // If the permissions of the user on the field are "2" (write),
                // subtract the readOnly field
                $fieldPerms[$record[0]] = $record[1] -
                        (integer) ((integer) $record[1] === 2 ? $record[2] : 0);
            }
            self::$_fieldPermissions[$class] = $fieldPerms;
        }
        return self::$_fieldPermissions[$class];
    }

    /**
     * Build a json-encoded form layout for models whose Forms are not editable or
     * for custom modules that do not yet have a user-created form.
     * @param string $modelname The model for which to build a default layout.
     * @return json The default layout for the selected model.
     */
    public static function getDefaultFormLayout($modelName) {
        $model = X2Model::model($modelName);
        $fields = Fields::model()->findAllByAttributes(
                array('modelName' => $modelName), new CDbCriteria(array('order' => 'attributeLabel ASC'))
        );
        $layout = array('sections' => array(array(
                    'collapsible' => false,
                    'title' => ucfirst($modelName) . ' Info',
                    'rows' => array(array(
                            'cols' => array(array(
                                    'items' => array(),
                                )),
                        )),
        )));

        foreach ($fields as $field) {
            if ($field->readOnly)
                continue;

            // hide associationType, it will be set in the dialog by associationName
            if ($field->fieldName == 'associationType')
                continue;

            $newField = array(
                'name' => 'formItem_' . $field->fieldName,
                'labelType' => 'left',
                'readOnly' => $field->readOnly,
                'height' => 30,
                'width' => 155,
            );
            $layout['sections'][0]['rows'][0]['cols'][0]['items'][] = $newField;
        }

        return json_encode($layout);
    }

    /**
     * Should be used before inserting user-generated input into SQL string in cases
     * where parameter binding cannot be used (e.g. for SQL object names). 
     * @param array|string $attribute Name of attribute(s)
     * @throws CException If attribute does not exist
     */
    public static function checkThrowAttrError($attribute) {
        if (is_array($attribute)) {
            foreach ($attribute as $name) {
                self::checkThrowAttrError($name);
            }
            return;
        }

        // prevent SQL injection by validating attribute name
        if (!self::model(get_called_class())->hasAttribute($attribute)) {
            throw new CException(
            Yii::t('app', '{attribute} is not an {modelClass} field.', array(
                '{attribute}' => $attribute,
                '{modelClass}' => get_called_class())));
        }
    }

    /**
     * Retrieves model of a specified type with a specified id 
     * @param bool $isAssocType If true, $type will be treated as an association type. Otherwise,
     *  $type will be treated as a model class name.
     * @return mixed object or null
     */
    public static function getModelOfTypeWithId($type, $id, $isAssocType = false) {
        if ($isAssocType) {
            if (!(empty($type) || empty($id)) &&
                    X2Model::getModelName($type)) { // both ID and type must be set
                return X2Model::model(X2Model::getModelName($type))->findByPk($id);
            }
        } else {
            if (!(empty($type) || empty($id)) &&
                    is_subclass_of(ucfirst($type), 'CActiveRecord')) { // both ID and type must be set
                return X2Model::model($type)->findByPk($id);
            }
        }
        return null; // invalid type or invalid id 
    }

    public static function getModelOfTypeWithName($type, $name) {
        $modelName = X2Model::getModelName($type); // both ID and type must be set
        if (!(empty($type) || empty($name)) && $modelName) { // both ID and type must be set
            $model = X2Model::model($modelName);
            if ($model->hasAttribute('name')) {
                return $model->findByAttributes(array(
                            'name' => $name
                ));
            }
        }
        return null; // invalid type or invalid name 
    }

    /**
     * @param string $modelClass the model class for which the autocomplete should be rendered
     * @param bool $ajax if true, registered scripts are processed with ajaxRender
     */
    public static function renderModelAutocomplete(
    $modelClass, $ajax = false, $htmlOptions = array(), $value = null) {

        $modelClass = self::getModelName($modelClass);

        if (!class_exists($modelClass) || !$modelClass::model()->asa('LinkableBehavior')) {
            if ($ajax) {
                echo 'failure';
                return;
            } else {
                return 'failure';
            }
            /* throw new CException (
              Yii::t('app',
              'Error: renderModelAutocomplete: {modelClass} does not have '.
              'LinkableBehavior', array ('{modelClass}' => $modelClass))); */
        }

        if ($ajax)
            Yii::app()->clientScript->scriptMap['*.css'] = false;

        $renderWidget = function () use ($modelClass, $htmlOptions, $value) {
            Yii::app()->controller->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name' => (isset($htmlOptions['name']) ? $htmlOptions['name'] : 'recordName'),
                'source' => Yii::app()->controller->createUrl(
                        X2Model::model($modelClass)->autoCompleteSource),
                'value' => $value ? $value : Yii::t('app', 'Start typing to suggest...'),
                'options' => array(
                    'minLength' => '1',
                    'create' =>
                    'js:function (event, ui) {
                        // check for callback in parent form
                        if ($(this).closest ("form").data ("afterAutocompleteCreated")) {
                            $(this).closest ("form").data ("afterAutocompleteCreated") ();
                        }
                    }',
                    'select' =>
                    'js:function (event, ui) {
                        $(this).val(ui.item.value);
                        // expects next input to be a hidden input which will contain the
                        // record id
                        $(this).nextAll ("input").val(ui.item.id);
                        return false;
                    }'
                ),
                'htmlOptions' => array_merge(array(
                    'class' => 'record-name-autocomplete x2-default-field',
                    'data-default-text' => Yii::t('app', 'Start typing to suggest...'),
                    'style' => $value ? '' : 'color:#aaa',
                        ), $htmlOptions),
            ));
            Yii::app()->clientScript->registerScript('renderModelAutocomplete', "
                x2.forms.enableDefaultText ($('.record-name-autocomplete'));
            ", CClientScript::POS_READY);
        };

        if ($ajax) {
            X2Widget::ajaxRender($renderWidget);
        } else {
            $renderWidget();
        }
    }

    /**
     * Returns list of insertable attribute tokens which can be used for this model 
     * @param int $_depth (private) 
     * @return array of strings
     */
    private static $getInsertableAttributeTokensDepth = 0; // limits recursive depth

    public function getInsertableAttributeTokens() {
        X2Model::$getInsertableAttributeTokensDepth++;
        $tokens = array();
        if (X2Model::$getInsertableAttributeTokensDepth > 2)
            return $tokens;

        // simple tokens
        $tokens = array_merge(
                $tokens, array_map(function ($elem) {
                    return '{' . $elem . '}';
                }, $this->attributeNames()));

        // assignment tokens
        if (X2Model::$getInsertableAttributeTokensDepth < 2) {
            $assignmentFields = array_filter(
                    $this->fields, function ($elem) {
                return $elem->type === 'assignment';
            });
            foreach ($assignmentFields as $field) {
                $assignmentModel = X2Model::model('Profile');
                $tokens = array_merge(
                        $tokens, array_map(function ($elem) use ($field) {
                            return '{' . $field->fieldName . '.' . $elem . '}';
                        }, $assignmentModel->attributeNames())
                );
            }
        }

        // link tokens
        if (X2Model::$getInsertableAttributeTokensDepth < 2) {
            $linkFields = array_filter($this->fields, function ($elem) {
                return $elem->type === 'link';
            });
            foreach ($linkFields as $field) {
                $linkModelName = $field->linkType;
                $linkModel = $linkModelName::model();
                $tokens = array_merge(
                        $tokens, array_map(function ($elem) use ($field) {
                            return '{' . $field->fieldName . '.' .
                                    preg_replace('/\{|\}/', '', $elem) . '}';
                        }, $linkModel->getInsertableAttributeTokens())
                );
            }
        }

        X2Model::$getInsertableAttributeTokensDepth--;
        return $tokens;
    }

    /**
     * Finds all active records satisfying the specified condition.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @param bool $getCommand If true, command is returned instead of populating records
     * @return CActiveRecord[] list of active records satisfying the specified condition. An 
     *  empty array is returned if none is found.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function findAll(
    $condition = '', $params = array()/* x2modstart */, $getCommand = false/* x2modend */) {

        Yii::trace(get_class($this) . '.findAll()', 'system.db.ar.CActiveRecord');
        $criteria = $this->getCommandBuilder()->createCriteria($condition, $params);
        return $this->query($criteria, true/* x2modstart */, $getCommand/* x2modend */);
    }

    public function duplicateFields() {
        return isset(Yii::app()->settings->duplicateFields) ? explode(",",Yii::app()->settings->duplicateFields) : array('name');
    }

    /**
     * Performs the actual DB query and populates the AR objects with the query result.
     * This method is mainly internally used by other AR query methods.
     * @param CDbCriteria $criteria the query criteria
     * @param boolean $all whether to return all data
     * @param bool $getCommand If true, command is returned instead of populating records
     * @return mixed the AR objects populated with the query result
     * @since 1.1.7
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    protected function query(
    $criteria, $all = false/* x2modstart */, $getCommand = false/* x2modend */) {

        $this->beforeFind();
        $this->applyScopes($criteria);

        if (empty($criteria->with)) {
            if (!$all)
                $criteria->limit = 1;

            /* x2modstart */
            $command = $this->getCommandBuilder()
                    ->createFindCommand($this->getTableSchema(), $criteria, $this->getTableAlias());
            /* x2modend */

            /* x2modstart */
            if ($getCommand)
                return $command;
            /* x2modend */
            return $all ? $this->populateRecords($command->queryAll(), true, $criteria->index) :
                    $this->populateRecord($command->queryRow());
        }
        else {
            /* x2modstart */
            if ($getCommand) {
                return null;
            }
            /* x2modend */
            $finder = $this->getActiveFinder($criteria->with);
            return $finder->query($criteria, $all);
        }
    }

    /**
     * Helper method for {@link getFieldsForDropdown}
     * @param string|null $parentAttribute Can be used to prefix names of attributes
     * @param bool $condList If true, returned array's values will include, in addition to the
     *  field label, field data required by the X2ConditionList widget.
     * @param bool $sorted if true, results will be sorted by field name 
     * @param function|null $filterFn if set, will be used to filter results
     * @param string $separator used to separate parent attribute from field name 
     * @return array 
     */
    private function _getFieldsForDropdown(
    $parentAttribute = null, $condList = false, $sorted = true, $filterFn, $separator = '.') {

        $fieldModels = $this->getFields(false, $filterFn, Fields::READ_PERMISSION);
        $permissions = $this->getFieldPermissions();
        $fields = array();

        foreach ($fieldModels as &$field) {
            if ($field->isVirtual)
                continue;

            $fieldName = $field->fieldName;
            if ($this instanceof Actions && $fieldName === 'actionDescription') {
                $fieldName = 'ActionText.text';
            }
            $attributes = $field->getAttributes();
            if ($parentAttribute !== null) {
                $fieldName = $parentAttribute . $separator . $fieldName;
            }
            if ($field->type === 'date' || $field->type === 'dateTime') {
                $dateFns = array('year', 'month', 'day');
                if ($field->type === 'dateTime') {
                    $dateFns = array_merge($dateFns, array('hour', 'minute', 'second'));
                }
                foreach ($dateFns as $fn) {
                    $name = $fn . '(' . $fieldName . ')';
                    $label = $this->getAttributeLabel($fieldName) . ' (' . Yii::t('app', $fn) . ')';
                    if ($condList) {
                        $fields[] = X2ConditionList::listOption(
                                        array_merge($attributes, array(
                                    'attributeLabel' => $label,
                                    'type' => 'varchar',
                                        )), $name);
                    } else {
                        $fields[$name] = $label;
                    }
                }
            }
            if ($condList) {
                $fields[] = X2ConditionList::listOption($attributes, $fieldName);
            } else {
                $fields[$fieldName] = $this->getAttributeLabel($fieldName);
            }
        }
        if ($sorted) {
            if ($condList) {
                usort($fields, function ($a, $b) {
                    return strcasecmp($a['label'], $b['label']);
                });
            } else {
                $fields = ArrayUtil::asorti($fields);
            }
        }
        return $fields;
    }

    public function getSummaryFields() {
        $summaryFields = array();
        if (isset($this->name)) {
            $summaryFields[] = 'name';
        }
        if (isset($this->email)) {
            $summaryFields[] = 'email';
        }
        if (isset($this->phone)) {
            $summaryFields[] = 'phone';
        }
        if ($this->getField('assignedTo')) {
            $summaryFields[] = 'assignedTo';
        }
        return $summaryFields;
    }

}
