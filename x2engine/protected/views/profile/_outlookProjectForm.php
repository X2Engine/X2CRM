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
    Yii::app()->theme->baseUrl.'/css/views/profile/googleProjectForm.css');

echo CHtml::openTag ('div', array ('id' => 'outlook-project-form'));

$admin = Yii::app()->settings;
echo CHtml::activeCheckbox ($admin, 'outlookIntegration');
echo CHtml::activeLabel ($admin, 'outlookIntegration', array('style'=>'display:inline;'));
echo '<br>';
echo '<br>';
?>
<div class='integration-description'>
<?php
echo Yii::t('app', 'Activating Outlook Integration enables the following features:');
echo X2Html::unorderedList (array (
    CHtml::encode (Yii::t('app', 'Microsoft sign in')),
    CHtml::encode (Yii::t('app', 'Outlook Calendar sync'))
));

?>
</div>
<?php

echo CHtml::tag ('h3', array (), Yii::t('app', 'Configuring Outlook Integration'));
?>
<hr>
<?php
echo X2Html::orderedList (array (
    Yii::t('app', 'Visit {link} and create or select a Outlook project.', array (
        '{link}' => 
            '<a href="https://apps.dev.microsoft.com">'.
                'the Developer Microsoft Link</a>'
    )),
    CHtml::encode (
        Yii::t('app', 'To configure Outlook integration for Calendar sync, Microsoft login')).
        X2Html::orderedList (array (
            CHtml::encode (
                Yii::t('app', '"App an App" after logging into your Microsoft Account.')
            ),
            CHtml::encode (
                Yii::t('app', 'Copy the "Application ID" and input the ID in the "OutlookID" field')
            ),
            CHtml::encode (
                Yii::t('app', 'Under the "Application Secret" Generate "New Password"')
            ),
            CHtml::encode (
                Yii::t('app', 'Save the New Generated Password and input in the field "Outlook Secret"')
            ),
            CHtml::encode (
                Yii::t('app', 'Create an OAuth 2.0 client ID.')
            ),
            CHtml::encode (
                Yii::t('app', 'When asked for "Authorized Redirect URIs," input the following '.
                    'urls:')).
                CHtml::tag (
                    'textarea', array ('readonly' => 'readonly', 'style' => 'display: block', 'class'=>'authorized-js-origins'),
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . 
                        Yii::app()->controller->createUrl('/admin/outlooksync')."\n"
                ),
            CHtml::encode (
                Yii::t('app', 'When asked for "Logout URL," input the '.
                    'following urls:')).
                CHtml::tag (
                    'textarea', array ('readonly' => 'readonly', 'style' => 'display: block'),
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
                ),
        ), array ('style' => 'list-style-type: lower-latin;')),
    CHtml::encode (
        Yii::t('app', 'Select Correct Microsoft Graph Permissions.')).
            X2Html::orderedList (array (
            CHtml::encode (
                Yii::t('app', 'Delegated Permissions => (1. Calendars.ReadWrite) and (2. User.ReadWrite).')
            ),
            CHtml::encode (
                Yii::t('app', 'Application Permissions => (1. Calendars.ReadWrite(AdminOnly)) and (2. Mail.ReadWrite(AdminOnly)) and (3. Mail.Send(AdminnOnly)).')
            ),
        ), array ('style' => 'list-style-type: lower-latin;')),
), array ('class' => 'config-instructions'));

echo X2Html::fragmentTarget ('oauth-2.0');
echo CHtml::tag ('h3', array (
    'class' => 'oauth-header'
), Yii::t('app', 'OAuth 2.0 Credentials'));
echo X2Html::hint2 (
    Yii::t('app', 'Needed for Outlook Calendar sync, Microsoft login.'));
echo '<hr />';

//clientId -> outlookId
echo CHtml::activeLabel($model, 'outlookId');
$model->renderProtectedInput ('outlookId');
echo CHtml::activeLabel($model, 'outlookSecret');
$model->renderProtectedInput ('outlookSecret');

echo CHtml::errorSummary($model);
echo '<br>';
echo '<br>';

echo CHtml::closeTag ('div', array ('id' => 'outlook-project-form'));
?>
