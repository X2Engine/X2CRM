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




class X2ActiveRecord extends CActiveRecord {

    protected $fieldFormatterClass = 'X2ActiveRecordFieldFormatter';
    protected $fieldInputRendererClass;

    private $_formatter;
    private $_inputRenderer;

    public function rules () {
        $rules = $this->getBehaviorRules ();
        return $rules;
    }

    /**
     * @return array All error messages for this model as an array of strings 
     */
    public function getAllErrorMessages() {
        $errors = $this->getErrors();
        $errorMessages = array();
        foreach ($errors as $attrErrors) {
            foreach ($attrErrors as $errorMessage) {
                if ($errorMessage != '') {
                    $errorMessages[] = $errorMessage;
                }
            }
        }
        return $errorMessages;
    }

    public function setFormatter ($class) {
        if (is_string ($class)) {
            $this->_formatter = Yii::createComponent (array (
                'class' => $class,
                'owner' => $this,
            ));
        } else if ($class instanceof FieldFormatter) {
            $this->_formatter = $class;
        } else {
            throw new CException ('Invalid formatter object');
        }
    }

    public function getFormatter () {
        if (!isset ($this->_formatter)) {
            $this->_formatter = Yii::createComponent (array (
                'class' => $this->fieldFormatterClass,
                'owner' => $this,
            ));
        }
        return $this->_formatter;
    }

    public function setInputRenderer ($class) {
        if (is_string ($class)) {
            $this->_inputRenderer = Yii::createComponent (array (
                'class' => $class,
                'owner' => $this,
            ));
        } else if ($class instanceof FieldInputRenderer) {
            $this->_inputRenderer = $class;
        } else {
            throw new CException ('Invalid input renderer object');
        }
    }

    public function getInputRenderer () {
        if (!isset ($this->_inputRenderer) && $this->fieldInputRendererClass) {
            $this->_inputRenderer = Yii::createComponent (array (
                'class' => $this->fieldInputRendererClass,
                'owner' => $this,
            ));
        }
        return $this->_inputRenderer;
    }

    public function getName () {
        if (property_exists ($this, 'name') || $this->hasAttribute ('name')) return $this->name;
        else return $this->id;
    }

    /**
     * Renders an attribute of the model based on its field type
     * @param string $fieldName the name of the attribute to be rendered
     * @param boolean $makeLinks whether to create HTML links for certain field types
     * @param boolean $textOnly whether to generate HTML or plain text
     * @return string the HTML or text for the formatted attribute
     */
    public function renderAttribute(
        $fieldName, $makeLinks = true, $textOnly = true, $encode = true) {

        $formatter = $this->getFormatter ();
        return $formatter->renderAttribute ($fieldName, $makeLinks, $textOnly, $encode);
    }

    protected function getBehaviorRules () {
        $rules = array ();
        foreach ($this->behaviors () as $name => $config) {
            if ($this->asa ($name) && $this->asa ($name)->getEnabled () && 
                $this->asa ($name) instanceof ActiveRecordBehavior) {

                $rules = array_merge ($this->asa ($name)->rules (), $rules);
            }
        }
        return $rules;
    }

}

?>
