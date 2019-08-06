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




Yii::import('application.components.behaviors.EncryptedFieldsBehavior');
Yii::import('application.models.embedded.*');

/**
 * Behavior class for more advanced JSON storage in fields, using CModel children
 * in protected/models/embedded for validation, input widget rendering, etc.
 *
 * Supports multiple distinct stored structures of JSON (and also, with
 * encryption), distinguished by a separate attribute in the model (specified by
 * {@link $templateAttr}), and each field embedded within the JSON has its own
 * special options (i.e. default values, specific input widgets, etc) defined
 * in the model classes used.
 * 
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class JSONEmbeddedModelFieldsBehavior extends EncryptedFieldsBehavior {

    /**
     * In this case, the structure of the embedded object will be defined in the
     * model classes, so there's no need to define the fields in the declaration
     * of {@link transformAttr}.
     * @var bool
     */
    protected $hasOptions = false;

    /**
     * An array storing attribute models. Eliminates the need to re-instantiate
     * during unpacking.
     * @var type
     */
    public $attrModels = array();

    public $checkObject = false;

    /**
     * Attribute of the model class indicating whether the attribute(s) is/are encrypted.
     * if set to false, encryption will be completely ignored.
     * @var type
     */
    public $encryptedFlagAttr = false;

    /**
     * An array of field name keys to model name values defining fields which
     * will always use the same embedded model type.
     * @var array
     */
    public $fixedModelFields = array();

    /**
     * Specifies the name(s) of the attribute(s) of the model implementing this
     * behavior to be used for determining the model class corresponding to the
     * embedded model. 
     * 
     * More than one model class can be specified by declaring this an array
     * with model field names and names of field that specify the embedded model
     * in those fields as the values, in which case every attribute declared in
     * {@link transformAttributes} must be declared in the array.
     * @var string|array
     */
    public $templateAttr;

    /**
     * Before attaching, check whether checking for a proper encryption object
     * and throwing an exception if there isn't one is actually necessary.
     * @param type $owner
     */
    public function attach($owner){
        if(self::$encrypt) {
            $this->checkObject = true;
        }
        parent::attach($owner);
    }

    /**
     * Returns the model object for the named attribute
     *
     * Instantiates a new model for the field and saves it in a "cache" of
     * embedded models for the active record object if necessary.
     * @param type $name The name of the attribute.
     * @return JSONEmbeddedModel
     */
    public function attributeModel($name,$attributes=null){
        if(!(array_key_exists($name, $this->attrModels) && 
             ($this->getOwner()->$name instanceof JSONEmbeddedModel))){

            $owner = $this->getOwner();
            // Resolve embedded model's class for this attribute.
            if(array_key_exists($name, $this->fixedModelFields)){ 
                // Assume a predefined, hard-coded model class to use.
                $embeddedModelClass = $this->fixedModelFields[$name];
            }else{ // Get the model class from another attribute.
                if(is_array($this->templateAttr)) {
                    // There are distinct definitions for different fields each
                    // stored in a different database column
                    if(array_key_exists($name,$this->templateAttr)) {
                        $templateAttr = $this->templateAttr[$name];
                    } else {
                        throw new CException(
                            Yii::t('app','No field for {class} specifying the embedded model '.
                            'class of its attribute {attribute} has been specified in the '.
                            'configuration of JSONEmbeddedModelFieldsBehavior.',
                            array('{class}'=>get_class($owner),'{attribute}'=>$name)));
                    }
                } else {
                    // There is one attribute that specifies the model class for all fields 
                    // containing embedded models:
                    $templateAttr = $this->templateAttr;
                }

                $embeddedModelClass = $owner->$templateAttr;
            }

            if(array_key_exists($name,$this->attrModels)) {
                // Fetch existing model
                $embeddedModel = $this->attrModels[$name];
            } else {
                // Create a new model

                $embeddedModel = new $embeddedModelClass;
                $embeddedModel->exoAttr = $name;
                $embeddedModel->exoModel = $owner;
                // Copy the reference into the "cache" array of models:
                $this->attrModels[$name] = $embeddedModel;
            }
            if(is_array($attributes)) { 
                // Set attributes of the new model to those specified:
                $embeddedModel->attributes = $attributes;
            } else if(is_array($owner->$name)) { 
                // Set attributes of the new model to those existing already and stored in the 
                // model:
                $embeddedModel->attributes = $owner->$name;
            }
            return $embeddedModel;
        } else
            return $this->getOwner()->$name;
    }

    /**
     * Performs validation on the embedded models, and instantiates/sets attributes
     * of the embedded model if necessary.
     */
    public function beforeValidate($event) {
        $owner = $this->getOwner();
        foreach($this->transformAttributes as $name) {
            $embeddedModel = $this->instantiateField($name);
            $embeddedModel->validate();
            if($embeddedModel->hasErrors()) {
                $owner->addError(
                    $name,
                    Yii::t(
                        'app','Errors encountered in {attribute}',
                        array('{attribute}'=>$owner->getAttributeLabel($name))
                    ));
            }
        }
        parent::beforeValidate($event);
    }

    /**
     * Sets the encryption flag such that it accurately reflects the status of
     * data going into the database.
     * @param type $event
     */
    public function beforeSave($event){
        $encryptFlag = $this->encryptedFlagAttr;
        if((bool)$encryptFlag)
            $this->getOwner()->$encryptFlag = self::$encrypt;
        parent::beforeSave($event);
    }

    /**
     * Loads the embedded model into the owner's attribute and returns it
     * @param string $name Attribute corresponding to the model
     */
    public function instantiateField($name) {
        $owner = $this->getOwner();
        $embeddedModel = $this->attributeModel($name);
        if(!$owner->$name instanceof JSONEmbeddedModel)
            $owner->$name = $embeddedModel;
        return $embeddedModel;
    }

    /**
     * Instantiates all fields. This method must be called if the active record
     * model is new.
     */
    public function instantiateFields(){
        foreach($this->transformAttributes as $name) {
            $this->instantiateField($name);
        }
    }

    /**
     * JSON-encodes (and optionally encrypts) the model's attributes for storage.
     * @param type $name
     * @return type
     */
    public function packAttribute($name) {
        $encoded = CJSON::encode($this->attributeModel($name)->attributes);
        return self::$encrypt && (bool) 
            $this->encryptedFlagAttr ? parent::$encryption->encrypt($encoded) : $encoded;
    }

    /**
     * Restores the model. It will also instantiate the embedded model if it
     * hasn't already been instantiated and "cache" it in {@link attrModels}.
     * @param string $name
     * @param bool $new Instantiates and returns a new model rather than using existing data
     * @return JSONEmbeddedModel
     */
    public function unpackAttribute($name,$new=false) {
        // First, fetch and decode the existing value
        $owner = $this->getOwner();
        $encryptedFlagAttr = $this->encryptedFlagAttr;
        if($encryptedFlagAttr && self::$encrypt) {
            $attributes = CJSON::decode(
                $owner->$encryptedFlagAttr ? 
                    parent::$encryption->decrypt($owner->$name) : $owner->$name);
        } else {
            $attributes = CJSON::decode($owner->$name);
        }
        // Now the values can be loaded into the model:
        return $this->attributeModel($name,$attributes);
    }

}

?>
