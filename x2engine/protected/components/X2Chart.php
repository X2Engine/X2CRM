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

/**
 */
class X2Chart extends X2Widget {
	public $getChartDataActionName;
	public $actionParams;
	public $suppressChartSettings;
	public $hideByDefault = false;
	public $actionsStartDate = null;
	public $metricTypes;
	public $chartType;
	public $chartPage;
	public $getDataOnPageLoad;

	public function init() {
		parent::init();
	}

	public function run() {
		$viewParams = array (
			'getChartDataActionName' => $this->getChartDataActionName,
			'actionParams' => $this->actionParams,
			'suppressChartSettings' => $this->suppressChartSettings,
			'hideByDefault' => $this->hideByDefault,
			'metricTypes' => $this->metricTypes,
			'chartType' => $this->chartType,
			'actionsStartDate' => $this->actionsStartDate,
			'chartPage' => $this->chartPage
		);
		if (!$this->suppressChartSettings) {
			$chartSettingsDataProvider = new CActiveDataProvider('ChartSetting', array(
					'criteria' => array(
						'condition' => 'userId='.Yii::app()->user->id,
						'order' => 'name ASC'
					)
				));
			$viewParams['chartSettingsDataProvider'] = $chartSettingsDataProvider;
		}
		
		if ($this->getDataOnPageLoad) {
			$secPerDay = 86400;
			if ($this->chartPage === 'recordView'  &&
				is_array ($this->actionParams) &&
				array_key_exists ('associationId', $this->actionParams) &&
				array_key_exists ('associationType', $this->actionParams)) {
				$actions = GetActionsBetweenAction::getData (
					$this->actionsStartDate, time () + $secPerDay,
					$this->actionParams['associationId'], 
					$this->actionParams['associationType']);
				$viewParams['chartData'] = $actions;
			} else if ($this->chartPage === 'activityFeed') {
				$cookies = Yii::app()->request->cookies;
				if ((string) $cookies['activityFeedchartIsShown'] !== '' &&
					$cookies['activityFeedchartIsShown']->value === 'true') {

					$startDate;
					$endDate;
					if ((string) $cookies['activityFeedstartDate'] !== '') {
						$startDate = $cookies['activityFeedstartDate']->value / 1000;
					} else {
						$secPerWeek = 604800;
						$startDate = time () - $secPerWeek;
					}
					if ((string) $cookies['activityFeedendDate'] !== '') { 
						$endDate = $cookies['activityFeedendDate']->value / 1000;
					} else {
						$endDate = time ();
					}
					$endDate += $secPerDay;
					$events = SiteController::getChartData (
						$startDate, $endDate);
					$viewParams['chartData'] = $events;
				}
			}
		}
		$this->render ('_x2chart', $viewParams);
	}
}

