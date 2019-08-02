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



$placeholderText = Yii::t('topics', 'New Reply');
Yii::app()->clientScript->registerCssFile(
        Yii::app()->controller->module->assetsUrl . '/css/topicReplyForm.css');
Yii::app()->clientScript->registerPackage('emailEditor');
Yii::app()->clientScript->registerScript('topic-reply-editor',
    'createCKEditor(
        "TopicReplies_text",{"height":"150px", placeholder: "' . $placeholderText . '"});',
CClientScript::POS_READY);

Yii::app()->clientScript->registerScript('topic-reply-file-upload','
    x2.topicReplyForm = {};
    x2.topicReplyForm.setUpFileUpload = function(){
        x2.topicReplyForm.fileUploader = x2.FileUploader.list["topic-reply-attachment"];
        if (typeof x2.topicReplyForm.fileUploader === "undefined") return;
        with (x2.topicReplyForm.fileUploader.dropzone) {
            options.autoProcessQueue = false;
            options.addRemoveLinks = true;
            options.dictRemoveFile = "";
            options.dictCancelUpload = "";

            // when finished uploading, minimize editor
            on ("success", function(){
                x2.topicReplyForm.fileUploader.toggle(false);
                x2.topicReplyForm.fileUploader.dropzone.removeAllFiles();
            });
            on("addedfile", function() {
                if (this.files[1]!=null){
                  this.removeFile(this.files[0]);
                }
              });
        }
    }
    $("#topic-save-button").click (function (evt) {
        if (typeof x2.topicReplyForm.fileUploader !== "undefined" && 
            x2.topicReplyForm.fileUploader.filesQueued()) {

            evt.preventDefault();
            x2.topicReplyForm.fileUploader.mediaParams.topicName = $("#Topics_name").val();
            x2.topicReplyForm.fileUploader.mediaParams.topicText = $("#TopicReplies_text").val();
            x2.topicReplyForm.fileUploader.dropzone.processQueue();
            x2.topicReplyForm.fileUploader.dropzone.on("success", function(files, response) {
                if (!$.isNumeric (response)) {
                    response = JSON.parse (response);
                    x2.FileUploader.destroy (x2.topicReplyForm.fileUploader);
                    $("#topic-reply-form").replaceWith (response.page);
                    x2.topicReplyForm.setUpFileUpload();
                } else {
                    window.location = "'.
                        Yii::app()->controller->createUrl('/topics/topics/view').'?id="+response;
                }
            });
        }
    });
    $("#topic-reply-submit").click (function (evt) {
        if (typeof x2.topicReplyForm.fileUploader !== "undefined" && 
            x2.topicReplyForm.fileUploader.filesQueued()) {

            evt.preventDefault();
            x2.topicReplyForm.fileUploader.mediaParams["TopicReplies[text]"] = 
                $("#TopicReplies_text").val();
            x2.topicReplyForm.fileUploader.upload();
            x2.topicReplyForm.fileUploader.dropzone.on("success", function(files, response) {
                if (!$.isNumeric (response)) {
                    response = JSON.parse (response);
                    x2.topFlashes.displayFlash (response.message, "error") 
                } else {
                    window.location = "' . $this->createUrl('/topics/topics/view',
                        array(
                            'id' => $topic->id,
                            'latest' => true)) . '";
                }
            });
            return false;
        }
    });
    $(document).on("ready",function(){
        x2.topicReplyForm.setUpFileUpload();
    });
',CClientScript::POS_READY);

?>
<div class='form'>
<?php

$form = $this->beginWidget('X2ActiveForm', array('formModel' => $model));
echo $form->textArea($model, 'text');
$model->topicId = isset($topic->id) ? $topic->id : null;
echo $form->hiddenField($model, 'topicId');
echo '<div id="topic-reply-submit-buttons">';
if ($method !== 'new-reply') {
    echo X2Html::submitButton($topic->isNewRecord ?
                    Yii::t('topics', 'Create') : Yii::t('topics', 'Update'),
            array('class' => 'x2-button highlight', 'id' => 'topic-save-button')
    );
    $this->widget('FileUploader',
            array(
        'id' => 'topic-reply-attachment',
        'url' => '/topics/create',
        'viewParams' => array(
            'showButton' => false
        )
    ));
} else {
    echo X2Html::ajaxSubmitButton('Post', $this->createUrl('/topics/newReply'),
            array(
        'beforeSend' => 'function(){x2.forms.inputLoading($("#topic-reply-submit"));}',
        'method' => 'POST',
        'success' => 'function(data){ window.location = "' . $this->createUrl('/topics/view',
                array(
            'id' => $topic->id)
        ) . '?replyId="+data }'
            ),
            array(
        'class' => 'x2-button highlight', 'id' => 'topic-reply-submit')
    );
    $this->widget ('FileUploader',array(
        'id' => 'topic-reply-attachment',
        'url' => '/site/upload',
        'mediaParams' => array(
            'associationType' => 'topicReply',
            'TopicReplies[topicId]' => $topic->id,
        ),
        'viewParams' => array (
            'showButton' => false
        )
    ));

}
echo CHtml::button(
        Yii::t('app', 'Attach A File/Photo'),
        array(
    'class' => 'x2-button',
    'onclick' => 'x2.topicReplyForm.fileUploader.toggle ()',
    'id' => "toggle-attachment-menu-button"));
echo '</div>';
$this->endWidget();

?>
</div>
<?php
