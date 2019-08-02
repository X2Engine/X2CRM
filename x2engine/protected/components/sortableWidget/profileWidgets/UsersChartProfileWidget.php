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




 Yii::import ('application.components.sortableWidget.ChartWidget');


/**
 * @package application.components
 */
class UsersChartProfileWidget extends ChartWidget {

    public $canBeDeleted = true;

    public $relabelingEnabled = true;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{chartSubtypeSelector}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    public $chartType = 'usersChart';

    private static $_JSONPropertiesStructure;

    public $defaultTitle = 'Users Summary';

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'User Events',
                    'hidden' => true,
                    'containerNumber' => 2,
                    'chartSubtype' => 'pie', 
                    'chartSettings' => array (
                        'startDate' => null,
                        'endDate' => null, 
                        'binSize' => null,
                        'firstMetric' => null, 
                        'chartSetting' => null,
                        'eventsFilter' => null,
                        'socialSubtypesFilter' => null,
                        'visibilityFilter' => null,
                        'dateRange' => null,
                    ),
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }


    /**
     * Returns a data provider containing chart settings records with the specified type
     */
	public static function getChartSettingsProvider ($chartType) {
        $chartSettingsDataProvider = new CActiveDataProvider('ChartSetting', array(
            'criteria' => array(
                'condition' => 
                    'userId='.Yii::app()->user->id.' AND '.
                    'chartType="usersChart"',
                'order' => 'name ASC'
            )
        ));
		return $chartSettingsDataProvider;
	}

	/**
	 * Retrieves all events between start and end timestamp. Query results are used to
	 * populate the activity feed chart.
	 */
	public static function getChartData ($startTimestamp, $endTimestamp){
        $command = Yii::app()->db->createCommand()
                ->select(
                        'type, subtype, visibility, user,'.
						'timestamp, COUNT(type) AS count,'.
                        'YEAR(FROM_UNIXTIME(timestamp)) AS year,'.
                        'MONTH(FROM_UNIXTIME(timestamp)) AS month,'.
                        'WEEK(FROM_UNIXTIME(timestamp)) AS week,'.
                        'DAY(FROM_UNIXTIME(timestamp)) AS day,'.
                        'HOUR(from_unixtime(timestamp)) as hour')
                ->from('x2_events');
        $command->where(
                'timestamp BETWEEN :startTimestamp AND :endTimestamp', 
				array('startTimestamp' => $startTimestamp, 'endTimestamp' => $endTimestamp));
        $events = $command->group(
                        'HOUR(FROM_UNIXTIME(timestamp)),'.
                        'DAY(FROM_UNIXTIME(timestamp)),'.
                        'WEEK(FROM_UNIXTIME(timestamp)),'.
                        'MONTH(FROM_UNIXTIME(timestamp)),'.
                        'YEAR(FROM_UNIXTIME(timestamp)),'.
                        'timestamp, type, subtype, visibility, user')
                ->order('year DESC, month DESC, week DESC, day DESC, hour desc')
                ->queryAll();
		return $events;
	}

    /**
     * Instantiates a subclass of X2Chart, passing it a function which allows it to save widget
     * settings.
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $chartData = $this->getInitialChartData ();
            $userNames = User::getNames ();
            $eventTypes = array ('all'=>Yii::t('app', 'All Events')) + Events::$eventLabels;
            $socialSubtypes = Dropdowns::getSocialSubtypes ();
            $visibilityFilters = array (
                '1'=>'Public',
                '0'=>'Private',
            );
            $chartSettingsData = self::getChartSettingsProvider ($this->chartType)->data;
            $this->_setupScript = parent::getSetupScript ()."
                $(function () {
                    var chartUID = '$this->chartType$this->widgetUID';
                    x2[chartUID] = {};
                    x2[chartUID].chart = X2Chart.instantiateTemporarySubtype (
                        X2UsersChart, {
                        ".(isset ($chartData) ?
                            "chartData :".CJSON::encode ($chartData)."," : '')."
                        actionParams: ".CJSON::encode (array (
                            'widgetType' => get_called_class (),
                        )).",
                        socialSubtypes:".CJSON::encode (array_keys ($socialSubtypes)).",
                        visibilityTypes:".CJSON::encode (array_keys ($visibilityFilters)).",
                        eventTypes:".CJSON::encode (array_keys ($eventTypes)).", 
                        translations: ".CJSON::encode ($this->getTranslations ()).",
                        getChartDataActionName: 'getEventsBetween',
                        saveChartSetting: function (key, value, callback) {
                            this.lastChartSettings[key] = value;
                            x2.$widgetClass$this->widgetUID.setProperty (
                                'chartSettings', this.lastChartSettings, callback);
                        },
                        suppressDateRangeSelector: false,
                        suppressChartSettings: false,
                        lastChartSettings: ".CJSON::encode ($this->getChartSettings ()).",
                        widgetUID: '$this->widgetUID',
                        chartType: '$this->chartType',
                        chartSubtype: '".self::getJSONProperty (
                            $this->profile, 'chartSubtype', $this->widgetType, $this->widgetUID)."',
                        chartSettings: ".CJSON::encode (
                            count ($chartSettingsData) ? array_combine (
                                array_map (function ($setting) {
                                    return $setting->name;
                                }, $chartSettingsData),
                                $chartSettingsData) : array ())."
                        });
                    $(document).trigger ('$this->chartType' + 'Ready');
                });
            ";
        }
        return $this->_setupScript;
    }

    /**
     * Send the chart type to the widget content view 
     */
    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'chartType' => $this->chartType,
			        'chartSettingsDataProvider' => self::getChartSettingsProvider (
                        $this->chartType),
                    'eventTypes' =>
                        array ('all'=>Yii::t('app', 'All Events')) + Events::$eventLabels,
                    'socialSubtypes' => Dropdowns::getSocialSubtypes (),
                    'visibilityFilters' => array (
                        '1'=>'Public',
                        '0'=>'Private',
                    ),
                    'suppressChartSettings' => false,
                    'chartType' => 'usersChart',
                    'widgetUID' => $this->widgetUID,
                    'metricTypes' => User::getUserOptions (),
                )
            );
        }
        return $this->_viewFileParams;
    }


    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'UsersChartProfileWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/X2Chart/X2UsersChart.js',
                        ),
                        'depends' => array ('ChartWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (
                parent::getTranslations (),
                array (
                    'event subtype(s) selected' => Yii::t('app', 'event subtype(s) selected'),
                    'visibility setting(s) selected' => Yii::t(
                        'app', 'visibility setting(s) selected'),
                    'metric1Label' => Yii::t('app', '{user}(s) selected', array(
                        '{user}' => strtolower(Modules::displayName(false, 'Users')),
                    )),
                    'event type(s) selected' => Yii::t('app', 'event type(s) selected'),
                )
            );
        }
        return $this->_translations;
    }

	/**
	 * Collect initial chart data so the client doesn't have to request it via ajax .
	 * Decreases time before chart render after page is loaded.
	 */
	protected function getInitialChartData () {
		
		/* 
		Chart data only needs to be sent with initial response if chart was
		left open.
		*/
		if (self::getJSONProperty (
            $this->profile, 'minimized', $this->widgetType, $this->widgetUID)) {

			$tsDict = $this->getStartEndTimestamp (time () - self::SECPERWEEK, time ());
			$startDate = $tsDict[0];
			$endDate = $tsDict[1];
			$events = self::getChartData ($startDate, $endDate);
			return $events;
		}
	}

     
}
?>
