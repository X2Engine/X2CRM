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




Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->assetsUrl.'/js/RecordViewController.js');

$supportsActionHistory = (bool) $this->asa ('MobileActionHistoryBehavior');

$authParams['X2Model'] = $model;

$this->onPageLoad ("
    x2.main.controllers['$this->pageId'] = new x2.RecordViewController (".CJSON::encode (array (
        'modelName' => get_class ($model),
        //'modelEmail' => $model->email,
        'modelId' => $model->id,
        'myProfileId' => Yii::app()->params->profile->id,
        'translations' => array (
            'deleteConfirm' => Yii::t('mobile', 'Are you sure you want to delete this record?'),
            'deleteConfirmOkay' => Yii::t('mobile', 'Okay'),
            'deleteConfirmCancel' => Yii::t('mobile', 'Cancel'),
        ),
        'supportsActionHistory' => $supportsActionHistory,
    )).");
", CClientScript::POS_END);



?>


<div class='refresh-content' data-refresh-selector='.page-title'>
    <h1 class='page-title ui-title'>
    <?php
    if ($model instanceof Profile) {
        echo CHtml::encode (Modules::displayName (false, 'Users'));
    } else {
        echo CHtml::encode ($this->getModuleObj ()->getDisplayName (false));
    }
    ?>
    </h1>
</div>
<?php

if ($model instanceof X2Model) {
    if ($this->hasMobileAction ('mobileUpdate') &&
        Yii::app()->user->checkAccess(ucfirst ($this->module->name).'Update', $authParams)) {
    ?>

    <div class='refresh-content' data-refresh-selector='.header-content-right'>
        <div class='header-content-right'>
            <div class='mail-button ui-btn icon-btn' style='margin-right: 50px;'>
            
            <?php
            echo X2Html::fa ('envelope');
            ?>
            </div>
            <div class='edit-button ui-btn icon-btn' 
             data-x2-url='<?php echo $this->createAbsoluteUrl ('mobileUpdate', array (
                'id' => $model->id
             )); ?>'>
            <?php
            echo X2Html::fa ('pencil');
            ?>
            </div>
        </div>
    </div>

    <?php
    }
}

if ($supportsActionHistory) {
?>
<div class='record-view-tabs'>
    <div data-role='navbar' class='record-view-tabs-nav-bar'>
        <ul>
            <li class='record-view-tab' data-x2-tab-name='record-details'>
                <a id="detail-tab-link" href='<?php echo '#'.MobileHtml::namespaceId ('detail-view-outer'); ?>'><?php 
                echo CHtml::encode (Yii::t('mobile', 'Details'));
                ?>
                </a>
            </li>
            <li class='record-view-tab' data-x2-tab-name='action-history'>
                <a id='history-tab-link' href='<?php echo '#'.MobileHtml::namespaceId ('action-history'); ?>'><?php 
                //echo CHtml::encode (Yii::t('mobile', 'History'));
                echo CHtml::encode (Yii::t('mobile', 'Action History'));
                ?>
                </a>
            </li>
            <li class='record-view-tab' data-x2-tab-name='action-history-attachments'>
                <a id='attachment-tab-link' href='<?php echo '#'.MobileHtml::namespaceId ('action-history-attachments'); ?>'><?php 
                //echo CHtml::encode (Yii::t('mobile', 'History'));
                echo CHtml::encode (Yii::t('mobile', 'Attachments'));
                ?>
                </a>
            </li>
        </ul>
    </div>

    <div id='<?php echo MobileHtml::namespaceId ('detail-view-outer');?>'>
    <?php
}
    
    $this->renderPartial ('application.modules.mobile.views.mobile._recordView', array (
        'model' => $model
    ));
    

if ($supportsActionHistory) {
        Yii::app()->clientScript->registerScript('hideBothPublisherButtons','
            $("#detail-tab-link").on("click",function(){
                $("#publisher-menu-button").attr("style", "display: none !important");
            });
        ');
    ?>
    </div>
    <div id='<?php echo MobileHtml::namespaceId ('action-history');?>' class='action-history-outer'>

    <?php
        $this->renderPartial ('application.modules.mobile.views.mobile._actionHistory', array (
            'model' => $model,
            'type' => 'all',
        ));
        Yii::app()->clientScript->registerScript('showPublishButto','
            $("#history-tab-link").on("click",function(){
                $("#publisher-menu-button").attr("style", "display: block !important");
            });
        ');
        
    ?>
    </div>
    <div id='<?php echo MobileHtml::namespaceId ('action-history-attachments');?>' class='action-history-outer'>

    <?php
        $this->renderPartial ('application.modules.mobile.views.mobile._actionHistoryAttachments', array (
            'model' => $model,
            'type' => 'attachments',
        ));
        Yii::app()->clientScript->registerScript('showPublishButton','
            $("#attachment-tab-link").on("click",function(){
                $("#publisher-menu-button").attr("style", "display: block !important");
            });
        ');
    ?>
    </div>
</div>
<?php
    }
?>
