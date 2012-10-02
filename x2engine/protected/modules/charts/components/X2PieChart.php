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
			'legend' => array('show' => true, 'location' => 'e', 'placement' => 'outsideGrid')
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
					$xval = Yii::t('dashboard', 'Unknown');
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
			$plotData[$i] = array(Yii::t('dashboard', 'Other'), $otherTotal);
		}

		$cs = Yii::app()->clientScript;
		$id = $this->htmlOptions['id'];
		$chartVals = CJavaScript::encode(array($plotData));

		//TODO Clean up Hack to fix up JS object ref

		$cs->registerPackage('jqpieplot');
		$jsChartOptions = CJavaScript::encode($this->chartOptions);
		$jsChartOptions = str_replace("'jquery.jqplot.PieRenderer'", "$.jqplot.PieRenderer", $jsChartOptions);
		$cmd = "$.jqplot('$id', $chartVals, $jsChartOptions)";
                if(count($plotData)!=0)
		$cs->registerScript($id, $cmd, CClientScript::POS_LOAD);
	}

}

?>