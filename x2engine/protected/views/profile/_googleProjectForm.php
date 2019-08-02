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

echo CHtml::openTag ('div', array ('id' => 'google-project-form'));

$admin = Yii::app()->settings;
echo CHtml::activeCheckbox ($admin, 'googleIntegration');
echo CHtml::activeLabel ($admin, 'googleIntegration', array('style'=>'display:inline;'));
echo '<br>';
echo '<br>';
?>
<div class='integration-description'>
<?php
echo Yii::t('app', 'Activating Google Integration enables the following features:');
echo X2Html::unorderedList (array (
    CHtml::encode (Yii::t('app', 'Google sign in')),
    CHtml::encode (Yii::t('app', 'Google Calendar sync')),
    CHtml::encode (Yii::t('app', 'Google Drive access')),
    CHtml::encode (Yii::t('app', 'Google Maps widget and Contact Heatmap')),
    CHtml::encode (Yii::t('app', 'Google+ Profile widget and profile search')),
));

?>
</div>
<?php

echo CHtml::tag ('h3', array (), Yii::t('app', 'Configuring Google Integration'));
?>
<hr>
<?php
echo X2Html::orderedList (array (
    Yii::t('app', 'Visit {link} and create or select a Google project.', array (
        '{link}' => 
            '<a href="https://console.developers.google.com/">'.
                'https://console.developers.google.com</a>'
    )),
    CHtml::encode (
        Yii::t('app', 'To configure Google integration for Calendar sync, Google login, and '.
            'Google Drive access:')).
        X2Html::orderedList (array (
            CHtml::encode (
                Yii::t('app', 'From the "APIs & auth" section in the left sidebar, select "APIs."')
            ),
            CHtml::encode (
                Yii::t('app', 'Search for and enable the following APIs:')
            ).
            X2Html::orderedList(array(
                'CalDav API',
                'Google Calendar API',
                'Google Drive API',
                )
            ),
            CHtml::encode (
                Yii::t('app', 'From the "APIs & auth" section in the left sidebar, select '.
                    '"Credentials."')
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
                        Yii::app()->controller->createUrl(
                            '/calendar/calendar/syncActionsToGoogleCalendar')."\n".
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . 
                        Yii::app()->controller->createUrl('/site/googleLogin')."\n".
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . 
                        Yii::app()->controller->createUrl('/site/upload')
                ),
            CHtml::encode (
                Yii::t('app', 'When asked for "Authorized JavaScript Origins," input the '.
                    'following urls:')).
                CHtml::tag (
                    'textarea', array ('readonly' => 'readonly', 'style' => 'display: block'),
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
                ),
            Yii::t('app', 'Copy the Client ID and Client Secret into OAuth 2.0 Credentials '.
                'section {below}.', array (
                '{below}' => CHtml::link (Yii::t('app', 'below'), '#oauth-2.0')
            )),
        ), array ('style' => 'list-style-type: lower-latin;')),
     
    Yii::t('app', 'To configure Google integration for the Google+ and Google Maps widgets:').
        X2Html::orderedList (array (
            CHtml::encode (
                Yii::t('app', 'From the "APIs & auth" section in the left sidebar, select "APIs."')
            ),
            CHtml::encode (
                Yii::t('app', 'Search for and enable following APIs.')
            ).X2Html::orderedList(array(
                'Google Maps Geocoding API', 
                'Google Maps Directions API',
                'Google Static Maps API',
                'Google Maps JavaScript API',
                'Google+ API',
            )),
            CHtml::encode (
                Yii::t('app', 'From the "APIs & auth" section in the left sidebar, select '.
                    '"Credentials."')
            ),
            CHtml::encode (
                Yii::t('app', 'Create an API key.')
            ),
            CHtml::encode (
                Yii::t('app', 'When asked for key type, select "Server key."')
            ),
            Yii::t('app', 'Copy the API key into the Google+ and Google Maps Integration section {below}.', array (
                '{below}' => CHtml::link (Yii::t('app', 'below'), '#api-key')
            )),
        ), array ('style' => 'list-style-type: lower-latin;')),
     
), array ('class' => 'config-instructions'));

echo X2Html::fragmentTarget ('oauth-2.0');
echo CHtml::tag ('h3', array (
    'class' => 'oauth-header'
), Yii::t('app', 'OAuth 2.0 Credentials'));
echo X2Html::hint2 (
    Yii::t('app', 'Needed for Google Calendar sync, Google login, and Google Drive access.'));
echo '<hr />';

echo CHtml::activeLabel($model, 'clientId');
$model->renderProtectedInput ('clientId');
echo CHtml::activeLabel($model, 'clientSecret');
$model->renderProtectedInput ('clientSecret');

 
echo X2Html::fragmentTarget ('api-key');
echo CHtml::tag ('h3', array (), Yii::t('app', 'Google+ and Google APIs Integration'));

echo CHtml::activeLabel($model, 'projectId');
$model->renderProtectedInput ('projectId');

echo CHtml::activeLabel($model, 'apiKey');
$model->renderProtectedInput ('apiKey');

/*
 * For taking in Google JSON server key file
 * 
 * echo CHtml::activeLabel($model, 'Service Account json key file');
 * echo CHtml::fileField('keyFile', '', array('id'=>'keyFile'));
 * $model->renderProtectedInputHidden ('serviceAccountKeyFileContents');
 * echo '<br>';
 * echo Yii::t('app','Allowed filetypes: .json'); 
 * 
 */

echo CHtml::tag ('h3', array (), Yii::t('app', 'Google Analytics Integration'));
echo '<hr />';
echo CHtml::activeLabel($admin, 'gaTracking_public');
echo CHtml::activeTextField ($admin, 'gaTracking_public');
echo CHtml::activeLabel($admin, 'gaTracking_internal');
echo CHtml::activeTextField ($admin, 'gaTracking_internal');
echo '<br>';
echo Yii::t('admin', 'Enter property IDs to enable Google Analytics tracking. The public ID will be used on publicly-accessible web lead and service case forms. The internal one will be used within X2CRM, for tracking the activity of authenticated users.');


echo CHtml::errorSummary($model);
echo '<br>';
echo '<br>';

echo CHtml::closeTag ('div', array ('id' => 'google-project-form'));
?>
