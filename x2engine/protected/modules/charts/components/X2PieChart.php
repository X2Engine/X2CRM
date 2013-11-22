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
class X2PieChart extends X2ChartWidget {

	public function init() {
		$this->defaultChartOptions = array(
			'seriesDefaults' => array(
				'renderer' => 'jquery.jqplot.PieRenderer',
				'rendererOptions' => array(
					// Turn off filling of slices.
					'fill' => true,
					'showDataLabels' => true,
					// Add a margin to seperate the slices.
					'sliceMargin' => 4,
					// stroke the slices with a little thicker line.
					'lineWidth' => 5
				)
			),
			'legend' => array('show' => true, 'location' => 'e', 'placement' => 'outsideGrid'),
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
				$plotData[$i] = array($xval, $yval);
				$i = $i + 1;
			}
		}
		if ($otherTotal > 0) {
			$plotData[$i] = array(Yii::t('charts', 'Other'), $otherTotal);
		}
        
		$cs = Yii::app()->clientScript;
		$id = $this->htmlOptions['id'];
		$chartVals = CJavaScript::encode(array($plotData));

		//TODO Clean up Hack to fix up JS object ref

		$cs->registerPackage('jqpieplot');
		$jsChartOptions = CJavaScript::encode($this->chartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.PieRenderer'", "$.jqplot.PieRenderer", $jsChartOptions);
		$cmd = "$.jqplot('$id', $chartVals, $jsChartOptions)";
        if(count($plotData)!=0){
            $cs->registerScript($id, $cmd, CClientScript::POS_LOAD);
        }
	}

}

?>