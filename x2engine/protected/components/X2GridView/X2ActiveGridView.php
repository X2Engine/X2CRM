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




class X2ActiveGridView extends X2GridViewBase {

    public $modelName;
    public $fieldFormatter = 'X2ActiveRecordFieldFormatter';
    public $specialColumns = array ();
    public $columnOverrides = array ();
    public $includedFields = array ();
    public $excludedFields = array ();
    public $isAdmin = false;
    protected $specialColumnNames = array();

    protected $_model;
    public function getModel ($row=null, $data=null) {
        if (!isset ($this->_model)) {
            $modelName = $this->modelName;
            $this->_model = $modelName::model ();
        }
        return $this->_model;
    }

    public function setFormatter ($data) {
        if (isset ($this->fieldFormatter) && 
            method_exists ($data, 'setFormatter')) {

            $data->formatter = $this->fieldFormatter;
        }
        return $data;
    }

    public function setSummaryText () {
        if ($this instanceof X2GridViewForSortableWidgets ||
            $this instanceof X2ActiveGridViewForSortableWidgets) {
            $this->setSummaryTextForSortableWidgets ();
            return;
        }
        $this->asa ('BaseListViewBehavior')->setSummaryText ();
    }

    protected function addSpecialFieldNames () {
        // load names from $specialColumns into $specialColumnNames
        foreach($this->specialColumns as $columnName => &$columnData) {
            if(isset($columnData['header'])) {
                $this->specialColumnNames[$columnName] = $columnData['header'];
            } else {
                $this->specialColumnNames[$columnName] = 
                    $this->getModel ()->getAttributeLabel ($columnName);
            }
        }

        if(!empty($this->specialColumnNames))
            $this->allFieldNames = array_merge ($this->allFieldNames, $this->specialColumnNames);

    }


    protected function addFieldNames () {
        $this->addSpecialFieldNames ();

        foreach($this->includedFields ? 
            $this->includedFields :
            array_diff ($this->getModel ()->attributeNames (), $this->excludedFields) as 
            $fieldName) {

            $this->allFieldNames[$fieldName] = $this->getModel ()->getAttributeLabel ($fieldName);
        }
    }

    protected function createSpecialColumn ($columnName, $width) {
        $newColumn = $this->specialColumns[$columnName];
        $newColumn['id'] = $this->namespacePrefix.'C_'.$columnName;
        $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$this->formatWidth ($width).';');
        if (!isset ($newColumn['name']) && !isset ($newColumn['value'])) {
            $newColumn['name'] = $columnName;
        }
        return $newColumn;
    }


    protected function generateColumns () {
        $columns = array ();

        foreach($this->gvSettings as $columnName => $width) {
            if($columnName == 'gvControls' && !$this->enableControls){
                continue;
            }

            $col = $this->addNewColumn ($columnName, $this->formatWidth ($width));
            if (sizeof ($col))
                $columns[] = $col;
        }

        $this->columns = $columns;
    }

    /**
     * @param int $width 
     * @param string $columnName 
     * @return array the new column
     */
    protected function addNewColumn ($columnName, $width) {
        $newColumn = array ();
        if(array_key_exists($columnName,$this->specialColumnNames)) {
            $newColumn = $this->createSpecialColumn ($columnName, $width);
        } else if($columnName == 'gvControls') {
            $newColumn = $this->getGvControlsColumn ($width);
            if(!$this->isAdmin)
                $newColumn['template'] = '{view}{update}';
        } else if ($columnName == 'gvCheckbox') {
            $newColumn = $this->getGvCheckboxColumn ($width);
        } else {
            $newColumn = $this->createDefaultStyleColumn ($columnName, $width);
        }
        if ($newColumn === array ()) return $newColumn;
        $newColumn['htmlOptions'] = X2Html::mergeHtmlOptions (
            isset ($newColumn['htmlOptions']) ? 
                $newColumn['htmlOptions'] : array (), array ('width' => $width));

        if (isset ($this->columnOverrides[$columnName])) {
            $newColumn = array_merge ($newColumn, $this->columnOverrides[$columnName]);
        }

        return $newColumn;
    }

    protected function createDefaultStyleColumn ($columnName, $width) {
        $isCurrency = in_array($columnName,array('annualRevenue','quoteAmount'));
        $newColumn = array();

        if (in_array ($columnName, array_keys ($this->allFieldNames))) { 

            $newColumn['name'] = $columnName;
            $newColumn['id'] = $this->namespacePrefix.'C_'.$columnName;
            $newColumn['header'] = $this->getModel ()->getAttributeLabel($columnName);
            $newColumn['headerHtmlOptions'] = array(
                'style'=>'width:'.$this->formatWidth ($width).';');

            $newColumn['value'] = 
                 '$this->grid->setFormatter ($data)
                     ->renderAttribute ("'.$columnName.'", false);';
        } 
        return $newColumn;
    }

}

?>
