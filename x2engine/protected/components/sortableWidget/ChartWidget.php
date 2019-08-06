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




 Yii::import ('application.components.sortableWidget.SortableWidget');

/**
 * @package application.components.sortableWidget
 */
abstract class ChartWidget extends SortableWidget {

	const SECPERDAY = 86400;
	const SECPERWEEK = 604800;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{chartSubtypeSelector}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    /**
     * @var string the type of chart (e.g. 'eventsChart', 'usersChart')
     */
    public $chartType;

    public $viewFile = '_chartWidget';

    protected $containerClass = 'sortable-widget-container x2-layout-island sortable-chart-widget';

    protected $_translations;

    private static $_JSONPropertiesStructure;

    private $_chartSettings;

    public function getChartSettings () {
        if (!isset ($this->_chartSettings)) {
            $this->_chartSettings = array_merge (
                array_filter (self::getJSONProperty (
                    $this->profile, 'chartSettings', $this->widgetType, $this->widgetUID),
                    function ($setting) {
                        return $setting !== null;
                    }),
                array (
                    'chartIsShown' => self::getJSONProperty (
                        $this->profile, 'minimized', $this->widgetType, 
                        $this->widgetUID),
                )
            );
        }
        return $this->_chartSettings;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'suppressDateRangeSelector' => false,
                    'chartSubtype' => self::getJSONProperty (
                        $this->profile, 'chartSubtype', $this->widgetType, $this->widgetUID),
                    'widgetUID' => $this->widgetUID
                )
            );
        }
        return $this->_viewFileParams;
    } 

    /**
     * overrides parent method
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'chartSubtype' => 'line', 
                    'chartSettings' => array (),
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * @param string $settingName
     */
    public function getChartSetting ($settingName) {
        $chartSettings = self::getJSONProperty (
            $this->profile, 'chartSettings', $this->widgetType, $this->widgetUID);

        if (in_array ($settingName, array_keys ($chartSettings))) {
            return $chartSettings[$settingName];
        } else {
            throw new CException (Yii::t('app', 'Invalid chart setting name.'));
        }
    }

    /**
     * overrides parent method. A sub prototype of SortableWidget.js is instantiated.
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $this->_setupScript = "
                $(function () {
                    x2.".$widgetClass.$this->widgetUID." = new ChartWidget (".
                        CJSON::encode ($this->getJSSortableWidgetParams ()).
                    ");
                });
            ";
        }
        return $this->_setupScript;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'ChartWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/jqplot/jquery.jqplot.js',
                            'js/jqplot/plugins/jqplot.pieRenderer.js',
                            'js/jqplot/plugins/jqplot.categoryAxisRenderer.js',
                            'js/jqplot/plugins/jqplot.pointLabels.js',
                            'js/jqplot/plugins/jqplot.dateAxisRenderer.js',
                            'js/jqplot/plugins/jqplot.highlighter.js',
                            'js/jqplot/plugins/jqplot.enhancedLegendRenderer.js',
                            'js/lib/moment-with-locales.min.js',
                            'js/sortableWidgets/ChartWidget.js',
                            'js/X2Chart/X2Chart.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                    'ChartWidgetCss' => array(
                        'baseUrl' => Yii::app()->getTheme ()->getBaseUrl (),
                        'css' => array(
                            'css/x2chart.css',
                        )
                    ),
                    'ChartWidgetCssExt' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'css' => array(
                            'js/jqplot/jquery.jqplot.css',
                        ),
                    ),
                )
            );
            if (AuxLib::isIE8 ()) {
                $this->_packages['ChartWidgetJS']['js'][] = 'js/jqplot/excanvas.js';
            }
        }
        return $this->_packages;
    }

    /**
     * Render the chart subtype selector
     */
    public function renderChartSubtypeSelector () {
        $subtype = self::getJSONProperty (
            $this->profile, 'chartSubtype', $this->widgetType, $this->widgetUID);

        echo 
            "<select class='x2-minimal-select chart-subtype-selector'>
                <option ".($subtype === 'line' ? 'selected="selected" ' : '')."value='line'>".
                    Yii::t('app', 'Line Chart').
                "</option>
                <option ".($subtype === 'pie' ? 'selected="selected" ' : '')."value='pie'>".
                    Yii::t('app', 'Pie Chart').
                "</option>
            </select>";
    }

    /**
     * Magic getter.
     */
    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge (
                parent::getJSSortableWidgetParams (), array (
                    'chartType' => $this->chartType,
                ));
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $longMonthNames = Yii::app()->getLocale ()->getMonthNames ();
            $shortMonthNames = Yii::app()->getLocale ()->getMonthNames ('abbreviated');

            $translations = array (
                'Create' => Yii::t('app','Create'),
                'Cancel' => Yii::t('app','Cancel'),
                'Create Chart Setting' => Yii::t('app','Create Chart Setting'),
                'Check all' => Yii::t('app','Check all'),
                'Uncheck all' => Yii::t('app','Uncheck all'),
                'metric(s) selected' => Yii::t('app','metric(s) selected')
            );

            $englishMonthNames =
                array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
                'September', 'October', 'November', 'December');
            $englishMonthAbbrs =
                array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                'Dec');

            foreach ($longMonthNames as $key=>$val) {
                $translations[$englishMonthNames[$key - 1]] = $val;
            }
            foreach ($shortMonthNames as $key=>$val) {
                $translations[$englishMonthAbbrs[$key - 1]] = $val;
            }

            $this->_translations = array_merge (
                parent::getTranslations (),
                $translations
            );
        }
        return $this->_translations;
    }

    /**
     * overrides parent method. Returns chart specific css
     */
    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array_merge (
                parent::getCss (),
                array (
                'sortableWidgetChartCss' => "
                    .sortable-widget-container .chart-subtype-selector {
	                    margin: 1px 0px 4px 5px;
                        border: 1px solid #ddd;
                    }

                    .sortable-widget-container div.chart-container {
                        -moz-border-radius: 0px !important;
                        -o-border-radius: 0px !important;
                        -webkit-border-radius: 0px !important;
                        border-radius: 0px !important;
                    }

                    .sortable-chart-widget .chart-controls-container {
                        width: 604px;
                        padding: 3px;
                    }

                    .sortable-chart-widget .chart-widget-button-container .relabel-widget-button {
                        margin-right: 5px;
                    }

                    @media (max-width: 684px) {
                        .sortable-chart-widget .chart-controls-container {
                            width: 95%;
                            padding: 3px;
                        }
                        .sortable-chart-widget .popup-dropdown-menu.flipped:before {
                            right: 52px;
                        }
                        .sortable-chart-widget .popup-dropdown-menu {
                            left: 0 !important;
                            right: 0!important;
                            margin: auto;
                        }
                    }

                    @media (max-width: 529px) {
                        .sortable-chart-widget .chart-container .bin-size-button-set {
                            margin-top: 6px;
                        }
                    }

                    /* menu contents */
                    @media (max-width: 500px) {
                        .sortable-chart-widget .chart-container .chart-filters-container {
                            height: auto;
                        }
                        .sortable-chart-widget .ui-multiselect {
                            margin-top: 0 !important;
                        }
                    }
                ")
            );
        }
        return $this->_css;
    }

    
    /**
     * Returns an array containing a start and end timestamp.
     * If a date range cookie is set, the timestamps get generated. Otherwise start and
     * end timestamp cookies are used. Specified default timestamps will be used when
     * cookies are not set.
     */
	protected function getStartEndTimestamp ($defaultStartTs, $defaultEndTs) {
		$startDate;
		$endDate;
		if ($this->getChartSetting ('dateRange') !== null &&
			$this->getChartSetting ('dateRange') !== 'Custom') {

			$dateRange = $this->getChartSetting ('dateRange');
			switch ($dateRange) {
				case 'Today':
					$startDate = time ();
					$endDate = time ();
					break;
				case 'Yesterday':
					$startDate = strtotime ('Yesterday');
					$endDate = strtotime ('Yesterday');
					break;
				case 'This Week':
					$startDate = strtotime ('Sunday this week');
					$endDate = time ();
					break;
				case 'Last Week':
					$startDate = strtotime ('-2 Sunday');
					$endDate = strtotime ('-1 Saturday');
					break;
				case 'This Month':
					$startDate = mktime (0, 0, 0, date ('m'), 1, date('o'));
					$endDate = time ();
					break;
				case 'Last Six Months':
					$startDate = mktime (0, 0, 0, date ('m') - 6, 1, date('o'));
					$endDate = time ();
					break;
				case 'This Year':
					$startDate = mktime (0, 0, 0, 1, 1, date('o'));
					$endDate = time ();
					break;
				case 'Last Year':
					$startDate = mktime (0, 0, 0, 1, 1, date('o') - 1);
					$endDate = mktime (0, 0, 0, 11, 31, date('o') - 1);
					break;
				case 'Last Month':
				default:
					$startDate = mktime (0, 0, 0, date ('m') - 1, 1, date('o'));
					$endDate = mktime (0, 0, 0, date ('m'), 1, date('o')) - self::SECPERDAY;
					break;
				/*case 'Data Domain':
					break;*/
			}
		} else {
			if ($this->getChartSetting ('startDate') !== null) {
				$startDate = $this->getChartSetting ('startDate') / 1000;
			} else {
				$startDate = $defaultStartTs;
			}
			if ($this->getChartSetting ('endDate')) { 
				$endDate = $this->getChartSetting ('endDate') / 1000;
			} else {
				$endDate = $defaultEndTs;
			}
		}
		$endDate += self::SECPERDAY - 1;
		return array ($startDate, $endDate);
	}


}
?>
