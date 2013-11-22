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
					 'js/jquery.js'
				 )
			 ),
			'jquerysparkline' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'css' => array(
					'css/charts.css'
				),
				'js' => array(
					'js/splunk/jquery.sparkline.js'
				),
				'depends' => array('jquery'),
			),
			'jqplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'css' => array(
					'js/jqplot/jquery.jqplot.css',
					'css/charts.css'
				),
				'js' => array(
					'js/jqplot/jquery.jqplot.js'
				),
				'depends' => array('jquery'),
			),
			'jqlineplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					'js/jqplot/plugins/jqplot.canvasTextRenderer.js',
					'js/jqplot/plugins/jqplot.categoryAxisRenderer.js',
					'js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js'
				),
				'depends' => array('jqplot'),
			),
			'jqpieplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					'js/jqplot/plugins/jqplot.pieRenderer.js',
				),
				'depends' => array('jqplot'),
			),
			'jqbubbleplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					'js/jqplot/plugins/jqplot.bubbleRenderer.js',
				),
				'depends' => array('jqplot'),
			),
			'jqfunnelplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					'js/jqplot/plugins/jqplot.funnelRenderer.js',
				),
				'depends' => array('jqplot'),
			),
			'jqbarplot' => array(
				'basePath' => $this->getBasePath(),
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					'js/jqplot/plugins/jqplot.barRenderer.js',
					'js/jqplot/plugins/jqplot.canvasTextRenderer.js',
					'js/jqplot/plugins/jqplot.categoryAxisRenderer.js',
					'js/jqplot/plugins/jqplot.canvasAxisTickRenderer.js',
					'js/jqplot/plugins/jqplot.dateAxisRenderer.js',
					'js/jqplot/plugins/jqplot.pointLabels.js',
				),
				'depends' => array('jqplot'),
			)
		);
        if (AuxLib::isIE8 ()) {
            $this->packages['jqplot']['js'][] = 'js/jqplot/excanvas.js';
        }

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
