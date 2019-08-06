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





Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->module->assetsUrl.'/js/SettingsController.js');

$this->onPageLoad ("
    x2.main.controllers['$this->pageId'] = new x2.SettingsController (".CJSON::encode (array (
        'translations' => array (
            'changeUrlMessage' => 
                Yii::t('app', 'Performing this action will log you out of X2CRM.'),
            'changeUrlTitle' => 'Change web address?',
            'changeUrlButtonConfirm' => 'Okay',
            'changeUrlButtonCancel' => 'Back',
        ),
    )).");
");

?>

<div class='detail-view'>
<?php
    $form = $this->beginWidget ('MobileActiveForm');
?>
    <div class="field-container">
        <div class="field-label"><?php 
            echo CHtml::encode ($profile->getField ('language')->attributeLabel); 
        ?></div>
        <div class="field-value">
        <?php
        echo X2Model::renderModelInput ($profile, $profile->getField ('language'), array (
            'class' => 'profile-language',
        ));
        ?>
        </div>
    </div>
    <?php
     
    if (Yii::app()->params->isPhoneGap) {
    ?>
    <div class="field-container">
        <div class="field-label"><?php 
            echo CHtml::encode (Yii::t('mobile', 'Web Address')); 
        ?></div>
        <div class='field-value change-url-button'><?php 
            echo CHtml::encode (Yii::app()->absoluteBaseUrl); 
        ?></div>
    </div>
<?php
    }

    $this->endWidget ();
?>
</div>
