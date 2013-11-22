<?php

/* * *******************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 * ****************************************************************************** */

/**
 * Class for displaying tags on a record.
 *
 * @package X2CRM.components
 */
class X2WidgetList extends X2Widget {

    public $model;
    public $modelType;
    public $block; // left, right, or center
    public $layout; // associative array with 3 lists of widgets: left, right, and center
    public $associationType;
    public $associationId;

    // widget specific javascript packages
    public static function packages () {
        $packages = array (
            'widgetListCombinedCss' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'css' => array (
                    'js/widgetListCombined.css'
                    /*'js/gallerymanager/bootstrap/css/bootstrap.css',
                    'js/jqplot/jquery.jqplot.css',
                    'js/checklistDropdown/jquery.multiselect.css'*/
                )
            ),
            'widgetListCombinedCss2' => array(
                'baseUrl' => Yii::app()->getTheme ()->getBaseUrl (),
                'css' => array (
                    'css/widgetListCombined.css'
                    /*'css/galleryWidgetCssOverrides.css',
                    'css/x2chart.css'*/
                )
            ),
            'GalleryWidgetJS' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/galleryManagerDialogSetup.js',
                    'js/gallerymanager/bootstrap/js/bootstrap.js',
                ),
                /*'css' => array (
                    'js/gallerymanager/bootstrap/css/bootstrap.css',
                )*/
            ),
            /*'GalleryWidgetCss' => array(
                'baseUrl' => Yii::app()->getTheme ()->getBaseUrl (),
                'css' => array(
                    'css/galleryWidgetCssOverrides.css',
                )
            ),*/
            'ChartWidgetExtJS' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/jqplot/jquery.jqplot.js',
                    'js/jqplot/plugins/jqplot.pieRenderer.js',
                    'js/jqplot/plugins/jqplot.categoryAxisRenderer.js',
                    'js/jqplot/plugins/jqplot.pointLabels.js',
                    'js/jqplot/plugins/jqplot.dateAxisRenderer.js',
                    'js/jqplot/plugins/jqplot.highlighter.js',
                    'js/jqplot/plugins/jqplot.enhancedLegendRenderer.js',
                    'js/checklistDropdown/jquery.multiselect.js',
                ),
                /*'css' => array(
                    'js/jqplot/jquery.jqplot.css',
                    'js/checklistDropdown/jquery.multiselect.css'
                ),*/
            ),
            'ChartWidgetExtCss' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'css' => array(
                    'js/jqplot/jquery.jqplot.css',
                    'js/checklistDropdown/jquery.multiselect.css'
                ),
            ),
            'ChartWidgetJS' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/auxlib.js',
                    'js/X2Chart.js',
                    'js/X2ActionHistoryChart.js',/* x2prostart */
                    'js/X2CampaignChart.js',/* x2proend */
                ),
            ),
            'ChartWidgetCss' => array(
                'baseUrl' => Yii::app()->getTheme ()->getBaseUrl (),
                'css' => array(
                    'css/x2chart.css'
                )
            ),
            'InlineRelationshipsJS' => array(
                'baseUrl' => Yii::app()->getTheme ()->getBaseUrl ().'/css/gridview/',
                'js' => array (
                    'jquery.yiigridview.js',
                )
            ),
            'InlineTagsJS' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/auxlib.js',
                    'js/X2Tags/TagContainer.js',
                    'js/X2Tags/TagCreationContainer.js',
                    'js/X2Tags/InlineTagsContainer.js',
                ),
            ),
        );
        if (AuxLib::isIE8 ()) {
            $packages['ChartWidgetExtJS']['js'][] = 'js/jqplot/excanvas.js';
        }
        return $packages;
    }


    public function init(){
        // widget layout
        if(!Yii::app()->user->isGuest){
            $this->layout = Yii::app()->params->profile->getLayout ();
        }else{
            $profile = new Profile();
            $this->layout = $profile->initLayout ();
        }

        parent::init();
    }

    public function run(){

        if($this->block == 'center'){
            echo '<div id="content-widgets">';
            foreach($this->layout['center'] as $name => $widget){ // list of widgets
                $viewParams = array(
                    'widget' => $widget,
                    'name' => $name,
                    'model' => $this->model,
                    'modelType' => $this->modelType,
                    'packagesOnly' => false
                );

                if(!$this->isExcluded ($name)){
                    $this->render(
                        'centerWidget',
                        $viewParams
                    );
                }
            }
            foreach($this->layout['hidden'] as $name => $widget){ // list of widgets
                $viewParams = array(
                    'widget' => $widget,
                    'name' => $name,
                    'model' => $this->model,
                    'modelType' => $this->modelType,
                    'packagesOnly' => true
                );
                if(!$this->isExcluded ($name)){
                    $this->render(
                        'centerWidget',
                        $viewParams
                    );
                }
            }

            echo '</div>';
        }
    }

    private function isExcluded ($name) {
        if ($this->modelType == 'BugReports' && ($name != 'InlineRelationships' && $name!='WorkflowStageDetails') ||
            $this->modelType == 'Quote' && $name == 'WorkflowStageDetails' ||
            $this->modelType == 'Marketing' &&
            ($name == 'WorkflowStageDetails' || $name === 'InlineRelationships') ||
            $this->modelType == 'services' && $name == 'InlineRelationships' ||
            $this->modelType === 'products' &&
            ($name === 'InlineRelationships' || $name === 'WorkflowStageDetails')) {
            return true;
        } else {
            return false;
        }
    }

}

