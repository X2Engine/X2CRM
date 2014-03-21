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

Yii::import('zii.widgets.grid.CGridView');
Yii::import('X2GridViewBase');

/**
 * Custom grid view display function.
 *
 * Displays a dynamic grid view that permits save-able resizing and reordering of
 * columns and also the adding of new columns based on the available fields for
 * the model.
 *
 * @property string $moduleName Name of the module that the grid view is being
 *  used in, for purposes of access control.
 * @property bool $isAdmin If true, the grid view will be generated under the
 *  assumption that the user viewing it has full/administrative access to
 *  whichever module that it is being used in.
 * @package application.components
 */
class X2GridView extends X2GridViewBase {
    public $modelName;
    public $viewName;
    public $enableTags = false;
    public $massActions = array ();
    public $fields;
    public $allFields = array ();
    public $specialColumns;

    protected $_fieldModels;
    protected $_isAdmin;
    protected $_moduleName;
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

        $this->fields = $fields;
    }

    protected function getSpecialColumnName ($columnName) {
        return  X2Model::model($this->modelName)->getAttributeLabel($columnName);

    }

    protected function createSpecialColumn ($columnName, $width) {
        $newColumn = $this->specialColumns[$columnName];
        $newColumn['id'] = $this->namespacePrefix.'C_'.$columnName;
        $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');
        return $newColumn;
    }

    protected function generateColumns () {
        $columns = array ();
        foreach($this->gvSettings as $columnName => $width) {
            if($columnName == 'gvControls' && !$this->enableControls){
                continue;
            }

            // make sure width is reasonable
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $col = $this->addNewColumn ($columnName, $width);
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
        return $newColumn;
    }

    protected function createDefaultStyleColumn ($columnName, $width) {
        $isCurrency = in_array($columnName,array('annualRevenue','quoteAmount'));
        $newColumn = array();
        $fields = $this->fields;

        if(isset ($fields) && 
            (array_key_exists($columnName, $this->allFields))) { 

            $newColumn['name'] = $columnName;
            $newColumn['id'] = $this->namespacePrefix.'C_'.$columnName;
            $newColumn['header'] = X2Model::model($this->modelName)
                ->getAttributeLabel($columnName);
            $newColumn['fieldModel'] = isset($this->fieldModels[$columnName]) ?
                $this->fieldModels[$columnName]->attributes : array();
            $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');

            if($isCurrency) {
                $newColumn['value'] = 'Yii::app()->locale->numberFormatter->'.
                    'formatCurrency($data["'.$columnName.'"],Yii::app()->params->currency)';
                $newColumn['type'] = 'raw';
            } else if($columnName == 'assignedTo' || $columnName == 'updatedBy') {
                $newColumn['value'] = 'empty($data["'.$columnName.'"])?'.
                    'Yii::t("app","Anyone"):User::getUserLinks($data["'.$columnName.'"])';
                $newColumn['type'] = 'raw';
            } elseif($this->allFields[$columnName]->type=='date') {
                $newColumn['value'] = 'empty($data["'.$columnName.'"])? "" : '.
                    'Formatter::formatLongDate($data["'.$columnName.'"])';
            } elseif($this->allFields[$columnName]->type=='percentage') {
                $newColumn['value'] = '$data["'.$columnName.'"]!==null&&$data["'.
                    $columnName.'"]!==""?((string)($data["'.$columnName.'"]))."%":null';
            } elseif($this->allFields[$columnName]->type=='dateTime') {
                $newColumn['value'] = 'empty($data["'.$columnName.'"])? "" : '.
                    'Yii::app()->dateFormatter->formatDateTime($data["'.
                    $columnName.'"],"medium")';
            } elseif($this->allFields[$columnName]->type=='link') {
                $newColumn['value'] = 'X2Model::getModelLinkMock(
                    "'.$this->allFields[$columnName]->linkType.'",
                    $data["'.$columnName.'"])';
                $newColumn['type'] = 'raw';
            } elseif($this->allFields[$columnName]->type=='boolean') {
                $newColumn['value']='$data["'.$columnName.'"]==1?Yii::t("actions","Yes"):'.
                    'Yii::t("actions","No")';
                $newColumn['type'] = 'raw';
            }elseif($this->allFields[$columnName]->type=='phone'){
                $newColumn['type'] = 'raw';
                $newColumn['value'] = 'X2Model::getPhoneNumber("'.$columnName.'","'.
                    $this->modelName.'",$data["id"])';
            }
        } else if($columnName == 'tags') {
            $newColumn['id'] = $this->namespacePrefix.'C_'.'tags';
            $newColumn['header'] = Yii::t('app','Tags');
            $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');
            $newColumn['value'] = 'Tags::getTagLinks("'.$this->modelName.'",$data->id,2)';
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

    public function getModuleName() {
        if(!isset($this->_moduleName)) {
            if(!isset(Yii::app()->controller->module))
                throw new CException('X2GridView cannot be used both outside of a module that uses X2Model and without specifying its moduleName property.');
            $this->_moduleName = Yii::app()->controller->module->getName();
        }
        return $this->_moduleName;
    }

    public function init () {
        $this->handleFields ();
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
                    'onchange' => '$.ajax ({'.
                        'data: {'.
                            'results: $(this).val ()'.
                        '},'.
                        'url: "'.$this->controller->createUrl('/profile/setResultsPerPage').'",'.
                        'complete: function (response) {'.
                            'console.log ("setResultsPerPage after ajax");'.
                            '$.fn.yiiGridView.update("'.$this->id.'", {'.
                                (isset($this->modelName) ?
                                    'data: {'.$this->modelName.'_page: 1},' : '') .
                                    'complete: function () {}'.
                            '});'.
                        '}'.
                    '});'
                )). 
            '</div>';
    }

    public function renderTopPager () {
        $this->controller->renderPartial (
            'application.components.views._x2GridViewTopPager', array (
                'gridId' => $this->id,
                'modelName' => $this->modelName,
                'gridObj' => $this,
                'namespacePrefix' => $this->namespacePrefix,
            )
        );
    }

    /**
     * Display mass actions ui buttons in top bar and set up related JS
     */
    public function renderMassActionButtons () {
        $auth = Yii::app()->authManager;
        $actionAccess = ucfirst(Yii::app()->controller->getId()). 'Delete';
        $authItem = $auth->getAuthItem($actionAccess);
        if(!is_null($authItem) && !Yii::app()->user->checkAccess($actionAccess)){
            if(in_array('delete',$this->massActions))
                unset($this->massActions[array_search('delete',$this->massActions)]);
        }

        $this->controller->renderPartial (
            'application.components.views._x2GridViewMassActionButtons', array (
                'UIType' => 'buttons',
                'massActions' => $this->massActions,
                'gridId' => $this->id,
                'namespacePrefix' => $this->namespacePrefix,
                'modelName' => $this->modelName,
                'gridObj' => $this,
                'fixedHeader' => $this->fixedHeader,
            ), false, ($this->ajax ? true : false)
        );
    }

    public function setModuleName($value) {
        $this->_moduleName = $value;
    }

    /***********************************************************************
    * Protected instance methods
    ***********************************************************************/

    /**
     * Creates column objects and initializes them. Overrides CGridView's method, swapping
     * CDataColumn for X2DataColumn.
     */
    protected function initColumns() {
        if($this->columns===array()) {
            if($this->dataProvider instanceof CActiveDataProvider) {
                $this->columns=$this->dataProvider->model->attributeNames();
            } elseif($this->dataProvider instanceof IDataProvider) {
                // use the keys of the first row of data as the default columns
                $data=$this->dataProvider->getData();
                if(isset($data[0]) && is_array($data[0]))
                    $this->columns=array_keys($data[0]);
            }
        }
        $id=$this->getId();
        foreach($this->columns as $i=>$column) {
            if(is_string($column)) {
                $column=$this->createDataColumn($column);
            } else {
                if(!isset($column['class']))
                    $column['class']='X2DataColumn';
                $column=Yii::createComponent($column, $this);
            }
            if(!$column->visible) {
                unset($this->columns[$i]);
                continue;
            }
            if($column->id===null) {
                $column->id=$id.'_c'.$i;
            }
            $this->columns[$i]=$column;
        }

        foreach($this->columns as $column) {
            $column->init();
        }
    }

}
?>
