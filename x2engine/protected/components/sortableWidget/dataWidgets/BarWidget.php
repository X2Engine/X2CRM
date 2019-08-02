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
 * @package application.components
 */
class BarWidget extends DataWidget {

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Bar Chart',
                    'displayType' => 'bar',
                    'orientation' => 'rows',
                    'stack' => false
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge(
                parent::getJSSortableWidgetParams(), array (
            ));
        }
        return $this->_JSSortableWidgetParams;
    }

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

    public static function getGridData($settings, $chart) {
        $report = $chart->report;
        
        $data = $report->instance->getData('all');
        if (!$data) {
            return self::error();
        }

        foreach ($data[2] as $key => $value) {
            if ($value == null) {
                $data[2][$key] = ' ';
            }
        }
        $formattedData = array( array_merge( array('categories'), $data[2]));
        array_pop($data[0]);
        foreach($data[0] as $key => $value) {
            unset($value[X2GridReport::TOTAL_ALIAS]);
            $row = array_values($value);
            if ($row[0] == null) {
                $row[0] = ' ';
            }
            $formattedData[] = $row;
        }

        $columns = $report->setting('columnField');
        $rows = $report->setting('rowField');
        $values = $report->setting('cellDataType');


        $chartData = array (
            'data' => $formattedData,
            'labels' => array (
                'rows' => $report->getAttrLabel($rows),
                'columns' => $report->getAttrLabel($columns),
                'values' => $report->getAttrLabel($values),
                )
            );

        return $chartData;
    }


    /**
     * Retrive and format Summation Data
     */
    public static function getSummationData ($settings, $chart){
        $report = $chart->report;        

        $values = $chart->setting ('values'); // The column with the numbers
        $categories = $chart->setting ('categories'); // first label columns
        $groups = $chart->setting ('groups'); // second label column
        $columns = array(
            $categories,
            $values
        );

        if ($groups) {
            $columns[] = $groups;
        }

         /*
         * Data should look like this:
         * array (
         *   0 => array (
         *       'assignedTo' => array( 
         *           'chames', 'admin', 'anyone', 'admin', ...
         *       ), 
         *       'count' => array(
         *           153, 623, 125, 521 ... 
         *       ), 
         *       'leadSource' => array(
         *           'facebook', 'facebook', 'facebook', 'google' ... 
         *       )
         *   ),
         *   ...
         *
         **/
        $data = $report->instance->getData($columns, array(), true);

        if (!$data) {
            return self::error('missingColumn');
        }

        //Artificially Create a group  if one was not chosen
        if (!$groups) {
            $groups = Yii::t('charts', 'All');
            $data[0][$groups] = array_fill(0, count($data[0][$categories]), Yii::t('charts', 'All'));
        }

        $columns = $data[0];
        $formattedData = array();
        
        // The possible group option
        $groupOptions = array_unique ($columns[$groups]);

        /** 
         * This next loop will format the data into a structure like this:
         * array(
         *     'admin' => array (
         *         'facebook' => 623,
         *         'google' => 521
         *         ...
         *     ),
         *     'chames' => array (
         *         'facebook' => 153
         *     ),
         *     ...
         * )
         **/
        for($i = 0; $i < count($columns[$categories]); $i++) {
            $category = $columns[$categories][$i];
            $value = $columns[$values][$i];
            $group = $columns[$groups][$i];

            if (!array_key_exists($category, $formattedData)) {
                $formattedData[$category] = array();
            }

            if (!array_key_exists($group, $formattedData[$category])) {
                $formattedData[$category][$group] = 0;
            }

            //If duplicate group entries exists it add them together
            $formattedData[$category][$group] += $value;
        }

        /**
         * the front-end does not like null keys so we will swap it with 'None'
         */
        foreach($groupOptions as $key => $value) {
            if ($value = null) {
                $groupOptions[$key] = ' ';
            }
        }

        /** 
         * Finally this next loop will format the data into a structure like this:
         * array(
         *     array ('categories', facebook', 'google' ...)
         *     array ('admin', 623, 521 ...)
         *     array ('chames', 153, 0,  ...)
         *      ...
         * )
         **/
        //First row
        $finalData = array ( array_merge( array('categories'), $groupOptions));
        foreach($formattedData as $key => $value) {
            if ($key == null) { $key = ' '; }
            
            $row = array($key);

            foreach($groupOptions as $group){
                $count = 0; // Default is 0 
                if (array_key_exists($group, $value)) {
                    $count = $value[$group];
                }

                $row[] = $count;
            }

            $finalData[] = $row;
        }

        /**
         * Arrange the data to how Barwidget.js expects it
         */
        $chartData = array(
            'data' => $finalData,
            'labels' => array (
                'rows' => $report->getAttrLabel($categories),
                'columns' => $report->getAttrLabel($groups),
                'values' => $report->getAttrLabel($values)
            )
        );

        return $chartData;

    }

    /**
     * Renders and inline report. @see DataWidget::renderInlineReport
     */
    public static function renderInlineReport($chart, $params) {
        $report = $chart->report;
        if($report->type != 'summation') {
            return;
        }

        $settings = CJSON::decode($report->settings);
        $chartSettings = $chart->settingsArr;

        if (empty($settings['drillDownColumns'])) {
            return;
        }

        $rowsReport = new X2RowsAndColumnsReport;

        $rowsReport->allFilters = array ();
        $rowsReport->anyFilters = array ();
        $rowsReport->orderBy = array();
        $rowsReport->columns = $settings['drillDownColumns'];

        foreach ($settings as $key => $value) {
            if (property_exists($rowsReport, $key) && $settings[$key]) {
                $rowsReport->$key = $settings[$key];
            }
        }

        $conditions = $params['conditions'];
        if ($conditions['group'] && $chartSettings['groups']) {
            $rowsReport->allFilters[] = array(
                'name' => $chartSettings['groups'],
                'operator' => '=',
                'value' => $conditions['group']
            );
        }

        if ($conditions['name'] && $chartSettings['categories']) {
            $rowsReport->allFilters[] = array(
                'name' => $chartSettings['categories'],
                'operator' => '=',
                'value' => $conditions['name']
            );
        }

        // Necessary for name spacing reasons
        $rowsReport->_gridId='inline-report-'.$params['id'];

        $rowsReport->generate();
    }


    public function configBarItems(){
        return array_merge( 
            parent::configBarItems(),
            array( 
                array( 
                    'id' => 'orientation',
                    'class' => 'display-type fa fa-exchange',
                    'title' => Yii::t('charts', 'Transpose Row and Columns')
                ),
                array( 
                    'id' => 'stack',
                    'class' => 'display-type fa fa-bars',
                    'title' => Yii::t('charts', 'Stack Bars')
                ),                
                array( 
                    'class' => 'spacer'
                ),
                array( 
                    'id' => 'bar',
                    'class' => 'display-type fa fa-bar-chart',
                    'title' => Yii::t('charts', 'Bar Chart')
                ),
                array( 
                    'id' => 'line',
                    'class' => 'display-type fa fa-line-chart',
                    'title' => Yii::t('charts', 'Line Chart')
                ),
                array( 
                    'id' => 'area',
                    'class' => 'display-type fa fa-area-chart',
                    'title' => Yii::t('charts', 'Area Chart')
                ),
                array( 
                    'id' => 'pie',
                    'class' => 'display-type fa fa-pie-chart',
                    'title' => Yii::t('charts', 'Pie Chart')
                ),

            )
        );
    }

}
?>
