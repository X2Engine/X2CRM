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
    CHtml::encode (Yii::t('app', 'Outlook Calendar sync'))
));

?>
</div>
<?php

echo CHtml::tag ('h3', array (), Yii::t('app', 'SETUP Outlook Integration'));
?>
<hr>
<?php
echo X2Html::orderedList (array (
    Yii::t('app', 'Visit {here} and sign in.', array (
        '{here}' =>
            '<a href="https://portal.azure.com">portal.azure.com</a>'
    )),
    Yii::t('app', 'Under Azure services, click on <b>"App Registration"</b>'),
    Yii::t('app', 'Create a <b>new registration</b>, or select an <b>existing one</b>.').
        X2Html::orderedList(array (
            Yii::t('app', '<b><font color="red">If new registration</font></b>, enter a name and leave the other choices default for now.')
        )),
        Yii::t('app', 'On the <b>left panel</b>, navigate to <b>"Authentication"</b>.').
        X2Html::orderedList (array (
            CHtml::encode (
                Yii::t('app', 'Set Redirect URL to the url:')).
                CHtml::tag (
                    'textarea', array ('readonly' => 'readonly', 'style' => 'display: block', 'class'=>'authorized-js-origins'),
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
                        Yii::app()->controller->createUrl('/calendar/outlooksync')."\n"
                ),
            CHtml::encode (
                Yii::t('app', 'Set Logout URL to the url:')).
                CHtml::tag (
                    'textarea', array ('readonly' => 'readonly', 'style' => 'display: block'),
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
                ),
        ), array ('style' => 'list-style-type: lower-latin;')),
        Yii::t('app', 'On the <b>left panel</b> nagivate to <b>"API permissions"</b>.').
        X2Html::orderedList (array (
                Yii::t('app', 'Delegated Permissions: <font color="blue">Calendars.ReadWrite</font>, <font color="blue">User.ReadWrite</font>.'),
                Yii::t('app', 'Application Permissions: <font color="blue">Calendars.ReadWrite</font>, <font color="blue">Mail.ReadWrite</font>, <font color="blue">Mail.Send</font>.'),
        ), array ('style' => 'list-style-type: lower-latin;')),
), array ('class' => 'config-instructions'));
echo '<hr>';

/**
 * Link to X2CRM page.
 */
echo CHtml::tag ('h3', array (), Yii::t('app', 'Link to X2CRM'));
echo '<hr>';
echo X2Html::orderedList (array (
    Yii::t('app', 'Navigate to the <b>overview</b>.').
    X2Html::orderedList(array (
        Yii::t('app', 'Save the <b><font color="red">Application ID</font></b> in the <b><font color="red">Outlook ID</font></b> field below.')
    ), array ('style' => 'list-style-type: lower-latin;')),
    Yii::t('app', 'On the <b>left panel</b>, nagivate to <b>"Certificates and secrets"</b>.').
    X2Html::orderedList(array (
        Yii::t('app', 'Generate a new client secret and save it in the <b><font color="red">Outlook Secret</font></b> field below.')
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
