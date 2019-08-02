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
 * Used by inline workflow widget to render the funnel 
 */


if (AuxLib::isIE8 ()) {
    Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/jqplot/excanvas.js');
}

if ($this->id !== 'Workflow')
    $assetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias('application.modules.workflow.assets'),false,-1,YII_DEBUG?true:null);
else 
    $assetsUrl = $this->module->assetsUrl;

Yii::app()->clientScript->registerScriptFile(
    $assetsUrl.'/js/X2Geometry.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $assetsUrl.'/js/BaseFunnel.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $assetsUrl.'/js/InlineFunnel.js', CClientScript::POS_END);


Yii::app()->clientScript->registerScript('_funnelJS',"

x2.inlineFunnel = new x2.InlineFunnel ({
    workflowStatus: ".CJSON::encode ($workflowStatus).",
    translations: ".CJSON::encode (array (
        'Completed' => Yii::t('workflow', 'Completed'),
        'Started' => Yii::t('workflow', 'Started'),
        'Details' => Yii::t('workflow', 'Details'),
        'Revert Stage' => Yii::t('workflow', 'Revert Stage'),
        'Complete Stage' => Yii::t('workflow', 'Complete Stage'),
        'Start' => Yii::t('workflow', 'Start'),
        'noRevertPermissions' => 
            Yii::t('workflow', 'You do not have permission to revert this stage.'),
        'noCompletePermissions' => 
            Yii::t('workflow', 'You do not have permission to complete this stage.'),


    )).",
    stageCount: ".$stageCount.",
    containerSelector: '#funnel-container',
    colors: ".CJSON::encode ($colors).",
    revertButtonUrl: '".Yii::app()->theme->getBaseUrl ()."/images/icons/Uncomplete.png',
    completeButtonUrl: '".Yii::app()->theme->getBaseUrl ()."/images/icons/Complete.png',
    stageNames: ".CJSON::encode (Workflow::getStageNames ($workflowStatus)).",
    stagePermissions: ".CJSON::encode (Workflow::getStagePermissions ($workflowStatus)).",
    uncompletionPermissions: ".
        CJSON::encode (Workflow::getStageUncompletionPermissions ($workflowStatus)).",
    stagesWhichRequireComments: ".
        CJSON::encode (Workflow::getStageCommentRequirements ($workflowStatus))."
});

", CClientScript::POS_END);

?>
<div id='funnel-container'></div>
<?php



