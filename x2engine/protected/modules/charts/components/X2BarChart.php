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
class X2BarChart extends X2ChartWidget {

	private $plotTicks = array();

	public function init() {
		$this->defaultChartOptions = array(
			'seriesDefaults' => array(
				'renderer' => 'jquery.jqplot.BarRenderer',
				'rendererOptions' => array(
					'fill-to-zero' => true,
				)
			),
			'legend' => array('show' => true, 'location' => 'e', 'placement' => 'outsideGrid'),
			'axes' => array(
				// Use a category axis on the x axis and use our custom ticks.
				'xaxis' => array(
					'renderer' => 'jquery.jqplot.CategoryAxisRenderer',
					'ticks' => array()
				),
				// Pad the y axis just a little so bars can get close to, but
				// not touch, the grid boundaries.  1.2 is the default padding.
				'yaxis' => array(
					'pad' => 1.05,
					'tickOptions' => array('formatString' => '$%d')
				)
			)
		);
		$this->defaultOptions = array(
			'use-column-names'=>false,
			'other-threshold' => 1,
			'statistic'=>'count',
			'skip-null'=>true
		);
		parent::init();
	}

	public function renderItems($data = array()) {

		$id = $this->getId();
		$otherThreshold = $this->options['other-threshold'];
		$skipNull = $this->options['skip-null'];
		$otherTotal = 0;

		$plotData = array();
		$i = 0;
		foreach ($data as $val) {
			$xval = $val[0];
			if (!isset($xval) || strlen($xval) == 0){
				if ($skipNull)
					continue;
				else
					$xval = Yii::t('charts', 'Unknown');
				}
			$yval = 0 + $val[1];
			if ($yval < $otherThreshold) {
				$otherTotal = $otherTotal + $yval;
			} else {
				$plotData[$i] = $yval;
				$this->plotTicks[$i] = $xval;
				$i = $i + 1;
			}
		}
		if ($otherTotal > 0) {
			$plotData[$i] = $otherTotal;
			$this->plotTicks[$i] = Yii::t('app', 'Other');
		}

		$cs = Yii::app()->clientScript;
		$id = $this->htmlOptions['id'];
		$chartVals = CJavaScript::encode(array($plotData));

		$this->chartOptions['axes']['xaxis']['ticks'] = $this->plotTicks;

		//TODO Clean up Hack to fix up JS object ref

		$cs->registerPackage('jqbarplot');
		$jsChartOptions = CJavaScript::encode($this->chartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.BarRenderer'", "$.jqplot.BarRenderer", $jsChartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.CategoryAxisRenderer'", "$.jqplot.CategoryAxisRenderer", $jsChartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.DateAxisRenderer'", "$.jqplot.DateAxisRenderer", $jsChartOptions);
		$cmd = "$.jqplot('$id', $chartVals, $jsChartOptions)";
                if(count($plotData)!=0)
                    $cs->registerScript($id, $cmd, CClientScript::POS_LOAD);
	}

}

?>