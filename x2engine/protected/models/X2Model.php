<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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


Yii::import('application.components.X2LinkableBehavior');
Yii::import('application.components.X2ChangeLogBehavior');
Yii::import('application.components.X2TimestampBehavior');
Yii::import('application.components.TagBehavior');
Yii::import('application.modules.users.models.*');

/**
 * General model class that uses dynamic fields
 *
 * @property string $myModelName (read-only) Model name of the instance.
 * @property array $relatedX2Models (read-only) Models associated via the associations table
 * @package X2CRM.models
 */
abstract class X2Model extends CActiveRecord {

    protected $_oldAttributes = array();

    /**
     * List of mapping between module names/associationType values and model class names
     */
    public static $associationModels = array(
        'actions' => 'Actions',
        'calendar' => 'X2Calendar',
        'contacts' => 'Contacts',
        'accounts' => 'Accounts',
        'product' => 'Product',
        'products' => 'Product',
        'Campaign' => 'Campaign',
        'marketing' => 'Campaign',
        'quote' => 'Quote',
        'quotes' => 'Quote',
        'opportunities' => 'Opportunity',
        'social' => 'Social',
        'services' => 'Services',
        '' => ''
    );
    protected static $_fields;    // one copy of fields for all instances of this model
    protected static $_linkedModels; // cache for models loaded for link field attributes (used by automation system)
    protected $_runAfterCreate;   // run afterCreate before afterSave, but only for new records
    private $_relatedX2Models;   // Relationship models stored in here

    /**
     * Calls {@link queryFields()} before CActiveRecord::__constructo() is called
     */

    public function __construct($scenario = 'insert'){
        $this->queryFields();

        parent::__construct($scenario);
    }

    public static function model($className = 'CActiveRecord'){
        $modelName = self::getModelName($className);
        if(class_exists($modelName)){
            return parent::model($modelName);
        }else{
            throw new CHttpException(500, 'Class: '.$className." not found.");
        }
    }

    public static function getModelName($type){
        if(array_key_exists(strtolower($type), X2Model::$associationModels)){
            return X2Model::$associationModels[strtolower($type)];
        }else{
            if(class_exists(ucfirst($type))){
                return ucfirst($type);
            }elseif(class_exists($type)){
                return $type;
            }else{
                return false;
            }
        }
    }

    /**
     * Magic getter for {@link myModelName}
     * @return string
     */
    public function getMyModelName(){
        return self::getModelName(get_class($this));
    }

    /**
     * Magic getter for {@link relatedX2Models}
     * @return array
     */
    public function getRelatedX2Models(){
        if(!isset($this->_relatedX2Models)){
            $myModelName = get_class($this);
            $this->_relatedX2Models = array();
            $relationships = Relationships::model()->findAllBySql("SELECT * FROM x2_relationships WHERE (firstType=\"{$myModelName}\" AND firstId=\"{$this->id}\") OR (secondType=\"{$myModelName}\" AND secondId=\"{$this->id}\")");
            $modelRelationships = array();
            foreach($relationships as $relationship){
                list($idAttr, $typeAttr) = ($relationship->firstId == $this->id && $relationship->firstType == $myModelName) ? array('secondId', 'secondType') : array('firstId', 'firstType');
                if(!array_key_exists($relationship->$typeAttr, $modelRelationships))
                    $modelRelationships[$relationship->$typeAttr] = array();
                if(!empty($relationship->$idAttr))
                    $modelRelationships[$relationship->$typeAttr][] = $relationship->$idAttr;
            }
            foreach($modelRelationships as $modelName => $ids)
                $this->_relatedX2Models = array_merge($this->_relatedX2Models, X2Model::model($modelName)->findAllByPk($ids));
        }
        return $this->_relatedX2Models;
    }

    /**
     * Queries and caches Fields objects for the model.
     *
     * This method obtains the fields defined for the model in
     * <tt>x2_fields</tt> and makes them avaialble for later usage to ensure
     * that the query does not need to be performed again. The vields are stored
     * as both static attributes of the model and and as Yii cache objects.
     */
    protected function queryFields(){
        $key = $this->tableName();

        if(!isset(self::$_fields[$key])){ // only look up fields if they haven't already been looked up
            self::$_fields[$key] = Yii::app()->cache->get('fields_'.$key); // check the app cache for the data
            if(self::$_fields[$key] === false){ // if the cache is empty, look up the fields
                $fieldList = CActiveRecord::model('Fields')->findAllByAttributes(array('modelName' => get_class($this), 'isVirtual' => 0));
                if(!empty($fieldList)){
                    self::$_fields[$key] = $fieldList;
                    Yii::app()->cache->set('fields_'.$key, self::$_fields[$key], 0); // cache the data
                }else{
                    self::$_fields[$key] = $this->attributeLabels();
                }
            }
        }
    }

    public function relations(){
        $relations = array();
        foreach(self::$_fields[$this->tableName()] as &$_field){
            if($_field->type === 'link')
                $relations[$_field->fieldName.'Model'] = array(self::BELONGS_TO, $_field->linkType, $_field->fieldName);
        }
        if(Yii::app()->params->edition == 'pro'){
            $relations['gallery'] = array(self::HAS_ONE, 'GalleryToModel', 'modelId', 'condition' => 'modelName="'.get_class($this).'"');
        }
        return $relations;
    }

    /**
     * Returns a list of behaviors that this model should behave as.
     * @return array the behavior configurations (behavior name=>behavior configuration)
     */
    public function behaviors(){
        $behaviors = array(
            'X2LinkableBehavior' => array('class' => 'X2LinkableBehavior'),
            'X2TimestampBehavior' => array('class' => 'X2TimestampBehavior'),
            'tags' => array('class' => 'TagBehavior'),
            'changelog' => array('class' => 'X2ChangeLogBehavior'),
            'permissions' => array('class' => 'X2PermissionsBehavior'),
        );
        if(Yii::app()->params->edition == 'pro'){
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
    public function afterFind(){
        $this->_oldAttributes = $this->getAttributes();
        parent::afterFind();
    }

    /**
     * Remembers if this was a new record before saving.
     * @returns the answer from {@link CActiveRecord::beforeSave()}
     */
    public function beforeSave(){
        $this->_runAfterCreate = $this->getIsNewRecord();
        return parent::beforeSave();
    }

    public function onAfterCreate($event){
        $this->raiseEvent('onAfterCreate', $event);
    }

    public function afterCreate(){
        $this->_runAfterCreate = false;

        if($this->hasEventHandler('onAfterCreate'))
            $this->onAfterCreate(new CEvent($this));
    }

    public function onAfterUpdate($event){
        $this->raiseEvent('onAfterUpdate', $event);
    }

    public function afterUpdate(){
        if($this->hasEventHandler('onAfterUpdate'))
            $this->onAfterUpdate(new CEvent($this));
    }

    /**
     * Runs when a model is deleted.
     * Clears any entries in <tt>x2_phone_numbers</tt>.
     * Fires onAfterDelete event.
     */
    public function afterDelete(){
        X2Model::model('PhoneNumber')->deleteAllByAttributes(array('modelId' => $this->id, 'modelType' => get_class($this))); // clear out old phone numbers

        if($this->hasEventHandler('onAfterDelete'))
            $this->onAfterDelete(new CEvent($this));
    }

    /**
     * Runs when a model is saved.
     * Scans attributes for phone numbers and index them in <tt>x2_phone_numbers</tt>.
     * Updates <tt>x2_relationships</tt> table based on link type fields.
     * Fires onAfterSave event.
     */
    public function afterSave(){

        if($this->_runAfterCreate)
            $this->afterCreate();
        else
            $this->afterUpdate();

        $phoneFields = array();
        $linkFields = array();

        // look through fields for phone numbers and relationships
        foreach(self::$_fields[$this->tableName()] as &$_field){
            if($_field->type === 'phone'){
                $phoneFields[$_field->fieldName] = $this->getAttribute($_field->fieldName);
            }elseif($_field->type === 'link'){
                $linkFields[$_field->fieldName] = array(
                    'id' => $this->getAttribute($_field->fieldName),
                    'type' => $_field->linkType
                );
            }
        }

        // deal with phone numbers
        if(count($phoneFields))
            X2Model::model('PhoneNumber')->deleteAllByAttributes(array('modelId' => $this->id, 'modelType' => get_class($this))); // clear out old phone numbers

        foreach($phoneFields as $field => &$number){  // create new entries in x2_phone_numbers
            if(!empty($number)){
                $num = new PhoneNumber;
                $num->number = preg_replace('/\D/', '', $number); // eliminate everything other than digits
                $num->modelId = $this->id;
                $num->modelType = get_class($this);
                $num->fieldName = $field;
                $num->save();
            }
        }

        /////////////// deal with relationships ///////////////
        $oldAttributes = $this->getOldAttributes();

        $relationSql = '(firstType=:type1 AND firstId=:id1 AND secondType=:type2 AND secondId=:id2) OR
						 (firstType=:type2 AND firstId=:id2 AND secondType=:type1 AND secondId=:id1)';

        foreach($linkFields as $fieldName => &$relation){
            if(isset($oldAttributes[$fieldName]))
                $oldLinkId = $oldAttributes[$fieldName];
            else
                $oldLinkId = null;

            if($relation['id'] == $oldLinkId) // skip field if it hasn't changed
                continue;

            // forget old relationship (wouldn't it be nice...)
            if(!empty($oldLinkId)){
                CActiveRecord::model('Relationships')->deleteAll($relationSql, array(
                    ':type1' => get_class($this),
                    ':id1' => $this->id,
                    ':type2' => $relation['type'],
                    ':id2' => $oldLinkId
                ));
            }
            // save new relationship
            if(!empty($relation['id']) && ctype_digit((string) $relation['id'])){
                if(!CActiveRecord::model('Relationships')->exists($relationSql, array(// check if there's already a relationship between these here thingies
                            ':type1' => get_class($this),
                            ':id1' => $this->id,
                            ':type2' => $relation['type'],
                            ':id2' => $relation['id']
                        ))){
                    $rel = new Relationships;
                    $rel->firstType = get_class($this);
                    $rel->secondType = $relation['type'];
                    $rel->firstId = $this->id;
                    $rel->secondId = $relation['id'];

                    $rel->save();
                }
            }
        }

        parent::afterSave(); // raise onAfterSave event for behaviors, such as X2ChangeLogBehavior
    }

    /**
     * Generates validation rules for custom fields
     * @return array validation rules for model attributes.
     */
    public function rules(){
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
            'link'
        );
        $fieldRules = array_fill_keys($fieldTypes, array());
        $validators = Fields::getFieldTypes('validator');

        foreach(self::$_fields[$this->tableName()] as &$_field){

            $fieldRules['search'][] = $_field->fieldName;
            if(isset($validators[$_field->type]) && $_field->safe){
                $fieldRules[$validators[$_field->type]][] = $_field->fieldName;
            }

            if($_field->required){
                $fieldRules['required'][] = $_field->fieldName;
            }
            if($_field->uniqueConstraint){
                $fieldRules['unique'][] = $_field->fieldName;
            }

            if($_field->type == 'link' && $_field->required)
                $fieldRules['link'][] = $_field->fieldName;
        }

        return array(
            array(implode(',', $fieldRules['required']), 'required', 'on' => 'insert'),
            array(implode(',', $fieldRules['unique']), 'unique'),
            array(implode(',', $fieldRules['numerical']), 'numerical'),
            array(implode(',', $fieldRules['email']), 'email'),
            array(implode(',', $fieldRules['int']), 'numerical', 'integerOnly' => true),
            array(implode(',', $fieldRules['boolean']), 'boolean'),
            array(implode(',', $fieldRules['link']), 'validLink'),
            array(implode(',', $fieldRules['safe']), 'safe'),
            array(implode(',', $fieldRules['search']), 'safe', 'on' => 'search')
        );
    }

    /**
     * Returns the named attribute value.
     * Recognizes linked attributes and looks them up with {@link getLinkedAttribute()}
     * @param string $name the attribute name
     * @return mixed the attribute value. Null if the attribute is not set or does not exist.
     * @see hasAttribute
     */
    public function getAttribute($name, $renderFlag = false){
        $nameParts = explode('.', $name); // check for a linked attribute (eg. "account.assignedTo")
        if(count($nameParts) > 1){ // We have a complicated link like "account.primaryContact.email"
            $linkField = array_shift($nameParts); // Remove the current model
            $linkModel = $this->getLinkedModel($linkField);
            $name = implode('.', $nameParts); // Put the name back together e.g. primaryContact.email
            if(isset($linkModel)){
                return $linkModel->getAttribute($name, $renderFlag);
            }else{
                $fieldInfo = $this->getField($linkField); // If it's an assignment field, check the Profile model
                if($fieldInfo instanceof Fields && $fieldInfo->type == 'assignment'){
                    $profRecord = X2Model::model('Profile')->findByAttributes(array('username' => $this->$linkField));
                    if(isset($profRecord)){
                        return $profRecord->getAttribute($name,false);
                    }
                }
            }
        }else{
            if($renderFlag){
                return $this->renderAttribute($name);
            }else{
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
    public function getLinkedAttribute($linkField, $attribute){
        if(null !== $model = $this->getLinkedModel($linkField))
            return $model->getAttribute($attribute);
        return null;
    }

    /**
     * Looks up a linked attribute by loading the linked model and calling renderAttribute() on it.
     * @param string $linkField the attribute of $this linking to the external model
     * @param string $attribute the attribute of the external model
     * @return mixed the properly formatted attribute value. Null if the attribute is not set or does not exist.
     * @see getLinkedAttribute
     */
    public function renderLinkedAttribute($linkField, $attribute){
        if(null !== $model = $this->getLinkedModel($linkField))
            return $model->renderAttribute($attribute);
        return null;
    }

    /**
     * Looks up an external model referenced in a link field.
     * Caches loaded models in X2Model::$_linkedModels
     * @param string $linkField the attribute of $this linking to the external model
     * @return mixed the active record. Null if the attribute is not set or does not exist.
     */
    public function getLinkedModel($linkField){
        $id = $this->getAttribute($linkField);

        if(ctype_digit((string) $id)){
            $field = $this->getField($linkField);

            if($field !== null && $field->type === 'link'){
                $modelClass = $field->linkType;

                // try to look up the linked model
                if(!isset(self::$_linkedModels[$modelClass][$id])){
                    self::$_linkedModels[$modelClass][$id] = X2Model::model($modelClass)->findByPk($id);
                    if(self::$_linkedModels[$modelClass][$id] === null)  // if it doesn't exist, set it to false in the cache
                        self::$_linkedModels[$modelClass][$id] = false;  // so isset() returns false and we can skip this next time
                }

                if(self::$_linkedModels[$modelClass][$id] !== false)
                    return self::$_linkedModels[$modelClass][$id];  // success!
            }
        }
        return null;
    }

    /**
     * Wrapper method for generating a link to the view for a model record.
     *
     * @param int $id the route to this model's AutoComplete data source
     * @param string $class the model class
     * @return string a link to the model, or $id if the model is invalid
     */
    public static function getModelLink($id, $class, $requireAbsoluteUrl = false){
        $model = X2Model::model($class)->findByPk($id);
        if(isset($model) && !is_null($model->asa('X2LinkableBehavior'))){
            if(isset(Yii::app()->controller) && method_exists(Yii::app()->controller, 'checkPermissions')){
                if(Yii::app()->controller->checkPermissions($model, 'view')){
                    if($requireAbsoluteUrl){
                        return $model->getUrlLink();
                    }else{
                        return $model->getLink();
                    }
                }else{
                    return $model->name;
                }
            }else{
                if($requireAbsoluteUrl){
                    return $model->getUrlLink();
                }else{
                    return $model->getLink();
                }
            }
            // return CHtml::link($model->name,array($model->getDefaultRoute().'/'.$model->id));
        }elseif(is_numeric($id)){
            return '';
        }else{
            return $id;
        }
    }

    /**
     * Returns all possible models, either as a regular array or associative
     * (key and value are the same)
     * @param boolean $assoc
     * @return array
     */
    public static function getModelTypes($assoc = false){
        $modelTypes = Yii::app()->db->createCommand()
                ->selectDistinct('modelName')
                ->from('x2_fields')
                ->where('modelName!="Calendar"')
                ->order('modelName ASC')
                ->queryColumn();

        if($assoc === true){
            return array_combine($modelTypes, array_map(function($term){
                                        return Yii::t('app', $term);
                                    }, $modelTypes));
        }
        $modelTypes = array_map(function($term){
                    return Yii::t('app', $term);
                }, $modelTypes);
        return $modelTypes;
    }

    /**
     * Returns custom attribute values defined in x2_fields
     * @return array customized attribute labels (name=>label)
     * @see generateAttributeLabel
     */
    public function attributeLabels(){
        $labels = array();

        foreach(self::$_fields[$this->tableName()] as &$_field){
            if(get_class($this) == "Opportunity"){
                $labels[$_field->fieldName] = Yii::t('opportunities', $_field->attributeLabel);
            }elseif(get_class($this) == "Quote"){
                $labels[$_field->fieldName] = Yii::t('quotes', $_field->attributeLabel);
            }elseif(get_class($this) == "Product"){
                $labels[$_field->fieldName] = Yii::t('products', $_field->attributeLabel);
            }else{
                $labels[$_field->fieldName] = Yii::t(strtolower(get_class($this)), $_field->attributeLabel);
            }
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
    public function getAttributeLabel($attribute){
        foreach(self::$_fields[$this->tableName()] as &$_field){ // don't call attributeLabels(), just look in self::$_fields
            if(isset($_field->fieldName)){
                if($_field->fieldName == $attribute){
                    if(get_class($this) == "Opportunity"){
                        return Yii::t('opportunities', $_field->attributeLabel);
                    }elseif(get_class($this) == "Quote"){
                        return Yii::t('quotes', $_field->attributeLabel);
                    }elseif(get_class($this) == "Product"){
                        return Yii::t('products', $_field->attributeLabel);
                    }else{
                        return Yii::t(strtolower(get_class($this)), $_field->attributeLabel);
                    }
                }
            }else{
                break;
            }
        }
        if(isset(self::$_fields[$this->tableName()][$attribute])){
            return self::$_fields[$this->tableName()][$attribute];
        }
        // original Yii code
        if(strpos($attribute, '.') !== false){
            $segs = explode('.', $attribute);
            $name = array_pop($segs);
            $model = $this;
            foreach($segs as $seg){
                $relations = $model->getMetaData()->relations;
                if(isset($relations[$seg]))
                    $model = X2Model::model($relations[$seg]->className);
                else
                    break;
            }
            return $model->getAttributeLabel($name);
        } else
            return $this->generateAttributeLabel($attribute);
    }

    public function getOldAttributes(){
        return $this->_oldAttributes;
    }

    public function getFields($assoc = false){
        if($assoc){
            $fields = array();
            foreach(self::$_fields[$this->tableName()] as &$field)
                $fields[$field->fieldName] = $field;
            return $fields;
        }else{
            return self::$_fields[$this->tableName()];
        }
    }

    public function getField($fieldName){
        foreach(self::$_fields[$this->tableName()] as &$_field){
            if($_field->fieldName == $fieldName)
                return $_field;
        }
        return null;
    }

    /**
     * Renders an attribute of the model based on its field type
     * @param string $fieldName the name of the attribute to be rendered
     * @param boolean $makeLinks whether to create HTML links for certain field types
     * @param boolean $textOnly whether to generate HTML or plain text
     * @return string the HTML or text for the formatted attribute
     */
    public function renderAttribute($fieldName, $makeLinks = true, $textOnly = true){

        $field = $this->getField($fieldName);
        if(!isset($field))
            return null;

        /**
         * Skip variable replacement in cases that a full session and request
         * aren't available:
         */
        if(Yii::app()->params->noSession){
            $webRequestAttributes = array(
                'rating', // Uses a Yii widget, which requires access to the controller
                'assignment', // Depends on getUserLinks, which depends on the user session
                'optionalAssignment', // Same as above
                'url', // Renders an actual link
                'text', // Uses convertUrls, which is in x2base
            );
            if(in_array($field->type, $webRequestAttributes))
                return $this->$fieldName;
        }

        switch($field->type){
            case 'date':
                if(empty($this->$fieldName))
                    return ' ';
                elseif(is_numeric($this->$fieldName))
                    return Formatter::formatLongDate($this->$fieldName);
                else
                    return $this->$fieldName;
            case 'dateTime':
                if(empty($this->$fieldName))
                    return ' ';
                elseif(is_numeric($this->$fieldName))
                    return Formatter::formatCompleteDate($this->$fieldName);
                else
                    return $this->$fieldName;

            case 'rating':
                if($textOnly){
                    return $this->$fieldName;
                }else{
                    return Yii::app()->controller->widget('CStarRating', array(
                                'model' => $this,
                                'name' => str_replace(' ', '-', get_class($this).'-'.$this->id.'-rating-'.$field->fieldName),
                                'attribute' => $field->fieldName,
                                'readOnly' => true,
                                'minRating' => 1, //minimal valuez
                                'maxRating' => 5, //max value
                                'starCount' => 5, //number of stars
                                'cssFile' => Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css',
                                    ), true);
                }

            case 'assignment':
                return User::getUserLinks($this->$fieldName, $makeLinks);

            case 'optionalAssignment':
                if($this->$fieldName == '')
                    return '';
                else
                    return User::getUserLinks($this->$fieldName);

            case 'visibility':
                switch($this->$fieldName){
                    case '1':
                        return Yii::t('app', 'Public');
                        break;
                    case '0':
                        return Yii::t('app', 'Private');
                        break;
                    case '2':
                        return Yii::t('app', 'User\'s Groups');
                        break;
                    default:
                        return '';
                }

            case 'email':
                if(empty($this->$fieldName)){
                    return '';
                }else{
                    $mailtoLabel = (isset($this->name) && !is_numeric($this->name)) ? '"'.$this->name.'" <'.$this->$fieldName.'>' : $this->$fieldName;
                    return $makeLinks ? CHtml::mailto($this->$fieldName, $mailtoLabel) : $this->$fieldName;
                }

            case 'phone':
                if(empty($this->$fieldName)){
                    return '';
                }else{
                    $phoneCheck = PhoneNumber::model()->findByAttributes(array('modelId' => $this->id, 'modelType' => get_class($this), 'fieldName' => $fieldName));
                    if(isset($phoneCheck) && strlen($phoneCheck->number) == 10){
                        $temp = $phoneCheck->number;
                        $this->$fieldName = "(".substr($temp, 0, 3).") ".substr($temp, 3, 3)."-".substr($temp, 6, 4);
                        $profile = Yii::app()->params->profile;
                        if ($makeLinks && !$profile->disablePhoneLinks) {
                            return '<a href="tel:+1'.$phoneCheck->number.'">'.$this->$fieldName.'</a>';
                        }
                    }
                    return $this->$fieldName;
                }

            case 'url':
                if(!$makeLinks)
                    return $this->$fieldName;

                if(empty($this->$fieldName)){
                    $text = '';
                }elseif(!empty($field->linkType)){
                    switch($field->linkType){
                        case 'skype':
                            $text = '<a href="callto:'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
                            break;
                        case 'googleplus':
                            $text = '<a href="http://plus.google.com/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
                            break;
                        case 'twitter':
                            $text = '<a href="http://www.twitter.com/#!/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
                            break;
                        case 'linkedin':
                            $text = '<a href="http://www.linkedin.com/in/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
                            break;
                        default:
                            $text = '<a href="http://www.'.$field->linkType.'.com/'.$this->$fieldName.'">'.$this->$fieldName.'</a>';
                    }
                }else{
                    $text = trim(preg_replace(
                                    array(
                                '/<a([^>]*)target="?[^"\']+"?/i',
                                '/<a([^>]+)>/i',
                                    ), array(
                                '<a\\1 target="_blank"',
                                '<a\\1 target="_blank">',
                                    ), $this->$fieldName
                            ));
                    $oldText = $text;
                    if(!function_exists('linkReplaceCallback')){

                        function linkReplaceCallback($matches){
                            return stripslashes((strlen($matches[2]) > 0 ? '<a href=\"'.$matches[2].'\" target=\"_blank\">'.$matches[0].'</a>' : $matches[0]));
                        }

                    }

                    $text = trim(preg_replace_callback(
                                    array(
                                '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+))/ix',
                                    ), 'linkReplaceCallback', $this->$fieldName
                            ));
                    if($text == trim($oldText)){
                        if(!function_exists('linkReplaceCallback2')){

                            function linkReplaceCallback2($matches){
                                return stripslashes((strlen($matches[2]) > 0 ? '<a href=\"http://'.$matches[2].'\" target=\"_blank\">'.$matches[0].'</a>' : $matches[0]));
                            }

                        }

                        $text = trim(preg_replace_callback(
                                        array(
                                    '/(^|\s|>)(www.[^<> \n\r]+)/ix',
                                        ), 'linkReplaceCallback2', $this->$fieldName
                                ));
                    }
                }
                return $text;

            case 'link':
                $linkedModel = $this->getLinkedModel($fieldName);
                if($linkedModel === null)
                    return $this->$fieldName;
                else
                    return $makeLinks ? $linkedModel->getLink() : $linkedModel->name;
            case 'boolean':
                return $textOnly ? $this->$fieldName : CHtml::checkbox('', $this->$fieldName, array('onclick' => 'return false;', 'onkeydown' => 'return false;'));

            case 'currency':
                if($this instanceof Product) // products have their own currency
                    return Yii::app()->locale->numberFormatter->formatCurrency($this->$fieldName, $this->currency);
                else
                    return empty($this->$fieldName) ? "&nbsp;" : Yii::app()->locale->numberFormatter->formatCurrency($this->$fieldName, Yii::app()->params['currency']);

            case 'percentage':
                return $this->$fieldName !== null && $this->$fieldName !== '' ? (string) ($this->$fieldName)."%" : null;

            case 'dropdown':
                return X2Model::model('Dropdowns')->getDropdownValue($field->linkType, $this->$fieldName);

            case 'parentCase':
                return Yii::t(strtolower(Yii::app()->controller->id), $this->$fieldName);

            case 'text':
                return Yii::app()->controller->convertUrls($this->$fieldName);

            case 'credentials':
                $sysleg = Yii::t('app', 'System default (legacy)');
                if($this->$fieldName == -1){
                    return $sysleg;
                }else{
                    $creds = Credentials::model()->findByPk($this->$fieldName);
                    if(!empty($creds))
                        return CHtml::encode($creds->name);
                    else
                        return $sysleg;
                }

            default:
                return $this->$fieldName;
        }
    }

    public static function getPhoneNumber($field, $class, $id){
        $phoneCheck = CActiveRecord::model('PhoneNumber')->findByAttributes(array('modelId' => $id, 'modelType' => $class, 'fieldName' => $field));
        if(isset($phoneCheck) && strlen($phoneCheck->number) == 10){
            $temp = $phoneCheck->number;
            return "(".substr($temp, 0, 3).") ".substr($temp, 3, 3)."-".substr($temp, 6, 4);
        }else{
            $record = X2Model::model($class)->findByPk($id);
            if(isset($record))
                return $record->$field;
        }
    }

    /**
     * Renders an attribute of the model based on its field type
     * @param string $fieldName the name of the attribute to be rendered
     * @param array $htmlOptions htmlOptions to be used on the input
     * @return string the HTML or text for the formatted attribute
     */
    public function renderInput($fieldName, $htmlOptions = array()){

        $field = $this->getField($fieldName);
        if(!isset($field))
            return null;
        switch($field->type){
            case 'text':
                return CHtml::activeTextArea($this, $field->fieldName, array_merge(array(
                                    'title' => $field->attributeLabel,
                                        ), $htmlOptions));
            // array(
            // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
            // 'disabled'=>$item['readOnly']? 'disabled' : null,
            // 'title'=>$field->attributeLabel,
            // 'style'=>$default?'color:#aaa;':null,
            // ));

            case 'date':
                $this->$fieldName = Formatter::formatDate($this->$fieldName, 'short');
                Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                return Yii::app()->controller->widget('CJuiDateTimePicker', array(
                            'model' => $this, //Model object
                            'attribute' => $fieldName, //attribute name
                            'mode' => 'date', //use "time","date" or "datetime" (default)
                            'options' => array(// jquery options
                                'dateFormat' => Formatter::formatDatePicker(),
                                'changeMonth' => true,
                                'changeYear' => true,
                            ),
                            'htmlOptions' => array_merge(array(
                                'title' => $field->attributeLabel,
                                    ), $htmlOptions),
                            'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                                ), true);
            case 'dateTime':
                $this->$fieldName = Formatter::formatDateTime($this->$fieldName, 'short');
                Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                return Yii::app()->controller->widget('CJuiDateTimePicker', array(
                            'model' => $this, //Model object
                            'attribute' => $fieldName, //attribute name
                            'mode' => 'datetime', //use "time","date" or "datetime" (default)
                            'options' => array(// jquery options
                                'dateFormat' => Formatter::formatDatePicker('medium'),
                                'timeFormat' => Formatter::formatTimePicker(),
                                'ampm' => Formatter::formatAMPM(),
                                'changeMonth' => true,
                                'changeYear' => true,
                            ),
                            'htmlOptions' => array_merge(array(
                                'title' => $field->attributeLabel,
                                    ), $htmlOptions),
                            'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                                ), true);
            case 'dropdown':
                $om = Dropdowns::getItems($field->linkType, null, true); // Note: if desired to translate dropdown options, change the seecond argument to $this->module
                $multi = (bool) $om['multi'];
                $dropdowns = $om['options'];
                $curVal = $multi ? CJSON::decode($this->{$field->fieldName}) : $this->{$field->fieldName};

                $dependencyCount = X2Model::model('Dropdowns')->countByAttributes(array('parent' => $field->linkType));
                $fieldDependencyCount = X2Model::model('Fields')->countByAttributes(array('modelName' => $field->modelName, 'type' => 'dependentDropdown', 'linkType' => $field->linkType));
                if($dependencyCount > 0 && $fieldDependencyCount > 0){
                    $ajaxArray = array('ajax' => array(
                            'type' => 'GET', //request type
                            'url' => Yii::app()->controller->createUrl('/site/dynamicDropdown'),
                            'data' => 'js:{"val":$(this).val(),"dropdownId":"'.$field->linkType.'", "field":true, "module":"'.$field->modelName.'"}',
                            'success' => 'function(data){
                                            if(data){
                                                data=JSON.parse(data);
                                                if(data[0] && data[1]){
                                                    $("#'.$field->modelName.'_"+data[0]).html(data[1]);
                                                }
                                            }
                                        }',
                            ));
                }else{
                    $ajaxArray = array();
                }
                $htmlOptions = array_merge($htmlOptions, $ajaxArray, array('title' => $field->attributeLabel));
                if($multi){
                    $multiSelectOptions = array();
                    if(!is_array($curVal))
                        $curVal = array();
                    foreach($curVal as $option)
                        $multiSelectOptions[$option] = array('selected' => 'selected');
                    $htmlOptions = array_merge($htmlOptions, array('options' => $multiSelectOptions, 'multiple' => 'multiple'));
                } else{
                    $htmlOptions = array_merge($htmlOptions, array('empty' => Yii::t('app', "Select an option")));
                }
                return CHtml::activeDropDownList($this, $field->fieldName, $dropdowns, $htmlOptions);

            case 'dependentDropdown':
                return CHtml::activeDropDownList($this, $field->fieldName, array('' => '-'), array_merge(
                                        array(
                                    'title' => $field->attributeLabel,
                                        ), $htmlOptions
                                ));
            case 'parentCase':
                $caseIds = Yii::app()->db->createCommand()->select('id')->from('x2_services')->queryAll();
                $cases = array();

                foreach($caseIds as $c){
                    $cases[$c['id']] = $c['id'];
                }
                unset($cases[$model->id]);

                return CHtml::activeDropDownList($this, $field->fieldName, $cases, array_merge(
                                        array(
                                    'title' => $field->attributeLabel,
                                    'empty' => Yii::t('app', ""),
                                        ), $htmlOptions
                                ));

            case 'link':
                $linkSource = null;
                $linkId = '';

                if(class_exists($field->linkType)){
                    // if the field is an ID, look up the actual name
                    if(isset($this->$fieldName) && ctype_digit((string) $this->$fieldName)){
                        $linkModel = X2Model::model($field->linkType)->findByPk($this->$fieldName);
                        if(isset($linkModel)){
                            $this->$fieldName = $linkModel->name;
                            $linkId = $linkModel->id;
                        }else{
                            $this->$fieldName = '';
                        }
                    }
                    $staticLinkModel = X2Model::model($field->linkType);

                    if(array_key_exists('X2LinkableBehavior', $staticLinkModel->behaviors()))
                        $linkSource = Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource);

                    /* $count = $staticLinkModel->count();
                      if($count <= 50) {
                      $names = array(''=>'');
                      $data =	Yii::app()->db->createCommand()
                      ->select('id,name')
                      ->from($staticLinkModel->tableName())
                      ->order('name ASC')
                      ->queryAll();

                      foreach($data as $row)
                      $names[$row['id']] = $row['name'];
                      return CHtml::dropDownList($field->modelName.'['.$fieldName.']',$linkId,$names);
                      } */
                }

                return CHtml::hiddenField($field->modelName.'['.$fieldName.'_id]', $linkId, array('id' => $field->modelName.'_'.$fieldName."_id"))
                        .Yii::app()->controller->widget('zii.widgets.jui.CJuiAutoComplete', array(
                            'model' => $this,
                            'attribute' => $fieldName,
                            // 'name'=>'autoselect_'.$fieldName,
                            'source' => $linkSource,
                            'value' => $this->$fieldName,
                            'options' => array(
                                'minLength' => '1',
                                'select' => 'js:function( event, ui ) {
								$("#'.$field->modelName.'_'.$fieldName.'_id").val(ui.item.id);
								$(this).val(ui.item.value);
								return false;
							}',
                                'create' => $field->linkType == 'Contacts' ? 'js:function(event, ui) {
									$(this).data( "uiAutocomplete" )._renderItem = function(ul,item) {
										return $("<li>").data("item.autocomplete",item).append(renderContactLookup(item)).appendTo(ul);
									};
								}' : ($field->linkType == 'BugReports' ? 'js:function(event, ui) {
									$(this).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
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

            case $field->type == 'rating':
                return Yii::app()->controller->widget('CStarRating', array(
                            'model' => $this,
                            'attribute' => $field->fieldName,
                            'readOnly' => isset($htmlOptions['disabled']) && $htmlOptions['disabled'],
                            'minRating' => 1, //minimal value
                            'maxRating' => 5, //max value
                            'starCount' => 5, //number of stars
                            'cssFile' => Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css',
                            'htmlOptions' => $htmlOptions
                                ), true);

            case 'boolean':
                return '<div class="checkboxWrapper">'
                        .CHtml::activeCheckBox($this, $field->fieldName, array_merge(array(
                                    'unchecked' => 0,
                                    'title' => $field->attributeLabel,
                                        ), $htmlOptions)).'</div>';

            case 'assignment':
                $this->$fieldName = !empty($this->$fieldName) ? $this->$fieldName : Yii::app()->user->getName();
                return CHtml::activeDropDownList($this, $fieldName, X2Model::getAssignmentOptions(true, true), array_merge(array(
                                    // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
                                    // 'disabled'=>$item['readOnly']? 'disabled' : null,
                                    'title' => $field->attributeLabel,
                                    'id' => $field->modelName.'_'.$fieldName.'_assignedToDropdown',
                                    'multiple' => ($field->linkType == 'multiple' ? 'multiple' : null),
                                        ), $htmlOptions));
            /*
              $group = is_numeric($this->$fieldName);
              // if(is_numeric($this->assignedTo)){
              // $group=true;
              // $groups=Groups::getNames();
              // }else{
              // $group=false;
              // }
              if (is_array($this[$fieldName]))
              $this[$fieldName] = implode(', ', $this[$fieldName]);

              if (empty($this->$fieldName))
              $this->$fieldName = Yii::app()->user->getName();
              return CHtml::activeDropDownList($this, $fieldName, $group ? Groups::getNames() : User::getNames(), array_merge(array(
              // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
              // 'disabled'=>$item['readOnly']? 'disabled' : null,
              'title' => $field->attributeLabel,
              'id' => $field->modelName . '_' . $fieldName . '_assignedToDropdown',
              'multiple' => ($field->linkType == 'multiple' ? 'multiple' : null),
              ), $htmlOptions))
              /* x2temp
              . '<div class="checkboxWrapper">'
              . CHtml::checkBox('group', $group, array_merge(array(
              // array(
              // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
              // 'disabled'=>$item['readOnly']? 'disabled' : null,
              'title' => $field->attributeLabel,
              'id' => $field->modelName . '_' . $fieldName . '_groupCheckbox',
              'ajax' => array(
              'type' => 'POST', //request type
              'url' => Yii::app()->controller->createUrl('/groups/getGroups'), //url to call.
              'update' => '#' . $field->modelName . '_' . $fieldName . '_assignedToDropdown', //selector to update
              'data' => 'js:{checked: $(this).attr("checked")=="checked", field:"' . $this->$fieldName . '"}',
              'complete' => 'function(){
              if($("#' . $field->modelName . '_' . $fieldName . '_groupCheckbox").attr("checked")!="checked"){
              $("#' . $field->modelName . '_' . $fieldName . '_groupCheckbox").attr("checked","checked");
              $("#' . $field->modelName . '_' . $fieldName . '_visibility option[value=\'2\']").remove();
              }else{
              $("#' . $field->modelName . '_' . $fieldName . '_groupCheckbox").removeAttr("checked");
              $("#' . $field->modelName . '_' . $fieldName . '_visibility").append(
              $("<option></option>").val("2").html("User\'s Groups")
              );
              }
              }')
              ), array_merge($htmlOptions, array('style' => 'margin-left:10px;'))))
              . '<label for="group" class="groupLabel">' . Yii::t('app', 'Group?') . '</label></div>';
              /* end x2temp

              // case 'association':
              // if($field->linkType!='multiple') {
              // return CHtml::activeDropDownList($this, $fieldName, $contacts,array_merge(array(
              // 'title'=>$field->attributeLabel,
              // ),$htmlOptions));
              // } else {
              // return CHtml::activeListBox($this, $fieldName, $contacts,array_merge(array(
              // 'title'=>$field->attributeLabel,
              // 'multiple'=>'multiple',
              // ),$htmlOptions));
              // }
             */
            case 'optionalAssignment': // optional assignment for users (can be left blank)

                $users = User::getNames();
                unset($users['Anyone']);

                return CHtml::activeDropDownList($this, $fieldName, $users, array_merge(array(
                                    // 'tabindex'=>isset($item['tabindex'])? $item['tabindex'] : null,
                                    // 'disabled'=>$item['readOnly']? 'disabled' : null,
                                    'title' => $field->attributeLabel,
                                    'empty' => '',
                                        ), $htmlOptions));

            case 'visibility':
                return CHtml::activeDropDownList($this, $field->fieldName, array(1 => Yii::t('app', 'Public'), 0 => Yii::t('app', 'Private'), 2 => Yii::t('app', 'User\'s Groups')), array_merge(array(
                                    'title' => $field->attributeLabel,
                                    'id' => $field->modelName."_visibility",
                                        ), $htmlOptions));

            // 'varchar', 'email', 'url', 'int', 'float', 'currency', 'phone'
            // case 'int':
            // return CHtml::activeNumberField($this, $field->fieldNamearray_merge(array(
            // 'title' => $field->attributeLabel,
            // ), $htmlOptions));

            case 'percentage':
                $htmlOptions['class'] = empty($htmlOptions['class']) ? 'input-percentage' : $htmlOptions['class'].' input-percentage';
                return CHtml::activeTextField($this, $field->fieldName, array_merge(array(
                                    'title' => $field->attributeLabel,
                                        ), $htmlOptions));

            case 'currency':
                Yii::app()->controller->widget('application.extensions.moneymask.MMask', array(
                    'element' => '#'.$field->modelName.'_'.$field->fieldName,
                    'currency' => Yii::app()->params['currency'],
                    'config' => array(
                        'showSymbol' => true,
                        'symbolStay' => true,
                        'decimal' => Yii::app()->locale->getNumberSymbol('decimal'),
                        'thousands' => Yii::app()->locale->getNumberSymbol('group'),
                    )
                ));
                return CHtml::activeTextField($this, $field->fieldName, array_merge(array(
                                    'title' => $field->attributeLabel,
                                    'class' => 'currency-field',
                                        ), $htmlOptions));
            case 'credentials':
                $typeAlias = explode(':', $field->linkType);
                $type = $typeAlias[0];
                if(count($typeAlias) > 1){
                    $uid = Credentials::$sysUseId[$typeAlias[1]];
                }else{
                    $uid = Yii::app()->user->id;
                }
                return Credentials::selectorField($this, $field->fieldName, $type, $uid);

            default:
                return CHtml::activeTextField($this, $field->fieldName, array_merge(array(
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

    /**
     * Sets attributes using X2Fields
     * @param array &$data array of attributes to be set (eg. $_POST['Contacts'])
     * @param bool $filter encode all HTML special characters in input
     */
    public function setX2Fields(&$data, $filter = false){
        $editableFieldsFieldNames = $this->getEditableFieldNames ();

        // loop through fields to deal with special types
        foreach(self::$_fields[$this->tableName()] as &$field){
            $fieldName = $field->fieldName;

            // skip fields that are read-only or haven't been set
            if($field->readOnly || !isset($data[$fieldName]) ||
               !in_array ($fieldName, $editableFieldsFieldNames)) {

                if (isset ($data[$fieldName]) &&
                    !in_array ($fieldName, $editableFieldsFieldNames)) {

                    if (YII_DEBUG) printR ('setX2Fields: Warning: '.$fieldName.' not set');
                }
                continue;
            }

            // eliminate placeholder values
            if($data[$fieldName] == $this->getAttributeLabel($fieldName))
                $data[$fieldName] = null;

            if($field->type === 'link'){
                $linkId = null;
                if(isset($data[$fieldName.'_id'])) {
                    // get the linked model's ID from the hidden autocomplete field
                    $linkId = $data[$fieldName.'_id'];
                }

                if(ctype_digit((string) $linkId)){
                    $linkName = Yii::app()->db->createCommand()
                            ->select('name')
                            ->from(X2Model::model($field->linkType)->tableName())
                            ->where('id=?', array($linkId))
                            ->queryScalar();
                    // make sure the linked model exists and that the name matches
                    if($linkName === $data[$fieldName]) {
                        // (ie, the hidden ID field isn't junk data)
                        $data[$fieldName] = (int) $linkId;
                    }
                }
            }

            $this->$fieldName = $field->parseValue($data[$fieldName], $filter);
        }
    }

    /**
     * Base search function, includes Retrieves a list of models based on the current search/filter conditions.
     * @param CDbCriteria $criteria the attribute name
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchBase($criteria){
        if($criteria === null)
            $criteria = $this->getAccessCriteria();
        else
            $criteria->mergeWith($this->getAccessCriteria());
        $this->compareAttributes($criteria);
        $with = array();
        foreach(self::$_fields[$this->tableName()] as &$field){
            if($field->type == 'link'){
                $with[] = $field->fieldName.'Model';
            }
        }
        $criteria->with = $with;
        $sort = new CSort(get_class($this));
        $sort->multiSort = false;
        $sort->attributes = $this->getSort();
        $sort->defaultOrder = 't.lastUpdated DESC, t.id DESC';
        $sort->sortVar = get_class($this)."_sort";
        $dataProvider = new SmartDataProvider(get_class($this), array(
                    'sort' => $sort,
                    'pagination' => array(
                        'pageSize' => !Yii::app()->user->isGuest ? ProfileChild::getResultsPerPage() : 20,
                    ),
                    'criteria' => $criteria,
                ));
        $sort->applyOrder($criteria);
        return $dataProvider;
    }

    public function getSort(){
        $attributes = array();
        foreach(self::$_fields[$this->tableName()] as &$field){
            $fieldName = $field->fieldName;
            switch($field->type){
                // Temporary until we can find a better way to do this.
//                case 'link':
//                    $linkType = $field->linkType;
//                    if(class_exists(ucfirst($linkType)) && X2Model::model(ucfirst($linkType))->hasAttribute('name')){
//                        $attributes[$fieldName] = array(
//                            'asc' => 'IF(t.'.$field->fieldName.' REGEXP "^-?[0-9]+$",'.$field->fieldName.'Model.name, t.'.$fieldName.') ASC',
//                            'desc' => 'IF(t.'.$field->fieldName.' REGEXP "^-?[0-9]+$",'.$field->fieldName.'Model.name, t.'.$fieldName.') DESC',
//                        );
//                    }else{
//                        $attributes[$fieldName] = array(
//                            'asc' => 't.'.$fieldName.' ASC',
//                            'desc' => 't.'.$fieldName.' DESC',
//                        );
//                    }
//                    break;
                default:
                    $attributes[$fieldName] = array(
                        'asc' => 't.'.$fieldName.' ASC',
                        'desc' => 't.'.$fieldName.' DESC',
                    );
            }
        }
        return $attributes;
    }

    public function compareAttributes(&$criteria){
        foreach(self::$_fields[$this->tableName()] as &$field){
            $fieldName = $field->fieldName;
            switch($field->type){
                case 'boolean':
                    $criteria->compare('t.'.$fieldName, $this->compareBoolean($this->$fieldName), true);
                    break;
                case 'link':
                    $criteria->compare('t.'.$fieldName, $this->compareLookup($field->linkType, $this->$fieldName), true);
                    $criteria->compare('t.'.$fieldName, $this->$fieldName, true, 'OR');
                    break;
                case 'assignment':
                    $criteria->compare('t.'.$fieldName, $this->compareAssignment($this->$fieldName), true);
                    break;
                case 'dropdown':
                    $criteria->compare('t.'.$fieldName, $this->compareDropdown($field->linkType, $this->$fieldName), true);
                    break;
                case 'phone':
                // $criteria->join .= ' RIGHT JOIN x2_phone_numbers ON (x2_phone_numbers.itemId=t.id AND x2_tags.type="Contacts" AND ('.$tagConditions.'))';
                default:
                    $criteria->compare('t.'.$fieldName, $this->$fieldName, true);
            }
        }
    }

    protected function compareLookup($linkType, $value){
        if(is_null($value) || $value == '')
            return null;

        $linkType = ucfirst($linkType);

        if(class_exists($linkType)){
            $class = new $linkType;
            $tableName = $class->tableName();

            if($linkType == 'Contacts')
                $linkIds = Yii::app()->db->createCommand()->select('id')->from($tableName)->where(array('like', 'CONCAT(firstName," ",lastName)', "%$value%"))->queryColumn();
            else
                $linkIds = Yii::app()->db->createCommand()->select('id')->from($tableName)->where(array('like', 'name', "%$value%"))->queryColumn();

            return empty($linkIds) ? -1 : $linkIds;
        }
        return -1;
    }

    protected function compareBoolean($data){
        if(is_null($data) || $data == '')
            return null;

        return in_array(mb_strtolower(trim($data)), array(0, 'f', 'false', Yii::t('actions', 'No')), true) ? 0 : 1;  // default to true unless recognized as false
    }

    protected function compareAssignment($data){
        if(is_null($data) || $data == '')
            return null;
        $userNames = Yii::app()->db->createCommand()->select('username')->from('x2_users')->where(array('like', 'CONCAT(firstName," ",lastName)', "%$data%"))->queryColumn();
        $groupIds = Yii::app()->db->createCommand()->select('id')->from('x2_groups')->where(array('like', 'name', "%$data%"))->queryColumn();

        return (count($groupIds) + count($userNames) == 0) ? -1 : $userNames + $groupIds;
    }

    protected function compareDropdown($ddId, $value){
        if(is_null($value) || $value == '')
            return null;
        $dropdown = X2Model::model('Dropdowns')->findByPk($ddId);
        if(isset($dropdown)){
            if(!is_null($dropdown->getDropdownIndex($ddId, $value))){
                return $dropdown->getDropdownIndex($ddId, $value);
            }else{
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
    /* 	public static function load($modelName,$id) {
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
    public static function getAssociationModel($type, $id){
        if($id != 0 && $modelName = X2Model::getModelName($type))
            return X2Model::model($modelName)->findByPk($id);
        else
            return null;
    }

    /**
     * Picks the primary key attribute out of an associative aray and finds the record
     * @param array $params
     * @return type
     */
    public function findByPkInArray(array $params){
        $pkc = $this->tableSchema->primaryKey;
        $pk = null;
        if(is_array($pkc)){ // Composite primary key
            $pk = array();
            foreach($pkc as $colName){
                if(array_key_exists($colName, $params))
                    $pk[$colName] = $params[$colName];
                else // Primary key column missing
                    return null;
            }
        } elseif(array_key_exists($pkc, $params)){ // Single-column primary key
            $pk = $params[$pkc];
        }else{ // Can't do anything; primary key not found in array.
            return null;
        }
        return $this->findByPk($pk);
    }

    public static function getAssignmentOptions($anyone = true, $showGroups = true){
        $users = User::getNames();
        if($anyone !== true)
            unset($users['Anyone']);

        if($showGroups === true){
            $groups = Groups::getNames();
            if(count($groups) > 0)
                $users = $users + array('' => '--------------------') + $groups;
        }
        return $users;
    }

    /**
     * Validator ensuring that what the user entered refers to a valid, existing record
     */
    public function validLink($attr, $params){
        if(!is_numeric($this->$attr)){
            $this->addError($attr, Yii::t('app', '{attr} does not refer to any existing record', array('{attr}' => $this->getAttributeLabel($attr))));
        }
    }

    /**
     * Returns an array of field names that the user has permission to edit
     * @param boolean if false, get attribute labels as well as field names
     * @return mixed if $suppressAttributeLabels is true, an array of field names is returned,
     *    otherwise an associative array is returned (fieldName => attributeLabel)
     */
    public function getEditableFieldNames ($suppressAttributeLabels=true) {
		if(!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
            $editableFieldsFieldNames = Yii::app()->db->createCommand()
                ->select('x2_fields.fieldName'.
                    ($suppressAttributeLabels ? '' : ', x2_fields.attributeLabel'))
                ->from('x2_role_to_permission')
                ->join('x2_fields','x2_role_to_permission.permission=2 AND x2_fields.readOnly!=1 '.
                    'AND x2_fields.modelName="'.
                    get_class ($this).'" AND x2_fields.id=fieldId AND roleId IN ('.
                        implode(',',Yii::app()->params->roles).')')
                ->queryAll();
        } else {
            $editableFieldsFieldNames = Yii::app()->db->createCommand()
                ->select('fieldName'.
                    ($suppressAttributeLabels ? '' : ', attributeLabel'))
                ->from('x2_fields')
                ->where ('readOnly!=1 AND modelName="'.get_class ($this).'"')
                ->queryAll();
        }

        if (!$suppressAttributeLabels) {
            $editableFieldsFieldNamesTmp = array ();
            foreach ($editableFieldsFieldNames as $fieldInfo) {
                $editableFieldsFieldNamesTmp[$fieldInfo['fieldName']] =
                    $fieldInfo['attributeLabel'];
            }
            $editableFieldsFieldNames = $editableFieldsFieldNamesTmp;
        } else {
            $editableFieldsFieldNames = array_map (
                function ($elem) { return $elem['fieldName']; }, $editableFieldsFieldNames);
        }

        return $editableFieldsFieldNames;
    }


}
