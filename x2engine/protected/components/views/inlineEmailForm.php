<?php
/*********************************************************************************
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
 ********************************************************************************/

?>
<div class="form email-status" id="inline-email-status" style="display:none"></div>
<div id="inline-email-top"></div>

<div id="inline-email-form">
    <span id="template-change-confirm" style="display:none"><?php echo Yii::t('app', 'Note: you have entered text into the email that will be lost. Are you sure you want to continue?'); ?></span>
<?php
echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/loading.gif', Yii::t('app', 'Loading'), array('id' => 'email-sending-icon'));
$emailSent = false;

if(!empty($this->model->status)){
    $index = array_search('200', $this->model->status);
    if($index !== false){
        unset($this->model->status[$index]);
        $this->model->message = '';
        $signature = Yii::app()->params->profile->getSignature(true);
        $this->model->message = '<font face="Arial" size="2">'.(empty($signature) ? '' : '<br><br>'.$signature).'</font>';
        $this->model->subject = '';
        $attachments = array();
        $emailSent = true;
    }
    echo '<div class="form email-status">';
    foreach($this->model->status as &$status_msg)
        echo $status_msg." \n";
    echo '</div>';
}
?>

    <div id="email-mini-module" class="wide form<?php if($emailSent) echo ' hidden'; ?>">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'enableAjaxValidation' => false,
        'method' => 'post',
            ));
    echo $this->specialFields;
    echo $form->hiddenField($this->model, 'modelId');
    echo $form->hiddenField($this->model, 'modelName');
    echo CHtml::hiddenField('contactFlag', $this->contactFlag);
    ?>
        <div class="row">
            <div id="inline-email-errors" class="error" style="display:none"></div>
<?php echo $form->errorSummary($this->model, Yii::t('app', "Please fix the following errors:"), null, array('style' => 'margin-bottom: 5px;')); ?>
        </div>
        <div class="row">
<?php echo $form->label($this->model, 'credId', array('class' => 'x2-email-label')); ?>
            <?php echo Credentials::selectorField($this->model, 'credId'); ?>
        </div><!-- .row -->
        <div class="row">
<?php //echo $form->error($this->model,'to');  ?>
            <?php echo $form->label($this->model, 'to', array('class' => 'x2-email-label')); ?>
            <?php echo $form->textField($this->model, 'to', array('id' => 'email-to', 'style' => 'width:400px;', 'tabindex' => '1')); ?>
            <a href="javascript:void(0)" id="cc-toggle"<?php if(!empty($this->model->cc)) echo ' style="display:none;"'; ?>>[cc]</a>
            <a href="javascript:void(0)" id="bcc-toggle"<?php if(!empty($this->model->bcc)) echo ' style="display:none;"'; ?>>[bcc]</a>
        </div>
        <div class="row" id="cc-row"<?php if(empty($this->model->cc)) echo ' style="display:none;"'; ?>>
<?php //echo $form->error($this->model,'to');  ?>
            <?php echo $form->label($this->model, 'cc', array('class' => 'x2-email-label')); ?>
            <?php echo $form->textField($this->model, 'cc', array('id' => 'email-cc', 'tabindex' => '2')); ?>
        </div>
        <div class="row" id="bcc-row"<?php if(empty($this->model->bcc)) echo ' style="display:none;"'; ?>>
<?php //echo $form->error($this->model,'to');  ?>
            <?php echo $form->label($this->model, 'bcc', array('class' => 'x2-email-label')); ?>
            <?php echo $form->textField($this->model, 'bcc', array('id' => 'email-bcc', 'tabindex' => '3')); ?>
        </div>
        <div class="row">
<?php echo $form->label($this->model, 'subject', array('class' => 'x2-email-label')); ?>
            <?php echo $form->textField($this->model, 'subject', array('style' => 'width: 265px;', 'tabindex' => '4')); ?>
            <?php
            $templateList = Docs::getEmailTemplates($type);
            $templateList = array('0' => Yii::t('docs', 'Custom Message')) + $templateList;
            echo $form->label($this->model, 'template', array('class' => 'x2-email-label', 'style' => 'float: none; margin-left: 10px; vertical-align: text-top;'));
            echo $form->dropDownList($this->model, 'template', $templateList, array('id' => 'email-template'));
            ?>

        </div>
        <div class="row" id="email-message-box">
<?php echo $form->textArea($this->model, 'message', array('id' => 'email-message', 'style' => 'margin:0;padding:0;')); ?>
        </div>

        <div class="row" id="email-attachments">
            <div class="form" style="text-align:left;background:none;overflow:visible;">
                <b><?php echo Yii::t('app', 'Attach a File'); ?></b><br />
<?php if(isset($attachments)){ // is this a refreshed form with previous attachments?  ?>
    <?php foreach($attachments as $attachment){ ?>
                        <div>
                            <span class="filename"><?php echo $attachment['filename']; ?></span>
                            <span class="remove"><a href="#">[x]</a></span>
                            <span class="error"></span>
                            <input type="hidden" name="AttachmentFiles[temp][]" value="<?php echo ($attachment['temp'] ? "true" : "false"); ?>">
                            <input type="hidden" name="AttachmentFiles[id][]" class="AttachmentFiles" value="<?php echo $attachment['id']; ?>">
                        </div>
    <?php } ?>
<?php } ?>
                <div class="next-attachment">
                    <span class="upload-wrapper">
                        <span class="x2-file-wrapper">
                            <input type="file" class="x2-file-input" name="upload" onChange="checkName(this, '#submitAttach'); if($('#submitAttach').attr('disabled') != 'disabled') {fileUpload(this.form, $(this), '<?php echo Yii::app()->createUrl('/site/tmpUpload'); ?>', '<?php echo Yii::app()->createUrl('/site/removeTmpUpload'); ?>'); }">
                            <input type="button" class="x2-button" value="Choose File">
<?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/loading.gif', Yii::t('app', 'Loading'), array('id' => 'choose-file-saving-icon', 'style' => 'position: absolute; width: 14px; height: 14px; filter: alpha(opacity=0); -moz-opacity: 0.00; opacity: 0.00;')); ?>
                        </span>
                        <span style="vertical-align: middle">
                            <?php echo Yii::t('media', 'Max').' '.Media::getServerMaxUploadSize(); ?> MB
                        </span>
                    </span>
                    <span class="filename"></span>
                    <span class="remove"></span>
                    <span class="error"></span>
                </div>
            </div>
        </div>

        <div class="row buttons" style="padding-left:0;">
<?php
echo CHtml::ajaxSubmitButton(
        Yii::t('app', 'Send'), array('inlineEmail', 'ajax' => 1,'postReplace'=>$this->postReplace,'contactFlag'=>$this->contactFlag,'skipEvent'=>$this->skipEvent), array(
    'beforeSend' => "setInlineEmailFormLoading",
    'dataType' => 'json',
    'success' => "handleInlineEmailActionResponse",
        ), array(
    'id' => 'send-email-button',
    'class' => 'x2-button highlight',
    // 'style'=>'margin-left:-20px;',
    'name' => 'InlineEmail[submit]',
    'onclick' => 'window.inlineEmailEditor.updateElement();',
        )
);

// if(is_file(__DIR__.'/inlineEmailForm_pro.php'))
// include('inlineEmailForm_pro.php');

echo CHtml::resetButton(Yii::t('app', 'Cancel'), array('class' => 'x2-button right', 'onclick' => "toggleEmailForm();return false;"));
?>
        </div>
            <?php $this->endWidget(); ?>
    </div>
</div>
