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
 * @package application.components.sortableWidget
 */
abstract class TransactionalViewWidget extends GridViewWidget {

    /**
     * @var CActiveRecord 
     */
    public $model;

    /**
     * @var TwoColumnSortableWidgetManager $widgetManager
     */
    public $widgetManager;
    public $viewFile = '_transactionalViewWidget';
    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{createButton}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';
    public $sortableWidgetJSClass = 'x2.TransactionalViewWidget';

    protected $compactResultsPerPage = true;
    protected $createButtonLabel = '';
    protected $labelIconClass = '';
    protected $historyType;
    protected $_dataProvider;
    protected $_gridViewConfig;
    protected $_searchModel;
    protected $containerClass = 'sortable-widget-container x2-layout-island transactional-view-widget';
    

    private static $_JSONPropertiesStructure;

    protected function getSearchModel() {
        if (!isset($this->_searchModel)) {
            $this->_searchModel = new Actions('search', $this->widgetKey);
        }
        return $this->_searchModel;
    }

    public static function getJSONPropertiesStructure() {
        if (!isset(self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge(
                parent::getJSONPropertiesStructure(), array(
                    'showHeader' => false,
                    'resultsPerPage' => 5,
                    'hideFullHeader' => true, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getPackages() {
        if (!isset($this->_packages)) {
            $this->_packages = array_merge(parent::getPackages(), array(
                'TransactionalViewWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/TransactionalViewWidget.js',
                    ),
                ),
            ));
        }
        return $this->_packages;
    }

    public function getSharedCssFileNames() {
        if (!isset($this->_sharedCssFileNames)) {
            $this->_sharedCssFileNames = array_merge(parent::getSharedCssFileNames(), array(
                'components/sortableWidget/recordViewWidgets/TransactionalViewWidget.css',
            ));
        }
        return $this->_sharedCssFileNames;
    }

    public function getIcon() {
        return X2Html::fa($this->labelIconClass, array(
                    'class' => 'widget-label-icon',
        ));
    }

    public function renderWidgetLabel() {
        $label = $this->getWidgetLabel();
        $count = $this->dataProvider->totalItemCount;
        echo 
            "<div class='widget-title'>" .
                $this->getIcon() .
                htmlspecialchars($label) .
                "&nbsp(<span class='transaction-count'>" . $count . "</span>)
             </div>";
    }

    public function getCreateButtonTitle() {
        return '';
    }

    public function getCreateButtonText() {
        return '';
    }

    public function renderCreateButton() {
        echo
            "<button class='x2-button create-button'
              title='" . CHtml::encode($this->createButtonTitle) . "'>
                <span class='fa fa-plus'></span>" .
                CHtml::encode($this->createButtonText) . 
            "</button>";
    }

    /**
     * @return object Data provider object to be used for the grid view
     */
    public function getDataProvider() {
        if (!isset($this->_dataProvider)) {
            $resultsPerPage = $this->getWidgetProperty(
                    'resultsPerPage');
            $historyCmd = History::getCriteria(
                $this->model->id, 
                X2Model::getAssociationType(get_class($this->model)),
                Yii::app()->params->profile->historyShowRels,
                $this->historyType
            );

            $this->_dataProvider = new CSqlDataProvider($historyCmd['cmd'], array(
                'totalItemCount' => $historyCmd['count'],
                'params' => $historyCmd['params'],
                'pagination' => array(
                    'pageSize' => $resultsPerPage,
                ),
            ));
//            $this->_dataProvider = $this->getSearchModel()->search(
//                    History::getCriteria(
//                            $this->model->id, X2Model::getAssociationType(get_class($this->model)), $this->getWidgetProperty('showRelatedRecords'), $this->historyType), $resultsPerPage
//            );
            // clear order set by Actions::search and History::getCriteria
            //$this->_dataProvider->criteria->order = '';
        }
        return $this->_dataProvider;
    }

    protected function getActionDescriptionHeader() {
        return Yii::t(
            'actions', 
            '{action} Description', 
            array('{action}' => Modules::displayName(false, 'Actions')));
    }

    public function getAjaxUpdateRouteAndParams() {
        list ($updateRoute, $updateParams) = parent::getAjaxUpdateRouteAndParams();
        $updateParams = array_merge($updateParams, array(
            'modelId' => $this->model->id,
            'modelType' => get_class($this->model),
        ));
        return array($updateRoute, $updateParams);
    }


    /**
     * @param array $collapse each entry should be an array of integers, corresponding to the
     *  indexes of columns whose widths will be combined
     * Precondition: Columns cannot be combined if their widths are of different types (percent vs.
     *  integer)
     * @return array width of column sizes 
     */
    public function getColumnWidths (array $collapse=array ()) {
        if (!Yii::app()->params->profile->historyShowRels) {
            $columnWidths = array (
                '52%', 
                '22%', 
                '12%', 
                '106', 
                '60', 
            );
        } else {
            $columnWidths = array (
                '42%', 
                '22%', 
                '22%', 
                '106', 
                '60', 
            );
        }

        $addWidths = function ($widthA, $widthB) {
            if (preg_match ('/%/', $widthA) && preg_match ('/%/', $widthB)) {
                //get numeric components
                $widthA = substr($widthA, 0, -1);
                $widthB = substr($widthB, 0, -1);
                return $widthA + $widthB . '%';
            } else if (!preg_match ('/%/', $widthA) && !preg_match ('/%/', $widthB)) {
                return $widthA + $widthB;
            } else {
                throw new CException ('Type mismatch: widths cannot be added');
            }
        };

        // combine columns, adding column widths
        foreach ($collapse as $cols) {
            $rest = $cols;
            unset ($rest[0]);
            $collapsedWidth = $columnWidths[$cols[0]];
            foreach ($rest as $otherCol) {
                $collapsedWidth = $addWidths ($collapsedWidth, $columnWidths[$otherCol]);
                unset ($columnWidths[$otherCol]);
            }
            $columnWidths[$cols[0]] = $collapsedWidth;
        }

        return $columnWidths;
    }

    /**
     * Build defaultGvSettings property for X2GridView
     */
    public function buildDefaultGvSettings ($attributes, array $combine = array ()) {
        return array_combine (
            $attributes,
            $this->getColumnWidths ($combine)
        );
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig() {
        if (!isset($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge(parent::getGridViewConfig(), array(
                'possibleResultsPerPage' => array(5, 10, 20, 30, 40, 50, 75, 100),
                'sortableWidget' => $this,
                'moduleName' => 'Actions',
                'sortableWidget' => $this,
                'id' => get_called_class() . '_' . $this->widgetUID,
                'fieldFormatter' => 'TransactionalViewFieldFormatter',
                'enableColDragging' => false,
                'evenPercentageWidthColumns' => true,
                'enableGridResizing' => false,
                'enableScrollOnPageChange' => false,
                'buttons' => array('clearFilters', 'columnSelector', 'autoResize'),
                'template' => '{summary}{items}{pager}',
                'fixedHeader' => false,
                'dataProvider' => $this->dataProvider,
                //'filter' => $this->model,
                'pager' => array('class' => 'CLinkPager', 'maxButtonCount' => 10),
                'modelName' => 'Actions',
                'viewName' => 'profile',
                'gvSettingsName' => get_called_class() . $this->widgetUID,
                'enableControls' => true,
                //'filter' => new Actions('search', $this->widgetKey),
                'fullscreen' => false,
                'enableSelectAllOnAllPages' => false,
                'hideSummary' => true,
                'enableGvSettings' => false,
                'defaultGvSettings' => $this->buildDefaultGvSettings (
                    array (
                        'actionDescription',
                        'assignedTo',
                        'createDate',
                    ), array (array (1, 2), array (3, 4))
                ),
                'specialColumns' => array(
                    'actionDescription' => array(
                        'header' => $this->getActionDescriptionHeader(),
                        'name' => 'actionDescription',
                        'value' => 'Actions::model()->findByPk($data["id"])->frameLink ()',
                        'type' => 'raw',
                        'filter' => false,
                        'htmlOptions' => array(
                            'title' => 'php:CHtml::encode(Formatter::trimText(Actions::model()->findByPk($data["id"])->actionDescription))',
                        )
                    ),
                ),
            ));
            $this->_gridViewConfig['specialColumns']['associationName'] = array(
                    'header' => Yii::t('app', 'Association'),
                    'name' => 'associationName',
                    'value' => '$data->getAssociationLink ()',
                    'type' => 'raw',
                    'filter' => false,
                );
            if (Yii::app()->params->profile->historyShowRels) {
                $this->_gridViewConfig['defaultGvSettings'] = $this->buildDefaultGvSettings (
                    array (
                        'actionDescription',
                        'assignedTo',
                        'associationName',
                        'createDate',
                    ), array (array (3, 4)) 
                );
            }
        }
        return $this->_gridViewConfig;
    }

    public function run() {
        $tabs = Yii::app()->settings->actionPublisherTabs;
        $actionTypeToTab = Publisher::$actionTypeToTab;

        if (isset($this->widgetManager) && $this->widgetManager->layoutManager->staticLayout) {
            // don't display transactional view widgets on legacy record view pages (e.g. on old
            // custom modules)
            return;
        }

        // don't display widget if corresponding tab isn't enabled
        if ($tabs && isset($actionTypeToTab[$this->historyType]) &&
            isset($tabs[$actionTypeToTab[$this->historyType]]) &&
            !$tabs[$actionTypeToTab[$this->historyType]]) {

            return;
        }
        // hide widget if transactional view is disabled
        if (!Yii::app()->params->profile->miscLayoutSettings['enableTransactionalView']) {
            $this->registerSharedCss();
            $this->render(
                'application.components.sortableWidget.views.' . $this->sharedViewFile, 
                array(
                    'widgetClass' => get_called_class(),
                    'profile' => $this->profile,
                    'hidden' => true,
                    'widgetUID' => $this->widgetUID,
                ));
            return;
        }
        parent::run();
    }

    protected function getJSSortableWidgetParams() {
        if (!isset($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge(
                parent::getJSSortableWidgetParams(), 
                array(
                    'actionType' => $this->historyType,
                    'modelName' => get_class($this->model),
                    'modelId' => $this->model->id,
                )
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    protected function getTranslations() {
        if (!isset($this->_translations)) {
            $this->_translations = array_merge(parent::getTranslations(), array(
                'dialogTitle' => ucwords($this->createButtonTitle),
            ));
        }
        return $this->_translations;
    }

    protected function getSettingsMenuContentEntries () {
        return 
            parent::getSettingsMenuContentEntries ().
            '<li class="relationships-toggle x2-hint" title="'.
                CHtml::encode (
                    Yii::t(
                        'app', 
                        'Click to toggle showing actions associated with related records.')).
            '">'.
                X2Html::checkbox ('historyShowRels', Yii::app()->params->profile->historyShowRels).
                '<span for="historyShowRels">'.
                    CHtml::encode (Yii::t('profile', 'Relationships')).'</span>'.
            '</li>';
    }

}

?>
