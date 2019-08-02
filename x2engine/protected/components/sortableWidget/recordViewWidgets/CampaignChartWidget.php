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
class CampaignChartWidget extends ChartWidget {

    public $model;

    public $chartType = 'campaignChart';

    public $launchDate;

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Campaign',
                    'chartSettings' => array (
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
	 * Retrieves all campaign list records between start and end timestamp. Query results are used 
     * to populate the campaign chart.
	 */
	public static function getChartData(
		$id, $modelName, $startTimestamp, $endTimestamp) {

        if (!isset ($id) || empty ($id)) return array ();

		$criteria = X2Model::model('Campaign')->getAccessCriteria();
		$conditions =$criteria->condition;
		$data = array ();
		$data = array_merge ($data, Yii::app()->db->createCommand()
			->select(
				'"sent" as type,'.
				'count(list.sent) as count, '.
				'YEAR(FROM_UNIXTIME(list.sent)) as year, '.
				'MONTH(FROM_UNIXTIME(list.sent)) as month, '. 
				'WEEK(FROM_UNIXTIME(list.sent)) as week, '. 
				'DAY(FROM_UNIXTIME(list.sent)) as day, '. 
				'HOUR(FROM_UNIXTIME(list.sent)) as hour')
			->from(X2ListItem::model()->tableName().' as list')
			->leftJoin(X2Model::model($modelName)->tableName().' t', 'list.contactId=t.id')
			->where('list.sent>0 AND list.listId=:listId AND ('.$conditions.') '.
                	'AND list.sent BETWEEN :startTimestamp AND :endTimestamp', 
				array_merge (array(
                    ':listId'=>$id,
                    'startTimestamp' => $startTimestamp,
					'endTimestamp' => $endTimestamp
                ), $criteria->params))
            ->group('HOUR(FROM_UNIXTIME(list.sent)),'.
					'DAY(FROM_UNIXTIME(list.sent)),'.
					'WEEK(FROM_UNIXTIME(list.sent)),'.
					'MONTH(FROM_UNIXTIME(list.sent)),'.
					'YEAR(FROM_UNIXTIME(list.sent))')
			->order('year DESC, month DESC, week DESC, day DESC, hour desc')
			->queryAll());
		$data = array_merge ($data, Yii::app()->db->createCommand()
			->select(
				'"opened" as type,'.
				'count(list.opened) as count, '.
				'YEAR(FROM_UNIXTIME(list.opened)) as year, '.
				'MONTH(FROM_UNIXTIME(list.opened)) as month, '. 
				'WEEK(FROM_UNIXTIME(list.opened)) as week, '. 
				'DAY(FROM_UNIXTIME(list.opened)) as day, '. 
				'HOUR(FROM_UNIXTIME(list.opened)) as hour')
			->from(X2ListItem::model()->tableName().' as list')
			->leftJoin(X2Model::model($modelName)->tableName().' t', 'list.contactId=t.id')
			->where('list.opened>0 AND list.listId=:listId AND ('.$conditions.') '.
                	'AND list.opened BETWEEN :startTimestamp AND :endTimestamp', 
				array_merge (array(
                    ':listId'=>$id,
                    'startTimestamp' => $startTimestamp,
					'endTimestamp' => $endTimestamp
                ), $criteria->params))
            ->group('HOUR(FROM_UNIXTIME(list.opened)),'.
					'DAY(FROM_UNIXTIME(list.opened)),'.
					'WEEK(FROM_UNIXTIME(list.opened)),'.
					'MONTH(FROM_UNIXTIME(list.opened)),'.
					'YEAR(FROM_UNIXTIME(list.opened))')
			->order('year DESC, month DESC, week DESC, day DESC, hour desc')
			->queryAll());
		$data = array_merge ($data, Yii::app()->db->createCommand()
			->select(
				'"clicked" as type,'.
				'count(list.clicked) as count, '.
				'YEAR(FROM_UNIXTIME(list.clicked)) as year, '.
				'MONTH(FROM_UNIXTIME(list.clicked)) as month, '. 
				'WEEK(FROM_UNIXTIME(list.clicked)) as week, '. 
				'DAY(FROM_UNIXTIME(list.clicked)) as day, '. 
				'HOUR(FROM_UNIXTIME(list.clicked)) as hour')
			->from(X2ListItem::model()->tableName().' as list')
			->leftJoin(X2Model::model($modelName)->tableName().' t', 'list.contactId=t.id')
			->where('list.clicked>0 AND list.listId=:listId AND ('.$conditions.') '.
                	'AND list.clicked BETWEEN :startTimestamp AND :endTimestamp', 
				array_merge (array(
                    ':listId'=>$id,
                    'startTimestamp' => $startTimestamp,
					'endTimestamp' => $endTimestamp
                ), $criteria->params))
            ->group('HOUR(FROM_UNIXTIME(list.clicked)),'.
					'DAY(FROM_UNIXTIME(list.clicked)),'.
					'WEEK(FROM_UNIXTIME(list.clicked)),'.
					'MONTH(FROM_UNIXTIME(list.clicked)),'.
					'YEAR(FROM_UNIXTIME(list.clicked))')
			->order('year DESC, month DESC, week DESC, day DESC, hour desc')
			->queryAll());
		$data = array_merge ($data, Yii::app()->db->createCommand()
			->select(
				'"unsubscribed" as type,'.
				'count(list.unsubscribed) as count, '.
				'YEAR(FROM_UNIXTIME(list.unsubscribed)) as year, '.
				'MONTH(FROM_UNIXTIME(list.unsubscribed)) as month, '. 
				'WEEK(FROM_UNIXTIME(list.unsubscribed)) as week, '. 
				'DAY(FROM_UNIXTIME(list.unsubscribed)) as day, '. 
				'HOUR(FROM_UNIXTIME(list.unsubscribed)) as hour')
			->from(X2ListItem::model()->tableName().' as list')
			->leftJoin(X2Model::model($modelName)->tableName().' t', 'list.contactId=t.id')
			->where('list.unsubscribed>0 AND list.listId=:listId AND ('.$conditions.') '.
                	'AND list.unsubscribed BETWEEN :startTimestamp AND :endTimestamp', 
				array_merge (array(
                    ':listId'=>$id,
                    'startTimestamp' => $startTimestamp,
					'endTimestamp' => $endTimestamp
                ), $criteria->params))
            ->group('HOUR(FROM_UNIXTIME(list.unsubscribed)),'.
					'DAY(FROM_UNIXTIME(list.unsubscribed)),'.
					'WEEK(FROM_UNIXTIME(list.unsubscribed)),'.
					'MONTH(FROM_UNIXTIME(list.unsubscribed)),'.
					'YEAR(FROM_UNIXTIME(list.unsubscribed))')
			->order('year DESC, month DESC, week DESC, day DESC, hour desc')
			->queryAll());
        $data = array_merge ($data, Yii::app()->db->createCommand()
            ->select(
                '"suppressed" as type,'.
                'count(list.suppressed) as count, '.
                'YEAR(FROM_UNIXTIME(list.suppressed)) as year, '.
                'MONTH(FROM_UNIXTIME(list.suppressed)) as month, '.
                'WEEK(FROM_UNIXTIME(list.suppressed)) as week, '.
                'DAY(FROM_UNIXTIME(list.suppressed)) as day, '.
                'HOUR(FROM_UNIXTIME(list.suppressed)) as hour')
            ->from(X2ListItem::model()->tableName().' as list')
            ->leftJoin(X2Model::model($modelName)->tableName().' t', 'list.contactId=t.id')
            ->where('list.suppressed>0 AND list.listId=:listId AND ('.$conditions.') '.
                'AND list.suppressed BETWEEN :startTimestamp AND :endTimestamp',
                array_merge (array(
                    ':listId'=>$id,
                    'startTimestamp' => $startTimestamp,
                    'endTimestamp' => $endTimestamp
                ), $criteria->params))
            ->group('HOUR(FROM_UNIXTIME(list.suppressed)),'.
                'DAY(FROM_UNIXTIME(list.suppressed)),'.
                'WEEK(FROM_UNIXTIME(list.suppressed)),'.
                'MONTH(FROM_UNIXTIME(list.suppressed)),'.
                'YEAR(FROM_UNIXTIME(list.suppressed))')
            ->order('year DESC, month DESC, week DESC, day DESC, hour desc')
            ->queryAll());
		return $data;
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

            $id = isset ($this->model->list) ? $this->model->list->id : null;
            $modelName = isset ($this->model->list) ?  $this->model->list->modelName : null;
            $launchDate = $this->model->launchDate;

            if (isset ($launchDate)) {
                $chartData = $this->getInitialChartData ($launchDate, $id, $modelName);
            }
            $this->_setupScript = parent::getSetupScript ()."
                $(function () {
                    var chartUID = '$this->chartType$this->widgetUID';
                    x2[chartUID] = {};
                    x2[chartUID].chart = X2Chart.instantiateTemporarySubtype (
                        X2CampaignChart, {
                        ".(isset ($chartData) ?
                            "chartData :".CJSON::encode ($chartData)."," : '')."
                        actionParams: ".CJSON::encode (array (
                            'id'=>$id,
                            'modelName'=>$modelName,
                        )).",
                        translations: ".CJSON::encode ($this->getTranslations ()).",
                        getChartDataActionName: '".
                            Yii::app()->request->getScriptUrl ().
                                "/marketing/marketing/getCampaignChartData',
                        saveChartSetting: function (key, value, callback) {
                            this.lastChartSettings[key] = value;
                            x2.$widgetClass$this->widgetUID.setProperty (
                                'chartSettings', this.lastChartSettings, callback);
                        },
                        suppressDateRangeSelector: true,
                        suppressChartSettings: true,
                        lastChartSettings: ".CJSON::encode ($this->getChartSettings ()).",
                        widgetUID: '$this->widgetUID',
                        chartType: '$this->chartType',
                        chartSubtype: '".self::getJSONProperty (
                            $this->profile, 'chartSubtype', $this->widgetType, $this->widgetUID)."',
                        ".(isset ($launchDate) ?
                            "dataStartDate: $launchDate * 1000," : '')."
                    });
                    $(document).trigger ('$this->chartType' + 'Ready');
                });
            ";
        }
        return $this->_setupScript;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'suppressChartSettings' => true,
                    'suppressDateRangeSelector' => true,
                    'metricTypes' => array (
                        'sent'=>Yii::t('app', 'Sent'),
                        'opened'=>Yii::t('app', 'Opened'),
                        'clicked'=>Yii::t('app', 'Clicked'),
                        'unsubscribed'=>Yii::t('app', 'Unsubscribed'),
                        'suppressed'=>Yii::t('app', 'Suppressed'),
                    ),
                    'chartType' => 'campaignChart',
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
                    'CampaignChartWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/X2Chart/X2CampaignChart.js',
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
	 * Collect initial chart data so the client doesn't have to request it via ajax.
	 * Decreases time before chart render after page is loaded.
	 */
	protected function getInitialChartData ($dataStartDate, $id, $modelName) {
		$startDate = $dataStartDate;
		$endDate = time () + self::SECPERDAY;
		$events = self::getChartData (
            $id, $modelName, $startDate, $endDate);
		return $events;
	}
}

?>
