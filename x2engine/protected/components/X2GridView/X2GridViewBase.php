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
Yii::import('X2DataColumn');
Yii::import('application.components.X2GridView.massActions.MassAction');
Yii::import('X2ButtonColumn');

/**
 * Custom grid view display function.
 *
 * Displays a dynamic grid view that permits save-able resizing and reordering of
 * columns and also the adding of new columns based on the available fields for
 * the model.
 *
 * @package application.components
 */
abstract class X2GridViewBase extends CGridView {
    public $selectableRows = 0;
    public $loadingCssClass = 'grid-view-loading';
    public $viewName;
    public $fullscreen = false;
    public $defaultGvSettings = array ();
    public $updateParams = array ();
    public $excludedColumns;
    public $enableGvSettings = true;
    public $enableControls = false;
    public $enableCheckboxColumn = true;
    public $enableGridResizing = true;
    public $enableColDragging = true;
    public $enableResponsiveTitleBar = true;
    public $enableDbPersistentGvSettings = true;
    public $fixedHeader = false;
    public $summaryText;
    public $buttons = array();
    public $title;
    public $gridViewJSClass = 'gvSettings';
    public $ajax = false;
    public $evenPercentageWidthColumns = false;
    public $showHeader = true;
    public $hideFullHeader = false;
    public $possibleResultsPerPage = array (10, 20, 30, 40, 50, 75, 100);
    public $hideSummary = false;

    /**
     * @var string $pagerClass
     */
    public $pagerClass = 'CLinkPager';
     
    /**
     * @var bool If true, the users will be able to select & perform mass action on all records 
     *  all records in the dataprovider.
     */
    public $enableSelectAllOnAllPages = false;

    public $calculateChecksum = false;

    /**
     * @var string $gvControlsTemplate
     */
    public $gvControlsTemplate = '{view} {update} {delete}';

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'CDataColumn'; 

    /**
     * @var bool If true, window will automatically scroll to the top when the page is changed
     */
    public $enableScrollOnPageChange = true;

    // JS which will be executed before/after yiiGridView.update () updates the grid view
    public $afterGridViewUpdateJSString = "";
    public $beforeGridViewUpdateJSString = "";

    /**
     * @var array the JS prototype name followed by properties of that prototype 
     */
    public $qtipManager;

    /**
     * @var bool whether qtips should be used, refresh method should be defined in a JS sub 
     *  prototype of X2GridViewQtipManager
     */
    public $enableQtips = false;

    /**
     * @var string $moduleName Name of the module that the grid view is being
     *  used in, for purposes of access control.
     */
    protected $_moduleName;

    protected $_resultsPerPage;
    protected $_packages;
    protected $allFieldNames = array();
    protected $gvSettings = null;
    protected $columnSelectorId;
    protected $columnSelectorHtml;

    /**
     * @var string Set to view name if value not passed to constructor. Used to save/access
     *  gridview settings saved as a JSON property in the profile model
     */
    protected $_gvSettingsName;

    /**
     * @var string Used to prefix javascript namespaces, GET parameter keys, Script names, and HTML
     *  attributes. Allows multiple instances of X2GridView to work on the same page. Generated by
     *  whitelisting the gridview id. 
     */
    protected $_namespacePrefix;

    abstract protected function setSummaryText ();

    abstract protected function generateColumns ();
    
    /**
     * Used instead of a closure because closure definition was causing errors, possibly related 
     * to APC cache size settings.
     */
    public static function massActionLabelComparison ($a, $b) {
        return strcmp ($a->getLabel (), $b->getLabel ());
    }

	public function __construct ($owner=null) {
        parent::__construct ($owner);
        $this->attachBehaviors ($this->behaviors ());
	}

    public function behaviors () {
        return array (
            'BaseListViewBehavior' => 'application.components.behaviors.BaseListViewBehavior'
        );
    }

    public function setResultsPerPage ($resultsPerPage) {
        $this->_resultsPerPage = $resultsPerPage;
    }

    public function getResultsPerPage () {
        if (!isset ($this->_resultsPerPage)) {
            $this->_resultsPerPage = Profile::getResultsPerPage ();
        }
        return $this->_resultsPerPage;
    }

    public function registerPackages () {
        Yii::app()->clientScript->registerPackages ($this->getPackages (), true);
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'QtipManager' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/QtipManager.js',
                    ),
                ),
                'X2GridViewQtipManager' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2GridView/X2GridViewQtipManager.js',
                    ),
                    'depends' => array ('QtipManager', 'auxlib'),
                ),
            );
        }
        return $this->_packages;
    }


    /**
     * Magic setter for gvSettingsName. 
     * @param string $gvSettingsName
     */
    public function setGvSettingsName ($gvSettingsName) {
        $this->_gvSettingsName = $gvSettingsName;
    }

    public function getModuleName() {
        if(!isset($this->_moduleName)) {
            if(!isset(Yii::app()->controller->module)) {
                throw new CException(
                    'X2GridView cannot be used both outside of a module that uses X2Model and '.
                    'without specifying its moduleName property.');
            }
            $this->_moduleName = Yii::app()->controller->module->getName();
        }
        return $this->_moduleName;
    }

    public function setModuleName ($moduleName) {
        $this->_moduleName = $moduleName;
    }

    /**
     * Magic getter for gvSettingsName. If not set explicitly, will be set to viewName
     * @return string  
     */
    public function getGvSettingsName () {
        if (isset ($this->_gvSettingsName)) {
            return $this->_gvSettingsName;
        } else if (isset ($this->viewName)) {
            $this->_gvSettingsName = $this->viewName;
        }
        return $this->_gvSettingsName;
    }

    /**
     * Magic setter for _namespacePrefix 
     * @param string
     */
    public function setNamespacePrefix ($namespacePrefix) {
        $this->_namespacePrefix = $namespacePrefix;
    }

    /**
     * Magic getter for _namespacePrefix 
     * @return string
     */
    public function getNamespacePrefix () {
        if (!isset ($this->_namespacePrefix)) {
            $this->_namespacePrefix = preg_replace (
                '/($[0-9])|([^a-zA-Z0-9_$])/', '', $this->id);
        }
        return $this->_namespacePrefix;
    }

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function renderKeys()
	{
		echo CHtml::openTag('div',array(
            /* x2modstart */ 
            // prevents name conflicts in nested grids
			'class'=>'keys '.$this->namespacePrefix.'keys',
            /* x2modend */ 
			'style'=>'display:none',
			'title'=>Yii::app()->getRequest()->getUrl(),
		));
		foreach($this->dataProvider->getKeys() as $key)
			echo "<span>".CHtml::encode($key)."</span>";
		echo "</div>\n";
	}

    /**
     * Registers JS which makes the grid header sticky
     * Preconditions:
     *     - The CGridView template string must be set up in a highly specific way
     *         - Example:
     *              '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">
     *               <div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">
     *               <div id="x2-gridview-page-title" class="x2-gridview-fixed-title">
     *               {items}{pager}'
     *          - there must be a pager and items.
     *          - the three opening divs with the specified classes and ids are required. The 
     *              divs
     *            get closed after the grid header is printed.
     *     - the X2GridView propert fixedHeader must be set to true
     */
    public function setUpStickyHeader () {

        // if the user is a guest, the left widgets will be hidden. remove padding to compensate
        // for missing widgets
        if(Yii::app()->user->isGuest) {
            Yii::app()->clientScript->registerCss('stickyHeaderGuestCss',"
                @media (min-width: 658px) {
                    .x2-gridview-fixed-top-bar-outer {
                        padding-left: 0px !important;
                    }
                    .x2-gridview-fixed-top-bar-outer .x2-gridview-fixed-top-bar-inner {
                        padding-left: 0px !important;
                    }
                }
            ");
        }

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/X2GridView/X2GridViewStickyHeader.js');      

        $makeHeaderStickyStr = "
            x2.gridViewStickyHeader.DEBUG && console.log ($('#".$this->id."').find ('.x2grid-body-container').find ('.x2grid-resizable').find ('tbody').find ('tr').length);

            if ($('#".$this->id."').find ('.x2grid-body-container').find ('.x2grid-resizable').
                find ('tbody').find ('tr').length <= 2 || x2.isIPad/* || x2.isAndroid*/) {

                x2.gridViewStickyHeader.DEBUG && console.log ('make sticky');
                x2.gridViewStickyHeader.makeSticky ();
            } else if (!$('#x2-gridview-top-bar-outer').
                hasClass ('x2-gridview-fixed-top-bar-outer')) {

                x2.gridViewStickyHeader.DEBUG && console.log ('make unsticky');
                x2.gridViewStickyHeader.makeUnsticky ();
            }

            x2.gridViewStickyHeader.DEBUG && console.log ('after grid update');
            if (!x2.gridViewStickyHeader.checkX2GridViewHeaderSticky ()) {

                $(window).unbind ('scroll.stickyHeader').
                    bind ('scroll.stickyHeader', function () {
                        x2.gridViewStickyHeader.checkX2GridViewHeaderSticky ();
                    });
            }
        ";

        $this->addToAfterAjaxUpdate ($makeHeaderStickyStr);

        Yii::app ()->clientScript->registerScript ('x2GridViewStickyHeader', "
            $(function () {
                x2.gridViewStickyHeader = new x2.GridViewStickyHeader ({
                    gridId: '".$this->id."'
                });
            });
        ", CClientScript::POS_HEAD);

    }

    /**
     * Used to populate allFieldNames property with attribute labels indexed by
     * attribute names.
     */
    abstract protected function addFieldNames ();

    protected function getGvControlsColumn ($width) {
        $width = $this->formatWidth ($width);
        $newColumn = array ();
        $newColumn['id'] = $this->namespacePrefix.'C_gvControls';
        $newColumn['class'] = 'X2ButtonColumn';
        $newColumn['header'] = Yii::t('app','Tools');
        $newColumn['template'] = $this->gvControlsTemplate;
        $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.';');
        return $newColumn;
    }

    protected function formatWidth ($width) {
        if (!is_scalar ($width)) return null;

        if (preg_match ('/[0-9]+px/', $width) ||
            preg_match ('/[0-9]+%/', $width)) {
            
            return $width;
        }

        if (is_numeric ($width)) {
            $width = $width . 'px';
        } else {
            if ($this->evenPercentageWidthColumns) {
                $width = ((1 / count ($this->gvSettings)) * 100).'%';
            } else {
                $width = null;
            }
        }
        return $width;
    }

    protected function getGvCheckboxColumn ($width=null, array $options=array ()) {
        $newColumn = array ();
        $newColumn['id'] = $this->namespacePrefix.'C_gvCheckbox';
        $newColumn['class'] = 'X2CheckBoxColumn';
        $newColumn['selectableRows'] = 2;
        $newColumn['htmlOptions'] = array('style'=>'text-align: center;');
        $newColumn['headerCheckBoxHtmlOptions'] = array('id'=>$newColumn['id'].'_all');
        $newColumn['checkBoxHtmlOptions'] = array('class'=>'checkbox-column-checkbox');
        if ($width) {
            $width = $this->formatWidth ($width);
            $newColumn['headerHtmlOptions'] = array(
                'style'=>'width:'.$width.';',
                'class'=>'checkbox-column',
            );
        }
        if (isset ($options['checkBoxHtmlOptions'])) {
            $newColumn['checkBoxHtmlOptions'] = array_merge (
                $newColumn['checkBoxHtmlOptions'],
                $options['checkBoxHtmlOptions']
            );
            unset ($options['checkBoxHtmlOptions']);
        }
        return array_merge ($newColumn, $options);
    }

    protected function extractGvSettings () {
        // update columns if user has submitted data
        // has the user changed column visibility?
        if(isset($_GET[$this->namespacePrefix.'columns']) && isset ($this->gvSettingsName)) {
            $columnsSelected = $_GET[$this->namespacePrefix.'columns'];

            foreach(array_keys($this->gvSettings) as $key) {
                // search $_GET['columns'] for the column
                $index = array_search($key,$columnsSelected);

                if($index === false) { // if it's not in there,
                    unset($this->gvSettings[$key]); // delete that junk
                } else { // othwerise, remove it from $_GET['columns']

                    // so the next part doesn't add it a second time
                    unset($columnsSelected[$index]);
                }
            }

            /* now go through $allFieldNames and add any fields that
               are present in $_GET['columns'] but not already in the list */
            foreach(array_keys($this->allFieldNames) as $key) {
                if(!isset($this->gvSettings[$key]) && 
                   in_array($key,$columnsSelected)) {

                    $this->gvSettings[$key] = 80; // default width of 80
                }
            }
        }
    }

    public function setPager () {
        $this->pager = array (
            'class' => $this->pagerClass, 
            'header' => '',
            'htmlOptions' => array (
                'id' => $this->namespacePrefix . 'Pager'
            ),
            'firstPageCssClass' => '',
            'lastPageCssClass' => '',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'firstPageLabel' => '<<',
            'lastPageLabel' => '>>',
        );
    }

    public function init() {
        $this->registerPackages ();
        $this->setPager ();

        $this->baseScriptUrl = Yii::app()->theme->getBaseUrl().'/css/gridview';

        $this->excludedColumns = empty($this->excludedColumns) ? array():
            array_fill_keys ($this->excludedColumns,1);

        // $this->id is the rendered HTML element's ID, i.e. "contacts-grid"
        $this->ajax = isset($_GET[$this->ajaxVar]) && $_GET[$this->ajaxVar] === $this->id;

        if($this->ajax) {
            ob_clean();
        }

        $this->columnSelectorId = $this->getId() . '-column-selector';

        /* 
        Get gridview settings by looking in the URL:
        This condition will pass in the case that an ajax update occurs following an ajax request 
        to save the grid view settings. It is necessary because it allows the grid view to render 
        properly even before the new grid view settings have been saved to the database.
        */
        if ($this->enableGvSettings) {
            if(isset($_GET[$this->namespacePrefix.'gvSettings']) && 
                isset ($this->gvSettingsName)) {

                $this->gvSettings = json_decode($_GET[$this->namespacePrefix.'gvSettings'],true);
                if ($this->enableDbPersistentGvSettings)
                    Profile::setGridviewSettings($this->gvSettings, $this->gvSettingsName);
            } else if ($this->enableDbPersistentGvSettings) {
                $this->gvSettings = Profile::getGridviewSettings($this->gvSettingsName);
            }
        }
        // Use the hard-coded defaults (note: gvSettings has column name keys:
        if($this->gvSettings == null)
            $this->gvSettings = $this->defaultGvSettings;

        if (!$this->enableGridResizing) {
            foreach ($this->defaultGvSettings as $col => $size) {
                $this->gvSettings[$col] = $size;
            }
        }

        // add controls column if specified
        if($this->enableControls)
            $this->allFieldNames['gvControls'] = Yii::t('app','Tools');

        if ($this->enableCheckboxColumn)
            $this->allFieldNames['gvCheckbox'] = Yii::t('app', 'Checkbox');

        $this->addFieldNames ();
        $this->extractGvSettings ();

        // prevents columns data from ending up in sort/pagination links
        unset($_GET[$this->namespacePrefix.'columns']); 
        unset($_GET['viewName']);
        unset($_GET[$this->namespacePrefix.'gvSettings']);

        // save the new Gridview Settings
        if ($this->enableDbPersistentGvSettings && $this->gvSettings !== Profile::getGridviewSettings($this->gvSettingsName))
            Profile::setGridviewSettings($this->gvSettings,$this->gvSettingsName);

        $columns = array();
        $datePickerJs = '';

        $this->generateColumns ();
        natcasesort($this->allFieldNames); // sort column names
        $this->generateColumnSelectorHtml ();
        
        // one blank column for the resizing widget
        if ($this->enableGridResizing) 
            $this->columns[] = array(
                'value'=>'',
                'header'=>'',
                'headerHtmlOptions' => array (
                    'class' => 'dummy-column',
                )); 

        $themeURL = Yii::app()->theme->getBaseUrl();
        
Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
    'dmFyIF8weDZjNzM9WyJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDZDXHg2R'
    .'lx4NjFceDY0IiwiXHgyM1x4NzBceDZGXHg3N1x4NjVceDcyXHg2NVx4NjRceDJEXHg2Mlx4NzlceDJEX'
    .'Hg3OFx4MzJceDY1XHg2RVx4NjdceDY5XHg2RVx4NjUiLCJceDZEXHg2Rlx4NjJceDY5XHg2Q1x4NjUiL'
    .'CJceDZDXHg2NVx4NkVceDY3XHg3NFx4NjgiLCJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzM'
    .'Vx4NjRceDMxXHg2Mlx4NjRceDYzXHgzMFx4NjJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzO'
    .'Fx4NjNceDY1XHgzN1x4MzRceDMzXHg2Nlx4MzZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzM'
    .'Vx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M1x4NjVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzM'
    .'lx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4NjRceDY1XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzI'
    .'iwiXHg2MVx4NzRceDc0XHg3MiIsIlx4M0FceDc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1IiwiXHg2O'
    .'Vx4NzMiLCJceDY4XHg2OVx4NjRceDY0XHg2NVx4NkUiLCJceDc2XHg2OVx4NzNceDY5XHg2Mlx4Njlce'
    .'DZDXHg2OVx4NzRceDc5IiwiXHg2M1x4NzNceDczIiwiXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiX'
    .'Hg3N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGXHg3MFx4NjFceDYzXHg2OVx4NzRceDc5IiwiXHg3M1x4N'
    .'zRceDYxXHg3NFx4NjlceDYzIiwiXHg3MFx4NkZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDY4X'
    .'Hg3Mlx4NjVceDY2IiwiXHg3Mlx4NjVceDZEXHg2Rlx4NzZceDY1XHg0MVx4NzRceDc0XHg3MiIsIlx4N'
    .'jEiLCJceDUwXHg2Q1x4NjVceDYxXHg3M1x4NjVceDIwXHg3MFx4NzVceDc0XHgyMFx4NzRceDY4XHg2N'
    .'Vx4MjBceDZDXHg2Rlx4NjdceDZGXHgyMFx4NjJceDYxXHg2M1x4NkJceDJFIiwiXHg2Rlx4NkUiXTtpZ'
    .'ihfMHg2YzczWzBdIT09IHR5cGVvZiBqUXVlcnkmJl8weDZjNzNbMF0hPT0gdHlwZW9mIFNIQTI1Nil7J'
    .'Ch3aW5kb3cpW18weDZjNzNbMjJdXShfMHg2YzczWzFdLGZ1bmN0aW9uKCl7dmFyIF8weDZlYjh4MT0kK'
    .'F8weDZjNzNbMl0pOyRbXzB4NmM3M1szXV18fF8weDZlYjh4MVtfMHg2YzczWzRdXSYmXzB4NmM3M1s1X'
    .'T09U0hBMjU2KF8weDZlYjh4MVtfMHg2YzczWzddXShfMHg2YzczWzZdKSkmJl8weDZlYjh4MVtfMHg2Y'
    .'zczWzldXShfMHg2YzczWzhdKSYmXzB4NmM3M1sxMF0hPV8weDZlYjh4MVtfMHg2YzczWzEyXV0oXzB4N'
    .'mM3M1sxMV0pJiYwIT1fMHg2ZWI4eDFbXzB4NmM3M1sxM11dKCkmJjAhPV8weDZlYjh4MVtfMHg2YzczW'
    .'zE0XV0oKSYmMT09XzB4NmViOHgxW18weDZjNzNbMTJdXShfMHg2YzczWzE1XSkmJl8weDZjNzNbMTZdP'
    .'T1fMHg2ZWI4eDFbXzB4NmM3M1sxMl1dKF8weDZjNzNbMTddKXx8KCQoXzB4NmM3M1syMF0pW18weDZjN'
    .'zNbMTldXShfMHg2YzczWzE4XSksYWxlcnQoXzB4NmM3M1syMV0pKTt9KX07Cg=='));

        $this->setSummaryText ();

        $this->addToAfterAjaxUpdate ("
            /* after user moves to a different page, make sure the tool tips get added to the
            newly displayed rows */
            $('.qtip-hint').qtip({content:false});
            $('#".$this->getNamespacePrefix ()."-filter-hint').qtip ();
            // refresh checklist dropdown filters for multi-dropdown fields
            x2.forms.initializeMultiselectDropdowns ();
        ");
        $this->jSClassInstantiation ();

        if ($this->enableQtips) $this->setUpQtipManager ();
        if ($this->fixedHeader) $this->setUpStickyHeader ();

        // Re-enable a datepicker widget in the data columns
        $this->addToAfterAjaxUpdate ("
                $('.datePicker').datepicker();
        ");

        parent::init();
    }

    public function generateColumnSelectorHtml () {
        $this->columnSelectorHtml = CHtml::beginForm(array('/site/saveGvSettings'),'get')
            .'<ul class="column-selector x2-dropdown-list'.
            ($this->fixedHeader ? ' fixed-header' : '').'" id="'.$this->columnSelectorId.'">';
        $i = 0;
        foreach($this->allFieldNames as $fieldName=>&$attributeLabel) {
            $i++;
            $selected = array_key_exists($fieldName,$this->gvSettings);
            $this->columnSelectorHtml .= "<li>"
            .CHtml::checkbox($this->namespacePrefix.'columns[]',$selected,array(
                'value'=>$fieldName,
                'id'=>$this->namespacePrefix.'checkbox-'.$i
            ))
            .CHtml::label($attributeLabel,$fieldName.'_checkbox')
            ."</li>";
        }
        $this->columnSelectorHtml .= '</ul></form>';
    }

    public function getAfterAjaxUpdateStr () {
        return $this->afterGridViewUpdateJSString;
    }

    public function getBeforeAjaxUpdateStr () {
        return $this->beforeGridViewUpdateJSString;
    }

    public function addToAfterAjaxUpdate ($str) {
        $this->afterGridViewUpdateJSString .= $str;
        if ($this->ajax) return;
        $this->afterAjaxUpdate =
            'js: function(id, data) {'.
                $this->afterGridViewUpdateJSString.
            '}';
    }

    public function addToBeforeAjaxUpdate ($str) {
        $this->beforeGridViewUpdateJSString .= $str;
        if ($this->ajax) return;
        $this->beforeAjaxUpdate =
            'js: function(id, data) {'.
                $this->beforeGridViewUpdateJSString .
            '}';
    }

    public function run() {
        if($this->ajax) {
            // remove any external CSS files
            Yii::app()->clientScript->scriptMap['*.css'] = false;
        }

        // give this a special class so the javascript can tell it apart from the Yii's gridviews 
        if(!isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = '';
        $this->htmlOptions['class'] .= ' x2-gridview';
        if($this->fullscreen)
            $this->htmlOptions['class'] .= ' fullscreen';

        echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

        $this->renderContent();
        $this->renderKeys();

        if($this->ajax) {
            echo CHtml::closeTag($this->tagName);
            ob_flush();
            Yii::app()->end();
        }
        echo CHtml::closeTag($this->tagName);

        $this->registerClientScript();
        Yii::app ()->clientScript->registerScript (
            $this->namespacePrefix.'gridAfterRender', $this->afterGridViewUpdateJSString, 
            CClientScript::POS_READY);

    }

    public static function getFilterHint() {
        $text = self::getFilterHintText ();
        return X2Html::hint ($text, false, 'filter-hint');
    }

    public static function getFilterHintText () {

        $text = Yii::t('app','<b>Tip:</b> You can use the following comparison operators with '.
            'filter values to fine-tune your search.');
        $text .= '<ul class="filter-hint">';
        $text .= '<li><b>&lt;</b> '        .Yii::t('app','less than')                .'</li>';
        $text .= '<li><b>&lt;=</b> '    .Yii::t('app','less than or equal to')        .'</li>';
        $text .= '<li><b>&gt;</b> '        .Yii::t('app','greater than')            .'</li>';
        $text .= '<li><b>&gt;=</b> '    .Yii::t('app','greater than or equal to')    .'</li>';
        $text .= '<li><b>=</b> '        .Yii::t('app','equal to')                    .'</li>';
        $text .= '<li><b>&lt;&gt</b> '    .Yii::t('app','not equal to')                .'</li>';
        $text .= '</ul>';
        return $text;
    }

    public function renderTopPager () {
        $this->controller->renderPartial (
            'application.components.views._x2GridViewTopPager', array (
                'gridId' => $this->id,
                'gridObj' => $this,
                'namespacePrefix' => $this->namespacePrefix,
            )
        );
    }

    protected $_massActions; 
    public function getMassActions () {
        if (!isset ($this->_massActions)) {
            $this->_massActions = array ();
        }
        return $this->_massActions;
    }

    public function setMassActions ($massActions) {
        $this->_massActions = $massActions;
    }
    
    /**
     * Display mass actions ui buttons in top bar and set up related JS
     */
    public function renderMassActionButtons () {
        $auth = Yii::app()->authManager;

        $actionAccess = ucfirst(Yii::app()->controller->getId()). 'Delete';
        $authItem = $auth->getAuthItem($actionAccess);
        
        if(!Yii::app()->params->isAdmin && !is_null($authItem) && 
            !Yii::app()->user->checkAccess($actionAccess)){

            if(in_array('MassDelete',$this->massActions)) {
                $massActions = $this->massActions;
                unset($massActions[array_search('MassDelete',$this->massActions)]);
                // reindex so it's still a valid JSON array
                $massActions = array_values ($massActions);
                $this->massActions = $massActions;
            }
        }

        if ($this->enableSelectAllOnAllPages && $this->calculateChecksum) {
            $idChecksum = $this->dataProvider->getIdChecksum ();
        } else {
            $idChecksum = null;
        }

        $massActionObjs = MassAction::getMassActionObjects ($this->massActions, $this);

        $this->controller->renderPartial (
            'application.components.views._x2GridViewMassActionButtons', array (
                'UIType' => 'buttons',
                'massActions' => $this->massActions,
                'massActionObjs' => $massActionObjs,
                'gridId' => $this->id,
                'namespacePrefix' => $this->namespacePrefix,
                'modelName' => (isset ($this->modelName)) ? $this->modelName : null,
                'gridObj' => $this,
                'fixedHeader' => $this->fixedHeader,
                'idChecksum' => $this->enableSelectAllOnAllPages ? $idChecksum : null,
            )//, false, ($this->ajax ? true : false)
        );
    }

    public function renderFilterHint() {
        echo X2Html::hint(
            self::getFilterHintText (), false, $this->getNamespacePrefix () . '-filter-hint',
            false, false);
    }

    /**
     * Renders the data items for the grid view.
     */
    public function renderItems() {

        if($this->dataProvider->getItemCount() > 0 || $this->showTableOnEmpty) {
            $pagerDisplayed = $this->dataProvider->getPagination()->getPageCount () > 1;

            if ($this->fixedHeader) echo '</div>';
            $this->renderContentBeforeHeader ();
            echo '<div class="x2grid-header-container">';

            echo '<table class="',$this->itemsCssClass,'"'.
                    (($this->showHeader || !$this->hideFullHeader) ? 
                        '' : "style='display: none;'").'>';
            $this->renderTableHeader();

            echo '</table></div>';
            if ($this->fixedHeader) echo '</div></div>';
            echo '<div class="x2grid-body-container'.
                (!$pagerDisplayed ? ' x2grid-no-pager' : '').'"><table class="'.
                $this->itemsCssClass.
                ($this->fixedHeader ? ' x2-gridview-body-with-fixed-header' : '')."\">\n";

            ob_start();
            $this->renderTableBody();
            $body = ob_get_clean();
            $this->renderTableFooter();
            echo $body; // TFOOT must appear before TBODY according to the standard.
            echo '</table>';

            echo '</div>';
        } else {
            $this->renderEmptyText();
        }

        echo X2Html::loadingIcon (
            array('class' => 'x2-gridview-updating-anim', 'style' => 'display: none'));
    }

    /**
     * @return array options to pass to grid view JS class constructor
     */
    protected function getJSClassOptions () {
        return array (
            'viewName' => $this->gvSettingsName,
            'columnSelectorId' => $this->columnSelectorId,
            'columnSelectorHtml' => addcslashes($this->columnSelectorHtml,"'"),
            'namespacePrefix' => $this->namespacePrefix,
            'ajaxUpdate' => $this->ajax,
            'fixedHeader' => $this->fixedHeader,
            'modelName' =>  (isset ($this->modelName) ? $this->modelName : ''),
            'enableScrollOnPageChange' => $this->enableScrollOnPageChange,
            'enableDbPersistentGvSettings' => $this->enableDbPersistentGvSettings,
            'enableGridResizing' => $this->enableGridResizing,
            'enableColDragging' => $this->enableColDragging,
            'sortStateKey' => 
                ($this->dataProvider->asa ('SmartDataProviderBehavior') ? 
                    $this->dataProvider->getSortKey () : ''),
            'updateParams' => $this->updateParams,
        );
    }

    /**
     * Register JS which instantiates grid view class
     */
    protected function jSClassInstantiation () {
        $this->addToAfterAjaxUpdate ("
            $('#".$this->getId()."').$this->gridViewJSClass (".
                CJSON::encode ($this->getJSClassOptions ()).");
        ");
    }

    /**
     * If enableQtips is true, instantiates the qtipManager prototype with configuration and 
     * prototype specified in qtipManager
     */
    protected function setUpQtipManager () {
        if (!$this->enableQtips || !isset ($this->qtipManager)) return;

        $protoName = $this->qtipManager[0];
        $protoProps = array_slice ($this->qtipManager, 1);
        Yii::app()->clientScript->registerScript($this->namespacePrefix.'qtipSetup', '
            x2.'.$this->namespacePrefix.'qtipManager = new x2.'.$protoName.' ('.
                CJSON::encode ($protoProps)
            .');
        ',CClientScript::POS_END);

        $this->addToAfterAjaxUpdate (
            "if(typeof(x2.".$this->namespacePrefix."qtipManager) !== 'undefined') { 
                x2.".$this->namespacePrefix."qtipManager.refresh (); }");
    }

	/**
	 * Registers necessary client scripts.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function registerClientScript()
	{
		$id=$this->getId();

        /* x2modstart */ 
		$options=$this->getYiiGridViewOptions ();
		$options=CJavaScript::encode($options);
        /* x2modend */ 

		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('jquery');
		$cs->registerCoreScript('bbq');
		if($this->enableHistory)
			$cs->registerCoreScript('history');
		$cs->registerScriptFile(
            $this->baseScriptUrl.'/jquery.yiigridview.js',CClientScript::POS_END);
		$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#$id').yiiGridView($options);");

        /* x2modstart */ 
        // Adds script essential to modifying the gridview (and saving its configuration).
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().
            '/js/X2GridView/x2gridview.js', CCLientScript::POS_END);
        /* x2modend */ 
	}

    public function getPossibleResultsPerPageFormatted () {
        $formatted = array ();
        foreach ($this->possibleResultsPerPage as $count) {
            $formatted[$count] = Yii::t('app', '{n} rows', array('{n}' => $count));
        }
        return $formatted;
    }

    public function renderSummary () {
        if (AuxLib::getLayoutType () === 'responsive' && $this->enableResponsiveTitleBar) {
        Yii::app()->clientScript->registerCss('mobileDropdownCss',"
            .grid-view .mobile-dropdown-button {
                float: right;
                display: block;
                margin-top: -24px;
                margin-right: 8px;
            }
        ");
        $afterUpdateJSString = "
            ;(function () {
            var grid = $('#".$this->id."');
            $('#".$this->namespacePrefix."-mobile-dropdown').unbind ('click.mobileDropdownScript')
                .bind ('click.mobileDropdownScript', function () {
                    if (grid.hasClass ('show-top-buttons')) {
                        grid.find ('.page-title').css ({ height: '' });
                        grid.removeClass ('show-top-buttons');
                    } else {
                        grid.find ('.page-title').animate ({ height: '68px' }, 300);
                        grid.addClass ('show-top-buttons');
                        $(window).one ('resize', function () {
                            grid.find ('.page-title').css ({ height: '' });
                            grid.removeClass ('show-top-buttons');
                        });
                    }
                });
            }) ();
        ";
        $this->addToAfterAjaxUpdate ($afterUpdateJSString);
        echo 
            '<div id="'.$this->namespacePrefix.'-mobile-dropdown" class="mobile-dropdown-button">
                <div class="x2-bar"></div>
                <div class="x2-bar"></div>
                <div class="x2-bar"></div>
            </div>';
        }
        parent::renderSummary ();
    }

    /**
     * Code moved out of registerClientScript, allowing it to be more easily overridden
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    protected function getYiiGridViewOptions () {
		$id=$this->getId();

		if($this->ajaxUpdate===false)
			$ajaxUpdate=false;
		else
			$ajaxUpdate=array_unique(preg_split('/\s*,\s*/',$this->ajaxUpdate.','.$id,-1,PREG_SPLIT_NO_EMPTY));
		$options=array(
			'ajaxUpdate'=>$ajaxUpdate,
			'ajaxVar'=>$this->ajaxVar,
			'pagerClass'=>$this->pagerCssClass,
			'loadingClass'=>$this->loadingCssClass,
			'filterClass'=>$this->filterCssClass,
			'tableClass'=>$this->itemsCssClass,
			'selectableRows'=>$this->selectableRows,
			'enableHistory'=>$this->enableHistory,
			'updateSelector'=>$this->updateSelector,
			'filterSelector'=>$this->filterSelector,
			'namespacePrefix'=>$this->namespacePrefix
		);

		if($this->ajaxUrl!==null)
			$options['url']=CHtml::normalizeUrl($this->ajaxUrl);
		if($this->ajaxType!==null)
			$options['ajaxType']=strtoupper($this->ajaxType);
		if($this->enablePagination)
			$options['pageVar']=$this->dataProvider->getPagination()->pageVar;
		foreach(array(
            'beforeAjaxUpdate', 'afterAjaxUpdate', 'ajaxUpdateError', 'selectionChanged') as 
            $event) {

			if($this->$event!==null)
			{
				if($this->$event instanceof CJavaScriptExpression)
					$options[$event]=$this->$event;
				else
					$options[$event]=new CJavaScriptExpression($this->$event);
			}
		}
        return $options;
    }

    protected function getSortDirections () {
        return $this->dataProvider->getSort()->getDirections();
    }

    /**
     * Echoes the markup for the gridview's table header.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function renderTableHeader() {
        if(!$this->hideHeader) {

            $filterOptions = array ();
            if (!$this->hideFullHeader && !$this->showHeader) {
                $filterOptions = array (
                    'style' => 'display: none;'
                );
            }

            $sortDirections = $this->getSortDirections ();
            foreach($this->columns as $column) {
                // determine sort state for this column (adapted from CSort::link())
                if(property_exists($column,'name')) {
                    if(isset($sortDirections[$column->name])) {
                        $class = $sortDirections[$column->name] ? 'desc' : 'asc';
                        if(isset($column->headerHtmlOptions['class']))
                            $column->headerHtmlOptions['class'].=' '.$class;
                        else
                            $column->headerHtmlOptions['class'] = $class;
                    }
                }
            }
            echo "<thead>\n";

            if($this->filterPosition===self::FILTER_POS_HEADER)
                $this->renderFilterWithOptions ($filterOptions);

            echo "<tr>\n";
            foreach($this->columns as $column) {
                $column->renderHeaderCell();
            }
            echo "</tr>\n";

            if($this->filterPosition===self::FILTER_POS_BODY)
                $this->renderFilterWithOptions($filterOptions);

            echo "</thead>\n";
        } else if($this->filter!==null &&
            ($this->filterPosition===self::FILTER_POS_HEADER ||
             $this->filterPosition===self::FILTER_POS_BODY)) {

            echo "<thead>\n";
            $this->renderFilterWithOptions();
            echo "</thead>\n";
        }
    }

    /**
     * Like renderFilter, but with html attribute options
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    public function renderFilterWithOptions (
        /* x2modstart */array $htmlOptions = array ()/* x2modend */) {

        if($this->filter!==null)
        {
            /* x2modstart */ 
            echo CHtml::openTag ('tr', X2Html::mergeHtmlOptions (array (
                'class' => $this->filterCssClass,
            ), $htmlOptions))."\n";
            /* x2modend */ 
            foreach($this->columns as $column)
                $column->renderFilterCell();
            echo "</tr>\n";
        }
    }

    public function renderTitle() {
        if(!empty($this->title))
            echo '<h2>',$this->title,'</h2>';
    }

    public function renderButtons() {
        if(0 === ($count = count($this->buttons)))
            return;
        if($count > 1)
            echo '<div class="x2-button-group x2-grid-view-controls-buttons">';
        else
            echo '<div class="x2-grid-view-controls-buttons">';
        $lastChildClass = '';
        for ($i = 0; $i < $count; ++$i) {
            $button = $this->buttons[$i];
            if ($i === $count - 1) $lastChildClass = ' x2-last-child';
            switch($button) {
                case 'advancedSearch':
                    break; // remove until fixed
                    echo CHtml::link(
                        '','#',array(
                            'title'=>Yii::t('app','Advanced Search'),
                            'class'=>'x2-button search-button'.$lastChildClass)
                        );
                    break;
                case 'map':
                    echo CHtml::link(
                        X2Html::fa('fa-map-marker').Yii::t('app', 'Map'),
                        array('/users/userMap'),
                        array('class'=>'x2-button', 'id'=>'map-link')
                    );
                    break;
                case 'clearFilters':
                    $url = array_merge(
                        array(Yii::app()->controller->action->id),
                        Yii::app()->controller->getActionParams(),
                        array('clearFilters'=>1)
                    );
                    echo CHtml::link(
                        '',$url,array('title'=>Yii::t('app','Clear Filters'),
                        'class'=>'fa fa-filter fa-lg x2-button filter-button'.$lastChildClass)
                    );
                    break;
                case 'columnSelector':
                    echo CHtml::link(
                        '','javascript:void(0);',array('title'=>Yii::t('app',
                        'Columns'),
                        'class'=>'fa fa-columns fa-lg column-selector-link x2-button'.
                            $lastChildClass)
                    );
                    break;
                case 'autoResize':
                    echo CHtml::link(
                        '','javascript:void(0);',
                        array(
                            'title'=>Yii::t('app','Auto-resize columns'),
                            'class'=>'fa fa-arrows-h fa-lg auto-resize-button x2-button'.
                                $lastChildClass)
                        );
                    break;
                case 'updateBouncedEmails':
                    $url = Yii::app()->controller->id. '/updateBouncedEmails';
                    echo CHtml::link(
                        '',$url,
                        array(
                            'title'=>Yii::t('app','Execute Bounce Handling Process'),
                            'class'=>'fa fa-envelope x2-button update-bounced-emails',
                            'id'=>'update-bounced-emails',
                            'href'=> $url)
                        );
                    break;
                case 'refresh':
                    echo CHtml::link(
                        '','javascript:void(0);',
                        array(
                            'title'=>Yii::t('app','Refresh grid'),
                            'class'=>'fa fa-refresh fa-lg refresh-button x2-button'.
                                $lastChildClass)
                        );
                    break;
                
                case 'exportLogins':
                    echo CHtml::link(
                        X2Html::fa('fa-share fa-lg').Yii::t('app', 'Export'),
                        array('/admin/exportLoginHistory'),
                        array('class'=>'x2-button', 'id'=>'login-history-export')
                    );
                    break;
                case 'exportFailedLogins':
                    echo CHtml::link(
                        X2Html::fa('fa-share fa-lg').Yii::t('app', 'Export'),
                        array('/admin/exportLoginHistory', 'type' => 'failed'),
                        array('class'=>'x2-button', 'id'=>'login-history-export')
                    );
                    break;
                
                case 'showHidden':
                    if(Yii::app()->user->checkAccess($this->_moduleName.'Admin')){
                        echo CHtml::link(
                            '','#',
                            array(
                                'title'=>Yii::t('app','Show Hidden'),
                                'class'=>
                                    (ArrayUtil::setAndTrue ($_GET, 'showHidden') ? 
                                        'clicked ' : '').
                                    'fa fa-eye-slash fa-lg x2-button show-hidden-button'.
                                    $lastChildClass
                            )
                        );
                    }
                    break;
                default:
                    echo $button;
            }
        }
        echo '</div>';
    }

    protected function renderContentBeforeHeader () {}

    /**
     * Creates column objects and initializes them. Overrides {@link parent::initColumns}, 
     * swapping hard coded reference to CDataColumn with the value of a public property.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
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
                if(!isset($column['class'])) {
                    /* x2modstart */ 
                    $column['class']=$this->dataColumnClass;
                    /* x2modend */ 
                }
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
