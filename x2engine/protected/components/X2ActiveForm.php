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




class X2ActiveForm extends CActiveForm {

    /**
     * @var string $id
     */
    public $id = 'x2-form'; 

    /**
     * @var bool $instantiateJSClassOnInit
     */
    public $instantiateJSClassOnInit = true;

    /**
     * @var string $JSClass 
     */
    public $JSClass = 'X2Form'; 

    /**
     * @var CFormModel $formModel
     */
    public $formModel; 

    public function __construct ($owner=null) {
        $this->attachBehaviors ($this->behaviors ());
        //$this->initNamespace ();
        parent::__construct ($owner);
    }

    public function behaviors () {
        return array (
            'WidgetBehavior' => array (
                'class' => 'application.components.behaviors.WidgetBehavior'
            ),
        );
    }

    public function getJSClassParams () {
        return array_merge (
            $this->asa ('WidgetBehavior')->getJSClassParams (),
            array (
                'formSelector' => '#'.$this->id,
                'submitUrl' => $this->action ? $this->action : '',
                'formModelName' => isset($this->formModel) ? get_class ($this->formModel) : false,
            )
        );
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge ($this->asa ('WidgetBehavior')->getPackages (), array(
                'X2FormJS' => array(
                    'baseUrl' => Yii::app()->baseUrl,
                    'js' => array(
                        'js/X2Form.js',
                    ),
                    'depends' => array ('auxlib'),
                ),
            ));
        }
        return $this->_packages;
    }

    public function multiTypeAutocomplete (
        $model, $typeAttribute, $idAttribute, $options, array $config = array ()) {
        return X2Html::activeMultiTypeAutocomplete (
            $model, $typeAttribute, $idAttribute, $options, $config);

    }

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function dropDownList($model,$attribute,$data,$htmlOptions=array()) {
		return X2Html::activeDropDownList($model,$attribute,$data,$htmlOptions);
	}

    public function richTextArea (CModel $model, $attribute, array $htmlOptions=array ()) {
        return X2Html::activeRichTextArea ($model, $attribute, $htmlOptions);
    }

    public function codeEditor (CModel $model, $attribute, array $htmlOptions = array ()) {
        return X2Html::activeCodeEditor ($model, $attribute, $htmlOptions);
    }

    public function resolveHtmlOptions (CModel $model, $attribute, array $htmlOptions = array ()) {
        CHtml::resolveNameID ($model, $attribute, $htmlOptions);
        $htmlOptions['id'] = $this->resolveId ($htmlOptions['id']);
        return $htmlOptions;
    }

    public function init () {
        $this->id = $this->resolveId ($this->id);

        if ($this->instantiateJSClassOnInit) {
            $this->registerPackages (); 
            $this->instantiateJSClass (false);
        }

        parent::init ();
        echo CHtml::hiddenField (WidgetBehavior::NAMESPACE_KEY, $this->namespace);
    }

}
