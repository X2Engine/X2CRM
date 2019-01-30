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
 * @package application.components.sortableWidget
 */
class ActionHistoryChartWidget extends ChartWidget {

    public $model;

    public $chartType = 'actionHistoryChart';

    private static $_JSONPropertiesStructure;

     

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Action History',
                    'chartSettings' => array (
                        'startDate' => null,
                        'endDate' => null, 
                        'dateRange' => null,
                        'binSize' => null,
                        'firstMetric' => null, 
                        'showRelationships' => null,
                    ),
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

	/**
	 * Retrieves all actions of a certain type associated with particular record
	 * between the start and end timestamps. Query results are used to populate the
	 * action history chart. Optionally, related records' actions can also be retrieved.
	 */
	public static function getChartData (
		$startTimestamp, $endTimestamp, $associationId, $associationType, 
		$showRelationships) {

		//printR (('startdate, enddate = '.$startTimestamp.', '.$endTimestamp), true);

		$associationType = strtolower ($associationType);
        
        if ((isset($showRelationships) && (($showRelationships !== 'true' && $showRelationships !== 'false'))) || !is_numeric($associationId)) {
            throw new CHttpException(400, Yii::t('admin', 'Incorrect parameters.'));
        }

    
		$associationCondition = self::getAssociationCond (
			$associationId, $associationType, $showRelationships);

		$date = 
			'IF(complete="No", '.
				'GREATEST(createDate, IFNULL(dueDate,0), IFNULL(lastUpdated,0)), '.
				'GREATEST(createDate, IFNULL(completeDate,0), IFNULL(lastUpdated,0)))';

		$command = Yii::app()->db->createCommand()
			->select(
				'type,'.
				'COUNT(id) AS count,'.
				'YEAR(FROM_UNIXTIME('.$date.')) AS year,'.
				'MONTH(FROM_UNIXTIME('.$date.')) AS month,'.
				'WEEK(FROM_UNIXTIME('.$date.')) AS week,'.
				'DAY(FROM_UNIXTIME('.$date.')) AS day,'.
				'HOUR(FROM_UNIXTIME('.$date.')) AS hour')
			->from('x2_actions');
		$command->where(
			$associationCondition . ' AND '.
			'(visibility="1" OR assignedTo="'.Yii::app()->user->getName().'") AND '.
			$date.' BETWEEN :startTimestamp AND :endTimestamp', 
			array(
				'startTimestamp' => $startTimestamp, 
				'endTimestamp' => $endTimestamp
			));
		$actions = $command->group(
				'day, week, month, year, type')
			->order('year DESC, month DESC, week DESC, day DESC, hour desc')
			->queryAll();
		return $actions;
	}

    /**
     * Instantiates a subclass of X2Chart, passing it a function which allows it to save widget
     * settings.
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $associationId = $this->model->id;
            $associationType = get_class ($this->model);
            $showRelationships = $this->getChartSetting ('showRelationships');
            $dataStartDate = self::getFirstActionDate (
                $associationId, $associationType, $showRelationships);
            if (!empty ($dataStartDate)) {
                $chartData = $this->getInitialChartData (
                    $dataStartDate, $associationId, $associationType, $showRelationships);
            }

            $this->_setupScript = parent::getSetupScript ()."
                $(function () {
                    var chartUID = '$this->chartType$this->widgetUID';
                    x2[chartUID] = {};
                    x2[chartUID].chart = X2Chart.instantiateTemporarySubtype (
                        X2ActionHistoryChart, {
                        ".(isset ($chartData) ?
                            "chartData :".CJSON::encode ($chartData)."," : '')."
                        actionParams: ".CJSON::encode (array (
                            'associationId' => $associationId,
                            'associationType' => $associationType,
                            'showRelationships' => ($showRelationships ? 'true' : 'false'),
                        )).",
                        translations: ".CJSON::encode ($this->getTranslations ()).",
                        getChartDataActionName: '".
                            Yii::app()->request->getScriptUrl ().'/site/GetActionsBetweenAction'."',
                        saveChartSetting: function (key, value, callback) {
                            this.lastChartSettings[key] = value;
                            x2.$widgetClass$this->widgetUID.setProperty (
                                'chartSettings', this.lastChartSettings, callback);
                        },
                        suppressDateRangeSelector: false,
                        suppressChartSettings: true,
                        lastChartSettings: ".CJSON::encode ($this->getChartSettings ()).",
                        widgetUID: '$this->widgetUID',
                        chartType: '$this->chartType',
                        chartSubtype: '".self::getJSONProperty (
                            $this->profile, 'chartSubtype', $this->widgetType, $this->widgetUID)."',
                        ".(!empty ($dataStartDate) ?
                            "dataStartDate : $dataStartDate * 1000," : '')."
                    });
                    $(document).trigger ('$this->chartType' + 'Ready');
                });
            ";
        }
        return $this->_setupScript;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $showRelationships = $this->getChartSetting ('showRelationships');
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'showRelationships' => $showRelationships,
                    'suppressChartSettings' => true,
                    'metricTypes' => array (
                        'any'=>Yii::t('app', 'All {Actions}', array(
                            '{Actions}' => Modules::displayName(true, 'Actions')
                        )),
                        ''=>Yii::t('app', 'Tasks'),
                        'attachment'=>Yii::t('app', 'Attachments'),
                        'call'=>Yii::t('app', 'Calls'),
                        'email'=>Yii::t('app', 'Emails'),
                        'emailOpened'=>Yii::t('app', 'Emails Opened'),
                        'event'=>Yii::t('app', 'Events'),
                        'note'=>Yii::t('app', 'Notes'),
                        'quotes'=>Yii::t('app', '{Quotes}', array(
                            '{Quotes}' => Modules::displayName(true, 'Quotes')
                        )),
                        'webactivity'=>Yii::t('app', 'Web Activity'),
                        'workflow'=>Yii::t('app', '{Process} Actions', array(
                            '{Process}' => Modules::displayName(false, 'Process')
                        )),
                        'time'=>Yii::t('app', 'Time Actions')
                    ),
                    'chartType' => 'actionHistoryChart',
                    'widgetUID' => $this->widgetUID,
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
                    'ActionHistoryChartWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/X2Chart/X2ActionHistoryChart.js',
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
                    'metric1Label' => Yii::t('app', 'metric(s) selected')
                )
            );
        }
        return $this->_translations;
    }

	/**
	 * Private helper function. Returns a SQL conditional statement used in 
	 * queries to the Actions table. Restricts query results to actions of a certain
	 * type, associated with a particular id, and, if specified, actions of related
	 * records.
	 */
	protected static function getAssociationCond (
		$associationId, $associationType, $showRelationships) {

        if ($showRelationships === 'true') {
            $model = X2Model::model($associationType)->findByPk($associationId);
            if (count($model->relatedX2Models) > 0) {
                $associationCondition = 
					"((associationId={$associationId} AND ".
					"associationType='{$associationType}')";
                foreach ($model->relatedX2Models as $relatedModel) {
                    if ($relatedModel instanceof X2Model) {
                        $associationCondition .=
							" OR (associationId={$relatedModel->id} AND ".
							"associationType='".
                                X2Model::getAssociationType ($relatedModel->myModelName)."')";
                    }
                }
                $associationCondition .= ")";
            } else {
                $associationCondition = 
					'associationId='.$associationId.' AND '.
					'associationType="'.$associationType.'"';
            }
        } else {
            $associationCondition = 
				'associationId='.$associationId.' AND '.
				'associationType="'.$associationType.'"';
        }
		return $associationCondition;
	}

	/**
	 * Collect initial chart data so the client doesn't have to request it via ajax .
	 * Decreases time before chart render after page is loaded.
	 */
	protected function getInitialChartData (
        $dataStartDate, $associationId, $associationType, $showRelationships) {

		$tsDict = $this->getStartEndTimestamp (
			$dataStartDate, time () + self::SECPERDAY);
		$startDate = $tsDict[0];
		$endDate = $tsDict[1];
		//printR (('startdate, enddate = '.$startDate.', '.$endDate), true);
		$events = self::getChartData (
            $startDate, $endDate, $associationId, $associationType, $showRelationships);
        return $events;
	}

	/**
	 * Get earliest date of actions which will visible on the initial chart.
	 */
	protected function getFirstActionDate  ($associationId, $associationType, $showRelationships) {
		if ($this->getChartSetting ('showRelationships') === 'true') {
			$associationCondition = self::getAssociationCond (
				$associationId, $associationType, 'true');
		} else {
			$associationCondition = self::getAssociationCond (
				$associationId, $associationType, $showRelationships);
		}

		$command = Yii::app()->db->createCommand()
            ->select('min(createDate)')
            ->from('x2_actions')
            ->where(
                $associationCondition.' AND '.
                '(visibility="1" OR assignedTo="'.Yii::app()->user->getName().'")', 
                array(
                    'associationId' => $associationId, 
                    'associationType' => $associationType
                ));
		$actionsStartDate = $command->queryScalar();
        return $actionsStartDate;
	}

}

?>
