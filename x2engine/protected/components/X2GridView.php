<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * @package X2CRM.components
 */
class X2GridView extends CGridView {
    public $selectableRows = 0;

    private $_moduleName;
    private $_isAdmin;

    public $modelName;
    public $viewName;
    public $enableGvSettings = true;

    public $fullscreen = false;

    public $defaultGvSettings;
    // jquery for generating defaults:
    // var a=''; $('.x2-gridview tr:first th:not(:last)').each(function(i,elem){a+="\t\t'"+$(elem).attr("id").substr(2)+"' => "+$(elem).width()+",\n";}); a;

    public $specialColumns;
    public $excludedColumns;
    public $enableControls = false;
    public $enableTags = false;
    
    public $fixedHeader = false;

    public $summaryText;

    public $buttons = array();
    public $title;
    public $massActions = array ();
    
    // JS which will be executed before/after yiiGridView.update () updates the grid view
    private $afterGridViewUpdateJSString = ""; 
    private $beforeGridViewUpdateJSString = ""; 

    protected $allFields = array();
    protected $allFieldNames = array();
    protected $specialColumnNames = array();
    protected $gvSettings = null;
    protected $columnSelectorId;
    protected $columnSelectorHtml;

    protected $ajax = false;


    public static function getFilterHint() {

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

        return X2Info::hint($text,false);
    }

    public function getIsAdmin() {
        if(!isset($this->_isAdmin)) {
            $this->_isAdmin = (bool) Yii::app()->user->checkAccess(ucfirst($this->moduleName).'AdminAccess');
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
     *          - the three opening divs with the specified classes and ids are required. The divs 
     *            get closed after the grid header is printed.
     *     - the X2GridView propert fixedHeader must be set to true
     */
    public function setUpStickyHeader () {

        $makeHeaderStickyStr = "
            x2.gridviewStickyHeader.DEBUG && console.log ($('#".$this->id."').find ('.x2grid-body-container').find ('.x2grid-resizable').find ('tbody').find ('tr').length);

            if ($('#".$this->id."').find ('.x2grid-body-container').find ('.x2grid-resizable').
                find ('tbody').find ('tr').length <= 2 || x2.isIPad || x2.isAndroid) {

                x2.gridviewStickyHeader.DEBUG && console.log ('make sticky');
                x2.gridviewStickyHeader.makeSticky ();
            } else if (!$(x2.gridviewStickyHeader.titleContainer).
                hasClass ('x2-gridview-fixed-top-bar-outer')) {

                x2.gridviewStickyHeader.DEBUG && console.log ('make unsticky');
                x2.gridviewStickyHeader.makeUnsticky ();
            }

            x2.gridviewStickyHeader.DEBUG && console.log ('after grid update');
            if (!x2.gridviewStickyHeader.isStuck && 
                !x2.gridviewStickyHeader.checkX2GridViewHeaderSticky ()) {

                $(window).
                    unbind (
                        'scroll.stickyHeader',
                        x2.gridviewStickyHeader.checkX2GridViewHeaderSticky).
                    bind (
                        'scroll.stickyHeader', 
                        x2.gridviewStickyHeader.checkX2GridViewHeaderSticky);
            }
        ";

        $this->addToAfterAjaxUpdate ($makeHeaderStickyStr);

        Yii::app ()->clientScript->registerScript ('x2GridViewStickyHeaderVarInit', "
            x2.gridviewStickyHeader.headerContainer =
                $('#".$this->id."').find ('.x2grid-header-container');
            x2.gridviewStickyHeader.titleContainer = $('#x2-gridview-top-bar-outer');
            x2.gridviewStickyHeader.bodyContainer = 
                $('#".$this->id."').find ('.x2grid-body-container');
            x2.gridviewStickyHeader.pagerHeight = 
                $('#".$this->id."').find ('.pager').length ? 
                    $('#".$this->id."').find ('.pager').height () : 7;
            x2.gridviewStickyHeader.stickyHeaderHeight = 
                $(x2.gridviewStickyHeader.headerContainer).height () + 
                $(x2.gridviewStickyHeader.titleContainer).height ();
            x2.gridviewStickyHeader.x2TitleBarHeight = $('#header-inner').height ();

        ", CClientScript::POS_READY);

        Yii::app ()->clientScript->registerScript ('x2GridViewStickyHeader', "
            x2.gridviewStickyHeader = {};
            x2.gridviewStickyHeader.DEBUG = false;
            x2.gridviewStickyHeader.isStuck;

            x2.gridviewStickyHeader.makeSticky = function () {
                var bodyContainer = x2.gridviewStickyHeader.bodyContainer;
                var \$titleBar = 
                    $('#x2-gridview-top-bar-outer').removeClass ('x2-gridview-fixed-top-bar-outer')
                $(bodyContainer).find ('table').removeClass ('x2-gridview-body-with-fixed-header');

                $('.column-selector').addClass ('stuck');
                $('#more-drop-down-list').addClass ('stuck');
                x2.gridviewStickyHeader.isStuck = true;
            };

            x2.gridviewStickyHeader.makeUnsticky = function () {
                var bodyContainer = x2.gridviewStickyHeader.bodyContainer;
                var \$titleBar = 
                    $('#x2-gridview-top-bar-outer').addClass ('x2-gridview-fixed-top-bar-outer')
                $(bodyContainer).find ('table').addClass ('x2-gridview-body-with-fixed-header');

                $('.column-selector').removeClass ('stuck');
                $('#more-drop-down-list').removeClass ('stuck');
                x2.gridviewStickyHeader.isStuck = false;
            };

            /*
            Bound to window scroll event. Check if the grid header should be made sticky.
            */
            x2.gridviewStickyHeader.checkX2GridViewHeaderSticky = function () {
                var headerContainer = x2.gridviewStickyHeader.headerContainer;
                var titleContainer = x2.gridviewStickyHeader.titleContainer;
                var bodyContainer = x2.gridviewStickyHeader.bodyContainer;
                var pagerHeight = x2.gridviewStickyHeader.pagerHeight;
                var stickyHeaderHeight = x2.gridviewStickyHeader.stickyHeaderHeight;
                var x2TitleBarHeight = x2.gridviewStickyHeader.x2TitleBarHeight;

                // check if none of grid view body is visible
                if (($(bodyContainer).offset ().top + $(bodyContainer).height ()) - 
                    ($(window).scrollTop () + stickyHeaderHeight + x2TitleBarHeight + 5) < 0) {

                    //x2.gridviewStickyHeader.isStuck = true;
                    x2.gridviewStickyHeader.DEBUG && console.log ('sticky');

                    $(titleContainer).hide ();

                    /* unfix header */
                    //$(bodyContainer).hide ();
                    /*var \$titleBar = 
                        $('#x2-gridview-top-bar-outer').removeClass (
                            'x2-gridview-fixed-top-bar-outer')
                    \$titleBar.attr (
                        'style', 'margin-top: ' + 
                        (($(bodyContainer).height () - stickyHeaderHeight - pagerHeight) + 5) + 
                        'px');*/

                    // hide mass actions dropdown
                    /*if ($('#more-drop-down-list').length) {
                        if ($('#more-drop-down-list').is (':visible')) {
                            x2.gridviewStickyHeader.listWasVisible = true;
                            $('#more-drop-down-list').hide ();
                        } else {
                            x2.gridviewStickyHeader.listWasVisible = false;
                        }
                    }*/

                    if ($('.column-selector').length) {
                        if ($('.column-selector').is (':visible')) {
                            x2.gridviewStickyHeader.columnSelectorWasVisible = true;
                            $('.column-selector').hide ();
                        } else {
                            x2.gridviewStickyHeader.columnSelectorWasVisible = false;
                        }
                    }

                    $(window).
                        unbind (
                            'scroll.stickyHeader', 
                            x2.gridviewStickyHeader.checkX2GridViewHeaderSticky).
                        bind (
                            'scroll.stickyHeader', 
                            x2.gridviewStickyHeader.checkX2GridViewHeaderUnsticky);

                    x2.gridviewStickyHeader.cachedTitleContainerOffsetTop = 
                        $(titleContainer).offset ().top;
                } else {
                    return false;
                }
            };

            /*
            Bound to window scroll event. Check if the grid header should be made fixed.
            */
            x2.gridviewStickyHeader.checkX2GridViewHeaderUnsticky = function () {
                var titleContainer = x2.gridviewStickyHeader.titleContainer;
                var x2TitleBarHeight = x2.gridviewStickyHeader.x2TitleBarHeight;


                // check if grid header needs to be made unsticky
                if ((($(window).scrollTop () + x2TitleBarHeight) - 
                    x2.gridviewStickyHeader.cachedTitleContainerOffsetTop) < 20) {
                    //x2.gridviewStickyHeader.DEBUG && console.log ('unsticky');

                    $(titleContainer).show ();

                    /*var bodyContainer = x2.gridviewStickyHeader.bodyContainer;
                    x2.gridviewStickyHeader.isStuck = false;*/

                    /* fix header */ 
                    /*var \$titleBar = 
                        $('#x2-gridview-top-bar-outer').
                            addClass ('x2-gridview-fixed-top-bar-outer'); 
                    \$titleBar.attr ('style', '');
                    $(bodyContainer).show ();*/

                    //for (var i = 0; i < 1000; ++i) console.log (i);

                    // show mass actions dropdown
                    /*if (x2.gridviewStickyHeader.listWasVisible && 
                          $('#more-drop-down-list').length) {
                        $('#more-drop-down-list').show ();
                    }*/
                    if (x2.gridviewStickyHeader.columnSelectorWasVisible && 
                        $('.column-selector').length && 
                        $('.column-selector-link').hasClass ('clicked')) {

                        $('.column-selector').show ();
                    }

                    
                    $(window).
                        unbind (
                            'scroll.stickyHeader', 
                            x2.gridviewStickyHeader.checkX2GridViewHeaderUnsticky).
                        bind ('scroll.stickyHeader', 
                            x2.gridviewStickyHeader.checkX2GridViewHeaderSticky);
                }
            };
        ", CClientScript::POS_HEAD);
    }

    public function init() {
        $this->baseScriptUrl = Yii::app()->theme->getBaseUrl().'/css/gridview';

        if ($this->fixedHeader) $this->setUpStickyHeader ();

        $this->excludedColumns = empty($this->excludedColumns)?array():
            array_fill_keys($this->excludedColumns,1);
//        die(var_dump($this->excludedColumns));
        // $this->id is the rendered HTML element's ID, i.e. "contacts-grid"
        $this->ajax = isset($_GET['ajax']) && $_GET['ajax']===$this->id;
        if($this->ajax)
            ob_clean();

        // $this->selectionChanged = 'js:function() { console.debug($.fn.yiiGridView.getSelection("'.$this->id.'")); }';

        // if(empty($this->modelName))
            // $this->modelName = $this->getId();
        if(empty($this->viewName))
            $this->viewName = $this->modelName;
        if($this->modelName=='Quotes')
            $this->modelName='Quote';


        $this->columnSelectorId = $this->getId() . '-column-selector';

        // Get gridview settings by looking in the URL:
        if(isset($_GET['gvSettings']) && isset($_GET['viewName']) && 
            $_GET['viewName'] == $this->viewName) {

            $this->gvSettings = json_decode($_GET['gvSettings'],true);
            // unset($_GET['gvSettings']);
            // die(var_dump($this->gvSettings));

            ProfileChild::setGridviewSettings($this->gvSettings,$this->viewName);
        } else {
            $this->gvSettings = ProfileChild::getGridviewSettings($this->viewName);
        }
        // Use the hard-coded defaults (note: gvSettings has column name keys:
        if($this->gvSettings == null)
            $this->gvSettings = $this->defaultGvSettings;
        // die(var_dump($this->gvSettings));
        // die(var_dump(ProfileChild::getGridviewSettings($this->viewName)));

        // load names from $specialColumns into $specialColumnNames
        foreach($this->specialColumns as $columnName => &$columnData) {
            if(isset($columnData['header'])) {
                $this->specialColumnNames[$columnName] = $columnData['header'];
            } else {
                $this->specialColumnNames[$columnName] = 
                    X2Model::model($this->modelName)->getAttributeLabel($columnName);
            }
        }

        // start allFieldNames with the special fields
        if(!empty($this->specialColumnNames))
            $this->allFieldNames = $this->specialColumnNames;

        // add controls column if specified
        if($this->enableControls)
            $this->allFieldNames['gvControls'] = Yii::t('app','Tools');

        $this->allFieldNames['gvCheckbox'] = Yii::t('app', 'Checkbox');

        // load fields from DB
        // $fields=Fields::model()->findAllByAttributes(array('modelName'=>ucwords($this->modelName)));
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
            // var_dump($rolePermissions);

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

        // add tags column if specified
        if($this->enableTags)
            $this->allFieldNames['tags'] = Yii::t('app','Tags');

        foreach($this->allFields as $fieldName=>&$field) {
            $this->allFieldNames[$fieldName] = 
                X2Model::model($this->modelName)->getAttributeLabel($field->fieldName);
        }

        // update columns if user has submitted data
        if(isset($_GET['columns']) && isset($_GET['viewName']) && 
           $_GET['viewName'] == $this->viewName) { // has the user changed column visibility?

            foreach(array_keys($this->gvSettings) as $key) {
                // search $_GET['columns'] for the column
                $index = array_search($key,$_GET['columns']); 

                if($index === false) { // if it's not in there,
                    unset($this->gvSettings[$key]); // delete that junk
                } else { // othwerise, remove it from $_GET['columns']

                    // so the next part doesn't add it a second time
                    unset($_GET['columns'][$index]); 
                }
            }

            // now go through $allFieldNames and add any fields that
            foreach(array_keys($this->allFieldNames) as $key) { 
                if(!isset($this->gvSettings[$key]) && in_array($key,$_GET['columns'])) { 
                    // are present in $_GET['columns'] but not already in the list

                    $this->gvSettings[$key] = 80; // default width of 80
                }
            }
        }
        unset($_GET['columns']); // prevents columns data from ending up in sort/pagination links
        unset($_GET['viewName']);
        unset($_GET['gvSettings']);
/*
        // adding/removing columns changes the total width,
        // so let's scale the columns to match the correct total (590px)
        $totalWidth = array_sum(array_values($this->gvSettings));

        if($totalWidth > 0) {
            $widthFactor = (585 ) / $totalWidth; //- count($this->gvSettings)
            $sum = 0;
            $scaledSum = 0;
            foreach($this->gvSettings as $columnName => &$columnWidth) {
                $sum += $columnWidth;
                $columnWidth = round(($sum) * $widthFactor)-$scaledSum;        // map each point onto the nearest integer in the scaled space
                $scaledSum += $columnWidth;
            }
        } */
        // die(var_dump($this->gvSettings).' '.$this->viewName);
       
       // save the new Gridview Settings
        Profile::setGridviewSettings($this->gvSettings,$this->viewName); 

        // die(var_dump($this->gvSettings));

        $columns = array();

        $datePickerJs = '';

        foreach($this->gvSettings as $columnName => $width) {
            if($columnName=='gvControls' && !$this->enableControls){
                continue;
            }

            // $width = (!empty($width) && is_numeric($width))? 'width:'.$width.'px;' : null;    // make sure width is reasonable, then convert it to CSS

            // make sure width is reasonable
            $width = (!empty($width) && is_numeric($width))? $width : null;

            // $isDate = in_array($columnName,array('createDate','completeDate','lastUpdated','dueDate', 'expectedCloseDate', 'expirationDate', 'timestamp','lastactivity'));

            $isCurrency = in_array($columnName,array('annualRevenue','quoteAmount'));

            $lang = (Yii::app()->language == 'en')? '':Yii::app()->getLanguage();

            //if($isDate)
                //$datePickerJs .= ' $("#'.$columnName.'DatePicker").datepicker('
                    //.'$.extend({showMonthAfterYear:false}, {"dateFormat":"'.Formatter::formatDatePicker().'"})); ';
                    // .'{"showAnim":"fold","dateFormat":"yy-mm-dd","changeMonth":"true","showButtonPanel":"true","changeYear":"true","constrainInput":"false"}));';


            $newColumn = array();

            if(array_key_exists($columnName,$this->specialColumnNames)) {

                $newColumn = $this->specialColumns[$columnName];
                // $newColumn['name'] = 'lastName';
                $newColumn['id'] = 'C_'.$columnName;
                // $newColumn['header'] = Yii::t('contacts','Name');
                $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');
                // $newColumn['value'] = 'CHtml::link($data->firstName." ".$data->lastName,array("view","id"=>$data->id))';
                // $newColumn['type'] = 'raw';
                // die(print_r($newColumn));
                $columns[] = $newColumn;

            } else if((array_key_exists($columnName, $this->allFields))) { // && $this->allFields[$columnName]->visible == 1)) {

                $newColumn['name'] = $columnName;
                $newColumn['id'] = 'C_'.$columnName;
                $newColumn['header'] = 
                    X2Model::model($this->modelName)->getAttributeLabel($columnName);
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
                    $newColumn['value'] = '!is_numeric($data["'.$columnName.'"])?$data["'.
                        $columnName.'"]:X2Model::getModelLink($data["'.$columnName.
                        '"],X2Model::getModelName("'.$this->allFields[$columnName]->linkType.'"))';
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


                if(Yii::app()->language == 'en') {
                    $format =  "M d, yy";
                } else {

                    // translate Yii date format to jquery
                    $format = Yii::app()->locale->getDateFormat('medium'); 

                    $format = str_replace('yy', 'y', $format);
                    $format = str_replace('MM', 'mm', $format);
                    $format = str_replace('M','m', $format);
                }

                $columns[] = $newColumn;

            } else if($columnName == 'gvControls') {
                $newColumn['id'] = 'C_gvControls';
                $newColumn['class'] = 'CButtonColumn';
                $newColumn['header'] = Yii::t('app','Tools');
                $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');
                if(!$this->isAdmin)
                    $newColumn['template'] = '{view}{update}';

                $columns[] = $newColumn;

            } else if($columnName == 'tags') {
                $newColumn['id'] = 'C_'.'tags';
                // $newColumn['class'] = 'CDataColumn';
                $newColumn['header'] = Yii::t('app','Tags');
                $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');
                $newColumn['value'] = 'Tags::getTagLinks("'.$this->modelName.'",$data->id,2)';
                $newColumn['type'] = 'raw';
                $newColumn['filter'] = CHtml::textField(
                    'tagField',isset($_GET['tagField'])? $_GET['tagField'] : '');

                $columns[] = $newColumn;
            } else if ($columnName == 'gvCheckbox') {
                $newColumn['id'] = 'C_gvCheckbox';
                $newColumn['class'] = 'CCheckBoxColumn';
                $newColumn['selectableRows'] = 2;
                $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');

                $columns[] = $newColumn;
            }
        }
        $columns[] = array('value'=>'','header'=>''/* ,'headerHtmlOptions'=>array('style'=>'width:0px;') */);        // one blank column for the resizing widget

        $this->columns = $columns;


        natcasesort($this->allFieldNames); // sort column names

        // generate column selector HTML
        $this->columnSelectorHtml = CHtml::beginForm(array('/site/saveGvSettings'),'get')
            .'<ul class="column-selector" id="'.$this->columnSelectorId.'">';
        foreach($this->allFieldNames as $fieldName=>&$attributeLabel) {
            $selected = array_key_exists($fieldName,$this->gvSettings);
            $this->columnSelectorHtml .= "<li>"
            .CHtml::checkbox('columns[]',$selected,array('id'=>$fieldName.'_checkbox','value'=>$fieldName))
            .CHtml::label($attributeLabel,$fieldName.'_checkbox')
            ."</li>";
        }
        $this->columnSelectorHtml .= '</ul></form>';
        // Yii::app()->clientScript->renderBodyBegin($columnHtml);
        // Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId().'_columnSelector',
        // "$('#".$this->getId()." table').after('".addcslashes($columnHtml,"'")."');

        // ",CClientScript::POS_READY);
        $themeURL = Yii::app()->theme->getBaseUrl();

        Yii::app()->clientScript->registerScript('logos',base64_decode(
            'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
            .'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
            .'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));

        // add a dropdown to the summary text that let's user set how many rows to show on each page
        $this->summaryText = Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>')

            // . '<div class="form no-border" style="display: block;padding-top: 5px;margin: 0 0 0 5px;float: right;">
                // <a class="x2-button" style="padding:0 15px;">&lt;</a>
                // <a class="x2-button" style="padding:0 15px;">&gt;</a>
            // </div>'

            . '<div class="form no-border" style="display:inline;"> '
            . CHtml::dropDownList(
                'resultsPerPage', Profile::getResultsPerPage(), 
                Profile::getPossibleResultsPerPage(), array(
                    'ajax' => array(
                        'url' => $this->controller->createUrl('/profile/setResultsPerPage'),
                        'complete' => "function(response) {
                            ".$this->beforeGridViewUpdateJSString."
                            \$.fn.yiiGridView.update('{$this->id}', {" .
                                (isset($this->modelName) ? 
                                    "data: {'{$this->modelName}_page': 1}," : "") . 
                                    "complete: function () {".$this->afterGridViewUpdateJSString .
                                    "}
                            });
                        }",
                        'data' => 'js: {results: $(this).val()}',
                    )
                ))
            . ' </div>';
            // . Yii::t('app', ' results per page');



        // $this->afterAjaxUpdate = 'function(id, data) { '.$datePickerJs.' }';
        // if(!empty($this->afterAjaxUpdate))
            // $this->afterAjaxUpdate = "var callback = ".$this->afterAjaxUpdate."; 
        // if(typeof callback == 'function') callback();";

        /* $this->afterAjaxUpdate = " function(id,data) { 
            ".$this->afterAjaxUpdate." ".$datePickerJs;*/

        // if($this->enableGvSettings) {
            // $this->afterAjaxUpdate.="
            // $('#".$this->getId()." table').gvSettings({
                // viewName:'".$this->viewName."',
                // columnSelectorId:'".$this->columnSelectorId."',
                // ajaxUpdate:true
            // });";
        // }
        // $this->afterAjaxUpdate .= " } ";
        $this->addToAfterAjaxUpdate ("if(typeof(refreshQtip) == 'function') { refreshQtip(); }");

        if(isset(Yii::app()->controller->module) && 
           Yii::app()->controller->module->id=='contacts'){
            /* after user moves to a different page, make sure the tool tips get added to the 
            newly showing rows */
            $this->addToAfterAjaxUpdate ('
                    $(".qtip-hint").qtip({content:false}); 
                    $(".x2-button-group").next (".x2-hint").qtip ();
            ');
        }
        parent::init();
    }

    public function getAfterAjaxUpdateStr () {
        return $this->afterGridViewUpdateJSString;
    }

    public function getBeforeAjaxUpdateStr () {
        return $this->beforeGridViewUpdateJSString;
    }

    public function addToAfterAjaxUpdate ($str) {
        $this->afterGridViewUpdateJSString .= $str; 
        $this->afterAjaxUpdate = 
            'js: function(id, data) {'.
                $this->afterGridViewUpdateJSString.
            '}';
    }

    public function addToBeforeAjaxUpdate ($str) {
        $this->beforeGridViewUpdateJSString .= $str; 
        $this->beforeAjaxUpdate = 
            'js: function(id, data) {'.
                $this->beforeGridViewUpdateJSString;
            '}';
    }

    public function run() {
        $this->registerClientScript();

        /* give this a special class so the javascript can tell it apart from the regular, lame 
        gridviews */
        if(!isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = '';
        $this->htmlOptions['class'] .= ' x2-gridview';
        if($this->fullscreen)
            $this->htmlOptions['class'] .= ' fullscreen';

        echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

        $this->renderContent();
        $this->renderKeys();


        if($this->ajax) {
            // remove any external JS and CSS files
            Yii::app()->clientScript->scriptMap['*.js'] = false;
            Yii::app()->clientScript->scriptMap['*.css'] = false;

            /* remove JS for gridview checkboxes and delete buttons (these events use jQuery.on() 
            and shouldn't be reapplied) */
            Yii::app()->clientScript->registerScript('CButtonColumn#C_gvControls',null);
            Yii::app()->clientScript->registerScript('CCheckBoxColumn#C_gvCheckbox',null);

            $output = '';
            Yii::app()->getClientScript()->renderBodyEnd($output);
            echo $output;

            echo CHtml::closeTag($this->tagName);
            ob_flush();


            Yii::app()->end();;
        }
        echo CHtml::closeTag($this->tagName);

        Yii::app ()->clientScript->registerScript (
            'gridAfterRender', $this->afterGridViewUpdateJSString, CClientScript::POS_READY);

    }



    /**
    * Renders the data items for the grid view.
    */
     public function renderItems() {
        if($this->dataProvider->getItemCount() > 0 || $this->showTableOnEmpty) {
            if($this->enableGvSettings) {
                if ($this->fixedHeader) echo '</div>';
                echo '<div class="x2grid-header-container">';
            }

            echo '<table class="',$this->itemsCssClass,'">';
            $this->renderTableHeader();

            if($this->enableGvSettings) {
                echo '</table></div>';
                if ($this->fixedHeader) echo '</div></div>';
                echo '<div class="x2grid-body-container"><table class="'.
                    $this->itemsCssClass.
                    ($this->fixedHeader ? ' x2-gridview-body-with-fixed-header' : '')."\">\n";
            }

            ob_start();
            $this->renderTableBody();
            $body = ob_get_clean();
            $this->renderTableFooter();
            echo $body; // TFOOT must appear before TBODY according to the standard.
            echo '</table>';

            if($this->enableGvSettings)
                echo '</div>';
        } else {
            $this->renderEmptyText();
        }

        echo "<div id='x2-gridview-updating-anim' style='display: none;' class='x2-loading-icon'>".
             "</div>";
    }

    /**
     * Override of {@link CGridView::registerClientScript()}.
     *
     * Adds scripts essential to modifying the gridview (and saving its configuration).
     */
    public function registerClientScript() {
        parent::registerClientScript();
        // die('taco bell');
        if($this->enableGvSettings) {
            if(!$this->ajax) {
                Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().
                    '/js/x2gridview.js');
            }

            Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId().'_gvSettings',
            "$('#".$this->getId()."').gvSettings({
                viewName:'".$this->viewName."',
                columnSelectorId:'".$this->columnSelectorId."',
                columnSelectorHtml:'".addcslashes($this->columnSelectorHtml,"'")."',
                ajaxUpdate:".($this->ajax?'true':'false')."
            });",CClientScript::POS_READY);
        }
    }

    /**
     * Echoes the markup for the gridview's table header.
     */
    public function renderTableHeader() {
        if(!$this->hideHeader) {
            // echo "<colgroup>";

            $sortDirections = $this->dataProvider->getSort()->getDirections();
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
                // echo ($column->headerHtmlOptions['colWidth'] === 0)? '<col>' : '<col style="width:'.$column->headerHtmlOptions['colWidth'].'px">';

                // $column->headerHtmlOptions['colWidth'] = null;
            }
            // echo "</colgroup>\n";
            echo "<thead>\n";

            if($this->filterPosition===self::FILTER_POS_HEADER)
                $this->renderFilter();

            echo "<tr>\n";
            foreach($this->columns as $column)
                $column->renderHeaderCell();
            echo "</tr>\n";

            if($this->filterPosition===self::FILTER_POS_BODY)
                $this->renderFilter();

            echo "</thead>\n";
        } else if($this->filter!==null && 
            ($this->filterPosition===self::FILTER_POS_HEADER || 
             $this->filterPosition===self::FILTER_POS_BODY)) {
            // echo "<colgroup>";
            // foreach($this->columns as $column) {
                // echo '<col width="'.$column->headerHtmlOptions['colWidth'].'">';
                // // $column->id = null;
                // $column->headerHtmlOptions['colWidth'] = null;
            // }
            // echo "</colgroup>\n";


            echo "<thead>\n";
            $this->renderFilter();
            echo "</thead>\n";
        }
    }
    public function renderTitle() {
        if(!empty($this->title))
            echo '<h2>',$this->title,'</h2>';
    }

    

    public function renderButtons() {
        if(0 === $count = count($this->buttons))
            return;
        if($count > 1)
            echo '<div class="x2-button-group">';
        //foreach($this->buttons as &$button) {
        $lastChildClass = '';
        for ($i = 0; $i < $count; ++$i) {//$this->buttons as &$button) {
            $button = $this->buttons[$i];
            if ($i === $count - 1) $lastChildClass = ' x2-last-child';
            switch($button) {
                case 'advancedSearch':
                    break; // remove until fixed
                    echo CHtml::link(
                        '<span></span>','#',array(
                            'title'=>Yii::t('app','Advanced Search'),
                            'class'=>'x2-button search-button'.$lastChildClass)
                        );
                    break;
                case 'clearFilters':
                    $url = array_merge(
                        array(Yii::app()->controller->action->id),
                        Yii::app()->controller->getActionParams(),
                        array('clearFilters'=>1)
                    );
                    echo CHtml::link(
                        '<span></span>',$url,array('title'=>Yii::t('app','Clear Filters'),
                        'class'=>'x2-button filter-button'.$lastChildClass)
                    );
                    break;
                case 'columnSelector':
                    echo CHtml::link(
                        '<span></span>','javascript:void(0);',array('title'=>Yii::t('app',
                        'Columns'),'class'=>'column-selector-link x2-button'.$lastChildClass)
                    );
                    break;
                case 'autoResize':
                    echo CHtml::link(
                        '<span></span>','javascript:void(0);',
                        array(
                            'title'=>Yii::t('app','Auto-Resize Columns'),
                            'class'=>'auto-resize-button x2-button'.$lastChildClass)
                        );
                    break;
                default:
                    echo $button;
            }
        }
        if($count > 1)
            echo '</div>';
    }

    public function renderFilterHint() {
        echo X2GridView::getFilterHint();
    }

    public function renderTopPager () {
        $this->controller->renderPartial (
            'application.components.views._x2GridViewTopPager', array (
                'gridId' => $this->id,
                'modelName' => $this->modelName,
                'gridObj' => $this
            )
        );
    }

    /**
     * Display mass actions ui buttons in top bar and set up related JS 
     */
    public function renderMassActionButtons () {
        $this->controller->renderPartial (
            'application.components.views._x2GridViewMassActionButtons', array (
                'UIType' => 'buttons',
                'massActions' => $this->massActions,
                'gridId' => $this->id,
                'modelName' => $this->modelName,
                'gridObj' => $this
            )
        );
    }

    /***********************************************************************
    * Protected instace methods
    ***********************************************************************/

    /**
     * Creates column objects and initializes them.
     */
    protected function initColumns()
    {
        if($this->columns===array())
        {
            if($this->dataProvider instanceof CActiveDataProvider)
                $this->columns=$this->dataProvider->model->attributeNames();
            elseif($this->dataProvider instanceof IDataProvider)
            {
                // use the keys of the first row of data as the default columns
                $data=$this->dataProvider->getData();
                if(isset($data[0]) && is_array($data[0]))
                    $this->columns=array_keys($data[0]);
            }
        }
        $id=$this->getId();
        foreach($this->columns as $i=>$column)
        {
            if(is_string($column))
                $column=$this->createDataColumn($column);
            else
            {
                if(!isset($column['class']))
                    $column['class']='X2DataColumn';
                $column=Yii::createComponent($column, $this);
            }
            if(!$column->visible)
            {
                unset($this->columns[$i]);
                continue;
            }
            if($column->id===null)
                $column->id=$id.'_c'.$i;
            $this->columns[$i]=$column;
        }

        foreach($this->columns as $column)
            $column->init();
    }

    public function setModuleName($value) {
        $this->_moduleName = $value;
    }
}
?>
