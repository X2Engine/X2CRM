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
					$xval = Yii::t('dashboard', 'Unknown');
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