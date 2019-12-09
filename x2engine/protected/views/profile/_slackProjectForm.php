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

/**
 * @edition: ent
 */

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/views/profile/googleProjectForm.css');

echo CHtml::openTag ('div', array ('id' => 'slack-project-form'));

$admin = Yii::app()->settings;
echo CHtml::activeCheckbox ($admin, 'slackIntegration');
echo CHtml::activeLabel ($admin, 'slackIntegration', array('style'=>'display:inline;'));
echo '<br>';
echo '<br>';
?>
<div class='integration-description'>
<?php
echo Yii::t('app', 'Activating Slack Integration enables the following features:');
echo X2Html::unorderedList (array (
    CHtml::encode (Yii::t('app', 'Slack sign in')),
    CHtml::encode (Yii::t('app', 'Slack Workflow Action')),
));

?>
</div>
<?php

echo CHtml::tag ('h3', array (), Yii::t('app', 'Configuring Slack Integration'));
?>
<hr>
<?php
echo X2Html::orderedList (array (
    CHtml::encode (
        Yii::t('app', 'Slack App Creation')).
        X2Html::orderedList (array (
            Yii::t('app', 'Navigate {link} to create an app with slack', array (
                '{link}' => '<a href="https://api.slack.com/apps"> Slack App Link</a>'
            )),
            CHtml::encode (
                Yii::t('app', 'Login with your slack creadentials if prompted.')
            ),
            Yii::t('app', 'Click <b> Create New App </b>'),
            CHtml::encode (
                Yii::t('app', 'Enter a name for your app and select your slack workspace.')
            ),
        ), array ('style' => 'list-style-type: lower-latin;')),
     CHtml::encode (
        Yii::t('app', 'Integration Setup')).
        X2Html::orderedList (array (
                Yii::t('app', 'Find the selection labeled <b> App Credentials </b>.'),
                Yii::t('app', 'Copy over the <b> Client ID </b> and <b> Client Secret </b> to this page.'),
        ), array ('style' => 'list-style-type: lower-latin;')),
     CHtml::encode (
        Yii::t('app', 'Redirect Setup')).
        X2Html::orderedList (array (
            Yii::t('app', 'Find the section labeled <b> Building Apps for Slack </b>.'),
            Yii::t('app', 'Under the <b> Add features and functionality </b> dropdown, click on <b> Permissions </b>.'),
            Yii::t('app', 'App and save the following URL as a <b> Redirect Url </b>:').
            CHtml::tag (
                  'textarea', array ('readonly' => 'readonly', 'style' => 'display: block', 'class'=>'authorized-js-origins'),
                  (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
                      Yii::app()->controller->createUrl('/profile/slackIntegration')
            ),
        ), array ('style' => 'list-style-type: lower-latin;')),
     CHtml::encode (
        Yii::t('app', 'Final Step')).
        X2Html::orderedList (array (
                Yii::t('app', 'Return to this page.'),
                Yii::t('app', 'Check the box near <b> Activate Slack Integration </b> and click save.'),
                Yii::t('app', 'Click on <b> Link to Slack Integration </b> to enable and save again.')
        ), array ('style' => 'list-style-type: lower-latin;')),
), array ('class' => 'config-instructions'));

echo X2Html::fragmentTarget ('oauth-2.0');
echo CHtml::tag ('h3', array (
    'class' => 'oauth-header'
), Yii::t('app', 'OAuth 2.0 Credentials'));
echo X2Html::hint2 (
    Yii::t('app', 'Needed for Slack Workflow Action, and Slack Login.'));
echo '<hr />';

echo CHtml::activeLabel($model, 'clientId');
$model->renderProtectedInput ('clientId');
echo CHtml::activeLabel($model, 'clientSecret');
$model->renderProtectedInput ('clientSecret');

echo CHtml::errorSummary($model);
echo '<br>';
echo '<br>';

$enable = Yii::app()->settings->slackIntegration; // Check if integration is enabled in the first place
//get credentials id and secret
$admin = Admin::model()->findByPk (1);
$id = $admin->slackCredentialsId;
$credential = Credentials::model()->findByAttributes(array('id'=>$id));
if(isset($credential) && $enable){
    $auth_credential = $credential->auth;
    $client_id = $auth_credential->clientId;
    $client_secret = $auth_credential->clientSecret;
    if($client_id != null && $client_secret != null && $enable){
        echo CHtml::link(Yii::t('calendar', "Link to Slack Integration"), "https://slack.com/oauth/authorize?client_id=".$client_id."&scope=channels%3Aread+chat%3Awrite%3Abot+chat%3Awrite%3Auser",array('class'=>'x2-button'));
    }
}
echo CHtml::closeTag ('div', array ('id' => 'slack-project-form'));
?>
