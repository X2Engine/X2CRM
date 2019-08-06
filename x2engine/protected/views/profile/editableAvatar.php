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




Yii::app()->clientScript->registerCss("AvatarCss", "

#profile-image-container {
    width: 100px;
    margin-top: 4px;
    margin: 15px;
    position: relative;
}

.file-wrapper {
    height: 119px;
    display: block;
}


#avatar-image {
    display:block;
}

#photo-upload-overlay {
    text-align: center;
    position: absolute;
    width: 91px;
    height: 35px;
    
    font-weight: bold;
    font-size: 12px;

    border-radius: 0 0 8px 8px;
    border-top: none;
    border: 2px solid rgb(204, 200, 200);
    
    color: rgb(95, 94, 94);
    background: rgb(213, 243, 255);

    opacity:0.7;
    top: 60px;
    left: 2px;
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

#reset-profile-avatar {
    display:inline-block;
    text-decoration: none;
    margin-bottom: 5px;
    margin-top: 5px;

}

");

Yii::app()->clientScript->registerScript('AvatarJs',"

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

$(function() {
    setUpAvatarUpload ();
});

",CClientScript::POS_HEAD);
?>
<div id='profile-image-container'>
<?php echo Profile::renderFullSizeAvatar ($id); 
	if($editable) { ?>
    <div id='photo-upload-overlay' style='display:none;'>
        <span><?php echo Yii::t('app', 'Change Avatar'); ?></span>
    </div>
	<?php 
        }
        $url = Yii::app()->createUrl ("profile/uploadPhoto", array ( 
            'id'    => $id,
            'clear' => true 
        ));
    ?>
    <?php if (Profile::model()->findByPk($id)->avatar) { ?>
    <a id='reset-profile-avatar' href='<?php echo $url ?>'>
       <?php echo Yii::t('app', 'Reset avatar') ?>
    </a>
    <?php } ?>
    </div>

<?php if($editable) { ?>
	<div id='photo-upload-dialog' style='display:none;'>
	<?php
	    echo CHtml::form (
            Yii::app()->createUrl ("profile/uploadPhoto", array ('id' => $id)),
                'post',
    	        array ('enctype'=>'multipart/form-data', 'id'=>'photo-form'));
	    echo CHtml::fileField(
	        'Profile[photo]','', array (
	            'id' => 'avatar-photo-file-field',
	        'onchange' => 
	            'validateAvatarFile ($(this), $(".photo-upload-dialog-submit-button"));'
	    )).'<br />';
	echo CHtml::endForm();
	?>
	</div>

<?php } ?>


