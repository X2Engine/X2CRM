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
    Yii::app()->controller->assetsUrl.'/js/EventCommentPublisherController.js');

$this->onPageLoad ("
    x2.main.controllers['$this->pageId'] = new x2.EventCommentPublisherController ({
        translations: ".CJSON::encode (array (
            'error code' => Yii::t('app','error code'),
            'error message' => Yii::t('app','error message'),
            'failed to parse response from server' => Yii::t('app','failed to parse response from server'),
            'Error' => Yii::t('app','Error'),
            'Upload failed' => Yii::t('app','Upload failed'),
        )).",
    });
", CClientScript::POS_END);


?>


<div class='event-view'>
    <?php

    $this->renderPartial (
        $this->pathAliasBase.'views.mobile._activityEventItem', array (
            'data' => $model,
            'showComments' => false,
        )
    );

    $this->widget (
        'application.modules.mobile.components.RecordIndexListView', 
        array (
            'dataProvider' => $dataProvider,
            'template' => '{items}{moreButton}',
            'itemView' => 'application.modules.mobile.views.mobile._activityEventItem',
            'viewData' => array (
                'showComments' => false,
                'showRecipient' => false,
            ),
            'htmlOptions' => array (
                'class' => 'record-index-list-view comment-list-view'
            ),
        ));

    ?>

    <div id='footer' data-role="footer" class='comment-publisher fixed-footer control-panel'>
        <?php
        $form = $this->beginWidget ('MobileActiveForm', array (
            'htmlOptions' => array (
                'class' => 'comment-publisher-form',
            ),
            'jSClassParams' => array (
                'validate' => 'js:function () {
                    return $.trim (this.form$.find ("input[type=\'text\']").val ());
                }',
            )
        ));
        echo $form->mobileCoordinates ();
        echo $form->mobileLocationCoordinates ();
        ?>
            <div class='photo-attachments-container'>
            </div>
             <!--<div class='photo-attach-button icon-button'>
                <?php
                    //echo X2Html::fa ('camera');
                ?>
            </div>
            <div class='audio-attach-button icon-button'>
                <?php
                    //echo X2Html::fa ('fa-file-audio-o');
                ?>
            </div>-->
            <?php
                echo $form->textField ($formModel, 'text', array (
                    'placeholder' => 'Add a reply...',
                    'class' => 'reply-box',
                ));
            ?>
             <div class='submit-button disabled'><?php  
                echo CHtml::encode (Yii::t('mobile', 'Post'));
             ?></div>
        <?php
        $this->endWidget ();    
        ?>
    </div>
</div>
