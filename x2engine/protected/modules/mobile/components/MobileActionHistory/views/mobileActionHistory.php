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




$htmlOptions = array (
    'class' => 'record-index-list-view',
);

if ($this->refresh) {
    $htmlOptions = X2Html::mergeHtmlOptions ($htmlOptions, array (
        'class' => 'refresh-content action list-view record-index-list-view',
        'data-refresh-selector' => '#action-history-'.$type.'-view',
        'data-x2-replace-on-refresh' => '1',
    ));
}


$this->widget('application.modules.mobile.components.MobileActionHistoryListView', array(
//$this->widget('zii.widgets.CListView', array(
    'id' => 'action-history-'.$type.'-view',
    'dataProvider' => $dataProvider,
    'model' => $this->model,
    'viewData' => array (
        'actionHistory' => $this,
    ),
    'itemView' => 
        'application.modules.mobile.components.MobileActionHistory.views._mobileActionHistoryItem',
    'template' => '{items}{moreButton}',
    'htmlOptions' => $htmlOptions,
));

$hasCreateAccess = Yii::app()->params->isAdmin || Yii::app()->user->checkAccess ('ActionsCreate');

if (!$this->refresh && $hasCreateAccess) {

?>

<div id='footer' data-role="footer" class='fixed-footer publisher-menu'>
    <ul>
        
        <?php
        $action = new Actions;
        if ($type === 'attachments') {
            $form = $this->beginWidget('MobileActiveForm',
                    array(
                'htmlOptions' => array(
                    'class' => 'publisher-file-upload-form'
                ),
                'action' => Yii::app()->controller->createAbsoluteUrl(
                        'mobileActionHistoryAttachmentsPublish',
                        array(
                    'id' => $this->model->id,
                    'type' => $type
                ))
            ));
            if (MobileModule::getPlatform () === 'iOS') {
            ?>
            <li class='photo-attachment-button'>
                <span><?php echo X2Html::fa('camera'); ?></span>
                <div>
                    <?php
                    echo CHtml::encode(Yii::t('mobile', 'Add photo attachment'));
                    ?>
                </div>
            </li>
            <li class='video-attachment-button'>
                <span><?php echo X2Html::fa('fa-file-video-o '); ?></span>
                <div>
                    <?php 
                    echo CHtml::encode(Yii::t('mobile', 'Add video attachment'));
                    ?>
                </div>
            </li>
            <?php 
            }
            ?>
            <li class='location-attachment-button'>
                <span><?php echo X2Html::fa('fa-location-arrow'); ?></span>
                <div>
                    <?php 
                    echo CHtml::encode(Yii::t('mobile', 'Add location attachment'));
                    ?>
                    
                    <?php
                    
                        echo $form->mobileCoordinates ();
                        echo $form->mobileLocationCoordinates ();
                        ?> 

                            
                </div>
            </li>
            <li class='audio-attachment-button'>
                <span><?php echo X2Html::fa('fa-file-audio-o'); ?></span>
                <div>
                    <?php 
                    echo CHtml::encode(Yii::t('mobile', 'Add audio note'));
                    ?>

                            
                </div>
            </li>
            <li class='file-attachment-button'>
                <span><?php echo X2Html::fa('file'); ?></span>
                <div>
                    <?php
                    echo CHtml::encode(Yii::t('mobile', 'Add file attachment'));
                    ?>
                </div>
            <?php


            echo $form->fileField($action, 'upload');
        } else {
            $form = $this->beginWidget('MobileActiveForm',
                    array(
                'htmlOptions' => array(
                    'class' => 'publisher-comment-form'
                ),
                'action' => Yii::app()->controller->createAbsoluteUrl(
                        'mobileActionHistoryPublish',
                        array(
                    'id' => $this->model->id,
                    'type' => $type
                ))
            ));
            echo $form->textField($action, 'actionDescription',
                    array(
                'placeholder' => 'Add a comment...',
                'class' => 'location-tag',
            ));    
        }
        $this->endWidget();
        ?>
            </li>

        </ul>
    </div>
<?php 
    echo CHtml::link(X2Html::fa('plus'),'#',array(
        'class' => 'fixed-corner-button publisher-menu-button',
        'id' => 'publisher-menu-button',
    ));
?>


<?php

}
?>


