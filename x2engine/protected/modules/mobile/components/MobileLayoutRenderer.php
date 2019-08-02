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




abstract class MobileLayoutRenderer extends X2Widget {

    public $mobileLayout;
    public $model;
    public $instantiateJSClassOnInit = true;

    abstract public function renderLayout ();

    abstract public function getLayout ();

    private $_packages;
    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'MobileLayoutRenderer' => array(
                'baseUrl' => Yii::app()->controller->assetsUrl,
                'js' => array(
                    'js/MobileLayoutRenderer.js',
                ),
                'depends' => array ('auxlib')
            ),
        ));
    }

    /**
     * Gets the field Permissions into an aray
     * @return array array of field permissions
     */
    public function getFieldPermissions () {
        // Admin can edit all.
        if (Yii::app()->params->isAdmin || empty(Yii::app()->params->roles)) {
            return;
        }

        if ($this->model instanceof X2Model) {
            return $this->model->getFieldPermissions();
        } else {
            return null;
        }
    }

    /**
     * Returns if the form or a specific field can be edited. 
     * If Field is empty, it returns permissions of whole form
     */
    public function canEdit($field) {
        if (!($field instanceof Fields)) return true;
        if(Yii::app()->params->isAdmin){
            return true;
        }
        
        // If field is read only no one can edit
        if($field->readOnly) {
            return false;
        }
        
        // If permissions aren't set, it can be edited
        if (!isset($this->fieldPermissions[$field->fieldName])) {
            return true;
        }

        // If permissions are set to 'edit', it can be edited
        if ($this->fieldPermissions[$field->fieldName] === 2) {
            return true;
        }

        // Otherwise, it cant be edited (permissions set to 0 or 1)
        return false;
    }

    public function canView($field) {
        if(Yii::app()->params->isAdmin){
            return true;
        }
        
        // If permissions aren't set, it can be viewed
        if (!isset($this->fieldPermissions[$field->fieldName])) {
            return true;
        }

        // If permissions are set to 'view', it can be viewed
        if ($this->fieldPermissions[$field->fieldName] >= 1) {
            return true;
        }

        // Otherwise, it cant be viewed (permissions set to 0 )
        return false;
    }

    private $_modelName;
    public function getModelName () {
        if (!isset ($this->_modelName)) {
            $this->_modelName = get_class ($this->model);
        }
        return $this->_modelName;
    }

    public function renderLabel ($text) {
    }

    public function renderValue ($fieldName, array $htmlOptions=array ()) {
        $html = '';
        $html .= CHtml::openTag (
            'div', X2Html::mergeHtmlOptions (array ('class' => 'field-value'), $htmlOptions));
        $html .= $this->model->renderAttribute ($fieldName);
        $html .= CHtml::closeTag ('div');
        return $html;
    }

    public function renderName () {
        $nameField = $this->model->getField ('name');
        $html = '';
        if ($nameField) {
            $html .= $this->renderField ($this->renderValue ('name'));
        }

        return $html;
    }

    public function renderField ($field, $inner, array $htmlOptions=array ()) {
        $html = '';
        if ($field->valueIsLink ()) {
            $classes = 'ui-link field-container field-link-container';
            if ($field->linkType === 'multiple')
                $classes .= ' multiple-links';
            $html .= CHtml::tag (
                'div', X2Html::mergeHtmlOptions (array (
                    'class' => $classes,
                ), $htmlOptions));
            $html .= $inner;
            $html .= CHtml::closeTag ('div');
        } else {
            $html .= CHtml::openTag (
                'div', X2Html::mergeHtmlOptions (
                    array ('class' => 'field-container'), $htmlOptions));
            $html .= $inner;
            $html .= CHtml::closeTag ('div');
        }
        return $html;
    }

    public function getLayoutData () {
        if (!$this->mobileLayout) {
            // if there's no mobile layout, generate layout from default desktop app record layout
            $layoutData = MobileLayouts::generateDefaultLayout (
                $this->layoutType, $this->modelName);
        } else {
            $layoutData = $this->mobileLayout->layout;
        }
        return $layoutData;
    }
    
    public function run () {
        $ret = parent::run ();
        echo $this->renderLayout ();
        return $ret;
    }

}

?>
