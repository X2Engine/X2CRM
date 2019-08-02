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
 * Class to handle the main profile page
 * @package application.components
 * @author Alex Rowe <alex@x2engine.com>
 */
class ProfileDashboardManager extends CWidget {

	/**
	 * @var model Profile to be rendered
	 */
	public $model;

	/**
	 * @var string The view file to be rendered
	 */
	public $viewFile = 'profileDashboard';

	/**
	 * @var float The default width of the first column (%percent)
	 */
	public $columnWidth = 52;

	/**
	 * @var float the margin on each percentage. 
	 * 50% column width with 1% margin would result in 49% / 49% columns
	 */
	public $columnMargin = 0.0;

	public function init() {
		Yii::app()->clientScript->registerPackages($this->getPackages(), true);

		$miscLayoutSettings = Yii::app()->params->profile->miscLayoutSettings;
		if (isset($miscLayoutSettings['columnWidth'])) {
			$this->columnWidth = $miscLayoutSettings['columnWidth'];
		}

		$this->instantiateJS();

		parent::init ();
	}

	public function run() {
		$this->render('layoutEditor', array ('namespace' => 'profile'));
		parent::run ();
	}

	public function renderContainer ($container) {
		$this->render('profileDashboard', array('container' => $container));
	}

	public function getPackages () {
        $baseUrl = Yii::app()->getBaseUrl ();
		$packages = array(
            'layoutEditorCss' => array(
                'baseUrl' => Yii::app()->theme->getBaseUrl (),
                'css' => array(
                    '/css/components/views/layoutEditor.css',
                )
            ),
            'X2WidgetJS' => array (
		        'baseUrl' => $baseUrl.'/js',
		    	'js' => array(
		    		'X2Widget.js',
				),
				'depends' => array('auxlib')
		    ),
		    'sortableWidgetJS' => array(
                'baseUrl' => $baseUrl.'/js/sortableWidgets/',
		    	'js' => array(
		    		'SortableWidget.js',
				    'SortableWidgetManager.js',
				    'TwoColumnSortableWidgetManager.js',
				    'ProfileWidgetManager.js',
				),
				'depends' => array('auxlib', 'X2WidgetJS')
            ),
	        'layoutEditorJS' => array(
	            'baseUrl' => $baseUrl.'/js/',
	        	'js' => array(
                    'LayoutEditor.js',
                    'ProfileLayoutEditor.js',
                )
	        )
		);

		return $packages;
	}

	/**
	 * Instantiates widgets in a contiainer, echoing them out.
	 * @param containerNumber int the number of container. (1 or 2 currently)
	 */
	public function displayWidgets ($containerNumber){
		$layout = $this->model->profileWidgetLayout;

		foreach ($layout as $widgetClass => $settings) {
		    if ($settings['containerNumber'] == $containerNumber) {
		        SortableWidget::instantiateWidget ($widgetClass, $this->model);
		    }

		}
	}

	/**
	 * Instantiates the JS fo rthe profile dashboard
	 */
	public function instantiateJS () {
        $miscSettings = Yii::app()->params->profile->miscLayoutSettings;
        $layoutEditorParams = array (
        	'miscSettingsUrl' => Yii::app()->controller->createUrl('saveMiscLayoutSetting'),
        	'margin' => $this->columnMargin
        );

        $columnWidth = $this->columnWidth;
        if ($columnWidth) {
        	$layoutEditorParams['columnWidth'] = $columnWidth;
        }

        $layoutEditorParams = CJSON::encode($layoutEditorParams);

		$widgetManagerParams = CJSON::encode (array(
            'setSortOrderUrl' => Yii::app()->controller->createUrl ('/profile/setWidgetOrder'),
            'showWidgetContentsUrl' => 
                Yii::app()->controller->createUrl ('/profile/view', array ('id' => 1)),
            'connectedContainerSelector' => '.connected-sortable-profile-container',
            'translations' => $this->getTranslations(),
            'createProfileWidgetUrl' => 
                Yii::app()->controller->createUrl ('/profile/createProfileWidget'),
            
            'createChartingWidgetUrl' => 
                Yii::app()->controller->createUrl ('/reports/addToDashboard'),
            
        ));

        $script = "
        	x2.profileWidgetManager = new ProfileWidgetManager ($widgetManagerParams);
        	x2.profileLayoutManager = new x2.ProfileLayoutEditor ($layoutEditorParams);

        	new PopupDropdownMenu ({
        	    containerElemSelector: '#x2-hidden-profile-widgets-menu-container',
        	    openButtonSelector: '#show-profile-widget-button',
        	    defaultOrientation: 'left'
        	});
			
        ";

        Yii::app()->clientScript->registerScript(
            'profilePageInitScript', $script, CClientScript::POS_END);
       
	}

	
	/**
	 * Creates a dropdown showing the different charts that can be created
	 * The values of the options are a JSON string that give the proper fields
	 * to call the action AddToDashboard in the charts controller.
	 * @return string HTML for a dropdown
	 */
	public function getChartingWidgetDropdown () {
		    $options = array();
		    $reports = Reports::model()->findAll();
		    foreach ($reports as $report) {
		    	foreach ($report->dataWidgetLayout as $key => $value) {
		    		list($class, $uid) = SortableWidget::parseWidgetLayoutKey ($key);
		    		if (!$value['chartId']) {
		    			continue;
		    		}

		    		$key = CJSON::encode(array(
		    			'modelId' => $report->id,
		    			'widgetClass' => $class,
		    			'widgetUID' => $uid
		     		));
			    	$options[$key] = $value['label'];
		    		
		    	}
		    }	
		    if (count($options) == 0) {
		    	$options['noCharts'] = Yii::t('charts', 'No charts have been created');
		    }

		    return CHtml::tag('span', 
			    array ( 'style' => 'display:none', 
			    		'id' => 'chart-name-container' ),
			    CHtml::dropDownList ('chartName', '', $options).
			    X2Html::hint(Yii::t('profile', 'You can create new charts in the reports module'))
		    );

	}
	

	/**
	 * Calculates the widths of the columns
	 * @return array(string, string) a pair of the column widths for a css rule. 
	 */
	public function getColumnWidths () {

		if(!$this->columnWidth) {
			return array('', '');
		}

		$column1 = $this->columnWidth;
		$column2 = 100 - $column1;

		$column1 = ($column1 - $this->columnMargin).'%';
		$column2 = ($column2 - $this->columnMargin).'%';

		return array(
			$column1,
			$column2
		);
	}


	public function getTranslations () {
		return array(
			'createProfileWidgetDialogTitle' => Yii::t('profile', 'Create Profile Widget'),
			'Create' => Yii::t('app',  'Create'),
			'Cancel' => Yii::t('app',  'Cancel'),
		);
	}

}

?>
