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