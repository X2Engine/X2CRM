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
 * This is the model class for table "x2_fields".
 *
 * @package application.models
 * @property integer $id
 * @property string $modelName
 * @property string $fieldName
 * @property string $attributeLabel
 * @property integer $show
 * @property integer $custom
 * @property string $myTableName The name of the table to which the field corresponds
 * @author Jake Houser <jake@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 */
class Fields extends CActiveRecord {

    /**
     * Defines the separator between name & ID in uniquely identifying "nameId"
     * fields
     */
    const NAMEID_DELIM = '_';

    const MULTI_ASSIGNMENT_DELIM = ', ';

    const RATING_MIN = 1;

    const RATING_MAX = 5;

    const WRITE_PERMISSION = 2;

    const READ_PERMISSION = 1;

    const NO_PERMISSION = 0;

    public $includeEmpty = true;

    private $_myTableName;

    private $_typeChanged = false;

    private static $_purifier;

    /**
     * PHP types corresponding to field types in X2Engine.
     *
     * This is to supplement Yii's active record functionality, which does not
     * typecast column values according to their canonical type.
     * @var type
     */
    public static $phpTypes = array(
        'assignment' => 'string',
        'boolean' => 'boolean',
        'credentials' => 'integer',
        'currency' => 'double',
        'date' => 'integer',
        'dateTime' => 'integer',
        'dropdown' => 'string',
        'email' => 'string',
        'int' => 'integer',
        'link' => 'string',
        'optionalAssignment' => 'string',
        'percentage' => 'double',
        'rating' => 'integer',
        'varchar' => 'string',
    );

    /**
     * Constructor override.
     */
    public function __construct($scenario = 'insert') {
        parent::__construct($scenario);
        if($scenario == 'search') {
            $this->setAttributes(
                array_fill_keys(
                    $this->attributeNames(),
                    null
                ),
                false);
        }   
    }

    public function behaviors () {
        return array_merge (parent::behaviors (), array (
            'CommonFieldsBehavior' => array (
                'class' => 'application.components.behaviors.CommonFieldsBehavior',
            )
        ));
    }

    public function getDropdownValue ($fieldValue) {
        return X2Model::model('Dropdowns')->getDropdownValue(
            $this->linkType, $fieldValue);
    }

    public function getDropdownOptions () {
        return Dropdowns::getItems($this->linkType, null, true);
    }

    public function setAttributes ($values, $safeOnly=true) {
        if (isset ($values['type']) && $this->type !== $values['type']) {
            $this->_typeChanged = true;
        }
        return parent::setAttributes ($values, $safeOnly);
    }

    /**
     * Rules for saving a field.
     *
     * See the following MySQL documentation pages for more info on length
     * restrictions and naming requirements:
     * http://dev.mysql.com/doc/refman/5.0/en/identifiers.html
     *
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('modelName, attributeLabel', 'length', 'max' => 250),
            array('fieldName','length','max'=>64), // Max length for column identifiers in MySQL
            array('fieldName','match','pattern'=>'/^[a-zA-Z]\w+$/','message'=>Yii::t('admin','Field name may only contain alphanumeric characters and underscores.')),
            array('fieldName','nonReserved'),
            array('modelName, fieldName, attributeLabel', 'required'),
            array(
                'modelName','in','range'=>array_keys(X2Model::getModelNames()),'allowEmpty'=>false),
            array('defaultValue','validDefault'),
            array('relevance','in','range'=>array_keys(self::searchRelevance())),
            array('custom, modified, readOnly, searchable, required, uniqueConstraint', 'boolean'),
            array('fieldName','uniqueFieldName'),
            array('linkType','length','max'=>250),
            array('description','length','max'=>500),
            array('type','length','max'=>20),
            array('keyType','in','range' => array('MUL','UNI','PRI','FIX','FOR'), 'allowEmpty'=>true),
            array('keyType','requiredUnique'),
            array('data','validCustom'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, modelName, fieldName, attributeLabel, custom, modified, readOnly, keyType', 'safe', 'on' => 'search'),
        );
    }
    
    /**
     * Counts the number of records such that the field is not null.
     *
     * @return integer
     */
    public function countNonNull() {
        return Yii::app()->db->createCommand()->
            select('COUNT(*)')->
            from(X2Model::model($this->modelName)->tableName())->
            where(
                 "{$this->fieldName} IS NOT NULL AND 
                {$this->fieldName} != :default AND
                {$this->fieldName} != ''",
                array(
                    ':default' => $this->defaultValue
                )
            )->queryScalar();
    }

    /**
     * Check for use of this field in dynamic list criteria
     * @return array Links to lists using this field in criteria
     */
    public function checkListCriteria() {
        $lists = Yii::app()->db->createCommand()
            ->select('l.id, l.name, c.id as criteriaId')
            ->from('x2_list_criteria c')
            ->join('x2_lists l', 'l.id = c.listId')
            ->where('c.attribute = :attr', array(':attr' => $this->fieldName))
            ->queryAll();
        if (!empty($lists)) {
            $listNames = array();
            foreach ($lists as $list) {
                $listNames[$list['criteriaId']] = CHtml::link($list['name'], array('/contacts/contacts/list?id='.$list['id']));
            }
            return $listNames;
        }
    }

    public static function getLinkTypes () {
        return Yii::app()->db->createCommand ("
            SELECT distinct(modelName)
            FROM x2_fields
            WHERE fieldName='nameId'
            ORDER by modelName ASC
        ")->queryColumn ();
    }

    /**
     * Legacy function kept for backwards compatibility.
     *
     * Moved from {@link Admin} and renamed from getModelList because it doesn't
     * make sense to put it there; it doesn't fit in a model intended for
     * managing system settings, but it does in this model, which is intended
     * for managing data models themselves.
     *
     * @return array
     */
    public static function getDisplayedModelNamesList(){
        $modelList = array();
        foreach(X2Model::model('Modules')->findAllByAttributes(array('editable' => true, 'visible' => 1)) as $module){
            if($modelName = X2Model::getModelName($module->name)){
                $modelName = $module->name;
            }else{
                $modelName = ucfirst($module->name);
            }
            if(Yii::app()->user->checkAccess(ucfirst($module->name).'Index', array())){
                $modelList[$modelName] = $module->title;
            }
        }
        return array_map(function($term){
                            return Yii::t('app', $term);
                        },$modelList);
    }
    
    /**
     * Function to return data about the field types used by X2.
     *
     * This function should be called whenever information about an x2_field type
     * is needed, as it can store all of that information in one consolidated
     * location and has a variety of uses. If no scenario is provided, it will turn
     * the array exactly as defined in $fieldTypes at the top of the function.
     * If a scenario is provided, it will attempt to fill only the information
     * for that scenario. So calling the function with "validator" will just return
     * an array of "type" => "validator" while calling it with an array of scenarios
     * (e.g. $scenario = array("title","validator")) will return an array containing
     * just that data, like: "type" => array("title", "validator").
     * @param mixed $scenario A string or array of the data scenario required
     * @return array An array of information about the field types.
     */
    public static function getFieldTypes($scenario = null){
        $fieldTypes = array(
            'varchar' => array(
                'title' => Yii::t('admin', 'Single Line Text'),
                'validator' =>'safe',
                'columnDefinition' => 'VARCHAR(255)',
                'phpType' => 'string'
            ),
            'text' => array(
                'title' => Yii::t('admin', 'Multiple Line Text Area'),
                'validator' => 'safe',
                'columnDefinition' => 'TEXT',
                'phpType' => 'string'
            ),
            'date' =>array(
                'title'=>Yii::t('admin','Date'),
                'validator'=>'int',
                'columnDefinition'=>'BIGINT',
                'phpType' => 'integer'
            ),
            'dateTime' =>array(
                'title'=>Yii::t('admin','Date/Time'),
                'validator'=>'int',
                'columnDefinition'=>'BIGINT',
                'phpType' => 'integer'
            ),
            'dropdown'=>array(
                'title'=>Yii::t('admin','Dropdown'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
                'phpType' => 'string'
            ),
            'int'=>array(
                'title'=>Yii::t('admin','Number'),
                'validator'=> 'int',
                'columnDefinition'=>'BIGINT',
                'phpType' => 'integer'
            ),
            'percentage'=>array(
                'title'=>Yii::t('admin','Percentage'),
                'validator' => 'numerical',
                'columnDefinition' => 'FLOAT',
                'phpType' => 'double'
            ),
            'email'=>array(
                'title'=>Yii::t('admin','Email'),
                'validator'=>'email',
                'columnDefinition'=>'VARCHAR(255)',
                'phpType' => 'string'
            ),
            'currency'=>array(
                'title'=>Yii::t('admin','Currency'),
                'validator'=>'numerical',
                'columnDefinition'=>'DECIMAL(18,2)',
                'phpType' => 'double'
            ),
            'url'=>array(
                'title'=>Yii::t('admin','URL'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
                'phpType' => 'string'
            ),
            'float'=>array(
                'title'=>Yii::t('admin','Decimal'),
                'validator'=>'numerical',
                'columnDefinition'=>'FLOAT',
                'phpType' => 'double'
            ),
            'boolean'=>array(
                'title'=>Yii::t('admin','Checkbox'),
                'validator'=>'boolean',
                'columnDefinition'=>'BOOLEAN NOT NULL DEFAULT 0',
                'phpType' => 'boolean'
            ),
            'link'=>array(
                'title'=>Yii::t('admin','Lookup'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
                'phpType' => 'integer'
            ),
            'rating'=>array(
                'title'=>Yii::t('admin','Rating'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
                'phpType' => 'integer'
            ),
            'assignment'=>array(
                'title'=>Yii::t('admin','Assignment'),
                'validator'=>'safe',
                'columnDefinition' => 'VARCHAR(255)',
                'phpType' => 'string'
            ),
            'visibility'=>array(
                'title'=>Yii::t('admin','Visibility'),
                'validator'=>'int',
                'columnDefinition'=>'INT NOT NULL DEFAULT 1',
                'phpType' => 'boolean'
            ),
            'timerSum'=>array(
                'title'=>Yii::t('admin','Action Timer Sum'),
                'validator'=>'safe',
                'columnDefinition'=>'INT',
                'phpType' => 'integer'
            ),
            'phone'=>array(
                'title'=>Yii::t('admin','Phone Number'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(40)',
                'phpType' => 'string'
            ),
            'custom'=>array(
                'title' => Yii::t('admin','Custom'),
                'validator' => 'safe',
                'columnDefinition' => 'VARCHAR(255)',
                'phpType' => 'string'
            ),
        );
        // No scenario, return all data
        if(empty($scenario)){
            return $fieldTypes;
        }else{
            // Scenario is a string, need to convert to array
            if(!is_array($scenario)){
                $scenario = array($scenario);
            }
            $ret = array();
            // Add the validator information to our return data
            if(in_array('validator', $scenario)){
                if(count($scenario) == 1){ // Only one scenario, can return a purely associative array
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType] = $data['validator'];
                    }
                }else{ // More than one scenario, need to return each field type as an array
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType]['validator']=$data['validator'];
                    }
                }
            }
            if(in_array('title', $scenario)){
                if(count($scenario) == 1){
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType] = $data['title'];
                    }
                }else{
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType]['title']=$data['title'];
                    }
                }
            }
            if(in_array('columnDefinition', $scenario)){
                if(count($scenario) == 1){
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType] = $data['columnDefinition'];
                    }
                }else{
                    foreach($fieldTypes as $fieldType => $data){
                        $ret[$fieldType]['columnDefinition']=$data['columnDefinition'];
                    }
                }
            }
            return $ret;
        }
    }

    /**
     * Finds a contact matching a full name; returns Contacts::name if a match was found, null otherwise.
     * @param string $type
     * @param string $name
     * @return mixed
     */
    public static function getLinkId($type, $name){
        if(strtolower($type) == 'contacts')
            $model = X2Model::model('Contacts')->find('CONCAT(firstName," ",lastName)=:name', array(':name' => $name));
        else
            $model = X2Model::model(ucfirst($type))->findByAttributes(array('name' => $name));
        if(isset($model))
            return $model->name;
        else
            return null;
    }

    /**
     * Constructs (if not constructed already) and returns a CHtmlPurifier instance
     *
     * @return CHtmlPurifier
     */
    public static function getPurifier(){
        if(!isset(self::$_purifier)){
            self::$_purifier = new CHtmlPurifier();
            // Set secure default options for HTML purification in X2Engine:
            self::$_purifier->options = array(
                'HTML.ForbiddenElements' => array(
                    'script', // Obvious reasons (XSS)
                    'form', // Hidden CSRF attempts?
                    'style', // CSS injection tomfoolery
                    'iframe', // Arbitrary location
                    'frame', // Same reason as iframe
                    'link', // Request to arbitrary location w/o user knowledge
                    'video', // No,
                    'audio', // No,
                    'object', // Definitely no.
                ),
                'HTML.ForbiddenAttributes' => array(
                    // Spoofing/mocking internal form elements:
                    '*@id',
                    '*@class',
                    '*@name',
                    // The event attributes should be removed automatically by HTMLPurifier by default
                ),
                
            );
        }
        return self::$_purifier;
    }

    /**
     * Returns the static model of the specified AR class.
     * @return Fields the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * The inverse operation of {@link nameId()}, this splits a uniquely
     * -identifying "nameId" field into name and ID.
     *
     * This function should always return an array with two elements, the first
     * being the name and the second being the ID.
     *
     * @param string $nameId The nameId reference.
     */
    public static function nameAndId($nameId) {
        // The last occurrence should be the delimeter
        $delimPos = strrpos($nameId,Fields::NAMEID_DELIM);

        if($delimPos === false) {
            // Delimeter not found
            return array($nameId,null);
        }
        
        if($delimPos >= strlen($nameId)-1) {
            // Delimeter at the end with nothing else, i.e. a name of a
            // non-existent model ending with the delimeter
            return array($nameId,null);
        }

        $id = substr($nameId,$delimPos+1);
        $name = substr($nameId,0,$delimPos);

        if(!ctype_digit($id)) {
            // Name has the delimeter in it, but does not refer to any record.
            return array($nameId,null);
        } else {
            // Name and ID acquired.
            return array($name,$id);
        }
    }

    public static function id ($nameId) {
        list ($name, $id) = self::nameAndId ($nameId);
        return $id;
    }

    /**
     * Generates a combination name and id field to uniquely identify the record.
     */
    public static function nameId($name,$id) {
        return $name.self::NAMEID_DELIM.$id;
    }

    /**
     * Implodes an array of usernames for multiple assignment fields. This method,
     * if still used anyhwere, could be refactored to use JSON
     * @param mixed $arr Array or string of usernames
     * @return string A properly formatted assignment string
     */
    public static function parseUsers($arr){
        /* filters out dummy option in multiselect element used to separate usernames from group 
           names */
		$str="";
        if(is_array($arr)){
            $arr = array_filter ($arr, function ($a) { return $a !== ''; });
            $str=implode(', ',$arr);
        } else if(is_string($arr))
            $str = $arr;
		return $str;
	}

    /**
     * Similar to {@link Fields::parseUsers} but is used in the case where it's an associative
     * array of username => full name and the array keys need to be used to generate
     * our assignment string
     * @param array $arr An array of format username => full name
     * @return string A properly formatted assignment string
     */
	public static function parseUsersTwo($arr){
		$str="";
		if(is_array($arr)){
            $arr=array_keys($arr);
            $str=implode(', ',$arr);
        }

		return $str;
	}

    public static function searchRelevance() {
        return array('Low' => Yii::t('app', 'Low'), "Medium" => Yii::t('app', "Medium"), "High" => Yii::t('app', "High"));
    }
    
    /**
     * Converts a string into a numeric value.
     *
     * @param string $input The string to convert
     * @param string $type A hint as to the type of input; one of 'int', 'float', 'currency' or 'percentage'
     * @param string $currencySymbol Optional currency symbol to trim off the string before conversion
     * @param string $percentSymbol Optional percent symbol to trim off the string before conversion
     */
    public static function strToNumeric($input, $type = 'float', $curSym = null){
        $sign = 1;
        // Typecasting in the case that it's not a string
        $inType = gettype($input);
        if($inType != 'string'){
            if($type == $inType)
                return $inType;
            else
                return ($type == 'int' ? (int) $input : (float) $input);
        }

        // Get rid of leading and trailing whitespace:
        $value = trim($input);
        if(strpos($value, '(') === 0) // Parentheses notation
            $sign = -1;
        $posNeg = strpos($value, '-');
        if($posNeg === 0 || $posNeg === strlen($value) - 1) // Minus sign notation
            $sign = -1;

        // Strip out currency/percent symbols and digit group separators, but exclude null currency symbols:
        if(!function_exists('stripSymbols')){
            function stripSymbols($s){ return !empty($s); }
        }
        $stripSymbols = array_filter(array_values(Yii::app()->params->supportedCurrencySymbols), 'stripSymbols');

        // Strip specified currency symbol
        if (!is_null($curSym))
            $stripSymbols[] = $curSym;
        // Just in case "Other" currency used: include that currency's symbol
        $defaultSym = Yii::app()->getLocale()->getCurrencySymbol(Yii::app()->settings->currency);
        if($defaultSym)
            if(!in_array($defaultSym, $stripSymbols))
                $stripSymbols[] = $defaultSym;
        $stripSymbols[] = '%';
        $grpSym = Yii::app()->getLocale()->getNumberSymbol('group');
        if(!empty($grpSym) && $type != 'percentage')
            $stripSymbols[] = $grpSym;
        $value = strtr($value, array_fill_keys($stripSymbols, ''));

        // Trim away negative symbols and any remaining whitespace:
        $value = trim($value, "-() ");
        $converted = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
        $value = trim($converted, chr(0xC2).chr(0xA0));

        /* 
        Setting numeric field to '' fails in MYSQL strict mode and gets coerced to 0 in non-strict 
        mode. null gets used instead to allow empty number field values.
        */
        if($value === null || $value === '') 
            return null; 
        else if(!in_array($type, array('int', 'currency', 'float', 'percentage')))
            return $value; // Unrecognized type
        else if(!preg_match('/^([\d\.,]+)e?[\+\-]?\d*$/', $value))
            return $input; // Unreadable input

        $value = str_replace(Yii::app()->getLocale()->getNumberSymbol('decimal'), '.', $value);
        if(in_array($type, array('float', 'currency', 'percentage'))){
            return ((float) $value) * $sign;
        }else if($type == 'int'){
            return ((int) $value) * $sign;
        } else
            return $value;
    }

    /**
     * Table modification is performed before field update since field should not be saved if
     * column data type cannot be updated
     * TODO: place column modification in a transaction with field update
     */
    public function beforeSave () {
        $valid = parent::beforeSave ();
        if ($valid && $this->_typeChanged) {
            $table = Yii::app()->db->schema->tables[$this->myTableName];
            $existing = array_key_exists($this->fieldName, $table->columns) && 
                $table->columns[$this->fieldName] instanceof CDbColumnSchema;
            if($existing){ 
                $valid = $this->modifyColumn();
            }
        }
        return $valid;
    }
    
    /**
     * Perform the creation of a new database column.
     *
     * The extra work in this method is skipped over in the "newModule" scenario
     * because the database schema altering commands to set up columns are
     * performed separately in that case.
     *
     * @return type
     */
    public function afterSave(){
        // Does the column already exist?
        $table = Yii::app()->db->schema->tables[$this->myTableName];
        $existing = array_key_exists($this->fieldName, $table->columns) && 
            $table->columns[$this->fieldName] instanceof CDbColumnSchema;

        if(!$existing){ // Going to create the column.
            $this->createColumn();
        } 
        if($this->keyType != 'PRI' && $this->keyType != 'FIX'){
            // The key for this column is not primary/hard-coded (managed by
            // X2Engine developers, and cannot be user-modified), so it can
            // be allowed to change.
            if($this->keyType != null){
                $this->dropIndex();
                $this->createIndex($this->keyType === 'UNI');
            }else{
                $this->dropIndex();
            }
        }
        if ($this->isNewRecord) {
            // A new fields permissions default to read/write for all roles
            $dataProvider = new CActiveDataProvider('Roles');
            foreach ($dataProvider->getData() as $role) {
                $permission = new RoleToPermission();
                $permission->roleId = $role->id;
                $permission->fieldId = $this->id;
                $permission->permission = 2;
                $permission->save();
            }
        }
        
        return parent::afterSave();
    }

    public function afterDelete() {
        $this->dropColumn();
        return parent::afterDelete();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin', 'ID'),
            'modelName' => Yii::t('admin', 'Model Name'),
            'fieldName' => Yii::t('admin', 'Field Name'),
            'attributeLabel' => Yii::t('admin', 'Attribute Label'),
            'custom' => Yii::t('admin', 'Custom'),
            'modified' => Yii::t('admin', 'Modified'),
            'readOnly' => Yii::t('admin', 'Read Only'),
            'required' => Yii::t('admin', "Required"),
            'searchable' => Yii::t('admin', "Searchable"),
            'relevance' => Yii::t('admin', 'Search Relevance'),
            'uniqueConstraint' => Yii::t('admin', 'Unique'),
            'defaultValue' => Yii::t('admin', 'Default Value'),
            'keyType' => Yii::t('admin','Key Type'),
            'data' => Yii::t('admin','Template'),
        );
    }

    /**
     * Adjust the field name accordingly
     *
     * Under certain circumstances, the field name will be given a prefix "c_"
     * to avoid name collisions with new default fields added in updates.
     *
     * @return bool
     */
    public function beforeValidate() {
        if($this->isNewRecord){
            if(strpos($this->fieldName,'c_') !== 0 && $this->custom && $this->scenario != 'test'){
                // This is a safeguard against fields that end up having
                // identical names to fields added later in updates.
                $this->fieldName = "c_{$this->fieldName}";
            }
        }
        return parent::beforeValidate();
    }

    /**
     * Creates a column for the new field model.
     */
    public function createColumn(){
        // Get the column definition.
        $fieldType = $this->type;
        $columnDefinitions = Fields::getFieldTypes('columnDefinition');
        if(isset($columnDefinitions[$fieldType])){
            $fieldType = $columnDefinitions[$fieldType];
        }else{
            $fieldType = 'VARCHAR(250)';
        }
        $sql = "ALTER TABLE `{$this->myTableName}` ADD COLUMN `{$this->fieldName}` $fieldType";
        try{
            Yii::app()->db->createCommand($sql)->execute();
        }catch(CDbException $e){
            $this->delete(); // If the SQL failed, remove the x2_fields record of it to prevent issues.
        }
    }

    /**
     * Modifies the data type of an existing column 
     */
    public function modifyColumn () {
        // Get the column definition.
        $fieldType = $this->type;
        $columnDefinitions = Fields::getFieldTypes('columnDefinition');

        if(isset($columnDefinitions[$fieldType])){
            $fieldType = $columnDefinitions[$fieldType];
        }else{
            $fieldType = 'VARCHAR(250)';
        }

        //Yii::app()->db->createCommand('set sql_mode=STRICT_ALL_TABLES;')->execute();
        $sql = "ALTER TABLE `{$this->myTableName}` MODIFY COLUMN `{$this->fieldName}` $fieldType";
        try{
            Yii::app()->db->createCommand($sql)->execute();
        }catch(CDbException $e){
            $this->addError ('type', $e->getMessage ());
            return false;
        }
        return true;
    }

    /**
     * Creates an index on the column associated with the current field record.
     */
    public function createIndex($unique = false){
        $indexType = $unique ? "UNIQUE" : "INDEX";
        $sql = "ALTER TABLE `{$this->myTableName}` ADD $indexType(`{$this->fieldName}`)";
        try{
            Yii::app()->db->createCommand($sql)->execute();
            return true;
        }catch(CDbException $e){
            // Fail quietly until there's a need to take additional action after
            // this happens
            return false;
        }
    }

    /**
     * Deletes the table column associated with the field record.
     */
    public function dropColumn(){
        $sql = "ALTER TABLE `{$this->myTableName}` DROP COLUMN `{$this->fieldName}`";
        try{
            Yii::app()->db->createCommand($sql)->execute();
            return true;
        }catch(CDbException $e){
            // Fail quietly until there's a need to take additional action after
            // this happens
            return false;
        }
    }

    /**
     * Drops the index on the column associated with the current field record.
     */
    public function dropIndex(){
        $sql = "ALTER TABLE `{$this->myTableName}` DROP INDEX `{$this->fieldName}`";
        try{
            Yii::app()->db->createCommand($sql)->execute();
            return true;
        }catch(CDbException $e){
            // Fail quietly until there's a need to take additional action after
            // this happens
            return false;
        }
    }

    /**
     * Obtains the value for {@link tableName}
     * @return type
     */
    public function getMyTableName() {
        if(!isset($this->_myTableName)) {
            $this->_myTableName = X2Model::model($this->modelName)->tableName();
        }
        return $this->_myTableName;
    }

    /**
     * Validator for ensuring an identifier does not include MySQL reserved words
     * or X2Engine reserved words
     */
    public function nonReserved($attribute,$params = array()) {
        if($this->isNewRecord){
            $dataFiles = array();
            $reservedWords = array();
            $dataFiles[] = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', 'mysqlReservedWords.php'));
            $dataFiles[] = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', 'modelReservedWords.php'));
            foreach($dataFiles as $path){
                if(file_exists($path)){
                    $reservedWords = array_merge($reservedWords, require($path));
                }
            }
            if(in_array($this->$attribute, $reservedWords)){
                $this->addError($attribute, Yii::t('admin', 'This field is a MySQL or X2Engine reserved word.  Choose a different field name.'));
            }
        }
    }

    /**
     * Parses a value for table insertion using X2Fields rules
     * @param mixed $value
     * @param bool $filter If true, replace HTML special characters (prevents markup injection)
     * @return mixed the parsed value
     */
    public function parseValue($value, $filter = false){
        if(in_array($this->type, array('int', 'float', 'currency', 'percentage'))){
            return self::strToNumeric($value, $this->type);
        }
        switch($this->type){
            case 'assignment':
                return ($this->linkType === 'multiple') ? self::parseUsers($value) : $value;

            case 'date':
            case 'dateTime':
                if(is_numeric ((string) $value))  // must already be a timestamp
                    return $value;
                $value = $this->type === 'dateTime' ? Formatter::parseDateTime($value) : Formatter::parseDate($value);
                return $value === false ? null : $value;

            case 'link':
                if(empty($value) || empty($this->linkType)){
                    return $value;
                }
                list($name, $id) = self::nameAndId($value);
                if(ctype_digit((string) $id)){
                    // Already formatted as a proper reference. Check for existence of the record.
                    $linkedModel = X2Model::model($this->linkType)->findByAttributes(array('nameId' => $value));
                    // Return the plain text name if the link is broken; otherwise,
                    // given how the record exists, return the value.
                    return empty($linkedModel) ? $name : $value;
                }else if(ctype_digit($value)){
                    // User manually entered the ID, i.e. in an API call
                    $link = Yii::app()->db->createCommand()
                            ->select('nameId')
                            ->from(X2Model::model($this->linkType)->tableName())
                            ->where('id=?', array($value))
                            ->queryScalar();
                }else{
                    // Look up model's unique nameId by its name:
                    $link = Yii::app()->db->createCommand()
                            ->select('nameId')
                            ->from(X2Model::model($this->linkType)->tableName())
                            ->where('name=?', array($name))
                            ->queryScalar();
                }
                return $link === false ? $name : $link;
            case 'boolean':
                return (bool) $value;
            case 'text':
                return self::getPurifier()->purify($value);
            case 'dropdown':
                return is_array($value) ? CJSON::encode($value) : $value;
            default:
                return $filter ? CHtml::encode($value) : $value;
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations(){
        return array();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search(){
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('modelName', $this->modelName, true);
        $criteria->compare('fieldName', $this->fieldName, true);
        $criteria->compare('attributeLabel', $this->attributeLabel, true);
        $criteria->compare('custom', $this->custom);
        $criteria->compare('modified', $this->modified);
        $criteria->compare('readOnly', $this->readOnly);

        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_fields';
    }

    /**
     * Validator that prevents adding a unique constraint to a field without
     * also making it required.
     */
    public function requiredUnique($attribute, $params = array()) {
        if($this->$attribute == 'UNI' && !$this->uniqueConstraint) {
            $this->addError($attribute,Yii::t('admin','You cannot add a unique constraint unless you also make the field unique and required.'));
        }
    }

    /**
     * Check that the combination of model and field name will not conflict
     * with any existing one.
     *
     * @param type $attribute
     * @param type $params
     */
    public function uniqueFieldName($attribute, $params = array()) {
        $fields = self::model()->findAllByAttributes(array($attribute=>$this->$attribute,'modelName'=>$this->modelName));
        if(count($fields) > 0) {
            // There can and should only be one.
            $existingField = reset($fields);
            if($this->id != $existingField->id) {
                // This is not the field! Saving will produce a database
                // cardinality violation error due to the unique constraint on
                // model name and field name.
                $this->addError($attribute,Yii::t('admin','A field in the specified data model with that name already exists.'));
            }
        }
    }

    /**
     * Check that the default value is appropriate given the type of the field.
     * 
     * @param string $attribute
     * @param array $params
     */
    public function validDefault($attribute,$params = array()) {
        if($this->fieldName == '')
            return; // Nothing is possible without the field name. Validation will fail for it accordingly.

        // Use the amorphous model for "proxy" validation, and use a "dummy"
        // field model (because we'll need to set the name differently to make
        // things easier on ourselves, given how user input for field name might
        // not be appropriate for a property name)
        $dummyModel = new AmorphousModel();
        $dummyField = new Fields;
        foreach($this->attributes as $name=>$value) {
            $dummyField->$name = $value;
        }
        $dummyField->fieldName = 'customized_field';
        $dummyModel->scenario = 'insert';
        $dummyModel->addField($dummyField,'customized_field');
        $dummyModel->setAttribute('customized_field',$this->$attribute);
        $dummyModel->validate();
        if($dummyModel->hasErrors('customized_field')) {
            foreach($dummyModel->errors['customized_field'] as $error) {
                $this->addError($attribute, str_replace($dummyField->attributeLabel, $dummyField->getAttributeLabel($attribute), $error));
            }
        }
    }


    /**
     * Alter/purify the input for the custom data field.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validCustom($attribute,$params = array()) {
        if($this->type == 'custom') {
            if($this->linkType == 'formula') {
                $this->$attribute = trim($this->$attribute);
                if(strpos($this->$attribute,'=')!==0) {
                    $this->$attribute = '='.$this->$attribute;
                }
            } else if($this->linkType == 'display') {
               $this->$attribute = self::getPurifier()->purify($this->$attribute);
            }
        }
    }

    /**
     * Retrieve associated dropdown, if it exists
     * @return null|Dropdowns
     */
    private $_dropdown;
    public function getDropdown () {
        if ($this->type !== 'dropdown') return null;
        if (!isset ($this->_dropdown)) {
            $this->_dropdown = Dropdowns::model ()->findByPk ($this->linkType);
        }
        return $this->_dropdown;
    }

    public static function getFieldsOfModelsWithFieldLevelPermissions () {
        $fields = Fields::model()
            ->findAll(array('order' => 'modelName ASC'));
        $filtered = array ();
        foreach ($fields as $field) {
            $modelClass = $field->modelName;
            if (class_exists ($modelClass) && 
                $modelClass::model ()->supportsFieldLevelPermissions) { 

                $filtered[] = $field;
            }
        }
        return $filtered;
    }

}
