<?php
/*********************************************************************************
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
 ********************************************************************************/


// remove the following lines when in production mode
//defined('YII_DEBUG') or define('YII_DEBUG', true);
// specify how many levels of call stack should be shown in each log message
//defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

/**
 * @package X2CRM.modules.charts 
 */
class ChartsModule extends CWebModule {
	public $packages = array();
	private $_assetsUrl;

	public function getAssetsUrl() {
		if ($this->_assetsUrl === null)
			$this->_assetsUrl = Yii::app()->getAssetManager()->publish(
					Yii::getPathOfAlias('charts.assets'));
		return $this->_assetsUrl;
	}

	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application
		// import the module-level models and components
		$this->setImport(array(
			'charts.models.*',
			'charts.components.*',
		));

		// Set module specific javascript packages

		$this->packages = array(
			 'jquery' => array(
				 'basePath' => $this->getBasePath(),
				 'baseUrl' => $this->assetsUrl,
				 'js' => array(
					 YII_DEBUG ? 'js/jquery.js' : 'js/jquery.min.js'
				 )
			 ),
			'jquerysparkline' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'css' => array(
					'css/charts.css'
				),
				'js' => array(
					YII_DEBUG ? 'js/splunk/jquery.sparkline.js' : 'js/splunk/jquery.sparkline.min.js'
				),
				'depends' => array('jquery'),
			),
			'jqplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'css' => array(
					YII_DEBUG ? 'js/jqplot/jquery.jqplot.css' : 'js/jqplot/jquery.jqplot.min.css',
					'css/charts.css'
				),
				'js' => array(
					YII_DEBUG ? 'js/jqplot/jquery.jqplot.js' : 'js/jqplot/jquery.jqplot.min.js',
				),
				'depends' => array('jquery'),
			),
			'jqlineplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.canvasTextRenderer.js' : 'js/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.categoryAxisRenderer.js' : 'js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js' : 'js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js'
				),
				'depends' => array('jqplot'),
			),
			'jqpieplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.pieRenderer.js' : 'js/jqplot/plugins/jqplot.pieRenderer.min.js',
				),
				'depends' => array('jqplot'),
			),
			'jqbubbleplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.bubbleRenderer.js' : 'js/jqplot/plugins/jqplot.bubbleRenderer.min.js',
				),
				'depends' => array('jqplot'),
			),
			'jqfunnelplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.funnelRenderer.js' : 'js/jqplot/plugins/jqplot.funnelRenderer.min.js',
				),
				'depends' => array('jqplot'),
			),
			'jqbarplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.barRenderer.js' : 'js/jqplot/plugins/jqplot.barRenderer.min.js',
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.canvasTextRenderer.js' : 'js/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.categoryAxisRenderer.js' : 'js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.canvasAxisTickRenderer.js' : 'js/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.dateAxisRenderer.js' : 'js/jqplot/plugins/jqplot.dateAxisRenderer.min.js',
					YII_DEBUG ? 'js/jqplot/plugins/jqplot.pointLabels.js' : 'js/jqplot/plugins/jqplot.pointLabels.min.js',
				),
				'depends' => array('jqplot'),
			)
		);
		Yii::app()->clientScript->packages = $this->packages;

		// set module layout
		// $this->layout = 'main';
	}

	public function beforeControllerAction($controller, $action) {
		if (parent::beforeControllerAction($controller, $action)) {
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}

}
?>
