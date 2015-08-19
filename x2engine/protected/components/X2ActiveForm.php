<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

class X2ActiveForm extends CActiveForm {

    /**
     * @var string $id
     */
    public $id = 'x2-form'; 

    /**
     * @var bool $instantiateJSClass
     */
    public $instantiateJSClass = true;

    /**
     * @var string $JSClass 
     */
    public $JSClass = 'X2Form'; 

    /**
     * @var CFormModel $formModel
     */
    public $formModel; 

    /**
     * @var string $namespace
     */
    public $namespace = '';  

    protected $_packages;

    public function __construct ($owner=null) {
        // TODO: refactor dependency to X2Widget into a behavior that manages namespacing
        if ($this->namespace === '' && isset ($_POST[X2Widget::NAMESPACE_KEY])) {
            $this->namespace = $_POST[X2Widget::NAMESPACE_KEY];
        }
        parent::__construct ($owner);
    }

    public function resolveIds ($selector) {
        return preg_replace ('/#/', '#'.$this->namespace, $selector);
    }

    public function resolveId ($id) {
        return $this->namespace.$id;
    }

    /**
     * @param array 
     */
    public function getJSClassConstructorArgs () {
        return array (
            'formSelector' => '#'.$this->id,
            'submitUrl' => $this->action ? $this->action : '',
            'formModelName' => get_class ($this->formModel),
            'translations' => array (),
            'namespace' => $this->namespace,
        );
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array(
                'X2FormJS' => array(
                    'baseUrl' => Yii::app()->baseUrl,
                    'js' => array(
                        'js/X2Form.js',
                    ),
                    'depends' => array ('auxlib'),
                ),
            );
        }
        return $this->_packages;
    }

    public function multiTypeAutocomplete ($model, $typeAttribute, $idAttribute, $options) {
        return X2Html::activeMultiTypeAutocomplete ($model, $typeAttribute, $idAttribute, $options);

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

    public function registerPackages () {
        Yii::app()->clientScript->registerPackages ($this->getPackages (), true);
    }

    protected function registerJSClassInstantiationScript () {
        Yii::app()->clientScript->registerScript(
            $this->getId ().'registerJSClassInstantiationScript', "
        
        ;(function () {     
            x2.".lcfirst($this->JSClass)." = new x2.$this->JSClass (".
                CJSON::encode ($this->getJSClassConstructorArgs ()).
            ");
        }) ();
        ", CClientScript::POS_END);
    }

    public function init () {
        $this->id = $this->resolveId ($this->id);

        if ($this->instantiateJSClass) {
            $this->registerPackages (); 
            $this->registerJSClassInstantiationScript ();
        }

        parent::init ();
        echo CHtml::hiddenField (X2Widget::NAMESPACE_KEY, $this->namespace);
    }

}
