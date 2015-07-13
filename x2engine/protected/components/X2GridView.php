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

Yii::import('zii.widgets.grid.CGridView');
Yii::import('X2GridViewBase');

/**
 * Custom grid view display function.
 *
 * Displays a dynamic grid view that permits save-able resizing and reordering of
 * columns and also the adding of new columns based on the available fields for
 * the model.
 *
 * @property bool $isAdmin If true, the grid view will be generated under the
 *  assumption that the user viewing it has full/administrative access to
 *  whichever module that it is being used in.
 * @package application.components
 */
class X2GridView extends X2GridViewBase {
    public $modelName;
    public $viewName;
    public $fieldFormatter = 'X2GridViewFieldFormatter';
    public $columnOverrides = array ();

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'X2DataColumn'; 

    /**
     * @var bool $enableTags if true, tags column can be added/removed by user. Don't enable this
     *  without adding support for tag filtering.
     */
    public $enableTags = false;

    public $allFields = array ();
    public $specialColumns = array ();
    public $massActions = array (
        );

    protected $_fieldModels;
    protected $_isAdmin;
    protected $specialColumnNames = array();

    public function __construct($owner = null){
        X2Model::$autoPopulateFields = false;
        parent::__construct($owner);
    }

    protected function addSpecialFieldNames () {
        // load names from $specialColumns into $specialColumnNames
        foreach($this->specialColumns as $columnName => &$columnData) {
            if(isset($columnData['header'])) {
                $this->specialColumnNames[$columnName] = $columnData['header'];
            } else {
                $this->specialColumnNames[$columnName] = $this->getSpecialColumnName ($columnName);
            }
        }

        if(!empty($this->specialColumnNames))
            $this->allFieldNames = array_merge ($this->allFieldNames, $this->specialColumnNames);

        // add tags column if specified
        if($this->enableTags)
            $this->allFieldNames['tags'] = Yii::t('app','Tags');
    }

    protected function addFieldNames () {
        $this->addSpecialFieldNames ();

        foreach($this->allFields as $fieldName=>&$field) {
            $this->allFieldNames[$fieldName] =
                X2Model::model($this->modelName)->getAttributeLabel($field->fieldName);
        }
    }

    protected $_model;
    public function getModel ($row=null, $data=null) {
        if (!isset ($this->_model)) {
            $this->_model = X2Model::model ($this->modelName);
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

    protected function handleFields () {
        $fields = X2Model::model($this->modelName)->getFields();

        $fieldPermissions = array();
        if(!$this->isAdmin && !empty(Yii::app()->params->roles)) {
            $rolePermissions = Yii::app()->db->createCommand()
                ->select('fieldId, permission')
                ->from('x2_role_to_permission')
                ->join('x2_fields','x2_fields.modelName="'.$this->modelName.
                    '" AND x2_fields.id=fieldId AND roleId IN ('.
                    implode(',',Yii::app()->params->roles).')')
                ->queryAll();

            foreach($rolePermissions as &$permission) {
                if(!isset($fieldPermissions[$permission['fieldId']]) ||
                   $fieldPermissions[$permission['fieldId']] < (int)$permission['permission']) {

                    $fieldPermissions[$permission['fieldId']] = (int)$permission['permission'];
                }
            }
        }

        // Begin setting fields
        foreach($fields as $field) {
            if (isset($this->excludedColumns[$field->fieldName]))
                continue;
            if((!isset($fieldPermissions[$field->id]) || $fieldPermissions[$field->id] > 0))
                $this->allFields[$field->fieldName] = $field;
        }
    }

    protected function getSpecialColumnName ($columnName) {
        return  X2Model::model($this->modelName)->getAttributeLabel($columnName);

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

        if ((array_key_exists($columnName, $this->allFields))) { 

            $newColumn['name'] = $columnName;
            $newColumn['id'] = $this->namespacePrefix.'C_'.$columnName;
            $newColumn['header'] = X2Model::model($this->modelName)
                ->getAttributeLabel($columnName);
            $newColumn['fieldModel'] = isset($this->fieldModels[$columnName]) ?
                $this->fieldModels[$columnName]->attributes : array();
            $newColumn['headerHtmlOptions'] = array(
                'style'=>'width:'.$this->formatWidth ($width).';');

            $makeLinks = in_array (
                $this->allFields[$columnName]->type, array ('phone', 'link', 'assignment'));
            
            $newColumn['value'] = 
                 '$this->grid->setFormatter ($data)
                     ->renderAttribute ("'.$columnName.'", '.($makeLinks ? 'true' : 'false').');';
        } else if($columnName == 'tags') {
            $newColumn['id'] = $this->namespacePrefix.'C_'.'tags';
            $newColumn['header'] = Yii::t('app','Tags');
            $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');
            $newColumn['value'] = 'Tags::getTagLinks("'.$this->modelName.'",$data->id)';
            $newColumn['type'] = 'raw';
            $newColumn['filter'] = CHtml::textField(
                'tagField',isset($_GET['tagField'])? $_GET['tagField'] : '');
        } 
        return $newColumn;
    }

    public function getFieldModels() {
        if(!isset($this->_fieldModels)) {
            $this->_fieldModels = X2Model::model($this->modelName)->getFields(true);
        }
        return $this->_fieldModels;
    }

    public function getIsAdmin() {
        if(!isset($this->_isAdmin)) {
            $this->_isAdmin = 
                (bool) Yii::app()->user->checkAccess(ucfirst($this->moduleName).'AdminAccess');
        }
        return $this->_isAdmin;
    }

    public function init () {
        $this->handleFields ();
        if ($this->enableSelectAllOnAllPages) $this->dataProvider->calculateChecksum = true;
        parent::init ();
    }

    public function setSummaryText () {
        if ($this instanceof X2GridViewForSortableWidgets ||
            $this instanceof X2GridViewLessForSortableWidgets) {
            $this->setSummaryTextForSortableWidgets ();
            return;
        }

        /* add a dropdown to the summary text that let's user set how many rows to show on each 
           page */
        $this->summaryText = Yii::t('app', '<span class="grid-view-summary-text">
            <b>{start}&ndash;{end}</b> of <b>{count}</b></span>').
            '<div class="form no-border" style="display:inline;"> '.
            CHtml::dropDownList(
                'resultsPerPage', 
                Profile::getResultsPerPage(),
                Profile::getPossibleResultsPerPage(), 
                array(
                    'class' => 'x2-minimal-select',
                    'onchange' => '$.ajax ({
                        data: {
                            results: $(this).val ()
                        },
                        url: "'.$this->controller->createUrl('/profile/setResultsPerPage').'",
                        complete: function (response) {
                            $.fn.yiiGridView.update("'.$this->id.'", {'.
                                (isset($this->modelName) ?
                                    'data: {'.$this->modelName.'_page: 1},' : '') .
                                    'complete: function () {}'.
                            '});
                        }
                    });'
                )). 
            '</div>';
    }

    public function setModuleName($value) {
        $this->_moduleName = $value;
    }

    protected function renderContentBeforeHeader () {
        if ($this->enableSelectAllOnAllPages) {
            $this->renderSelectAllRecordsOnAllPagesStrip ();
        }
    }

    private function renderSelectAllRecordsOnAllPagesStrip () {
        echo 
            '<div class="select-all-records-on-all-pages-strip-container" style="display: none;">
                <div class="select-all-notice">
                '.Yii::t('app', 'All {count} {recordType} on this page have been selected. '.
                '{clickHereLink} to select all {recordType} on all pages.', array (
                    '{count}' => '<b>'.$this->dataProvider->itemCount.'</b>',
                    '{clickHereLink}' => 
                        '<a class="select-all-records-on-all-pages" href="#">'.
                            Yii::t('app', 'Click here').
                        '</a>',
                    '{recordType}' => X2Model::getRecordName ($this->modelName, true),
                )).'
                </div>
                <div class="all-selected-notice" style="display: none;">
                '.Yii::t(
                    'app', 
                    'All {recordType} on all pages have been selected ({count} in total). '.
                        '{clickHereLink} to clear your selection.', 
                    array (
                        '{count}' => '<b>'.$this->dataProvider->totalItemCount.'</b>',
                        '{clickHereLink}' => 
                            '<a class="unselect-all-records-on-all-pages" href="#">'.
                                Yii::t('app', 'Click here').
                            '</a>',
                        '{recordType}' => X2Model::getRecordName ($this->modelName, true),
                    )).'
                </div>
            </div>';
    }

}
?>
