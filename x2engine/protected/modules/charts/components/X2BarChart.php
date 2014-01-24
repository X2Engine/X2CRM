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