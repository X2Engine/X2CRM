<?php

/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this
 *   list of conditions and the following disclaimer in the documentation and/or
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be
 *   used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */

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
					$xval = Yii::t('dashboard', 'Unknown');
			}
			$xval1 = $val[1];
			if (!isset($xval1) || strlen($xval1) == 0) {
				if ($skipNull)
					continue;
				else
					$xval1 = Yii::t('dashboard', 'Unknown');
			}

			if (isset($oldx) && $oldx != $xval && $otherTotal > 0) {
				$this->storeValue($oldx, Yii::t('dashboard', 'Other'), $otherTotal);
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
			$this->storeValue($oldx, Yii::t('dashboard', 'Other'), $otherTotal);
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