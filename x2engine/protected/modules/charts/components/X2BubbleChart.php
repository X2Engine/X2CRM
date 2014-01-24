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