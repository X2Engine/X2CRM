<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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
 * Class for displaying center widgets
 *
 * @package application.components
 */
class X2WidgetList extends X2Widget {

    public $model;

    private $_profile;

    public function getProfile () {
        if (!isset ($this->_profile)) {
            $this->_profile = (Yii::app()->user->isGuest ? 
                new Profile : Yii::app()->params->profile);
        }
        return $this->_profile;
    }

    /**
     * @var array (<widget name> => <array of parameters to pass to widget) 
     */
    public $widgetParamsByWidgetName = array ();

    /**
     * Renders widgets in layout 
     */
    private function renderWidget () {
        $layout = $this->profile->recordViewWidgetLayout;

        foreach($layout as $widgetClass => $settings){ // list of widgets
            $widgetParams = array(
                'model' => $this->model,
                'profile' => $this->profile,
                'widgetType' => 'recordView',
            );

            if (isset ($this->widgetParamsByWidgetName[$widgetClass])) {
                foreach ($this->widgetParamsByWidgetName[$widgetClass] as $paramName => $value) {
                    $widgetParams[$paramName] = $value;
                }
            }

            if(!$this->isExcluded ($widgetClass)){
                Yii::app()->controller->widget(
                    'application.components.sortableWidget.recordViewWidget.'.$widgetClass, 
                    $widgetParams);
            }
        }
    }

    public function run(){
        echo '<div id="content-widgets">';
        echo '<div id="recordView-widgets-container-inner">';
        $this->renderWidget ();
        echo '</div>';
        echo '</div>';


        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/sortableWidgets/SortableWidgetManager.js', 
            CClientScript::POS_END);
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/sortableWidgets/RecordViewWidgetManager.js', 
            CClientScript::POS_END);
        Yii::app()->clientScript->registerScript ('profilePageWidgetInitScript', "
            x2.recordViewWidgetManager = new RecordViewWidgetManager ({
                setSortOrderUrl: '".
                    Yii::app()->controller->createUrl ('/profile/setWidgetOrder')."',
                showWidgetContentsUrl: '".Yii::app()->controller->createUrl (
                    '/profile/view', array ('id' => 1))."',
                translations: ".CJSON::encode (array (
                    'Create' => Yii::t('app',  'Create'),
                    'Cancel' => Yii::t('app',  'Cancel'),
                )).",
                modelId: {$this->model->id},
                modelType: '".get_class ($this->model)."'
            });
        ", CClientScript::POS_READY);

    }

    private function isExcluded ($name) {
        $modelType = get_class ($this->model);

        if ($modelType === 'Actions' && $name !== 'InlineTagsWidget' ||
            $modelType !== 'Campaign' && $name === 'CampaignChartWidget' ||
            ($modelType == 'BugReports' && $name!='WorkflowStageDetailsWidget') ||
            ($modelType == 'Quote' && $name == 'WorkflowStageDetailsWidget') ||
            ($modelType == 'Campaign' &&
             ($name == 'WorkflowStageDetailsWidget' || $name === 'InlineRelationshipsWidget')) ||
            ($modelType === 'Product' && $name === 'WorkflowStageDetailsWidget')) {

            return true;
        } else {
            return false;
        }
    }

    /***********************************************************************
    * Legacy properties
    * Preserved for backwards compatibility with custom modules
    ***********************************************************************/
    
    public $block; 
    public $modelType;
}
