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




class RecordViewWidgetManager extends TwoColumnSortableWidgetManager {

    public $layoutManager;

    /**
     * @var CActiveRecord $model
     */
    public $model;

    /**
     * @var string $JSClass
     */
    public $JSClass = 'RecordViewWidgetManager';
    public $namespace = 'RecordViewWidgetManager';
    public $widgetType = 'recordView';

    /**
     * @var array (<widget name> => <array of parameters to pass to widget) 
     */
    public $widgetParamsByWidgetName = array();

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages() {
        if (!isset($this->_packages)) {
            $this->_packages = array_merge(parent::getPackages(), array(
                'RecordViewWidgetManagerJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/RecordViewWidgetManager.js',
                    ),
                    'depends' => array('TwoColumnSortableWidgetManagerJS')
                ),
            ));
        }
        return $this->_packages;
    }

    protected function getTranslations() {
        if (!isset($this->_translations)) {
            $this->_translations = array_merge(parent::getTranslations(), array(
                'Create' => Yii::t('app', 'Create'),
                'Cancel' => Yii::t('app', 'Cancel'),
            ));
        }
        return $this->_translations;
    }

    public function getJSClassParams() {
        if (!isset($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge(parent::getJSClassParams(), array(
                'connectedContainerSelector' => '.' . $this->connectedContainerClass,
                'setSortOrderUrl' =>
                Yii::app()->controller->createUrl('/profile/setWidgetOrder'),
                'showWidgetContentsUrl' => Yii::app()->controller->createUrl(
                        '/profile/view', array('id' => 1)),
                'modelId' => $this->model->id,
                'modelType' => get_class($this->model),
                'cssSelectorPrefix' => $this->namespace,
                'widgetType' => $this->widgetType
            ));
        }
        return $this->_JSClassParams;
    }

    public function displayWidgets($containerNumber) {
        $widgetLayoutName = $this->widgetLayoutName;
        $layout = Yii::app()->params->profile->$widgetLayoutName;

        foreach ($layout as $widgetClass => $settings) {
            if (self::isExcluded($widgetClass, get_class($this->model)))
                continue;

            if ($settings['containerNumber'] == $containerNumber) {

                if (isset($this->widgetParamsByWidgetName[$widgetClass])) {
                    $options = $this->widgetParamsByWidgetName[$widgetClass];
                } else {
                    $options = array();
                }
                $options = array_merge(array(
                    'model' => $this->model,
                    'widgetManager' => $this,
                        ), $options);
                SortableWidget::instantiateWidget(
                        $widgetClass, Yii::app()->params->profile, $this->widgetType, $options);
            }
        }
    }

    /**
     * @param bool $onReady whether or not JS class should be instantiated after page is ready
     */
    public function instantiateJSClass($onReady = true) {
        Yii::app()->clientScript->registerScript(
                $this->namespace . get_class($this) . 'JSClassInstantiation', ($onReady ? "$(function () {" : "") .
                $this->getJSObjectName() . "=
                    x2." . lcfirst($this->JSClass) . "= new x2.$this->JSClass (" .
                CJSON::encode($this->getJSClassParams()) .
                ");" .
                ($onReady ? "});" : ""), CClientScript::POS_END);
    }

    public static function isExcluded($name, $modelType) {
        if (// Only widgets in Topics module
                ($modelType === 'Topics' && !in_array($name, array(
                    'InlineTagsWidget',
                    'InlineRelationshipsWidget',
                ))) ||
                // Doesn't show in Docs or Media module
                (($modelType === 'Docs' || $modelType === 'Media') && in_array($name, array(
                    'InlineTagsWidget',
                    'WorkflowStageDetailsWidget',
                    'ActionHistoryChartWidget',
                    'ImageGalleryWidget',
                    'EmailsWidget',
                    'QuotesWidget',
                ))) ||
                // Only widgets in Actions module
                ($modelType === 'Actions' && !in_array($name, array(
                    'InlineTagsWidget'
                ))) ||
                // Shows only in Campaign module
                ($modelType !== 'Campaign' && in_array($name, array(
                    'CampaignChartWidget',
                ))) ||
                // Only widgets in Bug Reports module
                ($modelType === 'BugReports' && !in_array($name, array(
                    'WorkflowStageDetailsWidget'
                ))) ||
                // Doesn't show in Quote module
                ($modelType === 'Quote' && in_array($name, array(
                    'WorkflowStageDetailsWidget',
                    'QuotesWidget',
                ))) ||
                // Doesn't show in Opportunity module
                ($modelType === 'Opportunity' && in_array($name, array(
                    'EmailsWidget',
                    'RelatedDocsWidget',
                ))) ||
                // Doesn't show in Campaign module
                ($modelType === 'Campaign' && in_array($name, array(
                    'WorkflowStageDetailsWidget',
                    'InlineRelationshipsWidget',
                    'EmailsWidget',
                    'QuotesWidget',
                ))) ||
                // Doesn't show in Product module
                ($modelType === 'Product' && in_array($name, array(
                    'WorkflowStageDetailsWidget',
                    'QuotesWidget',
                    'EmailsWidget',
                )))
        ) {
            return true;
        } else {
            return false;
        }
    }

}
