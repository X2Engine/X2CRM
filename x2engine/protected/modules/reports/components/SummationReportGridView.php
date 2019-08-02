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




class SummationReportGridView extends ReportGridView {

    /**
     * @var array $hiddenColumns Columns which aren't displayed but which get used to render the
     *  atributes in the group header
     */
    public $hiddenColumns; 

    public $itemsCssClass = 'items grid-report-items';

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'SummationReportDataColumn'; 

    public $gridViewJSClass = 'summationReportGvSettings';

    /**
     * @var array fields indexed by opt group names. Used to populate column selection dropdown
     */
    public $allColumnOptions;

    /**
     * @var bool $rememberColumnSort
     */
    public $rememberColumnSort = false; 

    /**
     * @var array $groupAttrs attributes by which the data is grouped
     */
    public $groupAttrs; 

    /**
     * @var array $reportConfig configuration array used when generating the report
     */
    public $reportConfig; 

    /**
     * @var array $_allColumnsByName Used by renderGroupHeader to speed up column search. Hidden
     *  columns and columns indexed by name
     */
    private $_allColumnsByName; 

    public function renderItems () {
        $dataColumnClass = $this->dataColumnClass;
        //$dataColumnClass::renderHeaderOptionDropdown ();
        parent::renderItems ();
    }

    public function registerClientScript() {
        parent::registerClientScript();
        if($this->enableGvSettings) {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().
                '/js/X2GridView/x2gridview.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->controller->module->getAssetsUrl ().
                    '/js/reportGridSettings.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->controller->module->getAssetsUrl ().
                    '/js/summationReportGridSettings.js', CCLientScript::POS_END);
        }
    }

    /**
     * Initializes hidden columns 
     */
    public function initColumns () {
        if (count ($this->hiddenColumns)) {
            $tmp = $this->columns;
            $this->columns = $this->hiddenColumns;
            parent::initColumns ();
            $this->hiddenColumns = $this->columns;
            $this->columns = $tmp;
        }
        parent::initColumns ();
    }

    protected function getJSClassOptions () {
        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'enableColDragging' => false,
                'reportConfig' => $this->reportConfig 
            ));
    }

    /**
     * Magic getter for $_allColumnsByName 
     */
    private function getAllColumnsByName () {
        if (!isset ($this->_allColumnsByName)) {
            $this->_allColumnsByName = array ();
            $allColumns = array_merge ($this->columns, $this->hiddenColumns);
            foreach ($allColumns as $column) {
                $this->_allColumnsByName[$column->name] = $column;
            }
        }
        return $this->_allColumnsByName;
    }

}
