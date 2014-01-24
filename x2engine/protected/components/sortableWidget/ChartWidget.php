<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

 Yii::import ('application.components.sortableWidget.SortableWidget');

/**
 * @package X2CRM.components
 */
abstract class ChartWidget extends SortableWidget {


    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{chartSubtypeSelector}{closeButton}{minimizeButton}</div>{widgetContents}';

    /**
     * @var string the type of chart (e.g. 'eventsChart', 'usersChart')
     */
    public $chartType;

    private static $_JSONPropertiesStructure;

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'chartSubtype' => self::getJSONProperty (
                        $this->profile, 'chartSubtype', $this->widgetType)
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
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * overrides parent method. A sub prototype of SortableWidget.js is instantiated.
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $this->_setupScript = "
                $(function () {
                    x2.".$widgetClass." = new ChartWidget ({
                        'widgetClass': '".$widgetClass."',
                        'setPropertyUrl': '".Yii::app()->controller->createUrl (
                            '/profile/setWidgetSetting')."',
                        'cssSelectorPrefix': '".$this->widgetType."',
                        'chartType': '".$this->chartType."',
                        'widgetType': '".$this->widgetType."'
                    });
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
                            'js/sortableWidgets/ChartWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * Render the chart subtype selector
     */
    public function renderChartSubtypeSelector () {
        $subtype = self::getJSONProperty (
            $this->profile, 'chartSubtype', $this->widgetType);

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


                ")
            );
        }
        return $this->_css;
    }
}
?>
