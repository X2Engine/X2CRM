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




Yii::app()->clientScript->registerPackage ('X2CSS');
Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/profileSettings.js', CClientScript::POS_END);

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/profileSettings.css');

Tours::tips (array(
    array(
        'content' => Yii::t('app','You can disable tips like this by unchecking this box.'),
        'target' =>  '#Profile_showTours'
    )
));

$preferences = $model->theme;
$miscLayoutSettings = $model->miscLayoutSettings;

$passVariablesToClientScript = "
    x2.profileSettings.checkerImagePath = '".
        Yii::app()->theme->getBaseUrl()."/images/checkers.gif';
    x2.profileSettings.createThemeHint = '".
        Yii::t('profile', 'Save your current theme settings as a predefined theme.')."';
    x2.profileSettings.saveThemeHint = '".
        Yii::t('profile', 'Update the settings of the currently selected predefined theme.')."';
    x2.profileSettings.normalizedUnhideTagUrl = '".
        CHtml::normalizeUrl(array("/profile/unhideTag"))."';
    x2.profileSettings.translations = {
        themeImportDialogTitle: '".Yii::t('profile', 'Import a Theme')."',
        close: '".Yii::t('app', 'close')."',
        enableTwoFAMessage: '".Yii::t('profile', 'Are you sure you want to enable two factor authentication?')."',
        disableTwoFAMessage: '".Yii::t('profile', 'Are you sure you want to disable two factor authentication?')."',
        twoFACodeSentMessage: '".Yii::t('profile', 'A verification code has been sent to your phone number ').$model->cellPhone."',
        enableTwoFASuccessMessage: '".Yii::t('profile', 'Two factor authentication had been enabled')."',
        disableTwoFASuccessMessage: '".Yii::t('profile', 'Two factor authentication had been disabled')."',
    };
    x2.profileSettings.uploadedByAttrs = {};
    x2.profileSettings.beginTwoFAUrl = '".
        CHtml::normalizeUrl(array("/profile/beginTwoFactorActivation"))."';
    x2.profileSettings.completeTwoFAUrl = '".
        CHtml::normalizeUrl(array("/profile/completeTwoFactorActivation"))."';
    x2.profileSettings.disableTwoFAUrl = '".
        CHtml::normalizeUrl(array("/profile/disableTwoFactor"))."';
";

// pass array of predefined theme uploadedBy attributes to client
foreach($myThemes->data as $theme){
    $passVariablesToClientScript .= "x2.profileSettings.uploadedByAttrs['".
            $theme->id."'] = '".$theme->uploadedBy."';";
}

Yii::app()->clientScript->registerScript(
    'passVariablesToClientScript', $passVariablesToClientScript, CClientScript::POS_END);

// If the user was redirected from /site/upload and the "useId" parameter is 
// available, set the background to that so they get instant feedback
if(isset($_GET['bgId'])) {
    $media = Media::model()->findByPk($_GET['bgId']);
    if($media instanceof Media) {
        Yii::app()->clientScript->registerScript(
            'setBackgroundToUploaded',
            '$("select#backgroundImg").val('
                .$media->id.').trigger("change");'
            ,CClientScript::POS_READY);
    }
}

?>

<?php
$form = $this->beginWidget('X2ActiveForm', array(
    'id' => 'settings-form',
    'enableAjaxValidation' => false,
        ));
?>
<?php echo $form->errorSummary($model); ?>

<div id="profile-settings" class="form">
    <?php
    echo X2Html::getFlashes ();
    ?>
    <div class="row">
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disablePhoneLinks', array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disablePhoneLinks', array('style' => 'display:inline;'));
            echo X2Html::hint2 (
                Yii::t('app', 'Prevent phone number fields from being formatted as links.'));
            ?>
        </div>
    </div>
    <?php if(Yii::app()->contEd('pro')) { ?>
    <div class="row"> 
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disableTimeInTitle', array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disableTimeInTitle', array('style' => 'display:inline;'));
            ?>
        </div>
    </div>
    <?php } ?>
     <div class="row">
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disableNotifPopup', array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disableNotifPopup', array('style' => 'display:inline;'));
            ?>
        </div>
    </div>
    <div class="row" style="margin-bottom:10px;">
        <div class="cell">
            <?php
            if (Yii::app()->settings->twoFactorCredentialsId && !empty($model->cellPhone)) {
                echo $form->checkBox(
                        $model, 'enableTwoFactor', array('onchange' => 'js:x2.profileSettings.initiateTwoFAActivation(this);'));
                echo $form->labelEx(
                        $model, 'enableTwoFactor', array('style' => 'display:inline;'));
                echo X2Html::hint2 (
                    Yii::t('app', 'Enable two factor authentication to require a verification code on login'));
                echo CHtml::textField('code', '', array('class' => 'twofa-activation', 'style' => 'display: none', 'placeholder' => Yii::t('profile', 'Verification Code'), 'autocomplete' => 'off'));
                echo CHtml::button(Yii::t('profile', 'Activate'), array('class' => 'twofa-activation', 'style' => 'display: none'));
            } else {
                // Two factor auth is not yet configured
                if (empty($model->cellPhone))
                    $twoFactorTip = Yii::t('profile', 'Please add your cell phone number to enable two factor authentication');
                else
                    $twoFactorTip = Yii::t('profile', 'Please configure credentials in the security settings page to enable two factor authentication');
                echo $form->checkBox(
                        $model, 'enableTwoFactor', array('disabled' => 'disabled','style' => 'display:inline;opacity:0.5;'));
                echo $form->labelEx(
                        $model, 'enableTwoFactor', array('title' => $twoFactorTip, 'style' => 'display:inline;opacity:0.5;'));
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'startPage'); ?>
            <?php
            echo $form->dropDownList(
                $model, 'startPage', $menuItems,
                array('onchange' => 'js:x2.profileSettings.highlightSave();', 'style' => 'min-width:140px;'));
            ?>
        </div>
        <div class="cell">
            <?php echo $form->labelEx($model, 'resultsPerPage'); ?>
            <?php
            echo $form->dropDownList(
                    $model, 'resultsPerPage', Profile::getPossibleResultsPerPage(),
                    array('onchange' => 'js:x2.profileSettings.highlightSave();', 'style' => 'width:100px'));
            ?>
        </div>

    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'language'); ?>
            <?php
            echo $form->dropDownList(
                    $model, 'language', $languages, array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
        </div>
        <div class="cell">
            <?php
            if(!isset($model->timeZone))
                $model->timeZone = "Europe/London";
            ?>
            <?php echo $form->labelEx($model, 'timeZone'); ?>
            <?php
            echo $form->dropDownList(
                $model, 'timeZone', $times,
                array(
                    'onchange' => 'js:x2.profileSettings.highlightSave();'
                ));
            ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
        <label for="loginSounds">
            <?php echo Yii::t('profile', 'Login Sound'); ?>
        </label>
        <select id="loginSounds" name="preferences[loginSound]" class='x2-select'>
            <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
            <?php foreach($myLoginSounds->data as $loginSound){ ?>
                <option value="<?php
            echo $loginSound->id.",".
            $loginSound->fileName.",".$loginSound->uploadedBy;
                ?>"
                        id="sound-<?php echo $loginSound->id; ?>"
                        <?php
                        if ($loginSound->id == $model->loginSound) {
                            echo "selected='selected'";
                        }
                        ?>>
                            <?php echo $loginSound->fileName; ?>
                </option>
            <?php } ?>
        </select>
        <?php
        echo X2Html::fa ('upload', array (
            'id' => 'upload-login-sound-button',
            'class' => 'icon-button-min',
            'title' => Yii::t('profile', 'Upload Login Sound')
        ));
        ?>
        </div>
        <div class="cell">
            <label for="notificationSounds">
                <?php echo Yii::t('profile', 'Notification Sound'); ?>
            </label>
            <select id="notificationSounds" name="preferences[notificationSound]"
                    class='x2-select'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach($myNotificationSounds->data as $notificationSound){ ?>
                    <option value="<?php
                        echo $notificationSound->id.",".$notificationSound->fileName.",".
                            $notificationSound->uploadedBy; ?>"
                     id="sound-<?php echo $notificationSound->id; ?>"
                     <?php
                     if($notificationSound->id == $model->notificationSound){
                         echo "selected='selected'";
                     }
                     ?>><?php echo $notificationSound->fileName; ?></option>
                <?php } ?>
            </select>
            <?php
            echo X2Html::fa ('upload', array (
                'id' => 'upload-notification-sound-button',
                'class' => 'icon-button-min',
                'title' => Yii::t('profile', 'Upload Notification Sound')
            ));
            ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'defaultCalendar'); ?>
            <?php
            echo $form->dropDownList(
                $model, 'defaultCalendar', X2CalendarPermissions::getEditableUserCalendarNames());
            ?>
        </div>
    </div>
    <div class="row">
        <div class="cell" style="margin: 8px 0px">
            <label style='display:inline-block'><?php echo Yii::t('app','Show Tips');?></label>
            <?php echo X2Html::activeCheckBox ($model, 'showTours', array(
                'style' =>'margin: 0px; vertical-align: middle',
                'type' =>'checkbox'
             )); ?> 
        </div>
    </div>
    <div class="row">
        <span title='<?php echo Yii::t('app','Tips around the app will only be seen once. To see them again press this button.') ?>' id='reset-tips-button' class='x2-hint x2-button' style='color: inherit'><?php echo Yii::t('app', 'Reset Tips') ?></span>
    </div>
</div>
<div id="theme-attributes" class='form preferences-section<?php 
    echo ($displayThemeEditor ? 
        ' no-theme-editor': '')/* x2plaend */; 
    ?>'>
    <div id="theme-attributes-title-bar" class="row prefs-title-bar">
        <h3 class="left"><?php echo Yii::t('app', 'Theme'); ?></h3>
        <div class="right minimize-arrows">
            <img class="prefs-expand-arrow" src="<?php 
                echo Yii::app()->theme->getBaseUrl()."/images/icons/Expand_Widget.png"; ?>" />
            <img class="hide prefs-collapse-arrow" src="<?php 
                echo Yii::app()->theme->getBaseUrl()."/images/icons/Collapse_Widget.png"; ?>" />
        </div>
    </div>
    <div id="theme-attributes-body" class="row prefs-body" <?php echo
        ($miscLayoutSettings['themeSectionExpanded'] == false ? 'style="display: none;"' : ''); ?>>
        <div class="row" id='theme-mgmt-buttons'>
            <input type="hidden" id="themeName" class="theme-attr x2-select" 
             name="preferences[themeName]" />

            <div class='x2-button-group'>
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-create-theme-button'>
                            <?php echo X2Html::fa("fa-copy") ?>
                            <?php echo Yii::t('profile', 'New'); ?>
                </button>
                <!-- <span id="prefs-create-theme-hint" class='prefs-hint'></span> -->
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-save-theme-button'>
                            <?php echo X2Html::fa("fa-save") ?>
                            <?php echo Yii::t('profile', 'Save'); ?>
                </button>
                <!-- <span id="prefs-save-theme-hint" class='hide prefs-hint'></span> -->
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-delete-theme-button'>
                            <?php echo X2Html::fa("fa-trash") ?>
                            <?php echo Yii::t('profile', 'Delete'); ?>
                </button>
                <?php  ?>
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-import-theme-button'>
                            <?php echo X2Html::fa("fa-download") ?>
                            <?php echo Yii::t('profile', 'Import'); ?>
                </button>
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-export-theme-button'>
                            <?php echo X2Html::fa("fa-upload") ?>
                            <?php echo Yii::t('profile', 'Export'); ?>
                </button>
            </div>
            <?php  ?>
            <div style="clear:both"></div>

            <?php $this->renderPartial('_themeSettings', array(
                'myThemes' => $myThemes,
                'selected' => $preferences['themeName'])
            ); ?>

        </div>

        <?php 
            ThemeGenerator::renderThemeColorSelector ('', '', '', array (
                'id' => 'theme-color-selector-template',
                'style' => 'display: none;',
            ), true);
            ThemeGenerator::renderSettings();
            ?>
            <div id='module-theme-override' 
             <?php echo $preferences['themeName'] === 'Default' || !$preferences['themeName'] ? 
                'style="display: none;"' : ''; ?>>
                <?php
                echo Modules::dropDownList ('', '', array (
                    'class' => 'x2-select',
                ));
                ?>
                <button id='add-module-override-button' class='x2-button x2-small-button'>
                <?php
                    echo CHtml::encode (Yii::t('app', 'Add Module Color')) 
                ?>
                </button>
            </div>
            <?php
        ?>
        <div class="row">
            <label for="backgroundTiling">
                <?php echo Yii::t('app', 'Background Tiling') ?>
            </label>
            <select id="backgroundTiling" name="preferences[backgroundTiling]"
             class='theme-attr x2-select'>
                        <?php
                        $tilingOptions = array(
                            'stretch', 'center', 'repeat', 'repeat-x', 'repeat-y');
                        foreach($tilingOptions as $option){
                            ?>
                    <option value="<?php echo $option; ?>"
                    <?php
                    echo $option == $preferences['backgroundTiling'] ?
                            "selected=\'selected\'" : '';
                    ?>>
                                <?php echo Yii::t('app', $option) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="row">
            <label for="backgroundImg">
                <?php echo Yii::t('profile', 'Background Image'); ?>
            </label>
            <select id="backgroundImg" name="preferences[backgroundImg]"
                    class='theme-attr x2-select'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach ($myBackgrounds->data as $background) { ?>
                    <option value="<?php
                        echo $background->id; ?>"
                        <?php
                        if($background->id == $preferences['backgroundImg']){
                            echo "selected='selected'";
                        } ?>>
                        <?php echo $background->fileName; ?>
                    </option>
                <?php } ?>
            </select>
            <?php
            echo X2Html::fa ('upload', array (
                'id' => 'upload-background-img-button',
                'class' => 'icon-button-min',
                'title' => Yii::t('profile', 'Upload Background Image')
            ));
            ?>
            <br>
            <?php
            echo CHtml::checkBox (
                'preferences[enableLoginBgImage]', 
                in_array ($preferences['enableLoginBgImage'], array ("0", "1"), true) ?
                    $preferences['enableLoginBgImage'] : true, array (
                    'class' => 'theme-attr',
                    'uncheckValue' => "0",
                ));
            echo CHtml::label (
                Yii::t('profile', 'Apply to login screen?'), 
                'preferences[enableLoginBgImage]', array (
                    'class' => 'right-label'
                ));
            ?>
        </div>
    </div>

</div>

<div id="prefs-tags" class="form preferences-section">
    <div id="tags-title-bar" class="row prefs-title-bar">
        <h3 class="left"><?php echo Yii::t('profile', 'Unhide Tags'); ?></h3>
        <div class="right minimize-arrows">
            <img class="prefs-expand-arrow"
                 src="<?php 
                    echo Yii::app()->theme->getBaseUrl() ?>/images/icons/Expand_Widget.png"/>
            <img class="hide prefs-collapse-arrow"
                 src="<?php 
                    echo Yii::app()->theme->getBaseUrl() ?>/images/icons/Collapse_Widget.png"/>
        </div>
    </div>
    <div id="tags-body" class="row prefs-body" <?php echo
        ($miscLayoutSettings['unhideTagsSectionExpanded'] == false ? 
            'style="display: none;"' : ''); ?>>
        <?php
        foreach($allTags as &$tag){
            echo '<span class="tag unhide" tag-name="'.substr($tag['tag'], 1).'">'.
            CHtml::link(
                $tag['tag'], array('/search/search','term'=>'#'.ltrim($tag['tag'], '#')),
                array('class' => 'x2-link x2-tag')).
            '</span>';
        }
        ?>
    </div>
</div>

<div class="form">
    <br/>
    <div class="row buttons">
        <?php
        echo CHtml::submitButton(
            ($model->isNewRecord ? Yii::t('app', 'Create') :
                Yii::t('app', 'Save Profile Settings')), 
            array('id' => 'save-changes', 'class' => 'x2-button'));
        ?>
    </div>
</div>

<?php $this->endWidget(); ?>

<div class="form hide upload-box preferences-section" id="create-theme-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Save Theme As'); ?></h3>
        <span class='left'>
            <?php
            echo Yii::t('app', 'Saving a theme will create a theme from your'.
                    ' current theme settings');
            ?>.
        </span>
        <br/>
        <div class='theme-name-input-container'>
            <span class='left'> <?php echo Yii::t('app', 'Theme name'); ?>: </span>
            <input id="new-theme-name"> </input>
        </div>
        <select class='prefs-theme-privacy-setting x2-select'>
            <option value='0' selected='selected'>
                <?php echo Yii::t('app', 'Public'); ?>
            </option>
            <option value='1'>
                <?php echo Yii::t('app', 'Private'); ?>
            </option>
        </select>
        <br/>
        <div class="row buttons">
            <button id='create-theme-submit-button' class='x2-button submit-upload'>
                <?php echo Yii::t('app', 'Create'); ?>
            </button>
            <button class="x2-button cancel-upload"><?php echo Yii::t('app', 'Cancel'); ?></button>
        </div>
    </div>
</div>

<div class="form hide upload-box preferences-section" id="upload-background-img-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Background Image'); ?></h3>
        <?php echo CHtml::form(
            array('site/upload', 'id' => $model->id), 'post',
            array('enctype' => 'multipart/form-data'
        )); ?>
        <?php echo CHtml::dropDownList(
            'private',
            'public',
            array(
                '0' => Yii::t('actions', 'Public'), '1' => Yii::t('actions',
                'Private'
            ))); 
        echo CHtml::hiddenField('associationId', Yii::app()->user->getId()); 
        echo CHtml::hiddenField('associationType', 'bg'); 
        echo CHtml::fileField('upload', '', array('id' => 'background-img-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(
                Yii::t('app', 'Upload'), 
                array(
                    'id' => 'upload-background-img-submit-button', 'disabled' => 'disabled',
                    'class' => 'x2-button submit-upload'
                )); ?>
            <button class="x2-button cancel-upload"><?php echo Yii::t('app', 'Cancel'); ?></button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<div class="form hide upload-box" id="upload-login-sound-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Login Sound'); ?></h3>
        <?php echo CHtml::form(
            array('site/upload', 'id' => $model->id), 'post',
            array('enctype' => 'multipart/form-data')
        ); 
        echo CHtml::dropDownList(
            'private', 'public', array('0' => Yii::t('actions', 'Public'), '1' => Yii::t('actions',
            'Private'))); 
        echo CHtml::hiddenField('associationId', Yii::app()->user->getId()); 
        echo CHtml::hiddenField('associationType', 'loginSound'); 
        echo CHtml::fileField('upload', '', array('id' => 'login-sound-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(
                Yii::t('app', 'Upload'), 
                array(
                    'id' => 'upload-login-sound-submit-button', 'disabled' => 'disabled',
                    'class' => 'x2-button submit-upload'
                )
            ); ?>
            <button class="x2-button cancel-upload"><?php 
                echo Yii::t('app', 'Cancel'); ?></button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<div class="form hide upload-box" id="upload-notification-sound-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Notification Sound'); ?></h3>
        <?php echo CHtml::form(
            array('site/upload', 'id' => $model->id), 'post',
            array('enctype' => 'multipart/form-data')
        ); 
        echo CHtml::dropDownList(
            'private', 'public', 
            array(
                '0' => Yii::t('actions', 'Public'), '1' => Yii::t('actions',
                'Private'
            ))); 
        echo CHtml::hiddenField('associationId', Yii::app()->user->getId()); 
        echo CHtml::hiddenField('associationType', 'notificationSound'); 
        echo CHtml::fileField('upload', '', array('id' => 'notification-sound-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton (Yii::t('app', 'Upload'),
                array(
                    'id' => 'upload-notification-sound-submit-button', 'disabled' => 'disabled',
                    'class' => 'x2-button submit-upload'
                )); ?>
            <button class="x2-button cancel-upload"><?php echo Yii::t('app', 'Cancel'); ?></button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<?php

$this->renderPartial ('_themeImportForm');

?>
