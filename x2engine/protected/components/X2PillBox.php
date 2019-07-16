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




class X2PillBox extends X2Widget {

    /**
     * @var string $id html id for pill box element
     */
    public $id; 

    /**
     * @var string $name
     */
    public $name; 

    /**
     * @var array $options
     */
    public $options; 
      
    /**
     * @var string $optionsHeader will be displayed at the top of the options dropdown
     */
    public $optionsHeader; 

    /**
     * @var array $value; 
     */
    public $value = array (); 

    /**
     * @var array $translations
     */
    public $translations = array ();  

    /**
     * @var string $pillBoxJSClass
     */
    public $pillBoxJSClass = 'PillBox'; 

    /**
     * @var array $htmlOptions
     */
    public $htmlOptions = array (); 

    /**
     * @return arguments passed to $pillBoxJSClass constructor
     */
    public function getJSClassParams () {
        return array_merge (parent::getJSClassParams (), array (
            'name' => $this->name,
            'options' => $this->options,
            'value' => $this->value,
            'translations' => array_merge (array (
                'helpText' => Yii::t('app', 'Click to add'),
                'optionsHeader' => $this->optionsHeader,
                'delete' => Yii::t('app', 'Delete'),
            ), $this->translations),
            'pillClass' => $this->pillJSClass,
        ));
    }

    /**
     * @var string $pillJSClass
     */
    public $pillJSClass = 'Pill'; 

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'X2PillBoxJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2PillBox.js',
                    ),
                    'depends' => array ('auxlib', 'X2Widget')
                ),
            ));
        }
        return $this->_packages;
    }

    public function init () {
        unset ($this->htmlOptions['id']);
        unset ($this->htmlOptions['name']);
        parent::init ();
    }

    public function run () {
        $this->registerPackages ();
        $this->render ('x2PillBox');
    }

}

?>
