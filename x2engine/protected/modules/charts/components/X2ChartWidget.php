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