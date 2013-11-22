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
class X2StackedBarChart extends X2ChartWidget {

	private $plotData = array();
	private $plotTicks = array();
	private $plotSeries = array();

	public function init() {
		$this->defaultChartOptions = array(
			'stackSeries' => true,
			'axesDefaults' => array(
				'tickRenderer' => 'jquery.jqplot.CanvasAxisTickRenderer',
				'tickOptions' => array(
					'angle' => -45,
				)
			),
			'seriesDefaults' => array(
				'renderer' => 'jquery.jqplot.BarRenderer',
				'rendererOptions' => array(
					'barMargin' => 30,
				),
				'pointLabels' => array(
					'show' => true,
					'location' => 's',
					'hideZeros' => true)
			),
			'series' => array(),
			'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'outsideGrid'),
			'axes' => array(
				// Use a category axis on the x axis and use our custom ticks.
				'xaxis' => array(
					'renderer' => 'jquery.jqplot.CategoryAxisRenderer',
					'ticks' => array(),
				),
				// Pad the y axis just a little so bars can get close to, but
				// not touch, the grid boundaries.  1.2 is the default padding.
				'yaxis' => array(
					'padMin' => 0
				)
			),
			'seriesColors'=> array('#1D4C8C', '#45B41D', '#CEC415', '#CA8613', '#BC0D2C', '#5A1992', '#156A86', '#69B10A', '#C6B019', '#C87010', '#AB074F', '#3D1783'),
			'grid' => array(
			    'background'=> '#FFFFFF',
			    'borderColor'=> '#000000',
			    'borderWidth'=> 1.0,
			)
		);
		$this->defaultOptions = array(
			'use-column-names' => false,
			'other-threshold' => 1,
			'statistic' => 'count',
			'skip-null' => true
		);
		parent::init();
	}

	public function renderItems($data = array()) {

		$id = $this->getId();
		$otherThreshold = $this->options['other-threshold'];
		$skipNull = $this->options['skip-null'];
		$otherTotal = 0;
		$xval = null;
		$xval1 = null;

		$oldx = null;
		$oldx1 = null;

		foreach ($data as $val) {
			$xval = $val[0];
			if (!isset($xval) || strlen($xval) == 0) {
				if ($skipNull)
					continue;
				else
					$xval = Yii::t('charts', 'Unknown');
			}
			$xval1 = $val[1];
			if (!isset($xval1) || strlen($xval1) == 0) {
				if ($skipNull)
					continue;
				else
					$xval1 = Yii::t('charts', 'Unknown');
			}

			if (isset($oldx) && $oldx != $xval && $otherTotal > 0) {
				$this->storeValue($oldx, Yii::t('charts', 'Other'), $otherTotal);
				$otherTotal = 0;
			}
			$yval = 0 + $val[2];
			if ($yval < $otherThreshold) {
				$otherTotal = $otherTotal + $yval;
			} else {
				$this->storeValue($xval, $xval1, $yval);
			}
			$oldx = $xval;
			$oldx1 = $xval1;
		}
		if (isset($oldx1) && $otherTotal > 0) {
			$this->storeValue($oldx, Yii::t('charts', 'Other'), $otherTotal);
		}
		foreach ($this->plotSeries as $val) {
			$tmpa = CMap::mergeArray($this->plotTicks, $this->plotData[$val]);
			$this->plotData[$val] = array_values($tmpa);
			$this->chartOptions['series'][] = array('label' => $val);
		}
		$this->plotSeries = array_values($this->plotSeries);
		$this->plotTicks = array_keys($this->plotTicks);

		$cs = Yii::app()->clientScript;
		$id = $this->htmlOptions['id'];
		$chartVals = CJavaScript::encode(array_values($this->plotData));

		$this->chartOptions['axes']['xaxis']['ticks'] = $this->plotTicks;

		//TODO Clean up Hack to fix up JS object ref

		$cs->registerPackage('jqbarplot');
		$jsChartOptions = CJavaScript::encode($this->chartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.BarRenderer'", "$.jqplot.BarRenderer", $jsChartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.CategoryAxisRenderer'", "$.jqplot.CategoryAxisRenderer", $jsChartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.CanvasAxisTickRenderer'", "$.jqplot.CanvasAxisTickRenderer", $jsChartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.DateAxisRenderer'", "$.jqplot.DateAxisRenderer", $jsChartOptions);
		$cmd = "$.jqplot('$id', $chartVals, $jsChartOptions)";
                if(count($this->plotData)!=0)
		$cs->registerScript($id, $cmd, CClientScript::POS_LOAD);
	}

	private function storeValue($xval, $xval1, $yval) {
		if (!isset($this->plotSeries[$xval1]))
			$this->plotSeries[$xval1] = $xval1;
		if (!isset($this->plotData[$xval1]))
			$this->plotData[$xval1] = array();
		$this->plotData[$xval1][$xval] = $yval;
		if (!isset($this->plotTicks[$xval])) {
			$this->plotTicks[$xval] = 0;
		}
	}

}

?>