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
class X2SparkChart extends X2ChartWidget {

	public function init() {
		$this->defaultChartOptions = array(
			'type' => 'pie',
			'width' => 'auto',
			'height' => 'auto',
			'lineColor' => 'black',
			'fillColor' => false,
			'composite' => false
		);
		$this->defaultOptions = array(
			'use-column-names' => false,
			'other-threshold' => 0
		);
		parent::init();
	}

	public function renderItems($data = array()) {

		// fetch the data
		$id = $this->htmlOptions['id'];
		$incx = $this->options['type'] == 'line' ? true : false;
		$otherThreshold = $this->options['other-threshold'];
		$otherTotal = 0;

//		echo CHtml::openTag('span', );
		$vals = '';
		$x = 1;
		foreach ($data as $val) {
			$yval = 0+$val[1];
			if ($yval < $otherThreshold) {
				$otherTotal = $otherTotal + $yval;
			} else {
				if (strlen($vals) > 0)
					$vals = $vals . ',';
				if ($incx)
					$vals = $vals . $x . ":";
				$x = $x + 1;
				$vals = $vals . $yval;
			}
		}
		if ($otherTotal > 0) {
			if (strlen($vals) > 0)
				$vals = $vals . ',';
			if ($incx)
				$vals = $vals . $x . ':';
			$vals = $vals . $otherTotal;
		}
		echo '<!-- ' . $vals . ' -->';
//		echo CHtml::closeTag('span');

		$cs = Yii::app()->clientScript;
		$jsChartOptions = CJavaScript::encode($this->chartOptions);

		$cs->registerPackage('jquerysparkline');
		$cmd = "$('#x2Chart_$id').sparkline('html',$jsChartOptions)";
		$cs->registerScript($id, $cmd, CClientScript::POS_LOAD);
	}

}
?>