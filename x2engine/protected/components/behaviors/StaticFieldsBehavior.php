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
 * Allows fields to be defined in an array, instead of in the x2_fields table. Enables 
 * use of features that have a Fields dependency, without having to subclass X2Model or introduce
 * unnecessary database interaction.
 */

class StaticFieldsBehavior extends CBehavior {

    public $fields;

    // used to translate labels, if specified. Lazy translation is needed for profile model since
    // profile behavior is attached before application language is set.
    public $translationCategory;

    private $_fields;

    public function attach ($owner) {
        parent::attach ($owner);
        foreach ($this->fields as $field) {
            $this->_fields[$field['fieldName']] = Yii::createComponent (array_merge (array (
                'class' => 'X2StaticField',
                'translationCategory' => $this->translationCategory,
                'owner' => $this->owner
            ), $field));
        }
        $this->owner->setFormatter ('application.components.formatters.FieldFormatter');
    }

    public function getField ($fieldName) {
        return $this->_fields[$fieldName];
    }

    public function renderInput($fieldName, $htmlOptions = array()) {
        $field = $this->getField($fieldName);

        if (!$field) return;
        
        if (isset ($this->inputRenderer) && $this->inputRenderer instanceof FieldInputRenderer) {
            // check if there's a renderer for this field type
            if ($input = $this->inputRenderer->renderInput ($field, $htmlOptions)) {
                return $input;
            }
        }

        return X2Model::renderModelInput($this->owner, $field, $htmlOptions);

    }

}

/**
 * Behaves as stand in for Dropdowns class. $options can be set to a callable to enable lazy
 * loading.
 */
class X2StaticDropdown extends CComponent {

    private $_options;
    public function getOptions () {
        if (is_callable ($this->_options)) {
            $fn = $this->_options;
            return $fn ();
        } else {
            return $this->_options;
        }
    }

    public function setOptions ($options) {
        $this->_options = $options;
    }
}

/**
 * Behaves as stand in for Fields class
 */
class X2StaticField extends CComponent{

    public $translationCategory;
    public $type;
    public $fieldName;
    public $owner;
    public $required;
    public $includeEmpty = true;

    public function __construct () {
        $this->attachBehaviors ($this->behaviors ());
    }

    private $_linkType;
    public function getLinkType () {
        return $this->_linkType;
    }

    private $_attributeLabel;
    public function getAttributeLabel () {
        if ($this->translationCategory) {
            return Yii::t($this->translationCategory, $this->_attributeLabel);
        } else {
            return $this->_attributeLabel;
        }
    }

    public function setAttributeLabel ($attributeLabel) {
        $this->_attributeLabel = $attributeLabel;
    }

    public function setLinkType ($linkType) {
        if (is_callable ($linkType)) {
            $this->_linkType = Yii::createComponent (array (
                'class' => 'X2StaticDropdown',
                'options' => $linkType
            ));
        } else {
            $this->_linkType = $linkType;
        }
    }

    public function behaviors () {
        return array (
            'CommonFieldsBehavior' => array (
                'class' => 'application.components.behaviors.CommonFieldsBehavior',
            )
        );
    }

    public function valueIsLink () {
        static $linkTypes = null;
        if (!$linkTypes) 
            $linkTypes = array_flip (array (
                'link', 'updatedBy', 'createdBy', 'url', 'phone', 'email', 'assignment'));
        return isset ($linkTypes[$this->type]);
    }

    public function getDropdownOptions () {
        if ($this->linkType instanceof X2StaticDropdown) {
            return array (
                'options' => $this->linkType->options,
                'multi' => false
            );
        } else {
            return Dropdowns::getItems($this->linkType, null, true);
        }
    }

    public function getDropdownValue ($fieldValue) {
        if ($this->linkType instanceof X2StaticDropdown) {
            $options = $this->linkType->options;
            $fieldName = $this->fieldName;
            $value = $fieldValue;
            return isset ($options[$value]) ? $options[$value] : null;
        } else {
            return X2Model::model('Dropdowns')->getDropdownValue($this->linkType, $this->fieldName);
        }
    }
}

?>
