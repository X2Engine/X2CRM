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
abstract class ProfileGridViewWidget extends GridViewWidget {

    public $viewFile = '_gridViewProfileWidget';

    private static $_JSONPropertiesStructure;

    /**
     * @var object the model to be associated with this grid view widget 
     */
    protected $_model;

    /**
     * @var object 
     */
    protected $_dataProvider;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    /**
     * @return object the model to be associated with the grid view widget 
     */
    abstract protected function getModel ();

    /**
     * Should be called after model is instantiated in getModel 
     */
    protected function afterGetModel () {}

    /**
     * overrides parent method
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'dbPersistentGridSettings' => false, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getTemplate () {
        $model = $this->getModel ();
        $moduleName = strtolower (X2Model::getModuleName (get_class ($model)));
        return "<div class='submenu-title-bar widget-title-bar $moduleName'>{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}";
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (parent::getGridViewConfig (), array (
                'sortableWidget' => $this,
                'id'=>$this->getWidgetKey (),
                'enableScrollOnPageChange' => false,
                'possibleResultsPerPage' => array(5, 10, 20, 30, 40, 50, 75, 100),
                'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
                'template'=>
                    CHtml::openTag ('div', X2Html::mergeHtmlOptions (array (
                        'class' => 'page-title'
                    ), array (
                        'style' =>  
                            !CPropertyValue::ensureBoolean (
                                $this->getWidgetProperty('showHeader')) &&
                            !CPropertyValue::ensureBoolean (
                                $this->getWidgetProperty('hideFullHeader')) ?
                                'display: none;' : ''

                    ))).
                    '<h2 class="grid-widget-title-bar-dummy-element">'.
                    '</h2>{buttons}{filterHint}'.
                    '{massActionButtons}'.
                    '{summary}{topPager}<div class="clear"></div></div>{items}{pager}',
                'fixedHeader'=>false,
                'dataProvider'=>$this->dataProvider,
                'filter'=>$this->model,
                'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
                'modelName'=> get_class ($this->model),
                'viewName'=>'profile',
                'gvSettingsName'=> get_called_class ().$this->widgetUID,
                'enableControls'=>true,
                'fullscreen'=>false,
                'enableSelectAllOnAllPages' => false,
            ));
        }
        return $this->_gridViewConfig;
    }

    protected function getSettingsMenuContentEntries () {
        return 
            '<li class="grid-settings-button">'.
                X2Html::fa('fa-gear').
                Yii::t('profile', 'Widget Grid Settings').'
            </li>'.parent::getSettingsMenuContentEntries ();
    }

    /**
     * Magic getter. Returns this widget's css
     * @return array key is the proposed name of the css string which should be passed as the first
     *  argument to yii's registerCss. The value is the css string.
     */
    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array_merge (
                parent::getCss (),
                array (
                    'gridViewWidgetCss' => "
                        .sortable-widget-container .x2grid-header-container {
                            width: 100% !important;
                        }

                        .sortable-widget-container .page-title {
                            border-radius: 0 !important;
                        }

                        .sortable-widget-container .pager {
                            float: none;
                            -moz-border-radius: 0px 0px 4px 4px;
                            -o-border-radius: 0px 0px 4px 4px;
                            -webkit-border-radius: 0px 0px 4px 4px;
                            border-radius: 0px 0px 4px 4px;
                        }

                        .sortable-widget-container div.page-title {
                            background:#cfcfcf;
                            border-bottom: 1px solid #cfcfcf;
                        }

                        .sortable-widget-container div.page-title .x2-minimal-select {
                            border:1px solid #cfcfcf !important;
                        }

                        .sortable-widget-container div.page-title .x2-minimal-select:hover,
                        .sortable-widget-container div.page-title .x2-minimal-select:focus {
                            border: 1px solid #A0A0A0;
                            background: rgb(221, 221, 221);
                        }

                        .sortable-widget-container div.page-title .x2-minimal-select:hover + .after-x2-minimal-select-outer > .after-x2-minimal-select,
                        .sortable-widget-container div.page-title .x2-minimal-select:focus + .after-x2-minimal-select-outer > .after-x2-minimal-select {

                            background: rgb(221, 221, 221);
                            background-image: url(".Yii::app()->theme->getBaseUrl ()."/images/icons/Collapse_Widget.png) !important;
                            background-repeat: no-repeat !important;
                            background-position: 7px !important;
                        }

                        .grid-widget-title-bar-dummy-element {
                            height: 33px;
                            display: none !important;
                        }

                        @media (max-width: 657px) {
                            .grid-widget-title-bar-dummy-element {
                                display: block !important;
                            }
                            .sortable-widget-container .x2-gridview-mass-action-buttons {
                                top: -41px;
                                right: -20px;
                            }
                            .sortable-widget-container .show-top-buttons .x2-gridview-mass-action-buttons {
                                    right: -24px; 
                            }
                        }
                        
                        .sortable-widget-container .grid-view .page-title {
                            min-height: 34px;
                            height: auto;
                        }
                    "
                )
            );
        }
        return $this->_css;
    }

    /**
     * @return object Data provider object to be used for the grid view
     */
    protected function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $resultsPerPage = $this->getWidgetProperty (
                'resultsPerPage');
            $this->_dataProvider = $this->model->search (
                $resultsPerPage, get_called_class ().$this->widgetUID);
        }
        return $this->_dataProvider;
    }

    protected function getSettingsMenuContentDialogs () {
        return
            '<div id="grid-settings-dialog-'.$this->widgetKey.'" 
              style="display: none;">'.
                '<div>'.Yii::t('profile', 'Use persistent filter and sort settings?').'</div>'.
                CHtml::checkbox (
                    'dbPersistentGridSettings', 
                    self::getJSONProperty (
                        $this->profile, 'dbPersistentGridSettings', $this->widgetType,
                        $this->widgetUID), 
                    array (
                        'id' => 'dbPersistentGridSettings-'.$this->widgetKey,
                    )
                ).
                X2Html::hint (
                    Yii::t(
                        'profile', 'Leaving this box checked will prevent your grid filter and '.
                        'sort settings from being reset when you log out of the app.'), 
                    false, null, true, true).
            '</div>'.parent::getSettingsMenuContentDialogs ();
    }

}
?>
