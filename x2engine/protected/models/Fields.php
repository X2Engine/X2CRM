<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * This is the model class for table "x2_fields".
 *
 * @package X2CRM.models
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

    private $_myTableName;

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
            ),
            'text' => array(
                'title' => Yii::t('admin', 'Multiple Line Text Area'),
                'validator' => 'safe',
                'columnDefinition' => 'TEXT',
            ),
            'date' =>array(
                'title'=>Yii::t('admin','Date'),
                'validator'=>'int',
                'columnDefinition'=>'BIGINT',
            ),
            'dateTime' =>array(
                'title'=>Yii::t('admin','Date/Time'),
                'validator'=>'int',
                'columnDefinition'=>'BIGINT',
            ),
            'dropdown'=>array(
                'title'=>Yii::t('admin','Dropdown'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'int'=>array(
                'title'=>Yii::t('admin','Number'),
                'validator'=> 'int',
                'columnDefinition'=>'BIGINT',
            ),
            'email'=>array(
                'title'=>Yii::t('admin','Email'),
                'validator'=>'email',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'currency'=>array(
                'title'=>Yii::t('admin','Currency'),
                'validator'=>'numerical',
                'columnDefinition'=>'DECIMAL(18,2)',
            ),
            'url'=>array(
                'title'=>Yii::t('admin','URL'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'float'=>array(
                'title'=>Yii::t('admin','Decimal'),
                'validator'=>'numerical',
                'columnDefinition'=>'FLOAT'
            ),
            'boolean'=>array(
                'title'=>Yii::t('admin','Checkbox'),
                'validator'=>'boolean',
                'columnDefinition'=>'BOOLEAN NOT NULL DEFAULT 0',
            ),
            'link'=>array(
                'title'=>Yii::t('admin','Lookup'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'rating'=>array(
                'title'=>Yii::t('admin','Rating'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'assignment'=>array(
                'title'=>Yii::t('admin','Assignment'),
                'validator'=>'safe',
                'columnDefinition'=>'VARCHAR(255)',
            ),
            'visibility'=>array(
                'title'=>Yii::t('admin','Visibility'),
                'validator'=>'int',
                'columnDefinition'=>'INT',
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
     * @return type
     */
    public static function getModelNames() {
        $modelList = array();
        foreach(X2Model::model('Modules')->findAllByAttributes(array('editable' => true, 'visible' => 1)) as $module) {
            if($modelName = X2Model::getModelName($module->name))
                $modelList[$modelName] = Yii::t('app',$module->title);
            else // Custom module most likely
                $modelList[ucfirst($module->name)] = Yii::t('app',$module->title);
        }
        return $modelList;
    }

    /**
     * Returns the static model of the specified AR class.
     * @return Fields the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * Implodes an array of usernames for multiple assignment fields. This method,
     * if still used anyhwere, could be refactored to use JSON
     * @param mixed $arr Array or string of usernames
     * @return string A properly formatted assignment string
     */
    public static function parseUsers($arr){
		$str="";
        if(is_array($arr)){
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
    public static function strToNumeric($input, $type = 'float'){
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

        // Just in case "Other" currency used: include that currency's symbol
        $defaultSym = Yii::app()->getLocale()->getCurrencySymbol(Yii::app()->params->admin->currency);
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

        // Turn null string into zero:
        if($value === null || $value === '')
            return ($type != 'int') ? 0.0 : 0;
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
        $existing = array_key_exists($this->fieldName, $table->columns) && $table->columns[$this->fieldName] instanceof CDbColumnSchema;

        if(!$existing){ // Going to create the column.
            $this->createColumn();
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
            'defaultValue' => Yii::t('admin', 'Default Value')
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
     * Deletes the table column associated with the field record.
     */
    public function dropColumn() {
        $sql = "ALTER TABLE `{$this->myTableName}` DROP COLUMN `{$this->fieldName}`";
        try {
            Yii::app()->db->createCommand($sql)->execute();
        } catch (CDbException $e) {

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
     * or X2CRM reserved words
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
                $this->addError($attribute, Yii::t('admin', 'This field is a MySQL or X2CRM reserved word.  Choose a different field name.'));
            }
        }
    }

    /**
     * Parses a value for table insertion using X2Fields rules
     * @param mixed $value
     * @param bool $filter If true, replace HTML special characters (prevents markup injection)
     * @return mixed the parsed value
     */
    public function parseValue($value,$filter = false){
        if(in_array($this->type, array('int', 'float', 'currency', 'percentage'))){
            return self::strToNumeric($value, $this->type);
        }
        switch($this->type){
            case 'assignment':
                return ($this->linkType === 'multiple') ? self::parseUsers($value) : $value;

            case 'date':
            case 'dateTime':
                if(ctype_digit((string) $value))  // must already be a timestamp
                    return $value;
                $value = $this->type === 'dateTime' ? Formatter::parseDateTime($value) : Formatter::parseDate($value);
                return $value === false ? null : $value;

            case 'link':
                if(empty($value) || empty($this->linkType) || is_int($value)) // if it's empty, then whatever; if it's already numeric, assume it's valid
                    return $value;
                $linkId = Yii::app()->db->createCommand()
                        ->select('id')
                        ->from(X2Model::model($this->linkType)->tableName())
                        ->where('name=?', array($value))
                        ->queryScalar();
                return $linkId === false ? $value : $linkId;
            case 'boolean':
                return (bool) $value;
	    case 'dropdown':
	       return is_array($value)?CJSON::encode($value):$value;
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
            array('modelName','in','range'=>array_keys(self::getModelNames()),'allowEmpty'=>false),
            array('defaultValue','validDefault'),
            array('relevance','in','range'=>array_keys(self::searchRelevance())),
            array('custom, modified, readOnly, searchable, required, uniqueConstraint', 'boolean'),
            array('fieldName','uniqueFieldName'),
            array('linkType','length','max'=>250),
            array('type','length','max'=>20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, modelName, fieldName, attributeLabel, custom, modified, readOnly', 'safe', 'on' => 'search'),
        );
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

}
