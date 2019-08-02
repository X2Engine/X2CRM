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
 * @package application.components
 */
abstract class GridViewWidget extends SortableWidget {

    public $sortableWidgetJSClass = 'GridViewWidget';

    protected $compactResultsPerPage = false; 

    private static $_JSONPropertiesStructure;

    /**
     * @var object 
     */
    protected $_dataProvider;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    abstract protected function getDataProvider ();

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'GridViewWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/GridViewWidget.js',
                    ),
                    'depends' => array ('SortableWidgetJS')
                ),
                'GridViewWidgetCSS' => array(
                    'baseUrl' => Yii::app()->theme->baseUrl,
                    'css' => array(
                        'css/components/sortableWidget/views/gridViewWidget.css',
                    )
                ),
            ));
        }
        return $this->_packages;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'resultsPerPage' => 10, 
                    'showHeader' => false, 
                    'hideFullHeader' => false, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge (array( 
                'showHeader' => CPropertyValue::ensureBoolean (
                    $this->getWidgetProperty('showHeader')),
                'compactResultsPerPage' => $this->compactResultsPerPage,
                ), parent::getJSSortableWidgetParams ()
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * Send the chart type to the widget content view 
     */
    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'gridViewConfig' => $this->gridViewConfig,
                )
            );
        }
        return $this->_viewFileParams;
    }

    public function getAjaxUpdateRouteAndParams () {
        $updateRoute = '/profile/view';
        $updateParams =  array (
            'widgetClass' => get_called_class (),        
            'widgetType' => $this->widgetType,
            'id' => $this->profile->id,
        );
        return array ($updateRoute, $updateParams);
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            list ($updateRoute, $updateParams) = $this->getAjaxUpdateRouteAndParams ();
            $this->_gridViewConfig = array (
                'ajaxUrl' => Yii::app()->controller->createUrl ($updateRoute, $updateParams),
                'showHeader' => CPropertyValue::ensureBoolean (
                    $this->getWidgetProperty('showHeader')),
                'hideFullHeader' => CPropertyValue::ensureBoolean (
                    $this->getWidgetProperty('hideFullHeader')),
            );
        }
        return $this->_gridViewConfig;
    }


    protected function getSettingsMenuContentEntries () {
        return 
            '<li class="hide-settings">'.
                X2Html::fa('fa-toggle-down').
                Yii::t('profile', 'Toggle Settings Bar').
            '</li>'.
            ($this->compactResultsPerPage ?
                '<li class="results-per-page-container">
                </li>' : '').
            parent::getSettingsMenuContentEntries ();
    }

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (
                parent::getTranslations (), 
                array (
                    'Grid Settings' => Yii::t('profile', 'Widget Grid Settings'),
                    'Cancel' => Yii::t('profile', 'Cancel'),
                    'Save' => Yii::t('profile', 'Save'),
                ));
        }
        return $this->_translations;
    }

    public function init ($skipGridViewInit = false) {
        parent::init ();
        if (!$skipGridViewInit) {
            list ($updateRoute, $updateParams) = $this->getAjaxUpdateRouteAndParams ();
            $this->dataProvider->pagination->route = $updateRoute;
            $this->dataProvider->pagination->params = $updateParams;
            $this->dataProvider->sort->route = $updateRoute;
            $this->dataProvider->sort->params = $updateParams;
        }
    }


}
?>
