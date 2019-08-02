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






Yii::app()->clientScript->registerCssFile(
    X2WebModule::getAssetsUrlOfModule ('EmailInboxes').'/css/emailInboxes.css');
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/emailInboxWidget.css');


if ($mailbox && $mailbox->credentials && !$mailbox->credentials->auth->disableInbox) {

    $this->widget('EmailInboxesGridView', array(
        'id' => $this->getWidgetKey (),
        'htmlOptions' => array ('class' => 'email-inbox-grid grid-view'),
        'enableQtips' => true,
        'emailCount' => $mailbox->getMessageCount (),
        'qtipManager' => array (
            'EmailInboxesQtipManager',
            'loadingText'=> addslashes(Yii::t('app','loading...')),
            'qtipSelector' => ".contact-name"
        ),
        'columns' => array (
            array (
                'name' => 'flagged',
                'type' => 'raw',
                'value' => '$data->renderToggleImportant ()',
                'htmlOptions' => array (
                    'class' => 'flagged-cell'
                ),
            ),
            array (
                'name' => 'from',
                'type' => 'raw',
                'value' => '$data->renderFromField ()',
                'htmlOptions' => array (
                    'class' => 'from-cell'
                ),
            ),
            array (
                'name' => 'subject',
                'type' => 'text',
                'type' => 'raw',
                'value' => '$data->renderSubject (true)',
                'htmlOptions' => array (
                    'class' => 'subject-cell'
                ),
            ),
            array (
                'name' => 'date',
                'type' => 'raw',
                'value' => '$data->renderDate ()',
                'htmlOptions' => array (
                    'class' => 'date-cell'
                ),
            ),
        ),
        'rowCssClassExpression' => '$data->seen ? "seen-message-row" : "unseen-message-row"',
        'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
            '/css/gridview',
        'hideHeader' => true,
        'mailbox' => $mailbox,
        'messageView' => $uid !== null,
        'enableGridResizing' => false,
        'dataProvider' => $dataProvider,
        'template' => "<div class='grid-controls-bar'>{mailboxControlsCompact}</div> {items} {pager}",
        'fullscreen' => true,
        'loadingMailbox' => $loadMessagesOnPageLoad,
        'disableHistory' => true,
        'moduleName' => 'EmailInboxes',
        'updateParams' => array (
            'widgetClass' => get_class ($this),
            'widgetType' => 'profile',
        ),
    ));
    if (!Yii::app()->controller->isAjaxRequest ()) {
        // inlineEmailForm.js breaks when run before ckeditor textarea is added to DOM, which is
        // what would happen if this were instantiated during an ajax update
        $this->widget('EmailInboxesEmailForm', array(
            'action' => '/emailInboxes/emailInboxes/inlineEmail',
            'associationType' => 'EmailInboxes', 
            'moduleName' => 'EmailInboxes', 
            'mailbox' => $mailbox,
            'attributes' => array(
                'credId' => $mailbox->credentialId,
            ),
            'hideFromField' => true,
            'disableTemplates' => true,
            'disableHistory' => true,
            'enableResizability' => true,
            'startHidden' => true,
        ));
    } else {
        $settings = $this->mailbox->settings; 
        $logOutboundByDefault = ((bool) $settings['logOutboundByDefault']);
        // we're not reinstantiating the form, so we need to manually update the default settings
        Yii::app()->clientScript->registerScript('emailFormUpdate',"
            $('#inline-email-form [name=\"InlineEmail[emailInboxesEmailSync]\"]').prop (
                'checked', ".($logOutboundByDefault ? 'true' : 'false').");
        ");
    }

} else if ($mailbox) {
        ?><div>
            <div class='flash-error'>
                 <?php echo Yii::t('app', 'Inbox usage is disabled for these credentials. Please update the settings on the "Manage Apps" page to enable inbox access.'); ?>
            </div>
            <a class="centered" href='<?php echo $this->controller->createUrl ('manageCredentials'); ?>'><?php
                echo Yii::t('emailInboxes', 'Manage your application credentials');
            ?></a>
                </div><?php
}

?>
<div id='message-container' <?php echo ($uid === null) ? "style='display: none;'" : ''; ?>
 class='email-message-container'>
<?php
if ($uid !== null) {
    echo $mailbox->renderMessage ($uid);
}
?>
</div>
<?php

Yii::app()->clientScript->registerScriptFile (
    X2WebModule::getAssetsUrlOfModule ('EmailInboxes').'/js/emailInboxes.js');
Yii::app()->clientScript->registerScript ('emailInboxJS', '
;(function () {
    x2.emailInbox = new x2.EmailInbox ({
        notConfigured: '.($notConfigured ? 'true' : 'false').',
        noneSelectedText: "'.Yii::t('emailInboxes',
            'No messages are selected!').'",
        deleteConfirmTxt: "'.Yii::t('emailInboxes',
            'Are you sure you want to delete the selected messages?').'",
        pollTimeout: '.$pollTimeout.',
        disableHistory: true,
        gridId: "'.$this->getWidgetKey ().'",
        emailFolder: "'.($mailbox ? $mailbox->getCurrentFolder() : null).'",
        loadMessagesOnPageLoad: '.($loadMessagesOnPageLoad ? 'true' : 'false').',
        updateParams: '.CJSON::encode (array (
            'widgetClass' => get_class ($this),
            'widgetType' => 'profile',
        )).'
    });
}) ();

', CClientScript::POS_READY);

