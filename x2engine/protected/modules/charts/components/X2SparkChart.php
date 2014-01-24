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