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




/**
 * @package application.components.sortableWidget.dataWidget
 * Charts Widget class.
 * This class is responsible for taking the chart and report settings,
 * fetching the appropriate data from a {@link X2Report} getData() function,
 * and passing the data and widget settings to a javascript class.
 * @author Alex Rowe <alex@x2engine.com> 
 */
class DataWidget extends SortableWidget {

    /**
     * @see SortableWidget::$_JSONPropertiesStructure
     */
    public static $createByDefault = false;

    private static $_JSONPropertiesStructure;

    /**
     * @var mixed Overrides for sortable widget parameters in {@link SortableWidget}
     */
    public $canBeDeleted = true;
    public $defaultTitle = 'DataWidget';
    public $relabelingEnabled = true;
    public $widgetType = 'data';
    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}{reportButton}</div>{configBar}{widgetContents}{inlineReportContainer}';

    /**
     * @var boolean indicates whether this chart is on the reports page
     */
    public $onReport = false;

    protected $_sharedViewFile = 'dataWidget';

    public function behaviors () {
        $behaviors = parent::behaviors ();
        if (Yii::app()->params->isMobileApp) {
            $behaviors['MobileDataWidgetBehavior'] = array(
                'class' => 
                    'application.modules.mobile.components.behaviors.'.
                        'MobileDataWidgetBehavior');
        }
        return $behaviors;
    }

    /**
     * Initializes the widget
     */
    public function init() {
        if (!$this->chart) {
            return false;
        }
        // If the layout is contained in something other than profile,
        // this widget is on a report page
        $this->onReport = (get_class ($this->profile) != 'Profile');
        parent::init ();
    }

    public function run() {
        if (!$this->chart) {
            return false;
        }
        parent::run ();
    }

    /**
     * @see SortableWidget::getJSONPropertiesStructure()
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Data Widget', // Default name
                    'chartId' => null, // ID of the chart model
                    'displayType' => null, // The current type of chart to display (bar, pie, etc)
                    'legend' => null, // The current items hidden on the legend
                    'height' => false, // The current items hidden on the legend
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    protected static function getColumnIndex ($columns) {
        // Reports disallows duplicate columns, so we create an index mapping the chart column 
        // indices to the report data indices
        if (count (array_unique ($columns)) < count ($columns)) {
            $columnIndex = array ();
            $found = array ();
            $j = 0;
            foreach ($columns as $i => $col) {
                if (isset ($found[$col])) {
                    $columnIndex[$i] = $found[$col];
                } else {
                    $columnIndex[$i] = $j++;
                    $found[$col] = $i;
                }
            }
            unset ($found);
        } else {
            // mapping is one-to-one
            $columnIndex = array_keys ($columns);
        }
        return $columnIndex;
    }

    /**
     * Calls the approriate static methid based on the report type of this chart
     * @param array $settings the settings to render for this chart
     * @return array The array of all data the chart needs
     */
    public static function getChartData ($settings) {
        $chart = X2Model::model('Charts')->findByPk( $settings['chartId'] );

        // IE is NOT supported
        if (AuxLib::isIE8()) {
            return self::error ('ie8');
        }

        // This error should never be displayed, as the chartDashboard should catch this
        if (!$chart) {
            return self::error ('nochart');
        }

        $report = $chart->report;

        // check if report exists
        if( !$report )
            return self::error ('noreport');

        /** 
        * Calls a function specific to the Report Type
        * - getRowsAndColumnsData()
        * - getSummationData()
        * - getGridData()
        */
        $type = $report->type; 
        $getReportData = 'get'.ucfirst($type).'Data';

        $widgetClass = get_called_class();

        return $widgetClass::$getReportData($settings, $chart);

    }

    /**
     * Overridable function providing specific instructions how to take
     * data given by the Javascript class and format it into a filter
     * that a report can take. 
     * This function is the bridge to the drill-down functionality. 
     * @param  array $settings   Array of chart settings
     * @param  array $conditions Array of conditions to be made into filters
     * @return array             Array of filters that a report can read
     */
    public static function formatConditions ($settings, $conditions) {
        return array();
    }

    /**
     * Error array for possible errors the chart might run into
     * These errors are meant to be returned in the getData function 
     * If there is a problem. They will be rendered instead of the chart.
     * @param string $key key for predefined error message
     */
    public static function error ($key='general', $message=null) {
        $errors = array(
            'general' => Yii::t('charts', 'Something went wrong, sorry.'),
            'missingColumn' => 
                Yii::t('charts', "Report column could not be found. Did you remove a column?"),
            'noreport' => Yii::t('charts', 'Report could not be found'),
            'nochart' => Yii::t('charts', 'Chart object could not be found'),
            'ie8' => Yii::t('charts', 'Charts are not supported in Internet Explorer 8, sorry!')
        );

        if (!isset($errors[$key])) {
            $key='general';
        }

        if (!$message) {
            $message = $errors[$key];
        }

        return array('error' => $errors[$key]);
    }

    /**
     * Magic getter helpers
     */
    public function __get($name) {
        if ($name == 'settings') {
            return self::getJSONProperties (
                $this->profile, $this->widgetType, $this->widgetUID);
        }

        else if ($name == 'data') {
            return self::getChartData($this->settings);
        }

        else if ($name == 'chart') {
            return X2Model::model ('charts')->findByPk ($this->settings['chartId']);
        }

        return parent::__get($name);
    }

    /**
     * Constructs an array to pass to the javascript class
     * While SortableWidget does not pass all widget settings
     * to the front-end, DataWidget does.
     * Therefore anything in {@link getJSONPropertiesStructure} is 
     * packed into this array.
     * @see SortableWidget::getJSSortableWidgetParams()
     */
    protected function getJSSortableWidgetParams () {
        // If on a report page we need the report id
        $reportId = null;
        $modelName = null;
        if ($this->onReport) {
            $reportId = $this->chart->report->id;
            $modelName = 'Reports';
        }

        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge(
                parent::getJSSortableWidgetParams(), 
                array (
                    'chartData' => $this->data,
                    'locale' => Yii::app()->locale->id,
                    'settingsModelId' => $reportId,
                    'settingsModelName' => $modelName,
                ),
                $this->settings );
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * @see SortableWidget::getSetupScript()
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $this->_setupScript = "
                $(function () {
                    x2.$widgetClass{$this->widgetUID} = new x2.$widgetClass(
                        ".CJSON::encode( $this->getJSSortableWidgetParams() )."
                    );
                    
                    if (typeof x2.dataWidgetManager !== 'undefined') {
                        x2.dataWidgetManager.widgetList.push (x2.$widgetClass{$this->widgetUID});
                    }
                });
            ";
        }
        return $this->_setupScript;
    }

    /**
     * @see SortableWidget::getViewFileParams()
     */
    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'widgetUID' => $this->widgetUID,
                )
            );
        }
        return $this->_viewFileParams;
    } 

    /**
     * @see SortableWidget::getPackages()
     */
    public function getPackages () {
        $widgetClass = get_called_class();

        /*********************************
        * IE8 Package modifications
        ********************************/
        $IEjs = array();
        $depends = array();
        if (AuxLib::getIEVer() < 9) {
           $depends = array('aight');
           $IEjs = array(
               'aight' => array(
                   'baseUrl' => Yii::app()->request->baseUrl,
                   'js' => array('js/lib/aight/aight.js'),
                   'depends' => array('jquery')
               ),
               'aightd3' => array(
                   'baseUrl' => Yii::app()->request->baseUrl,
                   'js' => array('js/lib/aight/aight.d3.js'),
                   'depends' => array('d3')
               ),
               'd3' => array(
                   'baseUrl' => Yii::app()->request->baseUrl,
                   'js' => array(
                       'js/d3/d3.js',
                   ),
                   'depends' => array('aight')
               ),
           );
        }

        /*********************************
        * Retrieves packages for dataWidget
        * as well as child class-specific packages
        ********************************/
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'd3' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/d3/d3.js',
                        ),
                    ),
                    'c3' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/c3/c3.min.js',
                        ),
                        'css' => array(
                            'js/c3/c3.css'
                        ),
                        'depends' => array('d3')
                    ),
                    'DataWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/DataWidget/DataWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS', 'c3')
                    ),
                    'DataWidgetCSS' => array(
                        'baseUrl' => Yii::app()->theme->baseUrl,
                        'css' => array(
                            'css/components/DataWidget/DataWidget.css'
                        ),
                    ),
                    $widgetClass.'JS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/DataWidget/'.$widgetClass.'.js',
                        ),
                        'depends' => array ('DataWidgetJS', 'c3')
                    ),

                ),
                $IEjs
            );

        }
        return $this->_packages;
    }

    /**
     * Template Function. 
     * @see SortableWidget::renderWidget()
     */
    public function renderReportButton() {
        // On the report page this is the add to dashboard button
        if ($this->onReport) {
            $this->renderAddToDashboardButton ();
            return;
        }

        $id  = $this->chart->report->id;
        $text = Yii::t('charts', 'Go to Report');
        $link = Yii::app()->createUrl('/reports', array(
            'id' => $id
        ));

        echo CHtml::link($text, $link, array (
            'class' => 'x2-button go-to-report'
        ));
    }

    /**
     * Renders the add to dashboard button on the chart title
     * @see SortableWidget::renderWidget()
     */
    public function renderAddToDashboardButton (){
        $text = Yii::t('charts', 'Add to Dashboard');
        $link = '';

        echo CHtml::link($text, $link, array (
            'class' => 'x2-button add-to-dashboard'
        ));

        $list = array(
            array(
                'id' => 'add-to-charts',
                'content' => Yii::t('charts', 'Add to Charting Dashboard')
            ),
            array(
                'id' => 'add-to-profile',
                'content' => Yii::t('charts', 'Add to Profile Dashboard')
            )
        );

        echo X2Html::popUpDropDown ($list, array('class' => 'add-to-dashboard-dropdown')); 
    }

    /**
     * Renders the container for an inline report
     * will not render if there is no function 'renderInlineReport'
     */
    public function renderInlineReportContainer () {
        if (!method_exists($this, 'renderInlineReport')) {
            return;
        }
        $this->render('application.components.sortableWidget.views._inlineReport');
    }
    

    /**
     * @see SortableWidget::getTranslations()
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (
                parent::getTranslations (),
                array (
                    'addedToDashboard' => Yii::t('charts', 'Added to Dashboard')
                )
            );
        }
        return $this->_translations;
    }

    /**
     * @see SortableWidget::getSettingsMenuContentEntries()
     */
    protected function getSettingsMenuContentEntries(){
        return parent::getSettingsMenuContentEntries().
            '<li class="edit-widget-button">'.
                Yii::t('app', 'Edit Widget').
            '</li>';
    }
    /**
     * Renders the config bar at the top of the widget
     */
    protected function renderConfigBar() {
        echo "<div class='config-bar' >";

        foreach($this->configBarItems() as $item => $options) {
            $options['class'] .= ' config-bar-item';
            if (empty($options['content'])) {
                $options['content'] = ' ';
            }
            echo CHtml::tag('span', $options, $options['content']);

        }
        
        echo "<div class='clear' ></div>";
        echo "</div>";
    }

    /**
     * Supplies the items in for {@link renderConfigBar}
     * Each entry is an array of HTML options with an extra 'content' key
     * that will render as the content inisde the tag
     * @return array The list of items
     */
    protected function configBarItems() {
        return array(
            'delete' => array( 
                'id' => 'delete',
                'class' => 'fa fa-trash right',
                'title' => Yii::t('charts', 'Delete Chart') ),
            'relabel' => array( 
                'id' => 'relabel',
                'class' => 'fa fa-edit right',
                'title' => Yii::t('charts', 'Rename Chart') ),
            'clone' => array( 
                'id' => 'clone',
                'class' => 'fa fa-copy right',
                'title' => Yii::t('charts', 'Clone Chart') ),
            'print' => array( 
                'id' => 'print',
                'class' => 'fa fa-print right',
                'title' => Yii::t('charts', 'Export Chart') ),
        );
    }

}

?>
