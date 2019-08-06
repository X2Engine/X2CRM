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




echo Tours::tips (array(
    array (
        "content" => "<h3>".Yii::t('admin',"Welcome to the Advanced Security Settings page!")."</h3> ".Yii::t('admin',"This page provides configuration options for various security features, including scanning uploaded Media for viruses and restricting IP access to your system."),
        'type' => 'flash',
    ),
    array (
        'content' => Yii::t('admin','You can check this option to scan uploaded Media for viruses using ClamAV.'),
        'target' => '#Admin_scanUploads',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('admin','When X2 Hub Services or a Twilio account are configured, you can enable two-factor authentication for user logins.'),
        'target' => '#Admin_twoFactorCredentialsId',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('admin','Here you can configure either a blacklist or whitelist of IP addresses denied or allowed login to the CRM.'),
        'target' => '#aclMethodDropdown',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('admin','You can configure how many failed logins are allowed before the user is presented a CAPTCHA.'),
        'target' => '#failedLoginsBeforeCaptchaSlider',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('admin','You can then ban the user\'s IP address after additional failed logins.'),
        'target' => '#maxFailedLoginsSlider',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('admin','These options allow you to configure the password complexity requirements for your users, enforcing passwords according to your security requirements.'),
        'target' => '#requireSpecial',
        'highlight' => true
    ),
));

?>
