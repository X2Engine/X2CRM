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
 * Model that assumes any attributes given to it.
 *
 * Intended for handling special data validation in Fields input.
 *
 * @package application.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class AmorphousModel extends CModel {

    private $_attributes = array();

    private $_mockFields = array();

    private $_tableName;

    public function __get($name){
        if($this->hasAttribute($name)){
            return $this->_attributes[$name];
        } else
            return parent::__get($name);
    }

    public function __set($name, $value){
        if($this->hasAttribute($name, $value)){
            $this->setAttribute($name, $value);
        } else
            parent::__set($name, $value);
    }

    public function addField(Fields $field,$name=null){
        $name = empty($name) ? $field->fieldName : $name;
        $this->_mockFields[$name] = $field;
        if(!isset($this->_attributes[$name])){
            $this->_attributes[$name] = '';
        }
    }

    public function attributeNames(){
        return array_keys($this->_attributes);
    }

    public function getAttribute($name){
        return $this->_attributes[$name];
    }

    public function getAttributeLabel($name){
        if(isset($this->_mockFields[$name])){
            return $this->_mockFields[$name]->attributeLabel;
        } else
            return null;
    }

    public function getAttributes($names = null){
        if($names == null)
            return $this->_attributes;
        else
            parent::getAttributes($names);
    }

    public function hasAttribute($name){
        return array_key_exists($name,$this->_attributes);
    }

    /**
     * Automatically generate rules from X2Model.
     *
     * The "required" validator is excluded because, when validating input for a
     * default value for a field that is required, blank should be a valid value
     * because otherwise having the field be required would force the user to
     * specify a non-blank default value.
     * @return array
     */
    public function rules(){
        $rules = X2Model::modelRules($this->_mockFields, $this);
        foreach(array_keys($rules) as $ind) {
            if(in_array($rules[$ind][1],array('required','unique','application.components.ValidLinkValidator')))
                unset($rules[$ind]);
        }
        return $rules;
    }

    public function setAttribute($name, $value){
        $this->_attributes[$name] = $value;
    }

    public function setTableName($value){
        $this->_tableName = $value;
    }

    public function tableName(){
        return $this->_tableName;
    }

}

?>
