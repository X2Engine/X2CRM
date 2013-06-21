<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/modcoder_excolor/jquery.modcoder.excolor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/profileSettings.js');

Yii::app()->clientScript->registerCss("profileSettings", "

/* modify standard form style, remove rounded borders */
#settings-form .form {
    margin: 0 0 0 0;
    border-radius: 0;
    -webkit-border-radius: 0;
}

/* temporary change to allow small buttons, this should exist across the app */
#settings-form .x2-small-button  {
    padding: 0 4px 0 4px !important;
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

select#backgroundImg,
select#loginSounds,
select#themeName,
select#notificationSounds {
    float: left;
    margin-right: 4px;
}

button#create-theme-button,
button#upload-theme-button,
button#export-theme-button {
    float: left;
}

#save-changes {
    margin-bottom: 5px;
}

#create-theme-button,
#upload-theme-button,
#export-theme-button,
#upload-background-img-button,
#upload-login-sound-button,
#upload-notification-sound-button {
    margin-top: 2px;
}


");

$preferences = $model->theme;

?>

<?php
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'settings-form',
    'enableAjaxValidation'=>false,
)); ?>
<?php echo $form->errorSummary($model); ?>

<div id="profile-settings" class="form">
    <div class="row" style="margin-bottom:10px;">
        <div class="cell">
            <?php echo $form->checkBox(
                $model,'allowPost',array('onchange'=>'js:highlightSave();')); ?>
            <?php echo $form->labelEx(
                $model,'allowPost',array('style'=>'display:inline;')); ?>
        </div>
        <!--<div class="cell">
            <?php //echo $form->checkBox($model,'showSocialMedia',array('onchange'=>'js:highlightSave();')); ?>
            <?php //echo $form->labelEx($model,'showSocialMedia',array('style'=>'display:inline;')); ?>
            <?php //echo $form->dropDownList($model,'showSocialMedia',array(1=>Yii::t('actions','Yes'),0=>Yii::t('actions','No')),array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
        </div>-->
    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model,'startPage'); ?>
            <?php echo $form->dropDownList(
                $model,'startPage',$menuItems,
                array('onchange'=>'js:highlightSave();','style'=>'min-width:140px;')); ?>
        </div>
        <div class="cell">
            <?php echo $form->labelEx($model,'resultsPerPage'); ?>
            <?php echo $form->dropDownList(
                $model,'resultsPerPage',Profile::getPossibleResultsPerPage(),
                array('onchange'=>'js:highlightSave();','style'=>'width:100px')); ?>
        </div>

    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model,'language'); ?>
            <?php echo $form->dropDownList(
                $model,'language',$languages,array('onchange'=>'js:highlightSave();')); ?>
        </div>
        <div class="cell">
            <?php
            if(!isset($model->timeZone))
                $model->timeZone="Europe/London";
            ?>
            <?php echo $form->labelEx($model,'timeZone'); ?>
            <?php echo $form->dropDownList(
                $model,'timeZone',$times,array('onchange'=>'js:highlightSave();')); ?>
        </div>
    </div>
</div>

<div id="theme-attributes" class='form'>
    <div id="theme-attributes-title-bar" class="row prefs-title-bar">
        <h3 class="left"><?php echo Yii::t('app','Theme'); ?></h3>
        <div class="right minimize-arrows">
            <img class="prefs-expand-arrow" 
             src="<?php echo Yii::app()->request->baseUrl . "/themes/x2engine/images/icons/Expand_Widget.png"; ?>" />
            <img class="hide prefs-collapse-arrow" 
             src="<?php echo Yii::app()->request->baseUrl . "/themes/x2engine/images/icons/Collapse_Widget.png"; ?>" />
        </div>
    </div>
    <div id="theme-attributes-body" class="hide row prefs-body">
        <div class="row">
            <!--<label for="themeName">
                <?php //echo Yii::t('app','Predefined Theme') ?>
            </label>
            <select id="themeName" class="theme-attr" name="preferences[themeName]"> 
                <option value="" id="custom-theme-option"> 
                    <?php //echo Yii::t('app','Custom'); ?> 
                </option>
                <?php //foreach ($myThemes->data as $theme) { ?>
                <option value="<?php //echo $theme->fileName; ?>"
                 <?php 
                 /*if ($theme->fileName == $preferences['themeName']) {
                    echo "selected='selected'";
                 }*/
                 ?>>
                    <?php //echo $theme->fileName; ?>
                </option>
                <?php //} ?>
            </select>-->
            <!--<button type='button' class='x2-button x2-small-button' 
             id='create-theme-button'>
                <?php //echo Yii::t('profile', 'Create Theme'); ?>
            </button>-->
            <!--<div id="create-theme-dialog" title="Create Theme">
                <span class='left'> <?php //echo Yii::t('app', 'Theme name'); ?>: </span>
                <input id="new-theme-name"> </input>
                <input type="checkbox"> Private </input>
                <br/>
                <button class='dialog-create-button' class="x2-button"> 
                    <?php //echo Yii::t('app', 'Create'); ?>
                </button>
            </div>-->
            <!--<button type='button' class='x2-button' id='export-theme-button'>
                <?php //echo Yii::t('profile', 'Export Theme'); ?>
            </button>
            <button type='button' class='x2-button' id='upload-theme-button'>
                <?php //echo Yii::t('profile', 'Upload Theme'); ?>
            </button>-->
        </div>
        <!--<input id="owner" type="hidden" name="preferences[owner]" 
         value="<?php //echo $preferences['owner']; ?>"
         class='theme-attr'> </input>
        <input id="private" type="hidden" name="preferences[private]" 
         value="<?php //echo $preferences['private']; ?>"
         class='theme-attr'> </input>-->
        <div class="row">
            <label for="backgroundColor">
                <?php echo Yii::t('app','Background Color') ?>
            </label>
            <input id="backgroundColor" type="text" name="preferences[backgroundColor]" 
             value="<?php echo $preferences['backgroundColor']; ?>"
             class='theme-attr'> </input>
        </div>
        <div class="row">
            <label for="menuBgColor">
                <?php echo Yii::t('app','Menu Background Color') ?>
            </label>
            <input id="menuBgColor" type="text" name="preferences[menuBgColor]" 
             value="<?php echo $preferences['menuBgColor']; ?>"
             class='theme-attr'> </input>
        </div>
        <div class="row">
            <label for="menuTextColor">
                <?php echo Yii::t('app','Menu Text Color') ?>
            </label>
            <input id="menuTextColor" type="text" name="preferences[menuTextColor]" 
             value="<?php echo $preferences['menuTextColor']; ?>"
             class='theme-attr'> </input>
        </div>
        <div class="row">
            <label for="pageHeaderBgColor">
                <?php echo Yii::t('app','Page Header Background Color') ?>
            </label>
            <input id="pageHeaderBgColor" type="text" 
             name="preferences[pageHeaderBgColor]" 
             value="<?php echo $preferences['pageHeaderBgColor']; ?>"
             class='theme-attr'> </input>
        </div>
        <div class="row">
            <label for="pageHeaderTextColor">
                <?php echo Yii::t('app','Page Header Text Color') ?>
            </label>
            <input id="pageHeaderTextColor" type="text" 
             name="preferences[pageHeaderTextColor]" 
             value="<?php echo $preferences['pageHeaderTextColor']; ?>"
             class='theme-attr'> </input>
        </div>
        <div class="row">
            <label for="prefs-activity-feed-widget-bg-color">
                <?php echo Yii::t('app','Activity Feed Widget Background Color'); ?>
            </label>
            <input id="prefs-activity-feed-widget-bg-color" type="text" 
             name="preferences[activityFeedWidgetBgColor]" 
             value="<?php echo $preferences['activityFeedWidgetBgColor']; ?>"
             class='theme-attr'> </input>
        </div>
        <div class="row">
            <label for="backgroundTiling">
                <?php echo Yii::t('app','Background Tiling') ?>
            </label>
            <select id="backgroundTiling" name="preferences[backgroundTiling]" 
             class='theme-attr'>
             <?php
                $tilingOptions = array ('stretch', 'center', 'repeat', 'repeat-x', 'repeat-y');
                foreach ($tilingOptions as $option) { ?>
                <option value="<?php echo $option; ?>" 
                 <?php echo $option == $preferences['backgroundTiling'] ? "selected=\'selected\'" : '';?>>
                    <?php echo Yii::t('app', $option) ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="row">
            <label for="backgroundImg">
                <?php echo Yii::t('profile','Background Image'); ?>
            </label>
            <select id="backgroundImg" name="preferences[backgroundImg]"
            class='theme-attr'>
                <option value=""> <?php echo Yii::t('app','None'); ?> </option>
            <?php foreach ($myBackgrounds->data as $background) { ?>
                <option value="<?php 
                 echo $background->uploadedBy == null ? 
                     $background->fileName : 
                     ('media/' . $background->uploadedBy . '/' . $background->fileName);
                 ?>"
                 <?php 
                 if ($background->fileName == $preferences['backgroundImg']) {
                    echo "selected='selected'";
                 }
                 ?>>
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
                <?php echo Yii::t('profile','Login Sound'); ?>
            </label>
            <select id="loginSounds" name="Profile[loginSound]">
                <option value=""> <?php echo Yii::t('app','None'); ?> </option>
            <?php foreach ($myLoginSounds->data as $loginSound) { ?>
                <option value="<?php echo $loginSound->id . "," . 
                 $loginSound->fileName . "," . $loginSound->uploadedBy; ?>" 
                 id="sound-<?php echo $loginSound->id; ?>"
                 <?php 
                 if ($loginSound->fileName == $model->loginSound) {
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
                <?php echo Yii::t('profile','Notification Sound'); ?>
            </label>
            <select id="notificationSounds" name="Profile[notificationSound]">
                <option value=""> <?php echo Yii::t('app','None'); ?> </option>
                <?php foreach ($myNotificationSounds->data as $notificationSound) { ?>
                <option value="<?php echo $notificationSound->id . "," . 
                                          $notificationSound->fileName . "," . 
                                          $notificationSound->uploadedBy; ?>" 
                 id="sound-<?php echo $notificationSound->id; ?>"
                    <?php 
                    if ($notificationSound->fileName == $model->notificationSound) {
                        echo "selected='selected'";
                    }
                    ?>>
                    <?php echo $notificationSound->fileName; ?>
                </option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button' 
             id='upload-notification-sound-button'>
                <?php echo Yii::t('profile', 'Upload Notification Sound'); ?>
            </button>
        </div>
    </div>

    <?php /*<div class="row">
        <?php echo $form->checkBox($model,'enableFullWidth'); ?>
        <?php echo $form->labelEx($model,'enableFullWidth',array('style'=>'display:inline;')); ?>
    </div> */ ?>
</div>

<div id="prefs-tags" class="form">
    <div id="tags-title-bar" class="row prefs-title-bar">
        <h3 class="left"><?php echo Yii::t('profile','Unhide Tags'); ?></h3>
        <div class="right minimize-arrows">
            <img class="prefs-expand-arrow" 
             src="/themes/x2engine/images/icons/Expand_Widget.png"/>
            <img class="hide prefs-collapse-arrow" 
             src="/themes/x2engine/images/icons/Collapse_Widget.png"/>
        </div>
    </div>
    <div id="tags-body" class="row hide prefs-body">
        <?php   
        foreach($allTags as &$tag) {
            echo '<span class="tag unhide" tag-name="'.substr($tag['tag'],1).'">'.
                CHtml::link($tag['tag'],array(
                    '/search/search?term=%23'.substr($tag['tag'],1)), 
                    array('class'=>'x2-link x2-tag')).
                '</span>';
        }
        ?>
    </div>
</div>


<div class="form">
    <br/>
    <div class="row buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : 
          Yii::t('app','Save'),array('id'=>'save-changes','class'=>'x2-button')); ?>
    </div>
</div>

<?php $this->endWidget(); ?>

<div class="form hide upload-box" id="create-theme-box">
    <div class="row">
        <h3><?php echo Yii::t('profile','Create a Theme'); ?></h3>
            <span class='left'> 
                <?php echo Yii::t('app', 'Creating a theme will save your current ' .
                    'theme settings as a predefined theme'); ?>. 
            </span>
            <br/>
            <div class='theme-name-input-container'>
                <span class='left'> <?php echo Yii::t('app', 'Theme name'); ?>: </span>
                <input id="new-theme-name"> </input>
            </div>
            <input id='new-theme-privacy-setting' type="checkbox"> Private </input>
            <br/>
            <div class="row buttons">
                <button id='create-theme-submit-button' class='x2-button submit-upload'> 
                    <?php echo Yii::t('app', 'Create'); ?>
                </button>
                <button class="x2-button right cancel-upload"> Cancel </button>
            </div>
    </div>
</div>

<div class="form hide upload-box" id="upload-background-img-box">
    <div class="row">
        <h3><?php echo Yii::t('profile','Upload a Background Image'); ?></h3>
        <?php echo CHtml::form(array('site/upload','id'=>$model->id),'post',array('enctype'=>'multipart/form-data')); ?>
        <?php echo CHtml::dropDownList('private','public',array('0'=>Yii::t('actions','Public'),'1'=>Yii::t('actions','Private'))); ?>
        <?php echo CHtml::hiddenField('associationId',Yii::app()->user->getId()); ?>
        <?php echo CHtml::hiddenField('associationType', 'bg'); ?>
        <?php echo CHtml::fileField('upload','',array('id'=>'background-img-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'upload-background-img-submit-button','disabled'=>'disabled','class'=>'x2-button submit-upload')); ?>
            <button class="x2-button right cancel-upload"> Cancel </button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<div class="form hide upload-box" id="upload-login-sound-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Login Sound'); ?></h3>
        <?php echo CHtml::form(array('site/upload','id'=>$model->id),'post',array('enctype'=>'multipart/form-data')); ?>
        <?php echo CHtml::dropDownList('private','public',array('0'=>Yii::t('actions','Public'),'1'=>Yii::t('actions','Private'))); ?>
        <?php echo CHtml::hiddenField('associationId',Yii::app()->user->getId()); ?>
        <?php echo CHtml::hiddenField('associationType','loginSound'); ?>
        <?php echo CHtml::fileField('upload','',array('id'=>'login-sound-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'upload-login-sound-submit-button','disabled'=>'disabled','class'=>'x2-button submit-upload')); ?>
            <button class="x2-button right cancel-upload"> Cancel </button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<div class="form hide upload-box" id="upload-notification-sound-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Notification Sound'); ?></h3>
        <?php echo CHtml::form(array('site/upload','id'=>$model->id),'post',array('enctype'=>'multipart/form-data')); ?>
        <?php echo CHtml::dropDownList('private','public',array('0'=>Yii::t('actions','Public'),'1'=>Yii::t('actions','Private'))); ?>
        <?php echo CHtml::hiddenField('associationId',Yii::app()->user->getId()); ?>
        <?php echo CHtml::hiddenField('associationType','notificationSound'); ?>
        <?php echo CHtml::fileField('upload','',array('id'=>'notification-sound-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'upload-notification-sound-submit-button','disabled'=>'disabled','class'=>'x2-button submit-upload')); ?>
            <button class="x2-button right cancel-upload"> Cancel </button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>


<style>
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

</style>

<script>

    var debug = 0;

    function consoleLog (obj) {
        if (console != undefined) {
            if(console.log != undefined && debug) {
                console.log(obj);
            }
        }
    }

    function consoleDebug (obj) {
        if (console != undefined) {
            if(console.debug != undefined && debug) {
                console.debug (obj);
            }
        }
    }

    $('.unhide').mouseenter(function(){
        var tag=$(this).attr('tag-name');
        var elem=$(this);
        var content='<span class="hide-link-span"><a href="#" class="hide-link" style="color:#06C;">[+]</a></span>';
        $(content).hide().delay(500).appendTo($(this)).fadeIn(500);
        $('.hide-link').click(function(e){
           e.preventDefault();
           $.ajax({
              url:'<?php echo CHtml::normalizeUrl(array('/profile/unhideTag')); ?>'+'?tag='+tag,
              success:function(){
                  $(elem).closest('.tag').fadeOut(500);
              }
           });

        });
    }).mouseleave(function(){
        $('.hide-link-span').remove();
    });

    /*
    Maximize/minimize sub-menus
    */
    $('.prefs-title-bar').click (function () {
        var $body = $(this).siblings ('.prefs-body');
        if ($body.is (':visible')) {
            $(this).find ('.prefs-expand-arrow').show ();
            $(this).find ('.prefs-collapse-arrow').hide ();
            $body.slideUp ();
        } else {
            $(this).find ('.prefs-expand-arrow').hide ();
            $(this).find ('.prefs-collapse-arrow').show ();
            $('.modcoder_excolor_clrbox').css ({ // a hack to get positioning correct
                width: '19px',
                height: '16px',
                padding: '0'
            });
            $body.slideDown ();
            $('.modcoder_excolor_clrbox').css ({ // a hack to get positioning correct
                width: '19px',
                height: '16px',
                padding: '2px 4px'
            });
        }
    });

    // select a background from drop down
    $('#backgroundImg').change (function (event) {
        setBackground ($(event.target).val ());
    });

    // select a login sound from drop down
    $('#loginSounds').change (function (event) {
        var setSoundParams = $(event.target).val ().split (',');
        setSound ('loginSound',setSoundParams[0],setSoundParams[1],setSoundParams[2]); 
        return false;
    });

    // select a notification sound from drop down
    $('#notificationSounds').change (function (event) {
        var setSoundParams = $(event.target).val ().split (',');
        setSound ('notificationSound',setSoundParams[0],setSoundParams[1],setSoundParams[2]); 
        return false;
    });

    $('.upload-box').find ('button.cancel-upload').click (function () {
        $(this).parents ('.upload-box').slideUp ();
        return false;
    });

    $('#create-theme-button').click (function () {
        if (!$('#create-theme-box').is (":visible")) {
            $('#create-theme-box').slideDown ();
            $('html,body').animate({
                scrollTop: ($('#create-theme-box').offset().top - 100)
            }, 300);
        } else {
            $('#create-theme-box').slideUp ();
        }
    });

    function toggleUploadBox (boxId) {
        var selector = '#' + boxId;
        if (!$(selector).is (":visible")) {
            $(selector).slideDown ();
            $('html,body').animate({
                scrollTop: ($(selector).offset().top - 100)
            }, 300);
        } else {
            $(selector).slideUp ();
        }
    }

    $('#upload-background-img-button').click (function () {
        toggleUploadBox ('upload-background-img-box');
    });

    $('#upload-login-sound-button').click (function () {
        toggleUploadBox ('upload-login-sound-box');
    });

    $('#upload-notification-sound-button').click (function () {
        toggleUploadBox ('upload-notification-sound-box');
    });

    // file selected by user
    $('#background-img-file').change (function () {
        checkName ($(this).attr ("id"));
    });

    // file selected by user
    $('#notification-sound-file, #login-sound-file').change (function () {
        checkSoundName ($(this).attr ("id"));
    });

    /*
    Sets up behavior for theme creation sub-menu.
    */
    function setupThemeCreation () {

        /*
        Theme name validation
        */
        $('#create-theme-submit-button').click (function (event) {
            var themeName = $('#new-theme-name').val ();
            consoleLog (themeName);
            if (themeName === '') {
                consoleLog ('error');
                $('#new-theme-name').addClass ('error');
            } else {
                createTheme (themeName); 
            }
        });
    
        /*
        Save new theme to server via Ajax. Reset current theme.
        */
        function createTheme (themeName) {
            consoleLog (themeName);
            var themeAttributes = {};
            $.each ($("#theme-attributes").find ('.theme-attr'), function () {
                consoleDebug ($(this));
                themeAttributes[$(this).attr ('id')] = $(this).val ();
            });
            themeAttributes['themeName'] = themeName;
            /*themeAttributes['owner'] = yii.profile.username;
            themeAttributes['private'] = 
                $('#new-theme-privacy-setting').is (':checked') ? true : false;*/
            consoleDebug (themeAttributes);
            $.ajax ({
                url: "createTheme",
                data: {
                    'themeName': themeName, 
                    'themeAttributes': JSON.stringify (themeAttributes)
                },
                success: function (data) {
                    consoleDebug (data);
                    $('#create-theme-box').slideUp ();
                    $('#themeName').children ().removeAttr ('selected');
                    $('#themeName').append ($('<option>', {
                        'selected': 'selected',
                        'value': themeName,
                        'text': themeName
                    }));
                }
            });
        }

    }

    /*
    Sets up behavior for predifined theme selection.
    */
    function setupThemeSelection () {

        /*
        Request a JSON object containing the theme with the specified name.
        Populate the theme form with values contained in the JSON object.
        */
        function requestTheme (themeName) {
            consoleLog ('requestTheme, themeName = ' + themeName);
            $.ajax ({
                url: "loadTheme",
                data: {'themeName': themeName},
                success: function (data) {
                    $('#themeName').unbind ('change', selectTheme);
                    consoleLog ('requestTheme ajax ret');
                    consoleDebug (data);
                    if (data === '') return;
                    var theme = JSON.parse (data);
                    consoleLog (theme);
                    for (var attrName in theme) {
                        consoleLog (attrName);
                        consoleLog ($('#' + attrName).length);
                        if ($('#' + attrName).length !== 0) {
                            if (attrName.match (/color/i)) {
                                theme[attrName] = '#' + theme[attrName];
                            }
                            $('#' + attrName).val (theme[attrName]);
                            $('#' + attrName).siblings ('input.modcoder_excolor_clrbox').
                                css ('background-color', '#' + theme[attrName]);
                            $('#' + attrName).change ();
                        }
                    }
                    $('#themeName').bind ('change', selectTheme);
                }
            });
        }
    
        function selectTheme () {
            if ($(this).find (':selected').attr ('id') === 'custom-theme-option') return;
            requestTheme ($('#themeName').val ());
        }
    
        $('#themeName').bind ('change', selectTheme);

    }

    /*(function profileSettingsMain () {
        setupThemeSelection ();
        setupThemeCreation ();
    }) ();*/

</script>







