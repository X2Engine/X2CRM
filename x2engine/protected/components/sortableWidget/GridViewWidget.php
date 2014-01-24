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

 Yii::import ('application.components.sortableWidget.SortableWidget');

/**
 * @package X2CRM.components
 */
abstract class GridViewWidget extends SortableWidget {

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
     * overrides parent method
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'resultsPerPage' => 10, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * @return object Data provider object to be used for the grid view
     */
    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $resultsPerPage = self::getJSONProperty (
                $this->profile, 'resultsPerPage', $this->widgetType);
            $this->_dataProvider = $this->model->search ($resultsPerPage, get_called_class ());
        }
        return $this->_dataProvider;
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array (
                'sortableWidget' => $this,
                'id'=>get_called_class (),
                'enableScrollOnPageChange' => false,
                'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
                'template'=>
                    '<div class="page-title">{buttons}{filterHint}'.
                    
                    '{summary}{topPager}</div>{items}{pager}',
                'fixedHeader'=>false,
                'dataProvider'=>$this->dataProvider,
                'filter'=>$this->model,
                'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
                'modelName'=> get_class ($this->model),
                'viewName'=>'profile',
                'gvSettingsName'=> get_called_class (),
                'enableControls'=>true,
                'fullscreen'=>false,
            );
        }
        return $this->_gridViewConfig;
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
                            background:#cfcfcf !important;
                            border-bottom: 1px solid #cfcfcf !important;
                        }

                        .sortable-widget-container div.page-title .x2-minimal-select {
                            border:1px solid #cfcfcf !important;
                        }

                        .sortable-widget-container div.page-title .x2-minimal-select:hover,
                        .sortable-widget-container div.page-title .x2-minimal-select:focus {
                            border: 1px solid #A0A0A0 !important;
                            background: rgb(221, 221, 221)!important;
                        }
                    "
                )
            );
        }
        return $this->_css;
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

    public function init () {
        parent::init ();
        $updateRoute = '/profile/view';
        $updateParams =  array (
            'widgetClass' => get_called_class (),        
            'widgetType' => $this->widgetType,
            'id' => $this->profile->id,
        );

        $this->dataProvider->pagination->route = $updateRoute;
        $this->dataProvider->pagination->params = $updateParams;
    }

}
?>
