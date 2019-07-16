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





Yii::app()->clientScript->registerCss('inlineEmailFormCss',"
.cke_combopanel {
    display: none;
}

#email-mini-module > .email-title-bar {
    padding: 0 5px;
    line-height: 23px;
    height: 23px;
}


#inline-email-form form {
    position: relative;
}
#inline-email-form label {
	width:80px;
}
#inline-email-form .cancel-send-button {
    margin-right: 0;
}
#inline-email-form input[type='text'], #inline-email-form textarea {
	width:465px;
}
#inline-email-form a {
	text-decoration:none;
}
#inline-email-form input#emailSendTime {
	width:120px;
}



#email-mini-module .email-input-row {
    border-top: 1px solid #d3d3d3;
    height: 28px;
}

#cke_email-message {    
    border-left: none !important;
    border-right: none !important;
}

#email-mini-module .email-inputs.form {
    padding: 0;
    margin: 0;
    border-radius: 0;
}

#email-mini-module {
    padding: 0;
}

#email-mini-module .x2-email-label {
    vertical-align: middle;
    width: auto;
    display: inline-block;
    margin-top: -1px;
    color: rgb(93,93,93);
    margin-left: 9px;
    margin-right: 8px;
    font-weight: normal;
    font-size: 13px;
    min-width: 58px;
}

#email-mini-module .email-input-row > select {
    margin-top: 4px;
    margin-bottom: 0px;
}

#email-mini-module .email-input-row > input {
    width: 100%;
    box-sizing: border-box;
    margin: 0;
    border: none;
    border-radius: 0;
    padding: 7px;
}

#email-settings-button {
    margin-top: 6px;
    margin-right: 5px;
}

#email-mini-module .email-title-bar {
    font-weight: bold;
}

#email-mini-module .email-to-row,
#cc-row, 
#bcc-row {
    position: relative;
}

#cc-row, 
#bcc-row {
    border-top: 1px solid transparent !important;
}

#email-mini-module .email-to-row.show-input-name > input,
#cc-row > input,
#bcc-row > input {
    padding-left: 35px !important;
}

#email-mini-module .email-to-row.show-input-name::before {
    content: '".CHtml::encode (Yii::t('app', 'To'))."';
}

#email-to {
    padding-right: 52px;
}

#cc-row.show-input-name::before {
    content: 'Cc';
}

#bcc-row.show-input-name::before {
    content: 'Bcc';
}

#email-mini-module .email-to-row.show-input-name::before,
#cc-row.show-input-name::before,
#bcc-row.show-input-name::before {
    color: rgb(93,93,93);
    position: absolute;
    display: inline-block;
    top: 6px;
    left: 8px;
}

#email-mini-module .toggle-container {
    float: right;
    z-index: 1;
    position: relative;
    top: -22px;
    right: 8px;
    height: 0;
}

#email-mini-module .bottom-row-outer {
    min-height: 51px;
}

#email-mini-module .last-button-row {
    position: absolute;
    bottom: 0;
    right: 0;
    display: inline-block;
    float: right;
    padding: 3px;
}

#email-attachments {
    width: 316px;
    border-radius: 4px;
    float:left;
    padding-top: 10px;
    padding-left: 3px;
    overflow:visible;
}

#email-attachments > form {
    text-align:left;
    background:none;
    overflow:visible;
}

#email-attachments {
    position: relative;
    bottom: 0;
}

#email-attachments .upload-file-container {
    width: 300px;
    padding: 5px;
    background: gray;
    border: 1px solid #BEBDBD;
    background: #e9e9e9;
}

#email-attachments .upload-file-container > .remove {
    float: right;
}

#email-attachments .upload-file-container > .remove > a {
    color: rgb(144, 144, 144);
}

#email-attachments .upload-file-container > .remove > a:hover {
    color: rgb(186, 186, 186);
}

#email-attachments .next-attachment .remove {
    display: none;
}

#email-attachments .upload-file-container {
    margin-top: 3px;
}

#email-attachments .upload-file-container.next-attachment,
#email-attachments .upload-file-container:first-child {
    margin-top: 0;
}

#email-mini-module .attachment-button {
    padding: 0;
    height: 27px;
    width: 29px;
    margin-left: 0px;
    margin-bottom: 2px;
}

#send-email-button {
    margin-right: 5px !important;
}

#send-email-button,
#email-mini-module .cancel-send-button {
    height: 35px;
    margin: 0;
    display: inline-block;
}

");

Yii::app()->clientScript->registerResponsiveCss('inlineEmailFormResponsiveCss',"

#email-settings-info {
    margin-bottom: 5px;
    display: block;
}

@media (max-width: 840px) {
    #inline-email-form .email-input-row > input {
        width: 50% !important;
    }
    #InlineEmail_subject {
        display: block;
    }
    #email-template {
        margin-left: 8px;
    }
}

");


?>
<div class="form email-status" id="inline-email-status" style="display:none"></div>
<div id="inline-email-top"></div>

<div id="inline-email-form" <?php echo $this->startHidden ? "style='display: none;'" : ''; ?>
 class='fixed-email-form'>
    <span id="template-change-confirm" style="display:none"><?php 
        echo Yii::t(
            'app',
            'Note: you have entered text into the email that will be lost. Are you sure you want ' .
            'to continue?'); ?>
    </span>
    <?php

    $emailSent = false;

    if(!empty($this->model->status)){
        $index = array_search('200', $this->model->status);
        if($index !== false){
            unset($this->model->status[$index]);
            $this->model->message = '';
            $signature = Yii::app()->params->profile->getSignature(true);
            $this->model->message = '<font face="Arial" size="2">'.(empty($signature) ? 
                '' : '<br><br>'.$signature).'</font>';
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

    <div id="email-mini-module" 
     class="wide x2-layout-island<?php if($emailSent) echo ' hidden'; ?>">

    <span class='widget-resize-handle'></span>
    <div class='email-title-bar submenu-title-bar widget-title-bar'>
        <span class='widget-title'><?php 
            echo CHtml::encode (Yii::t('app', 'New Message')); ?></span>
        <?php
        if (!$this->disableTemplates) {
            echo X2Html::settingsButton (Yii::t('app', 'Email Widget Settings'), 
                array (
                    'id' => 'email-settings-button',
                    'class' => 
                        'right x2-popup-dropdown-button widget-settings-button x2-icon-button',
                    // hide the settings menu if email templates are disabled
                    'style' => 'display: none;'
                ));
        ?> 
        <ul id='email-settings-menu' class='x2-popup-dropdown-menu' style='display: none;'>
            <li>
                <span><?php echo Yii::t('app', 'Set Default Template'); ?></span>
            </li>
        </ul>
        <?php
        }
        ?>
        <?php
//        echo X2Html::fa ('fa-expand', array (
//            'class' => 'email-fullscreen-button x2-icon-button fa-lg x2-hide',
//        ));
        echo X2Html::fa ('fa-level-down', array (
            'class' => 'email-reattach-button x2-icon-button fa-lg x2-hide',
            'style' => 'visibility: hidden;',
            'title' => CHtml::encode (Yii::t('app', 'Reattach email form')),
        ));
        ?>
    </div>
    <?php

    $formConfig = array (
        'enableAjaxValidation' => false,
        'method' => 'post',
    );
    $form = $this->beginWidget('CActiveForm', $formConfig);
    echo X2Html::loadingIcon (array('id' => 'email-sending-icon', 'style' => 'display: none'));
    echo $this->specialFields;
    echo $form->hiddenField($this->model, 'modelId');
    echo CHtml::hiddenField ('associationType', $associationType);
    echo $form->hiddenField($this->model, 'modelName');
    echo CHtml::hiddenField('contactFlag', $this->contactFlag);
    ?>
    <div class='email-inputs form'>
        <div class="row">
            <div id="inline-email-errors" class="error" style="display:none"></div>
            <?php echo $form->errorSummary(
                $this->model, 
                Yii::t('app', "Please fix the following errors:"),
                null
            ); ?>
        </div>
        <div class="row email-input-row credId-row" 
         <?php echo $this->hideFromField ? 'style="display: none;"' : ''; ?>>
            <?php
            echo $form->label (
                $this->model,
                'credId',
                array(
                    'class' => 'credId-label x2-email-label',
                ));
            
            echo Credentials::selectorField($this->model, 'credId'); 
            ?>
        </div>
    <div class='addressee-rows'>
        <div class="row email-input-row email-to-row show-input-name">
            <?php 
            //echo $form->label($this->model, 'to', array('class' => 'x2-email-label')); 
            echo $form->textField(
                $this->model, 'to', array(
                    'id' => 'email-to',
                    'class' => 'x2-default-field',
                    'data-default-text' => CHtml::encode (Yii::t('app', 'Addressees')),
                    'tabindex' => '1',
                )); 
            ?>
            <div class='toggle-container'>
                <a href="javascript:void(0)" 
                 id="cc-toggle"<?php if(!empty($this->model->cc)) echo ' style="display:none;"'; ?>>
                    Cc
                </a>
                <a href="javascript:void(0)" 
                 id="bcc-toggle"<?php if(!empty($this->model->bcc)) echo ' style="display:none;"'; ?>>
                    Bcc</a>
            </div>
        </div>
        <div class="row email-input-row show-input-name" id="cc-row" 
         <?php if(empty($this->model->cc)) echo ' style="display:none;"'; ?>>
            <?php 
            //echo $form->label($this->model, 'cc', array('class' => 'x2-email-label')); 
            echo $form->textField($this->model, 'cc', array('id' => 'email-cc', 'tabindex' => '2'));
            ?>
        </div>
        <div class="row email-input-row show-input-name" id="bcc-row"
         <?php if(empty($this->model->bcc)) echo ' style="display:none;"'; ?>>
            <?php 
            //echo $form->label($this->model, 'bcc', array('class' => 'x2-email-label')); 
            echo $form->textField(
                $this->model, 'bcc', array('id' => 'email-bcc', 'tabindex' => '3')); 
        ?>
        </div>
    </div>
        <div class="row email-input-row">
            <?php 
            //echo $form->label($this->model, 'subject', array('class' => 'x2-email-label')); 
            echo $form->textField(
                $this->model, 'subject', 
                array(
                    'tabindex' => '4',
                    'class' => 'x2-default-field',
                    'data-default-text' => CHtml::encode (Yii::t('app', 'Subject')),
                )); 
            ?>
        </div>
        <?php
        if (!$this->disableTemplates) {
        ?>
        <div class="row email-input-row">
            <?php
            $templateList = Docs::getEmailTemplates($type, $associationType);
            $target = $this->model->targetModel;
            echo $form->label(
                $this->model, 'template',
                array(
                    'class' => 'x2-email-label',
                ));
            if (!isset($this->template) && 
                $target instanceof Quote && isset($target->template) &&
                !isset ($this->model->template)) {

                // When sending an InlineEmail targeting a Quote
                list($templateName, $selectedTemplate) = Fields::nameAndId($target->template);
                $this->model->template = $selectedTemplate;
            } 
            echo $form->dropDownList(
                $this->model, 'template',
                array('0' => Yii::t('docs', 'Custom Message')) + $templateList,
                array('id' => 'email-template'));
        ?>
        </div>
        <?php
        }
        ?>
        <div class="row" id="email-message-box">
        <?php 
        echo $form->textArea(
            $this->model, 'message',
            array(
                'id' => 'email-message',
                'style' => 'margin:0;padding:0;'
            )); 
        ?>
        </div>
    </div>
    <div class="row bottom-row-outer">
        <div class="row" id="email-attachments">
            <div>
                <?php 
                if (isset ($attachments)) { // is this a refreshed form with previous attachments?  
                    foreach ($attachments as $attachment) { ?>
                    <div class='upload-file-container'>
                        <span class="filename"><?php echo $attachment['filename']; ?></span>
                        <span class="remove"
                         title="<?php echo CHtml::encode (Yii::t('app', 'Remove')); ?>">
                            <a class='fa fa-close x2-icon-gray' href="#"></a>
                        </span>
                        <span class="error"></span>
                        <input type="hidden" name="AttachmentFiles[types][]" 
                         value="<?php echo $attachment['types']; ?>">
                        <input type="hidden" name="AttachmentFiles[id][]" class="AttachmentFiles" 
                         value="<?php echo $attachment['id']; ?>">
                    </div>
                    <?php 
                    } 
                } 
                ?>
                <div class="next-attachment">
                    <span class="upload-wrapper">
                        <span class="x2-file-wrapper">
                            <input type="file" class="x2-file-input" name="upload">
                            <button type="button" 
                             class="attachment-button x2-button fa fa-paperclip fa-lg">
                            </button>
                            <?php 
                            echo CHtml::image(
                                Yii::app()->theme->getBaseUrl().'/images/loading.gif', 
                                Yii::t('app', 'Loading'), 
                                array(
                                    'id' => 'choose-file-saving-icon',
                                    'style' => 
                                        'position: absolute; width: 14px; height: 14px; 
                                        filter: alpha(opacity=0); -moz-opacity: 0.00; 
                                        opacity: 0.00;'
                                )); 
                            ?>
                        </span>
                        <!--<span style="vertical-align: middle">
                            <?php 
                            echo Yii::t('media', 'Max').' '.Media::getServerMaxUploadSize(); 
                            ?> MB
                        </span>-->
                    </span>
                    <span class="filename"></span>
                    <span class="remove" 
                     title="<?php echo CHtml::encode (Yii::t('app', 'Remove')); ?>">
                        <a class='fa fa-close x2-icon-gray' href="#"></a>
                    </span>
                    <span class="error"></span>
                </div>
            </div>
        </div>

        <div class="row buttons last-button-row">
            <?php

            // if(is_file(__DIR__.'/inlineEmailForm_pro.php'))
            // include('inlineEmailForm_pro.php');
             
            if ($this instanceof EmailInboxesEmailForm) {
                $this->renderEmailSyncCheckBox ($form);
            }
             

            echo CHtml::button(
                Yii::t('app', 'Cancel'), 
                array(
                    'class' => 'x2-button right cancel-send-button x2-button-large'
                ));

            echo CHtml::ajaxSubmitButton(
                Yii::t('app', 'Send'), 
                Yii::app()->controller->createUrl (
                    (isset ($this->action) ? $this->action : 'inlineEmail'), array(
                        'ajax' => 1,
                        'postReplace'=>$this->postReplace,
                        'contactFlag'=>$this->contactFlag,
                        'skipEvent'=>$this->skipEvent
                    )
                ), 
                array(
                    'beforeSend' => "setInlineEmailFormLoading",
                    'dataType' => 'json',
                    'success' => "function (data) {
                        x2.inlineEmailEditorManager.handleInlineEmailActionResponse (data);
                    }",
                ), 
                array(
                    'id' => 'send-email-button',
                    'class' => 'x2-button highlight x2-button-large',
                    'name' => 'InlineEmail[submit]',
                    'onclick' => '
                        if (!x2.isAndroid) window.inlineEmailEditor.updateElement();
                        // campaign test email-specific. Causes recordId to update
                        $("#InlineEmail_recordName").blur ();
                    ',
                )
            );
            ?>
        </div>
        <div class='clearfix'></div>
    </div>
        <?php $this->endWidget(); ?>
    </div>
</div>

<?php
if (!$this->disableTemplates) {
?>
<div id='email-settings-dialog' style='display: none;'>
    <form><!-- saved via ajax, so it doesn't need a CSRF token hidden input -->
        <span id='email-settings-info'><?php echo Yii::t(
            'app', 'Designate an email template as the default template for {moduleName}.',
            array ('{moduleName}' => strtolower (X2Model::getModuleName ($associationType)))); ?>
        </span>

        <?php
        echo CHtml::label(
            Yii::t('app', 'Email Template: '), 'template');
        echo CHtml::dropDownList(
            'templateId', $this->template, array ('' => Yii::t('app', 'None')) + $templateList);
        echo CHtml::hiddenField ('moduleName', Yii::app()->controller->module->name);
        ?>
    </form>
</div>
<?php
}
?>
