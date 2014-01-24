<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/profileSettings.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/spectrumSetup.js', CClientScript::POS_END);

Yii::app()->clientScript->registerCss("profileSettings", "

.tag{
    -moz-border-radius:4px;
    -o-border-radius:4px;
    -webkit-border-radius:4px;
    border-radius:4px;
    border-style:solid;
    border-width:1px;
    border-color:gray;
    margin:2px 2px;
    display:block;
    float:left;
    padding:2px;
    background-color:#f0f0f0;
}
.tag a {
    text-decoration:none;
    color:black;
}

#settings-form .prefs-hint {
    color:#06c;
    float: left;
    margin-right: 4px;
}

/* override spectrum color picker css */
.sp-replacer {
    padding: 0px !important;
}
.sp-dd {
    height: 13px !important;
}
.sp-preview
{
    width:20px !important;
    height: 17px !important;
    margin-right: 5px !important;
}

/* modify standard form style, remove rounded borders */
#settings-form .form {
    margin: 0 0 0 0;
    border-radius: 0;
    -webkit-border-radius: 0;
}

#settings-form .color-picker-input {
    margin-right: 6px;
}

#settings-form #theme-attributes-body .row {
    margin-top: 5px;
    margin-bottom: 5px;
}

/* temporary change to allow small buttons, this should exist across the app */
#settings-form .x2-small-button  {
    padding: 0 4px 0 4px !important;
    margin: 2px 4px 0 0;
}

/* prevents side-by-side borders between touching forms */
#profile-settings {
    border-top: 0;
}

/* prevents side-by-side borders between touching forms */
#profile-settings,
#theme-attributes,
#prefs-tags {
    border-bottom: 0;
}

/* adds borders so that these boxes look like the rest */
.upload-box {
    border-left: 1px solid #aaa !important;
    border-right: 1px solid #aaa !important;
}

/* sub-menu maximize/minimize arrows */
#theme-attributes .minimize-arrows,
#prefs-tags .minimize-arrows {
    margin-top: 15px;
    width: 20px;
    height: 20px;
    text-align: center;
}

/* spacing in the create a theme sub menu */
.theme-name-input-container {
    margin-top: 9px;
    margin-bottom: 0px;
}

/* validation in the create a theme sub menu */
#create-theme-box input.error
{
    background: #FEE;
    border-color: #C00 !important;
}

/* spacing in the create a theme sub menu */
#create-theme-box input {
    margin-top: 0px;
}

/* spacing in the create a theme sub menu */
#new-theme-name {
    width: 170px;
    margin-left: 4px;
    margin-bottom: 4px;
}

select#themeName,
select#backgroundImg,
select#loginSounds,
select#themeName,
select#notificationSounds {
    margin-right: 4px;
}

#save-changes {
    margin-bottom: 5px;
}

#prefs-save-theme-button,
#prefs-create-theme-button,
#upload-theme-button,
#export-theme-button,
#upload-background-img-button,
#upload-login-sound-button,
#upload-notification-sound-button {
    margin-top: 2px;
}


");

$preferences = $model->theme;
$miscLayoutSettings = $model->miscLayoutSettings;

$passVariablesToClientScript = "
    x2.profileSettings = {};
    x2.profileSettings.checkerImagePath = '".
        Yii::app()->theme->getBaseUrl()."/images/checkers.gif';
    x2.profileSettings.createThemeHint = '".
        Yii::t('profile', 'Save your current theme settings as a predefined theme.')."';
    x2.profileSettings.saveThemeHint = '".
        Yii::t('profile', 'Update the settings of the currently selected predefined theme.')."';
    x2.profileSettings.normalizedUnhideTagUrl = '".
        CHtml::normalizeUrl(array("/profile/unhideTag"))."';
    x2.profileSettings.uploadedByAttrs = {};";

// pass array of predefined theme uploadedBy attributes to client
foreach($myThemes->data as $theme){
    $passVariablesToClientScript .= "x2.profileSettings.uploadedByAttrs['".
            $theme->fileName."'] = '".$theme->uploadedBy."';";
}

Yii::app()->clientScript->registerScript(
        'passVariablesToClientScript', $passVariablesToClientScript, CClientScript::POS_BEGIN);


?>

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'settings-form',
    'enableAjaxValidation' => false,
        ));
?>
<?php echo $form->errorSummary($model); ?>

<div id="profile-settings" class="form">
    <div class="row">
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disablePhoneLinks', array('onchange' => 'js:highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disablePhoneLinks', array('style' => 'display:inline;'));
            ?>
            <span class='x2-hint' title='<?php 
             echo Yii::t('app', 'Prevent phone number fields from being formatted as links.'); ?>'>[?]</span>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disableAutomaticRecordTagging', 
                    array('onchange' => 'js:highlightSave();'));
            echo $form->labelEx(
                    $model, 'disableAutomaticRecordTagging', array('style' => 'display:inline;'));
            ?>
            <span class='x2-hint' title='<?php 
             echo Yii::t('app', 'Prevent tags from being automatically generated when hashtags are detected in record fields.'); ?>'>[?]</span>
        </div>
    </div>
    <div class="row" style="margin-bottom:10px;"> 
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disableNotifPopup', array('onchange' => 'js:highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disableNotifPopup', array('style' => 'display:inline;'));
            ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'startPage'); ?>
            <?php
            echo $form->dropDownList(
                    $model, 'startPage', $menuItems,
                    array('onchange' => 'js:highlightSave();', 'style' => 'min-width:140px;'));
            ?>
        </div>
        <div class="cell">
            <?php echo $form->labelEx($model, 'resultsPerPage'); ?>
            <?php
            echo $form->dropDownList(
                    $model, 'resultsPerPage', Profile::getPossibleResultsPerPage(),
                    array('onchange' => 'js:highlightSave();', 'style' => 'width:100px'));
            ?>
        </div>

    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'language'); ?>
            <?php
            echo $form->dropDownList(
                    $model, 'language', $languages, array('onchange' => 'js:highlightSave();'));
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
                $model, 'timeZone', $times, array('onchange' => 'js:highlightSave();'));
            ?>
        </div>
    </div>
</div>
<div id="theme-attributes" class='form'>
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
        <div class="row">
            <label for="themeName">
                <?php echo Yii::t('app', 'Predefined Theme') ?>
            </label>
            <select id="themeName" class="left theme-attr" name="preferences[themeName]">
                <option value="" id="custom-theme-option">
                    <?php echo Yii::t('app', 'Custom'); ?>
                </option>
                <?php foreach($myThemes->data as $theme){ ?>
                    <option value="<?php echo $theme->fileName; ?>"
                    <?php
                    if($theme->fileName == $preferences['themeName']){
                        echo "selected='selected'";
                    }
                    ?>>
                                <?php echo $theme->fileName; ?>
                    </option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button left'
                    id='prefs-create-theme-button'>
                        <?php echo Yii::t('profile', 'Create Theme'); ?>
            </button>
            <span id="prefs-create-theme-hint" class='prefs-hint'>[?]</span>
            <button type='button' class='x2-button x2-small-button left'
                    id='prefs-save-theme-button'>
                        <?php echo Yii::t('profile', 'Save Theme'); ?>
            </button>
            <span id="prefs-save-theme-hint" class='hide prefs-hint'>[?]</span>
            <!--<div id="create-theme-dialog" title="Create Theme">
                <span class='left'> <?php //echo Yii::t('app', 'Theme name');    ?>: </span>
                <input id="new-theme-name"> </input>
                <input type="checkbox"> Private </input>
                <br/>
                <button class='dialog-create-button' class="x2-button">
            <?php //echo Yii::t('app', 'Create');  ?>
                </button>
            </div>-->
            <!--<button type='button' class='x2-button' id='export-theme-button'>
            <?php //echo Yii::t('profile', 'Export Theme');  ?>
            </button>
            <button type='button' class='x2-button' id='upload-theme-button'>
            <?php //echo Yii::t('profile', 'Upload Theme');   ?>
            </button>-->
        </div>
        <div class="row">
            <label for="backgroundColor">
                <?php echo Yii::t('app', 'Background Color') ?>
            </label>
            <input id="backgroundColor" type="text" name="preferences[backgroundColor]"
                   value="<?php echo $preferences['backgroundColor']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="menuBgColor">
                <?php echo Yii::t('app', 'Menu Background Color') ?>
            </label>
            <input id="menuBgColor" type="text" name="preferences[menuBgColor]"
                   value="<?php echo $preferences['menuBgColor']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="menuTextColor">
                <?php echo Yii::t('app', 'Menu Text Color') ?>
            </label>
            <input id="menuTextColor" type="text" name="preferences[menuTextColor]"
                   value="<?php echo $preferences['menuTextColor']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="pageHeaderBgColor">
                <?php echo Yii::t('app', 'Page Header Background Color') ?>
            </label>
            <input id="pageHeaderBgColor" type="text"
                   name="preferences[pageHeaderBgColor]"
                   value="<?php echo $preferences['pageHeaderBgColor']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="pageHeaderTextColor">
                <?php echo Yii::t('app', 'Page Header Text Color') ?>
            </label>
            <input id="pageHeaderTextColor" type="text"
                   name="preferences[pageHeaderTextColor]"
                   value="<?php echo $preferences['pageHeaderTextColor']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="activityFeedWidgetBgColor">
                <?php echo Yii::t('app', 'Activity Feed Widget Background Color'); ?>
            </label>
            <input id="activityFeedWidgetBgColor" type="text"
                   name="preferences[activityFeedWidgetBgColor]"
                   value="<?php echo $preferences['activityFeedWidgetBgColor']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="gridViewRowColorOdd">
                <?php echo Yii::t('app', 'Grid View Row Color 1'); ?>
            </label>
            <input id="gridViewRowColorOdd" type="text"
                   name="preferences[gridViewRowColorOdd]"
                   value="<?php echo $preferences['gridViewRowColorOdd']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="gridViewRowColorEven">
                <?php echo Yii::t('app', 'Grid View Row Color 2'); ?>
            </label>
            <input id="gridViewRowColorEven" type="text"
                   name="preferences[gridViewRowColorEven]"
                   value="<?php echo $preferences['gridViewRowColorEven']; ?>"
                   class='color-picker-input theme-attr'> </input>
        </div>
        <div class="row">
            <label for="backgroundTiling">
                <?php echo Yii::t('app', 'Background Tiling') ?>
            </label>
            <select id="backgroundTiling" name="preferences[backgroundTiling]"
                    class='theme-attr left'>
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
                    class='theme-attr left'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach ($myBackgrounds->data as $background) { ?>
                    <option value="<?php
                        echo $background->uploadedBy == null ?
                            $background->fileName :
                            ('media/'.$background->uploadedBy.'/'.$background->fileName); ?>"
                        <?php
                        if($background->fileName == $preferences['backgroundImg']){
                            echo "selected='selected'";
                        } ?>>
                        <?php echo $background->fileName; ?>
                    </option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button'
                    id='upload-background-img-button'>
                        <?php echo Yii::t('profile', 'Upload Background Image'); ?>
            </button>
        </div>
        <div class="row">
            <label for="loginSounds">
                <?php echo Yii::t('profile', 'Login Sound'); ?>
            </label>
            <select id="loginSounds" name="preferences[loginSound]" class='left'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach($myLoginSounds->data as $loginSound){ ?>
                    <option value="<?php
                echo $loginSound->id.",".
                $loginSound->fileName.",".$loginSound->uploadedBy;
                    ?>"
                            id="sound-<?php echo $loginSound->id; ?>"
                            <?php
                            if($loginSound->fileName == $model->loginSound){
                                echo "selected='selected'";
                            }
                            ?>>
                                <?php echo $loginSound->fileName; ?>
                    </option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button'
                    id='upload-login-sound-button'>
                        <?php echo Yii::t('profile', 'Upload Login Sound'); ?>
            </button>
        </div>
        <div class="row">
            <label for="notificationSounds">
                <?php echo Yii::t('profile', 'Notification Sound'); ?>
            </label>
            <select id="notificationSounds" name="preferences[notificationSound]"
                    class='left'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach($myNotificationSounds->data as $notificationSound){ ?>
                    <option value="<?php
                        echo $notificationSound->id.",".$notificationSound->fileName.",".
                            $notificationSound->uploadedBy; ?>"
                     id="sound-<?php echo $notificationSound->id; ?>"
                     <?php
                     if($notificationSound->fileName == $model->notificationSound){
                         echo "selected='selected'";
                     }
                     ?>><?php echo $notificationSound->fileName; ?></option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button'
                    id='upload-notification-sound-button'>
                        <?php echo Yii::t('profile', 'Upload Notification Sound'); ?>
            </button>
        </div>
    </div>

    <?php /* <div class="row">
      <?php echo $form->checkBox($model,'enableFullWidth'); ?>
      <?php echo $form->labelEx($model,'enableFullWidth',array('style'=>'display:inline;')); ?>
      </div> */ ?>
</div>

<div id="prefs-tags" class="form">
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

<div class="form hide upload-box" id="create-theme-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Create a Theme'); ?></h3>
        <span class='left'>
            <?php
            echo Yii::t('app', 'Creating a theme will save your current '.
                    'theme settings as a predefined theme');
            ?>.
        </span>
        <br/>
        <div class='theme-name-input-container'>
            <span class='left'> <?php echo Yii::t('app', 'Theme name'); ?>: </span>
            <input id="new-theme-name"> </input>
        </div>
        <select class='prefs-theme-privacy-setting'>
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

<div class="form hide upload-box" id="upload-background-img-box">
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
