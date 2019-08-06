<?php 
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/






/**
 * Renders a dashboard for dataWidgets
 * @package application.components
 * @author Alex Rowe <alex@x2engine.com>
 */
class ChartDashboard extends ChartDashboardBase {
	
	public $viewFile = 'chartDashboard';

	public function init() {
		parent::init();
	}

	public function run() {
		parent::run();

		if (AuxLib::getIEVer() < 9) {

			if (!$this->report) {
				X2Html::IEBanner();
			}
			return;
		}

		$this->registerPackages();
		$this->render($this->viewFile);
	}

	/**
	 * Echos the widgets in a specific container number 
	 * @param $containerNumber Number of container (1 or 2)
	 */
	public function displayWidgets ($containerNumber) {

		if ($this->report) {
			$profile = $this->report;
		} else {
		    $profile = Yii::app()->params->profile;
		}

	    $layout = $profile->dataWidgetLayout;

	    // display profile widgets in order
	    foreach ($layout as $widgetLayoutKey => $settings) {
	        if ($settings['containerNumber'] == $containerNumber) {
	            if( $this->filterReport($settings['chartId']) ){

	            	// $force = isset($this->report);
	            	SortableWidget::instantiateWidget($widgetLayoutKey, $profile, 'data');	
	            }
	        }
	    }
	}

	 /**
	 * Returns an array of charts currently soft deleted
	 */
	public function getHiddenCharts() {
	    if ($this->report) {
	    	$profile = $this->report;
	    } else {
	        $profile = Yii::app()->params->profile;
	    }
	    $settings = $profile->dataWidgetLayout;

	    $widgets = array();
	    foreach ($settings as $key => $setting) {
	        if( $setting['hidden'] && 
	        	$this->filterReport( $setting['chartId'] )) {
	            $widgets[$key] = $setting;
	        }
	    }
	    return $widgets;
	}

	public function getReportList() {
		$reports = X2Model::model('Reports')->findAll();

		$options = array();
		foreach($reports as $report) {
			$link = Yii::app()->createUrl (
				'/reports', array ('id' => $report->id));
			$content = "<a href='$link?chart=1'> ".CHtml::encode($report->name)."</a>";

			$options[] = array(
					'class' => 'report-option',
					'content' => $content
				);
		}

		if (empty($options) ){

			echo X2Html::tag('div', array(
					'id'=> 'no-reports'
				), Yii::t('charts', "Create a report to get started"));
		}

		return X2Html::ul ($options);
	}

	public function registerPackages() {
		$packages = array(
			'auxlib' => array(
			    'baseUrl' => Yii::app()->request->baseUrl,
			    'js' => array(
			        'js/auxlib.js',
			    ),
			),
			'topFlashJS' => array(
			    'baseUrl' => Yii::app()->request->baseUrl,
			    'js' => array(
			        'js/TopFlashes.js',
			    ),
			),
		    'dataWidgetManagerJS' => array(
		    	'baseUrl' => Yii::app()->baseUrl.'/js',
		        'js' => array(
		            'jquery.fullscreen-min.js',
		            'sortableWidgets/SortableWidgetManager.js', 
		            'sortableWidgets/TwoColumnSortableWidgetManager.js', 
		            'sortableWidgets/ProfileWidgetManager.js' ,
		            'PopupDropdownMenu.js', 
		            'DataWidgetManager.js'
		        ),
		        'depends' => array ('auxlib', 'topFlashJS'),
		    ),
		    'chartDashboardCSS' => array(
		    	'baseUrl' => Yii::app()->theme->baseUrl.'/css',
		        'css' => array( 
		        	'components/chartDashboard.css' 
		        )
		    )
		);

		Yii::app()->clientScript->registerPackages ($packages, CClientScript::POS_END);
		Yii::app()->clientScript->registerCSS ('dashboardPageModificationCSS', '
		#content {
			border: none !important;
		}
		');

		// If on a report, set the model to report. 
		// Otherwise set blank for default (Profile Model)
		if ($this->report) {
			$settingsModelName = 'Reports';
			$settingsModelId = $this->report->id;
		} else {
			$settingsModelName = '';
			$settingsModelId = '';
		}

		$jsParams = array(
			'settingsModelName' => $settingsModelName,
			'settingsModelId' => $settingsModelId,
			'translations' => array(
				'saveChart' => Yii::t('charts', 'Report must be saved before creating a chart'),
				'noWidgets' => Yii::t(
                    'charts', 'There are no widgets to print. Add some to this dashboard first.'),
            )
		);

		Yii::app()->clientScript->registerScript('ChartDasboardJS', 
			"x2.dataWidgetManager = new x2.DataWidgetManager(".CJSON::encode($jsParams).");"
		, CClientScript::POS_END);

	}

}
