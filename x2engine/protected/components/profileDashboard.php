<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * Description
 * @package application.components
 * @author Alex Rowe <alex@x2engine.com>
 */
class ProfileDashboard extends CWidget {

	public $model;

	public $viewFile = 'profileDashboard';

	public function init() {
		Yii::app()->clientScript->registerPackages($this->getPackages(), true);
		$this->instantiateJS();

		parent::init ();
	}

	public function run() {
		$this->render('profileDashboard');
		parent::run ();
	}


	public function getPackages () {
		$packages = array(
		    'sortableWidgetJS' => array(
		    	'baseUrl' => '/js/sortableWidgets/',
		    	'js' => array(
		    		'SortableWidget.js',
				    'SortableWidgetManager.js',
				    'ProfileWidgetManager.js'
				),
				'depends' => array('auxlib')
		    )
		);

		return $packages;
	}

	public function displayWidgets ($containerNumber){
		$layout = $this->model->profileWidgetLayout;

		foreach ($layout as $widgetClass => $settings) {
		    if ($settings['containerNumber'] == $containerNumber) {
		        SortableWidget::instantiateWidget ($widgetClass, $this->model);
		    }

		}
	}

	public function instantiateJS () {

		$JSParams = CJSON::encode (array(
            'setSortOrderUrl' => Yii::app()->controller->createUrl ('/profile/setWidgetOrder'),
            'showWidgetContentsUrl' => Yii::app()->controller->createUrl ('/profile/view', array ('id' => 1)),
            'connectedContainerSelector' => 'connected-sortable-profile-container',
            'translations' => $this->getTranslations(),
            'createProfileWidgetUrl' => Yii::app()->controller->createUrl ('/profile/createProfileWidget')
        ));

        $script = "
        	$('#content').addClass('profile-content');
        	x2.profileWidgetManager = new ProfileWidgetManager ($JSParams);
        ";

        Yii::app()->clientScript->registerScript('profilePageInitScript', $script, CClientScript::POS_READY);

       
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