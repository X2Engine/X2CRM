<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/*
Passes parameters to the _x2chart.php partial view and retrieves chart data or predefined
chart settings if specified by properties.
*/
class X2Chart extends X2Widget {

	const SECPERDAY = 86400;
	const SECPERWEEK = 604800;

	// The name of the action called to retrieve chart data
	public $getChartDataActionName;

	// The parameters required by the action specified with getChartDataActionName
	public $actionParams;

	// If false, indicates that the chart setting feature should be enabled.
	public $suppressChartSettings;

	public $suppressDateRangeSelector = false;

	// Determines which metrics can be plotted.
	public $metricTypes;

	public $chartType;

	public $widgetParams = array ();

	public $hideByDefault = false;

	/* 
	If true, retrieve chart data so the client doesn't have to make an ajax call
 	to initially populate the graph
	*/
	public $getDataOnPageLoad;


	public function init() {
		parent::init();
	}

	public function run() {
		$viewParams = array (
			'getChartDataActionName' => $this->getChartDataActionName,
			'actionParams' => $this->actionParams,
			'suppressChartSettings' => $this->suppressChartSettings,
			'suppressDateRangeSelector' => $this->suppressDateRangeSelector,
			'metricTypes' => $this->metricTypes,
			'chartType' => $this->chartType,
			'hideByDefault' => $this->hideByDefault
		);

		$cookies = Yii::app()->request->cookies;
		$socialSubtypes = json_decode (
			Dropdowns::model()->findByPk(113)->options,true);
		$visibilityFilters = array (
			'1'=>'Public',
			'0'=>'Private',
		);
		if ($this->chartType === 'eventsChart') {
		    $users = User::getNames ();
			$viewParams['userNames'] = $users;
			$viewParams['socialSubtypes'] = $socialSubtypes;
			$viewParams['visibilityFilters'] = $visibilityFilters;
		} else if ($this->chartType === 'usersChart') {
			$viewParams['socialSubtypes'] = $socialSubtypes;
			$viewParams['visibilityFilters'] = $visibilityFilters;
			$viewParams['eventTypes'] = 
				array ('all'=>Yii::t('app', 'All Events')) + Events::$eventLabels;
		} else if ($this->chartType === 'actionHistoryChart') {

			// chart starts at first action data
			$actionsStartDate = self::getFirstActionDate ();
			$viewParams['dataStartDate'] = $actionsStartDate;

			// if cookie is present, override default value
			if ((string) $cookies['actionHistoryChartshowRelationships']) {
				$this->actionParams['showRelationships'] = 
					$cookies['actionHistoryChartshowRelationships']->value;
			}
		}/* x2prostart */ else if ($this->chartType === 'campaignChart') {
			$viewParams['dataStartDate'] = $this->widgetParams['launchDate'];
		}/* x2proend */ 

		// Extract chart subtype from cookie to send to view
		$chartPage;
		if ($this->chartType === 'actionHistoryChart'/* x2prostart */ ||
			$this->chartType === 'campaignChart'/* x2proend */) {
			$chartPage = 'recordView';
		} else if ($this->chartType === 'eventsChart' ||
			$this->chartType === 'usersChart') {
			$chartPage = 'feed';
		}
		if ((string) $cookies[$chartPage.'ChartSelectedSubtype']) {
			$viewParams['subtype'] = 
				$cookies[$chartPage.'ChartSelectedSubtype']->value;
		}

		// supply view with names of predefined chart settings 
		if (!$this->suppressChartSettings) {
			$viewParams['chartSettingsDataProvider'] = 
				self::getChartSettingsProvider ($this->chartType);
		}
		
		if ($this->getDataOnPageLoad) {
			$this->setInitialChartData ($viewParams);
		}

		$this->render ('_x2chart', $viewParams);
	}

	/* x2prostart */
	/*
	Retrieves all campaign list records between start and end timestamp. Query results are used to
	populate the campaign chart.
	*/
	public static function getCampaignChartData(
		$id, $modelName, $startTimestamp, $endTimestamp) {

		$conditions = X2Model::model('Campaign')->getAccessCriteria()->condition;
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
				array(':listId'=>$id, 'startTimestamp' => $startTimestamp, 
					  'endTimestamp' => $endTimestamp))
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
				array(':listId'=>$id, 'startTimestamp' => $startTimestamp, 
					  'endTimestamp' => $endTimestamp))
            ->group('HOUR(FROM_UNIXTIME(list.opened)),'.
					'DAY(FROM_UNIXTIME(list.opened)),'.
					'WEEK(FROM_UNIXTIME(list.opened)),'.
					'MONTH(FROM_UNIXTIME(list.opened)),'.
					'YEAR(FROM_UNIXTIME(list.opened))')
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
				array(':listId'=>$id, 'startTimestamp' => $startTimestamp, 
					  'endTimestamp' => $endTimestamp))
            ->group('HOUR(FROM_UNIXTIME(list.unsubscribed)),'.
					'DAY(FROM_UNIXTIME(list.unsubscribed)),'.
					'WEEK(FROM_UNIXTIME(list.unsubscribed)),'.
					'MONTH(FROM_UNIXTIME(list.unsubscribed)),'.
					'YEAR(FROM_UNIXTIME(list.unsubscribed))')
			->order('year DESC, month DESC, week DESC, day DESC, hour desc')
			->queryAll());
		return $data;
	}
	/* x2proend */

	/*
	Retrieves all events between start and end timestamp. Query results are used to
	populate the activity feed chart.
	*/
	public static function getEventsData ($startTimestamp, $endTimestamp){
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

	/*
	Retrieves all actions of a certain type associated with particular record
	between the start and end timestamps. Query results are used to populate the
	action history chart. Optionally, related records' actions can also be retrieved.
	*/
	public static function getActionsData (
		$startTimestamp, $endTimestamp, $associationId, $associationType, 
		$showRelationships) {

		//printR (('startdate, enddate = '.$startTimestamp.', '.$endTimestamp), true);

		$associationType = strtolower ($associationType);

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
		$actions = $command ->group(
				'day, week, month, year, type')
			->order('year DESC, month DESC, week DESC, day DESC, hour desc')
			->queryAll();
		return $actions;
	}

    /*
    Returns a data provider containing chart settings records with the specified type
    */
	public static function getChartSettingsProvider ($chartType) {
		if ($chartType === 'usersChart') {
			$chartSettingsDataProvider = new CActiveDataProvider('ChartSetting', array(
						'criteria' => array(
							'condition' => 
								'userId='.Yii::app()->user->id.' AND '.
								'chartType="usersChart"',
							'order' => 'name ASC'
						)
					));
		} else if ($chartType === 'eventsChart') {
			$chartSettingsDataProvider = new CActiveDataProvider('ChartSetting', array(
						'criteria' => array(
							'condition' => 
								'userId='.Yii::app()->user->id.' AND '.
								'chartType="eventsChart"',
							'order' => 'name ASC'
						)
					));
		}
		return $chartSettingsDataProvider;
	}

	/*
	Collect initial chart data so the client doesn't have to request it via ajax .
	Decreases time before chart render after page is loaded.
	*/
	private function setInitialChartData (&$viewParams) {

		// Collect data for a record view page (i.e. the contact record view page).
		if ($this->chartType === 'actionHistoryChart'  &&
			$viewParams['dataStartDate'] !== false &&
			is_array ($this->actionParams) &&
			array_key_exists ('associationId', $this->actionParams) &&
			array_key_exists ('showRelationships', $this->actionParams) &&
			array_key_exists ('associationType', $this->actionParams)) {
			$this->preLoadActionsData ($viewParams);

		// Collect data for the activity feed chart
		} else if ($this->chartType === 'eventsChart' ||
		           $this->chartType === 'usersChart') {
			$this->preLoadEventsData ($viewParams);
		}/* x2prostart */ else if ($this->chartType === 'campaignChart') {
			$this->preLoadCampaignData ($viewParams);
		}/* x2proend */ 
	}

	/*
	Get earliest date of actions which will visible on the initial chart.
	*/
	private function getFirstActionDate  () {
		$associationId = $this->actionParams['associationId'];
		$associationType = $this->actionParams['associationType'];

		$cookies = Yii::app()->request->cookies;
		if ((string) $cookies['actionHistoryChartshowRelationships'] !== '' &&
			$cookies['actionHistoryChartshowRelationships']->value === 'true') {
			$associationCondition = self::getAssociationCond (
				$associationId, $associationType, 'true');
		} else {
			$associationCondition = self::getAssociationCond (
				$associationId, $associationType, 
				$this->actionParams['showRelationships']);
		}

		$command = Yii::app()->db->createCommand()
				->select('min(createDate)')
				->from('x2_actions');
		$command->where(
				$associationCondition.' AND '.
				'(visibility="1" OR assignedTo="'.Yii::app()->user->getName().'")', 
				array(
					'associationId' => $associationId, 
					'associationType' => $associationType
				));
		$actionsStartDate = $command->queryScalar();
		return $actionsStartDate;
	}
    
    /*
    Returns an array containing a start and end timestamp.
    If a date range cookie is set, the timestamps get generated. Otherwise start and
    end timestamp cookies are used. Specified default timestamps will be used when
    cookies are not set.
    */
	private function getStartEndTimestampFromCookies ($defaultStartTs, $defaultEndTs) {
		$cookies = Yii::app()->request->cookies;
		$startDate;
		$endDate;
		if ((string) $cookies[$this->chartType.'dateRange'] !== '' &&
			(string) $cookies[$this->chartType.'dateRange'] !== 'Custom') {
			$dateRange = $cookies[$this->chartType.'dateRange'];
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
				case 'Last Month':
				default:
					$startDate = mktime (0, 0, 0, date ('m') - 1, 1, date('o'));
					$endDate = mktime (0, 0, 0, date ('m'), 1, date('o')) - self::SECPERDAY;
					break;
				/*case 'Data Domain':
					break;*/
			}
		} else {
			if ((string) $cookies[$this->chartType.'startDate'] !== '') {
				$startDate = $cookies[$this->chartType.'startDate']->value / 1000;
			} else {
				$startDate = $defaultStartTs;
			}
			if ((string) $cookies[$this->chartType.'endDate'] !== '') { 
				$endDate = $cookies[$this->chartType.'endDate']->value / 1000;
			} else {
				$endDate = $defaultEndTs;
			}
		}
		$endDate += self::SECPERDAY - 1;
		return array ($startDate, $endDate);
	}

	/* x2prostart */
    /*
    Fetches chart data and Sets chartData attribute of view parameters
    */
	private function preLoadCampaignData (&$viewParams) {
		/*$tsDict = $this->getStartEndTimestampFromCookies (
			$viewParams['dataStartDate'], time () + self::SECPERDAY);*/
		$startDate = $viewParams['dataStartDate'];//$tsDict[0];
		$endDate = time () + self::SECPERDAY;//$tsDict[1];
		//printR (('startdate, enddate = '.$startDate.', '.$endDate), true);
		$events = self::getCampaignChartData (
				$this->actionParams['id'], 
				$this->actionParams['modelName'],
				$startDate, 
				$endDate);
		//printR ($events, true);
		$viewParams['chartData'] = $events;
	}
	/* x2proend */

    /*
    Fetches chart data and Sets chartData attribute of view parameters
    */
	private function preLoadActionsData (&$viewParams) {
		$tsDict = $this->getStartEndTimestampFromCookies (
			$viewParams['dataStartDate'], time () + self::SECPERDAY);
		$startDate = $tsDict[0];
		$endDate = $tsDict[1];
		//printR (('startdate, enddate = '.$startDate.', '.$endDate), true);
		$events = self::getActionsData ($startDate, $endDate,
				$this->actionParams['associationId'], 
				$this->actionParams['associationType'],
				$this->actionParams['showRelationships']);
		$viewParams['chartData'] = $events;
	}

    /*
    Fetches chart data and Sets chartData attribute of view parameters
    */
	private function preLoadEventsData (&$viewParams) {
		/* 
		Chart data only needs to be sent with initial response if chart was
		left open.
		*/
		$cookies = Yii::app()->request->cookies;
		if ((string) $cookies[$this->chartType.'chartIsShown'] !== '' &&
			$cookies[$this->chartType.'chartIsShown']->value === 'true') {

			$tsDict = $this->getStartEndTimestampFromCookies (time () - self::SECPERWEEK, time ());
			$startDate = $tsDict[0];
			$endDate = $tsDict[1];
			//printR (('startdate, enddate = '.$startDate.', '.$endDate), true);
			$events = self::getEventsData ($startDate, $endDate);
			$viewParams['chartData'] = $events;
		}
	}

	/*
	Private helper function. Returns a SQL conditional statement used in 
	queries to the Actions table. Restricts query results to actions of a certain
	type, associated with a particular id, and, if specified, actions of related
	records.
	*/
	private static function getAssociationCond (
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
							"associationType='{$relatedModel->myModelName}')";
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


}


