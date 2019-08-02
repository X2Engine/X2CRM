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






Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');


Yii::app()->clientScript->registerPackages (array (
    'X2History' => array (
        'baseUrl' => Yii::app()->request->baseUrl,                       
        'js' => array (
            'js/X2History.js', 
        ),
        'depends' => array ('history', 'auxlib'),
    ),
), true);

?>
<div id='inbox-body-container' class='inbox-body-container'>
<?php

if ($notConfigured) {
?>
    <div id='my-email-inbox-set-up-instructions-container'>
        <h2><?php 
            echo Yii::t('emailInboxes', 'Your personal email inbox has not yet been configured.'); 
        ?></h2>
        <a href='<?php echo $this->createUrl ('configureMyInbox'); ?>'><?php 
            echo Yii::t('emailInboxes', '-Click here to configure your personal email inbox-'); 
        ?></a>
    </div>
<?php
} else {
    $this->widget('EmailInboxesGridView', array(
        'id' => 'email-list',
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
                'value' => '$data->renderSubject ()',
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
        'template' => "{mailboxControls} {mailboxTabs} {items} {pager}",
        'fullscreen' => true,
        'loadingMailbox' => $loadMessagesOnPageLoad,
        /*'sortableAttributes' => array(
            'msgno' => Yii::t('emailInboxes', 'date'),
            'from',
            'subject',
        ),*/
    ));

?>
<div id='message-container' <?php echo ($uid === null) ? "style='display: none;'" : ''; ?>>
<?php
if ($uid !== null) {
    echo $mailbox->renderMessage ($uid);
}
?>
</div>
<div id='email-quota'>
<?php
    $quota = $mailbox->quotaString;
    echo Yii::t('emailInboxes',
        ($quota ? "{quota}" : "Unable to retrieve quota information."), array(
        '{quota}' => $quota,
    ));
}
?>
</div>
<?php
Yii::app()->clientScript->registerScriptFile ($this->module->assetsUrl.'/js/emailInboxes.js');
Yii::app()->clientScript->registerScript ('emailInboxJS', '
;(function () {
    x2.emailInbox = new x2.EmailInbox ({
        notConfigured: '.($notConfigured ? 'true' : 'false').',
        noneSelectedText: "'.Yii::t('emailInboxes',
                'No messages are selected!').'",
        deleteConfirmTxt: "'.Yii::t('emailInboxes',
                'Are you sure you want to delete the selected messages?').'",
        pollTimeout: '.$pollTimeout.',
        emailFolder: "'.($mailbox ? $mailbox->getCurrentFolder() : null).'",
        loadMessagesOnPageLoad: '.($loadMessagesOnPageLoad ? 'true' : 'false').'
    });
}) ();

', CClientScript::POS_READY);

?>
</div>
<div id='reply-form' style='display: none;'>
<?php
    if (isset($mailbox)) {
        $this->widget('EmailInboxesEmailForm', array(
            'mailbox' => $mailbox,
            'attributes' => array(
                'credId' => $mailbox->credentialId,
            ),
            'hideFromField' => true,
            'disableTemplates' => true,
            'startHidden' => true,
        ));
    }
?>
</div>
