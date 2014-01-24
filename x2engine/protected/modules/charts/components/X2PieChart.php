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