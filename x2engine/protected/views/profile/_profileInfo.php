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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/PopupDropdownMenu.js');

Yii::app()->clientScript->registerCss("viewProfile", "

.profile-picture-row {
    border-radius: 0 0px 3px 3px !important;
    -moz-border-radius: 0 0px 3px 3px !important;
    -webkit-border-radius: 0 0px 3px 3px !important;
    -o-border-radius: 0 0px 3px 3px !important;
}

#toggle-full-profile {
    background: rgb(255, 255, 255);
    height: 0px;
    font-size: 5px;
    font-weight: bold;
    line-height: 5px;
    text-align: center;
    padding: 1px;
    cursor: pointer;
    color: rgb(94, 94, 94);
}

#profile-image-container {
    width: 100px;
    height: 100px;
    margin: auto;
    margin-top: 4px;
}

.file-wrapper.full-profile-info {
    height:211px;
}

.file-wrapper {
    height: 119px;
    display: block;
}

.profile-picture-row {
    width: 35%;
}

#avatar-image {
    margin:auto;
    display:block;
}

#photo-upload-overlay {
    margin: auto;
    position: relative;
    width: 91px;
    border-top: none;
    font-weight: bold;
    font-size: 12px;
    border: 2px solid rgb(204, 200, 200);
    color: rgb(95, 94, 94);
    height: 35px;
    text-align: center;
    background: rgb(213, 243, 255);
    top: -37px;
    border-radius: 0 0 4px 4px;
    border-top: none;
    opacity:0.7;
}

#photo-upload-overlay:hover {
    cursor: pointer;
}

#photo-upload-overlay span {
    display: table-cell;
    vertical-align: middle;
    width: 97px;
    height: 35px;
}

.avatar-upload {
    -webkit-border-radius:8px;
    -moz-border-radius:8px;
    -o-border-radius:8px;
    border-radius:8px;
}

#profile-info {
    margin-top: 0;
    border-radius:            0px 0px 3px 3px;
    -moz-border-radius:        0px 0px 3px 3px;
    -webkit-border-radius:    0px 0px 3px 3px;
    -o-border-radius:        0px 0px 3px 3px;
    border-top: none;
}

");

Yii::app()->clientScript->registerScript('profileInfo',"

/**
 * Validate file extension of avatar image. Called during file field onchange event and upon dialog
 * submit.
 * @param object elem a jQuery object corresponding to the file field element
 * @param object submitButton a jQuery object corresponding to the dialog submit button
 * @return bool false if invalid, true otherwise
 */
function validateAvatarFile (elem, submitButton) {
    var isLegalExtension = false;
    auxlib.destroyErrorFeedbackBox (elem);

    // get the file name and split it to separate the extension
    var name = elem.val ();

    // name is valid
    if (name.match (/.+\..+/)) {
        var extension = name.split('.').pop ().toLowerCase ();

        var legalExtensions = ['png','gif','jpg','jpe','jpeg'];        
        if ($.inArray (extension, legalExtensions) !== -1)
            isLegalExtension = true;
    } else if (name !== '') {
        var extension = '';
    }

    if(isLegalExtension) { // enable submit
        submitButton.addClass ('highlight');
    } else { // delete the file name, disable Submit, Alert message
        elem.val ('');
        if (typeof extension !== 'undefined') {
            auxlib.createErrorFeedbackBox ({
                prevElem: elem,
                message: '".Yii::t('app', 'Invalid file type.')."'
            });
        } else {
            auxlib.createErrorFeedbackBox ({
                prevElem: elem,
                message: '".Yii::t('app', 'Please upload a file.')."'
            });
        }
        submitButton.removeClass ('highlight');
    }
        
    return isLegalExtension;
}

/**
 * Setup avatar upload UI element behavior 
 */
function setUpAvatarUpload () {

    // hide/show overlay
    $('#profile-image-container').mouseover (function () {
        $('#photo-upload-overlay').show ();
    }).mouseleave (function (evt) {
        if ($(evt.relatedTarget).closest ('#avatar-image').length === 0 &&
            $(evt.relatedTarget).closest ('#photo-upload-overlay span').length === 0)
            $('#photo-upload-overlay').hide ();
    });

    // instantiate image upload dialog
    $('#photo-upload-overlay').click (function () {
        $('#photo-upload-dialog').dialog ({
            title: '".Yii::t('app', 'Upload an Avatar Photo')."',
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: '".Yii::t('app', 'Submit')."',
                    'class': 'photo-upload-dialog-submit-button',
                    click: function () {
                        if (validateAvatarFile (
                            $('#avatar-photo-file-field'), 
                            $('.photo-upload-dialog-submit-button'))) {

                            $('#photo-form').submit ();
                        }
                    }
                },
                {
                    text: '".Yii::t('app', 'Cancel')."',
                    click: function () {
                        $(this).dialog ('close');
                    }
                }
            ],
            close: function () {
                $('#photo-upload-dialog').hide ();
                $(this).dialog ('destroy');
                auxlib.destroyErrorFeedbackBox ($('#avatar-photo-file-field'));
            }
        });
    });
}

/**
 * Set up behavior of hide/show profile full details button 
 */
function setUpProfileDetailsBehavior () {
    $('#toggle-full-profile').click (function () {
        if ($('.full-profile-details-row').is (':visible')) {
            $('.full-profile-details-row').each (function () { $(this).hide (); });
            $('.file-wrapper').removeClass ('full-profile-info');
            auxlib.saveMiscLayoutSetting ('fullProfileInfo', 0);
        } else {
            $('.full-profile-details-row').each (function () { $(this).show (); });
            $('.file-wrapper').addClass ('full-profile-info');
            auxlib.saveMiscLayoutSetting ('fullProfileInfo', 1);
        }
    });
}

$(function() {
    setUpProfileDetailsBehavior ();
    setUpAvatarUpload ();

    // set up add profile widgets dropdown
    new PopupDropdownMenu ({
        containerElemSelector: '#x2-hidden-profile-widgets-menu-container',
        openButtonSelector: '#add-profile-widget-button'
    });
});

",CClientScript::POS_HEAD);

$attributeLabels = $model->attributeLabels();
if ($isMyProfile) {
    $miscLayoutSettings = $model->miscLayoutSettings;
    $profileInfoMinimized = $miscLayoutSettings['profileInfoIsMinimized'];
    $fullProfileInfo = $miscLayoutSettings['fullProfileInfo'];
}
?>
<div id='profile-info-container'>

<div class="page-title icon profile">
    <h2>
        <span class="no-bold"><?php echo Yii::t('profile','Profile:'); ?></span>
        <?php echo $model->fullName; ?>
    </h2>
<?php
    if ($isMyProfile) {
        echo CHtml::link(
            Yii::t('profile', 'Minimize'), '#',
            array(
                'class' => 'x2-minimal-button x2-button icon right',
                'id' => 'profile-info-minimize-button',
                'title' => Yii::t('app', 'Minimize Profile Info'),
                'style' => ($profileInfoMinimized ? 'display: none;' : ''), 
                'onclick' => '
                    auxlib.saveMiscLayoutSetting ("profileInfoIsMinimized", 1); 
                    $("#profile-info-minimize-button").hide ();
                    $("#profile-info-maximize-button").show ();
                    $("#profile-info-contents-container").slideUp ();
                    return false;'
            )
        );
        echo CHtml::link(
            Yii::t('profile', 'Maximize'), '#',
            array(
                'class' => 'x2-minimal-button x2-button icon right',
                'id' => 'profile-info-maximize-button',
                'title' => Yii::t('app', 'Maximize Profile Info'),
                'style' => (!$profileInfoMinimized ? 'display: none;' : ''), 
                'onclick' => '
                    auxlib.saveMiscLayoutSetting ("profileInfoIsMinimized", 0); 
                    $("#profile-info-maximize-button").hide ();
                    $("#profile-info-minimize-button").show ();
                    $("#profile-info-contents-container").slideDown ();
                    return false;'
            )
        );
        echo CHtml::link(
            Yii::t('profile', 'Add Profile Widget'), '#',
            array(
                'class' => 'x2-minimal-button x2-button icon right',
                'id' => 'add-profile-widget-button',
            )
        );
        echo $model->getHiddenProfileWidgetMenu ();
        echo CHtml::link(
            '<span></span>', $this->createUrl('update', array('id' => $model->id)),
            array(
                'class' => 'x2-minimal-button x2-button icon edit right',
                'title' => Yii::t('app', 'Edit Profile'),
            )
        );
        ?>
        <?php
    }
?>
</div>

<div id='profile-info-contents-container'
 <?php echo ($isMyProfile && $profileInfoMinimized ? 'style="display: none;"' : ''); ?>>
<table id='profile-info' class="details">
    <tr>
        <td class="label" width="20%"><?php echo $attributeLabels['fullName']; ?></td>
        <td><b><?php echo CHtml::encode($model->fullName); ?></b></td>
        <td class='profile-picture-row' rowspan="9" style="text-align:center;">
            <span class="file-wrapper<?php 
             echo (!$isMyProfile || $fullProfileInfo ? ' full-profile-info' : ''); ?>">
            <div id='profile-image-container'>
            <?php Profile::renderFullSizeAvatar ($model->id); ?>
            <?php 
            if($isMyProfile) {
                ?>
                <div id='photo-upload-overlay' style='display:none;'>
                    <span><?php echo Yii::t('app', 'Change Avatar'); ?></span>
                </div>
            </div>
            <div id='photo-upload-dialog' style='display:none;'>
            <?php
            echo CHtml::form(
                'uploadPhoto/'.$model->id,'post',
                array('enctype'=>'multipart/form-data', 'id'=>'photo-form'));
            echo CHtml::fileField(
                'photo','', array (
                    'id' => 'avatar-photo-file-field',
                    'onchange' => 
                        'validateAvatarFile ($(this), $(".photo-upload-dialog-submit-button"));'
                )).'<br />';
            echo CHtml::endForm();
            ?>
            </div>
            <?php
            } 
            ?>
            </span>
            <?php 
            if ($isMyProfile) {
            ?>
            </br>
            <a id='view-public-profile-link' 
             href="<?php echo Yii::app()->controller->createUrl (
                '/profile/view', array ('id' => $model->id, 'publicProfile' => true)); ?>">
                -<?php echo Yii::t('app', 'View Public Profile'); ?>-</a>
            <?php
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['tagLine']; ?></td>
        <td><?php echo CHtml::encode($model->tagLine); ?></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['username']; ?></td>
        <td><b><?php echo CHtml::encode($model->username); ?></b></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['officePhone']; ?></td>
        <td><b><?php echo CHtml::encode($model->officePhone); ?></b></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['cellPhone']; ?></td>
        <td><b><?php echo CHtml::encode($model->cellPhone); ?></b></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['emailAddress']; ?></td>
        <td><b><?php echo CHtml::mailto($model->emailAddress); ?></b></td>
    </tr>
    <tr class='full-profile-details-row' <?php 
     echo (!$isMyProfile || $fullProfileInfo ? '' : 'style="display:none;"'); ?>>
        <td class="label"><?php echo $attributeLabels['googleId']; ?></td>
        <td><b><?php echo CHtml::mailto($model->googleId); ?></b></td>
    </tr>
    <tr class='full-profile-details-row' <?php 
     echo (!$isMyProfile || $fullProfileInfo ? '' : 'style="display:none;"'); ?>>
        <td class="label"><?php echo Yii::t('profile','Signature'); ?></td>
        <td><div style="height:50px;width:0px;float:left;"></div><?php echo $model->getSignature(true); ?></td>
    </tr>
    <tr>
        <td id='toggle-full-profile' colspan='2'
         <?php echo (!$isMyProfile ? 'style="display:none;"' : ''); ?>
         title='<?php echo Yii::t('app', 'Toggle Full Profile Details'); ?>'>| | |</td>
    </tr>
</table>
</div>
</div>
