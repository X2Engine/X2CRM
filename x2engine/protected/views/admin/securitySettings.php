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






Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/admin/securitySettings.css');
Tours::loadTips('admin.securitySettings');
?>

<div class="page-title"><h2><?php echo Yii::t('admin', 'Security Settings'); ?></h2></div>
<div id='security-settings-form' class="form">
<div class='admin-form-container'>
    <?php
    X2Html::getFlashes();
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'settings-form',
        'enableAjaxValidation' => false,
    ));

    // Anti virus scanning settings
    echo '<h3>'.Yii::t('admin', 'Anti Virus Scanning').'</h3>';
    echo Yii::t ('admin', 'Configure whether to scan uploaded Media. Note: this requires that '.
                          'the clamav utility is installed on the system.<br /><br />');
    echo $form->labelEx ($model, 'scanUploads', array('style'=>'display:inline-block;margin-right:5px;'));
    echo $form->checkbox ($model, 'scanUploads').'<br />';

    // Two factor auth settings
    echo '<h3>'.Yii::t('admin', 'Two Factor Authentication').'</h3>';
    echo Yii::t ('admin', 'Configure whether to enable two factor authentication for user logins. '.
                          'Please select a credential to use for sending.<br /><br />');
    echo $form->labelEx ($model, 'twoFactorCredentialsId', array('style'=>'display:inline-block;margin-right:5px;'));
    echo $form->dropDownList ($model, 'twoFactorCredentialsId', $twoFAOptions).'<br />';

    // IP Whitelist/Blacklist settings
    $hint = Yii::t('admin', 'You may enter entire address blocks here, either using '.
        'a * to signify the entire subnet, such as "192.168.1.*", or using CIDR notation '.
        ' to specify a subnet using a prefix like "192.168.1.0/24". Note that '.
        'entries using a * will be converted to CIDR notation.');
    echo '<h3>'.Yii::t('admin', 'IP Access Control').'</h3>';
    echo Yii::t ('admin', 'Configure the method of IP address access control used by X2. '.
                          'A whitelist will restrict logins to only those addresses that '.
                          'are present in the whitelist, while a blacklist will block '.
                          'the listed addresses from connecting.').'<br /><br />';
    echo $form->labelEx ($model, 'accessControlMethod');
    echo $form->dropDownList ($model, 'accessControlMethod', array(
        'whitelist' => Yii::t('admin', 'Whitelist'),
        'blacklist' => Yii::t('admin', 'Blacklist'),
    ), array(
        'id' => 'aclMethodDropdown',
    )).'<br /><br />';
    echo '<div class="row" id="x2-whitelist">';
    echo $form->labelEx ($model, 'ipWhitelist', array(
        'style' => 'margin-right:5px;display: inline-block;'
    ));
    echo X2Html::hint($hint, false).'<br />';
    echo Yii::t ('admin', 'To grant login permission to a select IP address, enter '.
                          'the IP address here, one address per line. All other '.
                          'login attempts will be forbidden.');
    echo $form->textArea ($model, 'ipWhitelist');
    echo '</div>';

    echo '<div class="row" id="x2-blacklist">';
    echo $form->labelEx ($model, 'ipBlacklist', array(
        'style' => 'margin-right:5px;display: inline-block;'
    ));
    echo X2Html::hint($hint, false).'<br />';
    echo Yii::t ('admin', 
        'To ban an IP address from logging in to X2, enter '.
      'the IP address here, one address per line.');

    echo $form->textArea ($model, 'ipBlacklist');
    echo '</div>';

    echo '<h3>'.Yii::t('admin', 'Failed Login Penalties').'</h3>';
    echo '<div class="row">';
    echo Yii::t ('admin', 'Configure the timeout in between failed login attempts, and the '.
                          'number of failed login attempts before the IP address is banned.'.
                          ' To view a record of failed and successful login attempts, please '.
                          'visit the {link} page.',
                          array(
                              '{link}' => CHtml::link(Yii::t('admin', 'User History'), array('admin/userHistory'))
                          )
    ).'<br /><br />';

    // Login timeout controls
    echo $form->labelEx ($model, 'loginTimeout');
    echo Yii::t('admin', 'Number of minutes user logins will be locked after too many failed '.
                         'login attempts');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->loginTimeout,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 5,
            'max' => 1440,
            'step' => 5,
            'change' => "js:function(event,ui) {
                            $('#loginTimeout').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#loginTimeout').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'loginTimeoutSlider'
        ),
    ));
    echo $form->textField ($model, 'loginTimeout', array(
        'id' => 'loginTimeout'
    ));
    echo $form->error ($model, 'loginTimeout').'<br /><br />';

    // Failed logins before Captcha controls
    echo $form->labelEx ($model, 'failedLoginsBeforeCaptcha');
    echo Yii::t('admin', 'Configure the maximum number of failed logins before '.
                         'the user is presented with a CAPTCHA');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->failedLoginsBeforeCaptcha,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 1,
            'max' => 100,
            'step' => 1,
            'change' => "js:function(event,ui) {
                            $('#failedLoginsBeforeCaptcha').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#failedLoginsBeforeCaptcha').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'failedLoginsBeforeCaptchaSlider'
        ),
    ));
    echo $form->textField ($model, 'failedLoginsBeforeCaptcha', array(
        'id' => 'failedLoginsBeforeCaptcha'
    ));
    echo $form->error ($model, 'failedLoginsBeforeCaptcha').'<br /><br />';

    // Maximum failed logins controls
    echo $form->labelEx ($model, 'maxFailedLogins');
    echo Yii::t('admin', 'Configure the maximum number of failed logins before the '.
                         'user\'s IP address is locked out. Note that this must be '.
                         'higher than the number of failed logins');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->maxFailedLogins,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 1,
            'max' => 100,
            'step' => 1,
            'change' => "js:function(event,ui) {
                            $('#maxFailedLogins').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#maxFailedLogins').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'maxFailedLoginsSlider'
        ),
    ));
    echo $form->textField ($model, 'maxFailedLogins', array(
        'id' => 'maxFailedLogins'
    ));
    echo $form->error ($model, 'maxFailedLogins').'<br /><br />';


    // Maximum successful logins to keep in history
    echo $form->labelEx ($model, 'maxLoginHistory');
    echo Yii::t('admin', 'Configure the maximum number of successful logins to '.
                         'store in the login history');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->maxLoginHistory,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 10,
            'max' => 10000,
            'step' => 5,
            'change' => "js:function(event,ui) {
                            $('#maxLoginHistory').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#maxLoginHistory').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'maxLoginHistorySlider'
        ),
    ));
    echo $form->textField ($model, 'maxLoginHistory', array(
        'id' => 'maxLoginHistory'
    ));
    echo $form->error ($model, 'maxLoginHistory').'<br /><br />';
    echo '</div>';

    // Maximum failed logins to keep in history
    echo $form->labelEx ($model, 'maxFailedLoginHistory');
    echo Yii::t('admin', 'Configure the maximum number of failed logins to '.
                         'store in the login history');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->maxFailedLoginHistory,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 10,
            'max' => 10000,
            'step' => 5,
            'change' => "js:function(event,ui) {
                            $('#maxFailedLoginHistory').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#maxFailedLoginHistory').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'maxFailedLoginHistorySlider'
        ),
    ));
    echo $form->textField ($model, 'maxFailedLoginHistory', array(
        'id' => 'maxFailedLoginHistory'
    ));
    echo $form->error ($model, 'maxFailedLoginHistory').'<br /><br />';
    echo '</div>';

    // User password complexity settings
    echo '<h3>'.Yii::t('admin', 'User Password Requirements').'</h3>';
    echo Yii::t ('admin', 'Configure the required password complexity for users.').'<br /><br />';

    echo '<div id="password-settings-form">';
    echo CHtml::label (Yii::t('admin', 'Minimum Length'), 'minLength');
    echo CHtml::numberField ('minLength', $model->passwordRequirements['minLength']).'<br />';

    $hint = Yii::t('admin', 'Here you can specify the total number of required character classes, '.
        'such as upper case, lower case, digits, etc.');
    echo CHtml::label (Yii::t('admin', 'Types of Characters'), 'requireCharClasses');
    echo CHtml::numberField ('requireCharClasses', $model->passwordRequirements['requireCharClasses']);
    echo X2Html::hint($hint, false).'<br />';

    echo CHtml::label (Yii::t('admin', 'Require Mixed Case'), 'requireMixedCase');
    echo CHtml::checkbox ('requireMixedCase', $model->passwordRequirements['requireMixedCase']).'<br />';

    echo CHtml::label (Yii::t('admin', 'Require Numeric'), 'requireNumeric');
    echo CHtml::checkbox ('requireNumeric', $model->passwordRequirements['requireNumeric']).'<br />';

    echo CHtml::label (Yii::t('admin', 'Require Special'), 'requireSpecial');
    echo CHtml::checkbox ('requireSpecial', $model->passwordRequirements['requireSpecial']).'<br />';
    echo '</div>';

    echo '</div>';

    echo '<br /><div class="row">';
    echo CHtml::submitButton(
        Yii::t('app', 'Save'), array(
            'class' => 'x2-button', 'id' => 'save-button'
        )) . "\n";
    echo '</div><br />';

    $this->endWidget();

    // Choose which UI section to hide on page load
    $hideControl = ($model->accessControlMethod === 'blacklist' ? 'whitelist' : 'blacklist');

    Yii::app()->clientScript->registerScript ('firewallSettingsJs', '
        // Hide unnecessary UI controls
        $("#x2-'.$hideControl.'").hide();

        $("#aclMethodDropdown").change (function() {
            if ($(this).val() === "blacklist") {
                $("#x2-whitelist").slideUp();
                $("#x2-blacklist").slideDown();
            } else {
                $("#x2-blacklist").slideUp();
                $("#x2-whitelist").slideDown();
            }
        });

        // Set up sliders to sync with text fields
        $("#loginTimeout").change (function () {
            $("#loginTimeoutSlider").slider("value", $(this).val());
        });
        $("#failedLoginsBeforeCaptcha").change (function () {
            $("#failedLoginsBeforeCaptchaSlider").slider("value", $(this).val());
        });
        $("#maxFailedLogins").change (function () {
            $("#maxFailedLoginsSlider").slider("value", $(this).val());
        });
        $("#maxLoginHistory").change (function () {
            $("#maxLoginHistorySlider").slider("value", $(this).val());
        });
        $("#maxFailedLoginHistory").change (function () {
            $("#maxFailedLoginHistorySlider").slider("value", $(this).val());
        });
    ', CClientScript::POS_READY);
?>
</div>
</div>
