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
class X2BubbleChart extends X2ChartWidget {

	private $plotData = array();

	public function init() {
		$this->defaultChartOptions = array(
			'seriesDefaults' => array(
				'renderer' => 'jquery.jqplot.BubbleRenderer',
				'rendererOptions' => array(
					// Turn off filling of slices.
					'bubbleGradients' => true,
					'bubbleAlpha' => 0.6,
					'highlightAlpha' => 0.8
				),
				'shadow' => true,
				'shadowAlpha' => 0.2
			),
		);
		$this->defaultOptions = array(
			'use-column-names' => true,
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

		$i = 0;
		if (isset($this->options['slice'])) {
			if (isset($this->options['slice']['part']))
				$slice = $this->options['slice']['part'];
			else
				throw new CException(Yii::t('app', 'The "slice[part] property is not valid'));

			if ($slice > 0) {
				$c = count($data);
				switch ($slice) {
					case 1: // smallest
						$data = array_slice($data, 0, floor(0.25 * $c), $preserve_keys = true);
						break;
					case 2: // others
						$data = array_slice($data, floor(0.25 * $c) + 1, floor(0.75 * $c), $preserve_keys = true);
						break;
					case 3: // largest
						$data = array_slice($data, floor(0.75 * $c) + 1, $c, $preserve_keys = true);
						break;
				}
			}
		}
		foreach ($data as $val) {
			$xval = 0 + $val['xval'];
			if (!isset($xval) || strlen($xval) == 0) {
				if ($skipNull)
					continue;
				else
					$xval = Yii::t('charts', 'Unknown');
			}
			$rval = 0 + $val['rval'];
			$rlab = $val['rlab'];
			$yval = 0 + $val['yval'];

			if ($yval < $otherThreshold) {
				$otherTotal = $otherTotal + $yval;
			} else {
				$this->plotData[$i] = array($xval, $yval, $rval, $rlab);
				$i = $i + 1;
			}
		}

		$cs = Yii::app()->clientScript;
		$id = $this->htmlOptions['id'];
		$chartVals = CJavaScript::encode(array($this->plotData));

		//TODO Clean up Hack to fix up JS object ref

		$cs->registerPackage('jqbubbleplot');
		$jsChartOptions = CJavaScript::encode($this->chartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.BubbleRenderer'", "$.jqplot.BubbleRenderer", $jsChartOptions);
		$cmd = "$.jqplot('$id', $chartVals, $jsChartOptions)";
                if(count($this->plotData)!=0)
		$cs->registerScript($id, $cmd, CClientScript::POS_LOAD);
	}

}

?>