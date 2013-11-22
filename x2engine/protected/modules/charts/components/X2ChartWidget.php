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

/**
 * @package X2CRM.modules.charts.components 
 */
abstract class X2ChartWidget extends CWidget {

	public $tagName = 'div';
	public $htmlOptions = array();
	public $chartOptions = array();
	public $model; // active record to get hold of table metadata, db handle, ...
	public $range = array();  // range properties: type, label, value, options[],  column
	public $filters = array();  // filter properties: expression
	public $options = array();  // chart properties: statistic, sql, type, other-threshold, label, x-axis[label, column], y-axis[label,column], options[]
	public $actions = array(); // actions:  javscript handlers
	protected $defaultChartOptions = array();
	protected $defaultOptions = array();
	protected $defaultHtmlOptions = array('class' => 'x2-chart');
	protected $dbcmdBuilder;
	protected $dbcmd;
	protected $sqlCmd;
	protected $data;

	/*
	 * select x-axis-col, count(*), sum(y-axis-col) from table
	 * where range-col >= :min range-col <= :max and filter in (:fil)
	 * group by x-axis-col
	 */

	public function init() {
		// TODO translate exception message

		if ($this->model === null)
			throw new CException(Yii::t('app', 'The "model" property cannot be empty.'));
		if ($this->options === null)
			throw new CException(Yii::t('app', 'The "options" property cannot be empty.'));

		if (is_string($this->model)) {
			$this->dbcmdBuilder = Yii::app()->getDb()->getCommandBuilder();
			if (strpos($this->model, 'select') !== false)
				$this->sqlCmd = $this->model;
		}else if ($this->model instanceOf CActiveRecord)
			$this->dbcmdBuilder = $this->model->getCommandBuilder();
		else if ($this->model instanceOf X2ChartWidget)
			$this->dbcmdBuilder = $this->model->dbcmdBuilder;

		if (is_string($this->chartOptions)) {
			if (!$this->chartOptions = CJSON::decode($this->chartOptions))
				throw new CException(Yii::t('app', 'The "chart" property is not valid JSON.'));
		}
		// merge chart options with default values
		$this->chartOptions = CMap::mergeArray($this->defaultChartOptions, $this->chartOptions);
		$this->options = CMap::mergeArray($this->defaultOptions, $this->options);
		$this->htmlOptions = CMap::mergeArray($this->defaultHtmlOptions, $this->htmlOptions);

		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id'] = 'x2Chart_' . $this->getId();
	}

	/**
	 * fetches the chart data.
	 */
	protected function getData() {
		$this->data = array();
		try {
			if (is_string($this->model))
				$tn = $this->model;
			else if ($this->model instanceof CActiveRecord)
				$tn = $this->model->tableName();
			else if ($this->model instanceof X2ChartWidget)
				$this->dbcmd = $this->model->dbcmd;
			else
				throw new CException(Yii::t('app', 'The "model property is not valid'));

			if (!isset($this->dbcmd)) {
				if (!isset($tn))
					throw new CException(Yii::t('app', 'The "model[tablename] property is not valid'));
				$cmd = "select ";
				if (isset($this->options['x-axis']['label']))
					$cmd = $cmd . $this->options['x-axis']['label'] . " as xlab, ";
				if (isset($this->options['x-axis']['column']))
					$cmd = $cmd . $this->options['x-axis']['column'] . " as xval, ";
				else
					throw new CException(Yii::t('app', 'The "options[x-axis]" property is not valid'));

				if ($this->options['statistic'] == 'none') {
					if (isset($this->options['r-axis']['label']))
						$cmd = $cmd . $this->options['r-axis']['label'] . " as rlab, ";
					if (isset($this->options['r-axis']['column']))
						$cmd = $cmd . $this->options['r-axis']['column'] . " as rval, ";
					if (isset($this->options['y-axis']['label']))
						$cmd = $cmd . $this->options['y-axis']['label'] . " as ylab, ";
					if (isset($this->options['y-axis']['column']))
						$cmd = $cmd . $this->options['y-axis']['column'] . " as yval ";
					else
						throw new CException(Yii::t('app', 'The "options[y-axis]" property is not valid'));
				} else {
					$groupby = " group by xval";
					for ($i = 1; $i < 3; $i = $i + 1) {
						if (isset($this->options['x-axis' . $i]['column'])) {
							$cmd = $cmd . $this->options['x-axis' . $i]['column'] . " as xval$i, ";
							$groupby = $groupby . ", xval$i";
						}
					}
					switch ($this->options['statistic']) {
						case 'count':
							$cmd = $cmd . "count(";
							break;
						case 'sum':
							$cmd = $cmd . "sum(";
							break;
						default:
							throw new CException(Yii::t('app', 'The "options[statistic]" property is not valid'));
					}
					if (isset($this->options['y-axis']['column']))
						$cmd = $cmd . $this->options['y-axis']['column'] . ")";
					else if ($this->options['statistic'] == 'count')
						$cmd = $cmd . "*) as yval";
					else
						throw new CException(Yii::t('app', 'The "options[y-axis]" property is not valid'));
				}
				$cmd = $cmd . " from " . $tn . ' where 1 = 1';
				foreach ($this->filters as $fil) {
					$cmd = $cmd . ' and ' . $fil;
				}
				if (isset($groupby))
					$cmd = $cmd . $groupby;
				if (isset($this->options['orderby']))
					$this->sqlCmd = $cmd . ' order by ' . $this->options['orderby'];
				else
					$this->sqlCmd = $cmd;
				$this->dbcmd = $this->dbcmdBuilder->createSqlCommand($this->sqlCmd);
				$this->data = $this->dbcmd->queryAll($this->options['use-column-names']);
			}else
				$this->data = $this->model->data;
		} catch (Exception $e) {
			var_dump($e->getMessage());
		}
		return $this->data;
	}

	/**
	 * Renders the chart widget.
	 */
	public function run() {

		$this->registerClientScript();

		echo CHtml::openTag($this->tagName, $this->htmlOptions) . "\n";

		$this->renderContent();

		echo CHtml::closeTag($this->tagName);
	}

	public function renderContent() {
		ob_start();
		echo $this->renderItems($this->getData());
		ob_end_flush();
	}

	abstract public function renderItems($data = array());

	protected function registerClientScript() {
		$cs = Yii::app()->clientScript;

		$i = 0;
		foreach ($this->actions as $ext) {
			$cs->registerScript("ext" . $i++, $ext, CClientScript::POS_BEGIN);
		}
	}

}

?>