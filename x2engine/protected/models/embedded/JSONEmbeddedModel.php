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
 * A model class for dealing with "embedded" models, whose attributes are stored
 * in a database column as a JSON-encoded string, when using
 * {@link JSONEmbeddedModelFieldsBehavior}.
 *
 * The methods for form inputs and rendering detail should be included in the
 * models themselves, to keep each model self-contained.
 * 
 * @package application.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class JSONEmbeddedModel extends CModel {

    /**
     * Whether or not admin rights are required to access this model
     * @var bool
     */
    public $requiresAdmin = false;

    /**
     * Stores derived value returned by {@link attributeNames()}
     * @var type
     */
    protected $_attributeNames;

    /**
     * Name of the attribute in the containing model that contains this model
     * @var type
     */
    public $exoAttr;

    /**
     * Form field name prefix
     * @var type
     */
    public $exoFormName;

    /**
     * The model to which this embedded model belongs
     * @var CActiveRecord
     */
    public $exoModel;

    public static function getProtectedFieldPlaceholder () {
        return Yii::t('app', 'Protected field value');
    }

    public function getProtectedFields () {
        return array ();
    }

    /**
     * Overridden to skip placeholder values 
     */
    public function setAttributes ($values, $safeOnly=true) {
        if(!is_array($values))
            return;
        $protectedFields = array_flip ($this->getProtectedFields ());

        foreach ($values as $fieldName => $value) {
            if (isset ($protectedFields[$fieldName]) && 
                $value === $this->getProtectedFieldPlaceholder ()) {

                unset ($values[$fieldName]);
            }
        }
        return parent::setAttributes ($values, $safeOnly);
    }

    public function renderProtectedInput ($field) {
        $htmlOptions = array ();
        if ($this->$field) {
            $htmlOptions['class'] = 'x2-protected-field';
            $this->$field = self::getProtectedFieldPlaceholder ();
        }
        Yii::app()->clientScript->registerScript('renderProtectedInput',"
        ");
        echo CHtml::activeTextField($this, $field, $this->htmlOptions($field, $htmlOptions));
    }
    
    public function renderProtectedInputHidden ($field) {
        $htmlOptions = array ();
        if ($this->$field) {
            $htmlOptions['class'] = 'x2-protected-field';
            $this->$field = self::getProtectedFieldPlaceholder ();
        }
        Yii::app()->clientScript->registerScript('renderProtectedInputHidden',"
        ");
        echo CHtml::activeTextField($this, $field, $this->htmlOptions($field, $htmlOptions));
    }
    
    public function renderInput ($field) {
        $protectedFields = array_flip ($this->getProtectedFields ());
        if (isset ($protectedFields[$field])) {
            echo $this->renderProtectedInput ($field);
        } else { 
            echo CHtml::activeTextField($this, $field, $this->htmlOptions($field));
        }
    }

    public function renderForm () {
		echo '<br />';
		echo '<br />';
		echo CHtml::tag ('h3', array (), $this->exoModel->getAttributeLabel ($this->exoAttr));
		echo '<hr />';
		$this->renderInputs();
		echo '<br />';
		echo '<br />';
    }

    /**
     * Values of properties of parent model to set when embedded model is created
     */
    public function getMetaData () {
        return array ();
    }

    public function attributeNames() {
        if(!isset($this->_attributeNames)) {
            $this->_attributeNames = array_keys($this->attributeLabels());
        }
        return $this->_attributeNames;
    }

    /**
     * Child classes implementing this should override this function to generate
     * a detail view. The resulting markup should be echoed out, not returned.
     */
    public function detailView() {

    }

    /**
     * A UI-friendly name that the model should be called.
     */
    public function modelLabel() {}

    /**
     * Child classes implementing this should generate all necessary input form
     * elements for modifying fields of the embedded model. The resulting
     * markup should be echoed out, not returned.
     */
    public function renderInputs() {}

    /**
     * Generate form input name for an attribute so that the urlencoded post data
     * comes in a form that can be properly interpreted by setAttributes in the
     * container model
     * {@link JSONEmbeddedModelFieldsBehavior}
     * @param string $attribute
     */
    public function resolveName($attribute) {
        if(!isset($this->exoFormName))
            $this->exoFormName = CHtml::resolveName($this->exoModel,$this->exoAttr);
        return $this->exoFormName.strtr(CHtml::resolveName($this,$attribute),array(get_class($this)=>''));
    }

    /**
     * Generate a list of options to send to methods within {@link CHtml} that
     * take HTML element options/properties, so that it includes the proper name
     * of the input.
     * @param type $options
     */
    public function htmlOptions($name,$options=array()) {
        return X2Html::mergeHtmlOptions ($options,array('name'=>$this->resolveName($name)));
    }

}

?>
