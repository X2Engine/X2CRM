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
