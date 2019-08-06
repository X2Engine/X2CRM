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




 Yii::import ('application.components.sortableWidget.views');

/**
 * @package application.components.compontents.sortableWidget.datawidget
 */
class TimeSeriesWidget extends DataWidget {

    /**
     * @see SortableWidget::$_JSONPropertiesStructure
     */
    private static $_JSONPropertiesStructure;

    /**
     * @see SortableWidget::getJSONPropertiesStructure()
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Activity Chart',
                    'displayType' => 'line',
                    'subchart' => false,
                    'timeBucket' => 'day',
                    'filter' => 'month',
                    'filterType' => 'trailing',
                    'filterFrom' => null,
                    'filterTo' => null,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * @see SortableWidget::getJSSortableWidgetParams()
     */
    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge(
                parent::getJSSortableWidgetParams(),
                array (
                    'primaryModelType' => $this->chart->report->setting ('primaryModelType')
                )
            );
        }
        return $this->_JSSortableWidgetParams;
    }


    /**
     * @see SortableWidget::getPackages()
     */
    public function getPackages () {
        $widgetClass = get_called_class();
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'momentJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/lib/moment-with-locales.min.js',
                        )
                    ),
                    'timeSeriesCSS' => array(
                        'baseUrl' => Yii::app()->theme->baseUrl,
                        'css' => array(
                            'css/components/DataWidget/TimeSeriesWidget.css',
                        )
                    )
                )
            );
        }
        return $this->_packages;
    }

    /**
     * Gets the data for a RowsAndColumns Report 
     * @see DataWidget::getData()
     */
    public static function getRowsAndColumnsData ($settings, $chart){
        $report = $chart->report;

        $timeField = $chart->setting('timeField');
        $labelField = $chart->setting('labelField');
        $aggregateField = $chart->setting('aggregateField');

        // Get a formatted array of the timeframe
        // ex. array (
        //     'start' => <unix timestamp>,
        //     'end' => <unix timestamp>
        // )
        $timeFrame = self::getTimeFrame($settings);

        // Next, we load an array of correctly formatted filters
        // ex. array (
        //        array (
        //          'name' => 'createDate'
        //          'operator' => '>'
        //          'value' =>  <unix timestamp>
        //        )
        //     )
        $newFilters = self::applyTimeFilters($timeField, $timeFrame);
        $report->addFilters ($newFilters);

        // Set up the API call to reports
        $columns = array($timeField);

        if ($labelField) {
            $columns[] = $labelField;
        }

        if ($aggregateField) {
            $columns[] = $aggregateField;
        }

        $columnIndex = self::getColumnIndex ($columns);

        try {
            $data = $report->instance->getData (array_unique ($columns));
        } catch (X2RowsAndColumnsReportException $e) {
            return self::error (null, $e->getMessage ());
        }
        if (!$data) {
            return self::error('missingColumn');
        }

        // Format the Chart data into how the javascript file
        // wants  to recieve it 
        $chartData = array (
            'timeField' => $data[0][$timeField],    
            'timeFrame' => $timeFrame,
            'labels' => array(
                'timeField' => $data[2][0],
                'aggregateField' => Yii::t('charts', 'Count')
            )
        );

        if($labelField) {
            $chartData['labelField'] = $data[0][$labelField];
            $chartData['labels']['labelField'] = $data[2][$columnIndex[1]];
        }

        if($aggregateField) {
            $chartData['aggregateField'] = $data[0][$aggregateField];
            $chartData['labels']['aggregateField'] = $data[2][$columnIndex[2]];
        }

        return $chartData;
    }

    /**
     * Formats a dateRange into a report 
     * @param  string $timeField The name of the field in the report,
     * Such as 'createDate' or 'lastUpdated'
     * @param  array $timeFrame  A date range array
     * ex. array (
     *     'start' => <unix timestamp>
     *     'end' =>   <unix timestamp>
     * )
     * 
     * @return array             An array of report filters
     */
    public static function applyTimeFilters($timeField, $timeFrame) {
        $filters = array();
        if (isset($timeFrame['start'])) {
            array_push ( 
                $filters,
                array( 
                    'name' => $timeField,
                    'operator' => '>=',
                    'value' => $timeFrame['start']
                )
            );
        }

        if (isset($timeFrame['end'])) {
            array_push ( 
                $filters,
                array(
                    'name' => $timeField,
                    'operator' => '<',
                    'value' => $timeFrame['end']
                )
            );
        }
        return $filters;
    }

    /**
     * Renders an inline report based on extra parameters sent
     * @param  Charts $chart  chart object to use
     * @param  Array $params  Parameters sent via ajax
     * @return (echo)         Echos a rendered report
     */
    public static function renderInlineReport($chart, $params) {
        $settings = $chart->settingsArr;
        $widgetName = $chart->widgetName;
        $report = $chart->report;

        $timeField = $settings['timeField'];
        $labelField = $settings['labelField'];

        $conditions = $params['conditions'];

        // Set up an array of the filters 
        $filters = self::applyTimeFilters ($timeField, $conditions);
        if (isset($conditions['name']) && !empty($conditions['name'])) {
            $filters[] = array(
                'name' => $labelField,
                'operator' => '=', 
                'value' => "$conditions[name]"
            );
        }

        // Add the filters onto the report
        $report->addFilters ($filters);
        $widget = $report->instance;
        $widget->_gridId = 'inline-report-'.$params['id'];
        // render the report
        $widget->generate();
    }


    /**
     * Uses {@link X2DateUtil} to compute a timeframe from chart settings
     * @param $settings array Chart settings must have keys
     * @return array 
     * @see X2DateUtil::parseDateRange
     */
    public static function getTimeFrame($settings) {
        $filter = $settings ['filter'];
        $filterType = $settings ['filterType'];

        if ($filterType == 'custom') {
            $filterFrom = $settings['filterFrom'];
            $filterTo = $settings['filterTo'];
            $range = 'custom';
            return X2DateUtil::parseDateRange($range, $filterFrom, $filterTo);
        }

        $key = $filterType.ucfirst($filter);
        $range = X2DateUtil::parseDateRange($key);
        return $range;
    }

    /**
     * @see SortableWidget:: renderWidgetContent()
     */
    public function renderWidgetContents() {
        $this->render('application.components.sortableWidget.views._filterMenu', array(
            'relativeTimeOptions' => $this->relativeTimeOptions(),
            'timeUnitOptions' => $this->timeUnitOptions(),
            'timeBucketOptions' => $this->timeBucketOptions(),
            ));
        parent::renderWidgetContents();
    }

    /**
     * @see SortableWidget::getTranslations()
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (
                parent::getTranslations (),
                array (
                    'this hour' => Yii::t('charts', 'this hour'),
                    'this day' => Yii::t('charts', 'today'),
                    'this month' => Yii::t('charts', 'this month'),
                    'this week' => Yii::t('charts', 'this week'),
                )
            );
        }
        return $this->_translations;
    }

    /**
     * @see SortableWidget:: configBarItem()
     */
    protected function configBarItems(){
        return array_merge( 
            parent::configBarItems(),
            array(
                array(
                    'class' => 'fa fa-toggle-down',
                    'id' => 'subchart',
                    'title' =>Yii::t('charts',  'Toggle mini-chart')
                ),
            ),
            array(
                array(
                   'class' => 'spacer',
                )
            ),
            self::displayTypeItems()
        );
    }

    /**
     * Menu options for the chart parameter timeBucket
     */
    private static function displayTypeItems(){
        return array( 
            array( 
                'id' => 'line',
                'class' => 'display-type fa fa-line-chart',
                'title' => Yii::t('charts', 'Line Chart') ),
            array( 
                'id' => 'area',
                'class' => 'display-type fa fa-area-chart',
                'title' => Yii::t('charts', 'Line Chart') ),
            array( 
                'id' => 'bar',
                'class' => 'display-type fa fa-bar-chart',
                'title' => Yii::t('charts', 'Line Chart') ),
            array( 
                'id' => 'pie',
                'class' => 'display-type fa fa-pie-chart',
                'title' => Yii::t('charts', 'Pie Chart') ),
            array( 
                'id' => 'gauge',
                'class' => 'display-type fa fa-tachometer',
                'title' => Yii::t('charts', 'Gauge Chart') ),
            array( 
                'class' => 'spacer'),
            );
    }

    /**
     * Menu options for the chart parameter timeBucket
     */
    private function timeBucketOptions(){
        return  array( 
            array( 
                'id' => 'hour',
                'content' => Yii::t('charts', 'hour'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per hour') ),
            array( 
                'id' => 'day',
                'content' => Yii::t('charts', 'day'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per day') ),
            array( 
                'id' => 'week',
                'content' => Yii::t('charts', 'week'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per week') ),
            array( 
                'id' => 'month',
                'content' => Yii::t('charts', 'month'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per month') ),
            );
    }


    /**
     * Menu options for the parameter filterType
     */
    private function relativeTimeOptions() {
        return array(
            array(
                'id' => 'trailing', 
                'content' => Yii::t('charts', 'trailing')
            ),
            array(
                'id' => 'this', 
                'content' => Yii::t('charts', 'this')
            ),
            array(
                'id' => 'custom', 
                'content' => Yii::t('charts', 'custom')
            )
        );
    }

    private function timeUnitOptions() {
        return array(
            array('id' => 'day',    'content' => Yii::t('charts', 'day')),
            array('id' => 'week',   'content' => Yii::t('charts', 'week')),
            array('id' => 'month',  'content' => Yii::t('charts', 'month')),
            array('id' => 'quarter','content' => Yii::t('charts', 'quarter')),
            array('id' => 'year',   'content' => Yii::t('charts', 'year')),
        );
    }
}
?>
