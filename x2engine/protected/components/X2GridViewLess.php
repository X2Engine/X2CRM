<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

Yii::import ('application.components.X2GridView');


/**
 * X2GridView for CActiveRecord models
 *
 * @package X2CRM.components
 */
class X2GridViewLess extends X2GridView {

    /**
     * @var array The columns that can be added with the column selector menu 
     */
    protected $_modelAttrColumnNames;

    /**
     * @var object the model associated with the grid 
     */
    protected $_model;

    public function getIsAdmin() {
        if(!isset($this->_isAdmin)) {
            $this->_isAdmin = Yii::app()->params->isAdmin;
        }
        return $this->_isAdmin;
    }

    protected function getSpecialColumnName ($columnName) {
        return $this->model->getAttributeLabel ($columnName);
    }

    public function setModelAttrColumnNames ($val) {
        $this->_modelAttrColumnNames = $val;
    }

    protected function getModel () {
        if (!isset ($this->_model)) {
            $modelName = $this->modelName;
            $this->_model = $modelName::model ();
        }
        return $this->_model;
    }

    public function getModelAttrColumnNames () {
        if (!isset ($this->_modelAttrColumnNames)) { // use model attributes if none specified
            $attrs = array_keys ($this->model->getAttributes ());
            $this->_modelAttrColumnNames = $attrs;
        }
        return $this->_modelAttrColumnNames;
    }

    protected function addFieldNames () {
        $attrs = $this->modelAttrColumnNames;
        foreach ($attrs as $name) {
            $this->allFieldNames[$name] = $this->model->getAttributeLabel ($name);
        }
    }

    protected function createDefaultStyleColumn ($columnName, $width, $isCurrency) {
        $newColumn = array ();

        $newColumn['name'] = $columnName;
        $newColumn['id'] = $this->namespacePrefix.'C_'.$columnName;
        $newColumn['header'] = $this->model->getAttributeLabel ($columnName);
        $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');

        if($isCurrency) {
            $newColumn['value'] = 'Yii::app()->locale->numberFormatter->'.
                'formatCurrency($data["'.$columnName.'"],Yii::app()->params->currency)';
            $newColumn['type'] = 'raw';
        } else if($columnName == 'assignedTo' || $columnName == 'updatedBy') {
            $newColumn['value'] = 'empty($data["'.$columnName.'"])?'.
                'Yii::t("app","Anyone"):User::getUserLinks($data["'.$columnName.'"])';
            $newColumn['type'] = 'raw';
        } else {
            $newColumn['value'] = '$data["'.$columnName.'"]';
            $newColumn['type'] = 'raw';
        }

        if(Yii::app()->language == 'en') {
            $format =  "M d, yy";
        } else {

            // translate Yii date format to jquery
            $format = Yii::app()->locale->getDateFormat('medium');

            $format = str_replace('yy', 'y', $format);
            $format = str_replace('MM', 'mm', $format);
            $format = str_replace('M','m', $format);
        }

        return $newColumn;
    }

    protected function handleFields () { return null; }




}
?>
