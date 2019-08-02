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




class ChartCreator extends CWidget {
	
	/**
	 * @var string Viewfile to render
	 */
	public $viewFile = 'chartCreator';

	/**
	 * @var The report object if on a report page
	 * On the users chart dashboard, this stays null
	 */
	public $report;

	/**
	 * @var boolean if true, the dialog will automaticcaly open
	 */
	public $autoOpen;

	/**
	 * @var array an array of supported chart types for the 
	 * Current chart type
	 */
	public $chartTypes;

	/**
	 * Registers Javascript and CSS
	 */
	public function init() {
		$packages = array(
			'ChartCreatorJS' => array(
				'baseUrl' => Yii::app()->request->baseUrl.'/js',
				'js' => array(
					'ChartCreator.js'
				),
			),
			'ChartCreatorCSS' => array(
				'baseUrl' => Yii::app()->theme->baseUrl.'/css',
				'css' => array(
					'ChartCreator.css',
				),
			),
		);
		Yii::app()->clientScript->registerPackages ($packages, true);

		// Load entries into the chart Types
		$this->chartTypes = array();
		foreach (Charts::$chartTypes as $chartType) {
	    	if ($this->report->chartSupports ($chartType)) {
	    		$this->chartTypes[] = $chartType;
			}
		}
	}	

	/**
	 * @see run()
	 */
	public function run(){		
		parent::run();

		$jsParams = CJSON::encode (array(
			'report' => $this->report->attributes,
			'translations' => $this->getTranslations(),
			'autoOpen' => $this->autoOpen
		));

		Yii::app()->clientScript->registerScript('ChartCreatorRunJS',"
			x2.chartCreator = new x2.ChartCreator($jsParams);
		", CClientScript::POS_END);

		$this->render ($this->viewFile);
	}


	/**
	 * The function to render the different FormModels for each form type. 
	 * the list of charts is static in Charts Model and there should be a corresponding FormModel 
     * and Form class, with corresponding view files and client Scripts
	 */
	public function renderForms(){

	    foreach($this->chartTypes as $chartType) {
	    	$formName = 'ChartForm';

	    	if (class_exists (Charts::toFormName($chartType))) {
	    		$formName = Charts::toFormName($chartType);
	    	}

	        $form = $this->beginWidget($formName, array(
	        	'report' => $this->report,
	        	'chartType' => $chartType
	        ));
	        $form->render (null);
	        $this->endWidget();

	    }

	}

	/**
	 * @return array Array of translations for the front-end
	 */
	public function getTranslations() {
		return array (
			'exitSelection' => Yii::t('charts', 'Click to select'),
			'inSelection' => Yii::t('charts', 'Click on a row or column'),
			'inSelectionrow' => Yii::t('charts', 'Click on a row'),
			'inSelectioncolumn' => Yii::t('charts', 'Click on a column'),
		);
	}

}
