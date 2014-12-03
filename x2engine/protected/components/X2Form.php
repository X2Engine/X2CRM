<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

class X2Form extends CActiveForm {

    /**
     * @var $id
     */
    public $id = 'x2-form'; 

    /**
     * @var string $JSClass 
     */
    public $JSClass = 'X2Form'; 

    /**
     * @var CFormModel $formModel
     */
    public $formModel; 

    protected $_packages;

    /**
     * @var string $_primaryModelTypeDropdownId
     */
    private $_primaryModelTypeDropdownId = 'primary-model-type'; 

    /**
     * @param array 
     */
    public function getJSClassConstructorArgs () {
        return array (
            'formSelector' => '#'.$this->id,
            'submitUrl' => '',
            'formModelName' => get_class ($this->formModel),
            'translations' => array (),
        );
    }

    public function registerPackages () {
        Yii::app()->clientScript->registerPackages ($this->getPackages (), true);
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
                'X2FormUtilJS' => array(
                    'baseUrl' => Yii::app()->baseUrl,
                    'js' => array(
                        'js/X2Forms.js',
                    ),
                    'depends' => array ('auxlib'),
                ),
            );
        }
        return $this->_packages;
    }

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function dropDownList($model,$attribute,$data,$htmlOptions=array()) {
		return X2Html::activeDropDownList($model,$attribute,$data,$htmlOptions);
	}

    protected function registerJSClassInstantiationScript () {

        Yii::app()->clientScript->registerScript(
            $this->getId ().'registerJSClassInstantiationScript', "
        
        (function () {     
            x2.".lcfirst($this->JSClass)." = new x2.$this->JSClass (".
                CJSON::encode ($this->getJSClassConstructorArgs ()).
            ");
        }) ();
        ", CClientScript::POS_END);
    }

    public function init () {
        if(!isset($this->formModel)){
            $formModel = get_class($this)."Model";
            $this->formModel = new $formModel;
        }
        $this->registerPackages (); 
        $this->registerJSClassInstantiationScript ();
        parent::init ();
    }


}

?>
