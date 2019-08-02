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
class ScatterPlotWidget extends DataWidget {

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        self::$_JSONPropertiesStructure = array_merge (
            parent::getJSONPropertiesStructure (),
            array (
                'grid' => true
            )
        );
        return self::$_JSONPropertiesStructure;
    }

    public static function getRowsAndColumnsData ($settings, $chart) {
        return self::getSummationData ($settings, $chart);
    }

    /**
     * Retrive and format Summation Data
     */
    public static function getSummationData ($settings, $chart){
        $report = $chart->report;        

        $xAxis  = $chart->setting ('xAxisField'); // second label column
        $yAxis  = $chart->setting ('yAxisField'); // first label columns
        $groups = $chart->setting ('groupField'); // second label column


        $columnNames = array(
            $xAxis,
            $yAxis,
        );

        if($groups) {
            $columnNames[] = $groups;
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
         *       'dealValue' => array(
         *           '154, 152, 1231, ...
         *       )
         *   ),
         *   ...
         *
         **/
        // column index isn't needed since x and y columns must be unique
        // $columnIndex = self::getColumnIndex ($columnNames);
        $data = $report->instance->getData(array_unique ($columnNames));
        if (!$data) {
            return self::error('missingColumn');
        }

        $columns = $data[0];
        $formattedData = array();
        $xAxisNames = array();
        
        /** 
         * This next loop will format the data into a structure like this:
         * array (
         *     'admin_x' => array (152, 324, 413)
         *     'admin'   => array (123, 123, 123)
         *     'chames_x'  => array (124, 421, 233)
         *     'chames'  => array (174, 173, 423)
         * ),
         * array (
         *     'admin' => 'admin_x',
         *     'chames' => 'chames_x'
         * )
         * 
         **/
        for($i = 0; $i < count($columns[$groups]); $i++) {
            $xValue = $columns[$xAxis][$i];
            $yValue = $columns[$yAxis][$i];

            if (isset($columns[$groups])){
                $group = $columns[$groups][$i];
            }  else {
                $group = Yii::t('charts', 'all');
            }

            if (!isset($xAxisNames[$group])) {
                $xAxisNames[$group] = $group.'_x';
                $formattedData[$group] = array();
                $formattedData[$group.'_x'] = array();
            }

            if (!$yValue) $yValue = 0;
            if (!$xValue) $xValue = 0;

            $formattedData[$group][] = $yValue;
            $formattedData[$group.'_x'][] = $xValue;

        }


        /**
         * This sections is to differentiate between R+C reports and Summation
         * They return their labels differently, which is the only difference
         */
        
        // R+C way (Labels come in order)
        $labels = array_values($data[2]);

        // Summation way 
        if (array_key_exists($xAxis, $data[2])) {
            $labels[0] = $data[2][$xAxis];
        }
        if (array_key_exists($yAxis, $data[2])) {
            $labels[1] = $data[2][$yAxis];
        }
        

        /**
         * Arrange the data to how Barwidget.js expects it
         */
        $chartData = array(
            'json' => $formattedData,
            'xs' => $xAxisNames,
            'size' => count($xAxisNames),
            'labels' => array(
                'x' => $labels[0],
                'y' => $labels[1]
            )
        );

        return $chartData;

    }


    public function configBarItems(){
        return array_merge( 
            parent::configBarItems(),
            array( 
                array (
                    'id' => 'grid',
                    'class' => 'fa fa-arrows',
                    'title' => Yii::t('charts', 'Toggle Grid')
                )
            )
        );
    }

}
?>
