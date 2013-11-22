<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * A model class for dealing with "embedded" models, whose attributes are stored
 * in a database column as a JSON-encoded string, when using
 * {@link JSONEmbeddedModelFieldsBehavior}.
 *
 * The methods for form inputs and rendering detail should be included in the
 * models themselves, to keep each model self-contained.
 * 
 * @package X2CRM.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class JSONEmbeddedModel extends CModel {

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
     * The name of the model to which this embedded model belongs
     * @var type
     */
    public $exoModel;

    public function attributeNames() {
        if(!isset($this->_attributeNames)) {
            $this->_attributeNames = array_keys($this->attributeLabels());
        }
        return $this->_attributeNames;
    }

    /**
     * Child classes implementing this should generate the detail view. The
     * resulting markup should be echoed out, not returned.
     */
    public abstract function detailView();

    /**
     * A UI-friendly name that the model should be called.
     */
    public abstract function modelLabel();

    /**
     * Child classes implementing this should generate all necessary input form
     * elements for modifying fields of the embedded model. The resulting
     * markup should be echoed out, not returned.
     */
    public abstract function renderInputs();

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
        return array_merge($options,array('name'=>$this->resolveName($name)));
    }

}

?>
