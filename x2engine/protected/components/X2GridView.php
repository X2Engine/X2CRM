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
class X2GridView extends X2ActiveGridView {
    public $viewName;
    public $fieldFormatter = 'X2GridViewFieldFormatter';

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'X2DataColumn'; 

    public $allFields = array ();

    /**
     * @deprecated tag column enabled if model has TagBehavior
     */
    public $enableTags = false;

    protected $_fieldModels;
    protected $_isAdmin;

    public function __construct($owner = null){
        X2Model::$autoPopulateFields = false;
        parent::__construct($owner);
    }

     
    public function getMassActions () {
        if (!isset ($this->_massActions)) {
            $this->_massActions = array ('MassDelete', 'MassUpdateFields', 'MassExecuteMacro');
            $model = $this->getModel ();
            if ($this->model->asa('relationships')) {
                $this->_massActions[] = 'MassAddRelationship';
            }
            if ($this->model->asa ('TagBehavior')) {
                $this->_massActions[] = 'MassTag';
                $this->_massActions[] = 'MassTagRemove';
            }
            if ($this->model->asa ('ModelConversionBehavior')) {
                $this->_massActions[] = 'MassConvertRecord';
            }
            if (!RecordViewWidgetManager::isExcluded ('PublisherWidget', $this->modelName)) {
                $this->_massActions = array_merge ($this->_massActions, array (
                    'MassPublishNote', 'MassPublishCall', 'MassPublishTime', 'MassPublishAction',
                ));
            }
            if($this->model->asa('MergeableBehavior')){
                $this->_massActions[] = 'MergeRecords';
            }
        }
        return $this->_massActions;
    }
     

    protected function addSpecialFieldNames () {
        parent::addSpecialFieldNames ();

        // add tags column if specified
        if($this->model->asa ('TagBehavior'))
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
        $excludedColumns = array_flip ($this->excludedColumns ? $this->excludedColumns : array ());
        foreach($fields as $field) {
            if (isset($excludedColumns[$field->fieldName]))
                continue;
            if((!isset($fieldPermissions[$field->id]) || $fieldPermissions[$field->id] > 0))
                $this->allFields[$field->fieldName] = $field;
        }
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
            $newColumn['filter'] = $this->filter->renderTagInput ();
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
        $this->calculateChecksum = filter_input (INPUT_GET, 'calculateGridViewChecksum');
        $this->handleFields ();
        if ($this->enableSelectAllOnAllPages && 
            $this->calculateChecksum) {

            $this->dataProvider->calculateChecksum = true;
        }
        parent::init ();
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
