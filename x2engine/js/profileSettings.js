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

function highlightSave() {
	$('#save-changes').addClass('highlight'); 
}
        
function convertTextColor(colorString){
    var redHex = colorString.slice(1,2);
    var greenHex = colorString.slice(3,4);
    var blueHex = colorString.slice(5,6);
        
    var red = parseInt(redHex, 16);
    var green = parseInt(greenHex, 16);
    var blue = parseInt(blueHex, 16);
        
    if((red*0.299 + green*0.587 + blue*0.114) > 186){
        return '#000000';
    } else {
        return '#ffffff';
    }
}

/*
Set the url to the sound file and play the sound
*/
function setSound(sound, id, filename, uploadedBy) {
    if(filename!=null){
        if(uploadedBy){
            $('#'+sound).attr('src',yii.baseUrl+'/uploads/media/'+uploadedBy+'/'+filename);
        }else{
            $('#'+sound).attr('src',yii.baseUrl+'/uploads/'+filename);
        }

        var soundFile = $("#"+sound)[0];
        if (Modernizr.audio) soundFile.play();
    }
}

function deleteSound(sound, id){
    $.ajax({
        url: yii.scriptUrl+'/profile/deleteSound?sound='+sound,
        type: 'get',
        data: 'id='+id,
        success: function(){
            $('#'+sound+'_'+id).hide();
        }
    });
}

/*
change the background image
*/
function setBackground(filename) {
		if(filename=='') {
				$('body').css('background-image','none').removeClass("no-borders");
		} else {
			$('body').css('background-image','url('+yii.baseUrl+'/uploads/'+filename+')').
                toggleClass("no-borders",($('#backgroundTiling').val() === 'stretch'));
			$(window).trigger('resize');
		}
}

function deleteBackground(id,filename) {
	$.ajax({
		url: yii.scriptUrl+'/profile/deleteBackground',
		type: 'get',
		data: 'id='+id,
		success: function(response) {
			if(response=='success') {
				$('#background_'+id).hide();

		        // if this is the current background,
				if($.inArray (filename, $('#header').css('background-image')) > -1) {

					// remove it from the page
					if($('#backgroundColor').val() === '') {
						$('#header').addClass('defaultBg').css('background-image','');
					} else {
						$('#header').removeClass('defaultBg').css('background-image','');
                    }
				}
			}
		}
	});
}

/*
background uploader
*/
function showAttach () {
	var e = document.getElementById ('attachments');
	if(e.style.display === 'none') {
		e.style.display = 'block';
	} else {
		e.style.display = 'none';
    }
}

/*
Enables submit if the file has the correct extension, alerts the user if it does not
*/
var ar_ext = ['png', 'jpg','jpe','jpeg','gif','svg']; // array with allowed extensions
function checkName(id) {
// - www.coursesweb.net
	// get the file name and split it to separate the extension
    var selector = "#" + id;
	var name = $(selector).val();
	var ar_name = name.split('.');

	// check the file extension
	var re = 0;
	for(var i = 0; i < ar_ext.length; i++) {
		if(ar_ext[i] === ar_name[1].toLowerCase()) {
			re = 1;
			break;
		}
	}
	// if re is 1, the extension is in the allowed list
	if(re === 1) { // enable submit
	    $(selector).parents ('.upload-box').find ('.submit-upload').removeAttr('disabled','disabled');
	} else { // delete the file name, disable Submit, Alert message
		$(selector).val('');
	    $(selector).parents ('.upload-box').find ('.submit-upload').attr('disabled','disabled');
		alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
	}
}

/*
Enables submit if the file has the correct extension, alerts the user if it does not
*/
var s_ext = ['mp3', 'wav', 'aiff'];
function checkSoundName(id) {
    var selector = "#" + id;
    var name = $(selector).val();
    var ar_name = name.split('.');

    var re = 0;
    for (var i = 0; i < s_ext.length; i++) {
        if(s_ext[i] === ar_name[1].toLowerCase()) {
            re = 1;
            break;
        }
    }
    if(re==1){
	    $(selector).parents ('.upload-box').find ('.submit-upload').removeAttr('disabled','disabled');
    } else { // delete the file name, disable Submit, Alert message
	    $(selector).val('');
	    $(selector).parents ('.upload-box').find ('.submit-upload').attr('disabled','disabled');
	    alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
    }
}

/*
Helper function which gets called when the user changes the predefined theme setting
*/
function changeThemeAttr () {
    consoleDebug ($(this));
    if ($(this).attr ('id') === 'themeName') return;
    if (!checkPredefThemeEditPermissions ()) {
        $('#themeName').find (':selected').removeAttr ('selected');
        $('#themeName').find ('#custom-theme-option').attr ('selected', 'selected');
    }
};


/*
Shows/hides the upload box with the specified id.
Parameters:
    boxId - the id of the upload box
*/
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


function setupPrefsEventListeners () {

    /*
    Convert relevent input fields to color pickers.
    Trigger change event when color is picked.
    */
    $('.color-picker-input').each (function (index, element) {
        setupSpectrum ($(element), true);
    });

	$('#menuBgColor').change(function() {
        if ($(this).data ('ignoreChange')) {
            return;
        }
        var text = $(this).val();
		if(text === '') {
			$('#header').css('background','').addClass('defaultBg');
            //addCheckerImage ($(this));
		} else {
			$('#header').removeClass('defaultBg').css('background','#' + text);
		}
		highlightSave();
	});

    function selectPreferredColor (CSSProperty, CSSdefault, colorInputElem, targetElem) {
        if ($(colorInputElem).data ('ignoreChange')) {
            return;
        }
		var text = $(colorInputElem).val();
		if(text === '') {
            //addCheckerImage ($(colorInputElem));
			$(targetElem).css(CSSProperty, CSSdefault);
		} else {
			$(targetElem).css(CSSProperty, '#' + text);
		}
		highlightSave();
    }

	$('#backgroundColor').change(function() {
        selectPreferredColor ('background-color', '#efeee8', $(this), $('body'));
	});

	$('#menuTextColor').change(function() {
        selectPreferredColor (
            'color', '#fff', $(this), $('ul.main-menu > li > a, ul.main-menu > li > span'));
	});

	$('#pageHeaderBgColor').change(function() {
        selectPreferredColor ('background', '#fff', $(this), $('.page-title'));
	});

	$('#pageHeaderTextColor').change(function() {
        selectPreferredColor ('color', '#fff', $(this), $('.page-title, .page-title h2'));
	});

    $('#activityFeedWidgetBgColor').change(function() {
        selectPreferredColor ('background-color', '#fff', $(this), $('#chat-box'));
	});

    $('#gridViewRowColorOdd').change(function() {
        selectPreferredColor (
            'background', '', $(this), $('div.grid-view table.items tr.odd'));
	});

    $('#gridViewRowColorEven').change(function() {
        selectPreferredColor (
            'background', '#F5F4DE', $(this), $('div.grid-view table.items tr.even'));
	});

    $('.color-picker-input').blur (function () {
        var text = $(this).val ();

        // make color picker color match input field without triggering change events
        if (text !== '') { 
            removeCheckerImage ($(this));
            $(this).next ('div.sp-replacer').find ('.sp-preview-inner').css (
                'background', '#' + text);
        }
    });

	$('#backgroundTiling').change(function() {
		var val = $(this).val();
		var noBorders = false;
		switch(val) {
			case 'repeat-x':
			case 'repeat-y':
			case 'repeat':
				$("body").css({
                    "background-attachment":"",
                    "background-size":"",
                    "background-position":"",
                    "background-repeat":val
                });
				break;
			case 'center':
				$("body").css({
                    "background-attachment":"",
                    "background-size":"",
                    "background-repeat":"no-repeat",
                    "background-position":"center center"
                });
				break;
			case 'stretch':
				$("body").css({
                    "background-attachment":"fixed",
                    "background-size":"cover",
                    "background-position":"",
                    "background-repeat":""
                });
				noBorders = true;
				break;
		}
		$("body").toggleClass("no-borders",noBorders);

		highlightSave();
	}).change();

    /*
    Unhide tags menu behavior
    */
    $('.unhide').mouseenter(function(){
        var tag=$(this).attr('tag-name');
        var elem=$(this);
        var content='<span class="hide-link-span"><a href="#" class="hide-link" style="color:#06C;">[+]</a></span>';
        $(content).hide().delay(500).appendTo($(this)).fadeIn(500);
        $('.hide-link').click(function(e){
           e.preventDefault();
           $.ajax({
              url: x2.profileSettings.normalizedUnhideTagUrl + '?tag=' + tag,
              success: function(){
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
            $body.slideDown ();
        }
    });

    $('#prefs-create-theme-button').click (function () {
        toggleUploadBox ('create-theme-box');
        $('#new-theme-name').focus ();
    });

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
    Set theme name to custom upon edit if user does not have edit permissions for 
    current predefined theme.
    */
    $('.theme-attr').bind ('change', changeThemeAttr);

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

    /*
    Minimizes the upload box.
    */
    $('.upload-box').find ('button.cancel-upload').click (function () {
        $(this).parents ('.upload-box').slideUp ();
        return false;
    });

}

/*
Sets up behavior for theme save button
*/
function setupThemeSaving () {

    /*
    Save theme via Ajax.
    */
    function saveTheme () {
        if ($('prefs-save-theme-button').attr ('disabled')) return;
        var themeAttributes = {};
        $.each ($("#theme-attributes").find ('.theme-attr'), function () {
            consoleDebug ($(this));
            var themeAttrName = $(this).attr ('name').match (/\[(\w+)\]/)[1];
            themeAttributes[themeAttrName] = $(this).val ();
        });
        themeAttributes['owner'] = yii.profile.username;
        //themeAttributes['private'] = $('.prefs-theme-privacy-setting').val ();
        consoleDebug (themeAttributes);
        $.ajax ({
            url: "saveTheme",
            data: {
                'themeAttributes': JSON.stringify (themeAttributes)
            },
            success: function (data) {
                consoleDebug (data);
                auxlib.createReqFeedbackBox ({
                    prevElem: $('#prefs-save-theme-hint'), 
                    disableButton: $('#prefs-save-theme-button'), 
                    message: data,
                    delay: 3000
                });
            }
        });
    }
    
    $('#prefs-save-theme-button').click (function () {
        saveTheme ();
    });

}


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
            $(this).attr ('disabled', 'disabled');
            createTheme (themeName); 
        }
    });

    /*
    Save new theme to server via Ajax. Reset current theme. Handle errors.
    */
    function createTheme (themeName) {
        consoleLog (themeName);
        if ($('prefs-create-theme-button').attr ('disabled')) return;

        // build theme attribute dictionary to send to server
        var themeAttributes = {};
        $.each ($("#theme-attributes").find ('.theme-attr'), function () {
            consoleLog ($(this).attr ('name'));
            var themeAttrName = $(this).attr ('name').match (/\[(\w+)\]/)[1];
            themeAttributes[themeAttrName] = $(this).val ();
        });
        themeAttributes['themeName'] = themeName;
        themeAttributes['owner'] = yii.profile.username;
        themeAttributes['private'] = $('.prefs-theme-privacy-setting').val ();
        consoleDebug (themeAttributes);

        $.ajax ({
            url: "createTheme",
            data: {
                'themeAttributes': JSON.stringify (themeAttributes)
            },
            success: function (data) {
                var respObj = JSON.parse (data);
                consoleDebug (respObj);

                if (respObj['success']) {
                    consoleLog ('success');
                    destroyErrorBox ($('#create-theme-box'));
                    $('#create-theme-box').slideUp ();

                    // select new theme from drop down
                    $('#themeName').children ().removeAttr ('selected');
                    $('#themeName').append ($('<option>', {
                        'selected': 'selected',
                        'value': themeName,
                        'text': themeName
                    }));

                    // indicate successful creation
                    auxlib.createReqFeedbackBox ({
                        prevElem: $('#prefs-save-theme-hint'),
                        message: respObj['msg'],
                        delay: 3000,
                        disableButton: $('#prefs-create-theme-button')
                    });
                    x2.profileSettings.uploadedByAttrs[themeName] = 
                        yii.profile.username;

                    showHideThemeSaveButton ();
                    $('#new-theme-name').removeClass ('error');

                } else {
                    consoleLog ('failure');

                    // display error messages
                    destroyErrorBox ($('#create-theme-box'));
                    var errorBox = createErrorBox (
                        respObj['errorListHeader'], [respObj['errorMsg']]);
                    $('.prefs-theme-privacy-setting').after ($(errorBox));
                    $('#new-theme-name').addClass ('error');

                }
            },
            complete: function () {
                consoleLog ('complete');
                $('#create-theme-submit-button').removeAttr ('disabled');
            }
        });
    }

}

/*
Removes an error div created by createErrorBox ().  
Parameters:
    parentElem - a jQuery element which contains the error div
*/
function destroyErrorBox (parentElem) {
    var $errorBox = $(parentElem).find ('.error-summary-container');
    if ($errorBox.length !== 0) {
        $errorBox.remove ();
    }
}

/*
Returns a jQuery element corresponding to an error box. The error box will
contain the specified errorHeader and a bulleted list of the specified error
messages.
Parameters:
    errorHeader - a string
    errorMessages - an array of strings
*/
function createErrorBox (errorHeader, errorMessages) {
    var errorBox = $('<div>', {'class': 'error-summary-container'}).append (
        $("<div>", { 'class': "error-summary"}).append (
            $("<p>", { text: errorHeader }),
            $("<ul>")
    ));
    for (var i in errorMessages) {
        var msg = errorMessages[i];
        $(errorBox).find ('.error-summary').
            find ('ul').append ($("<li> " + msg + " </li>"));
    }
    return errorBox;
}

/*
Returns true if the user has edit permissions for the current predefined theme,
false otherwise.
*/
function checkPredefThemeEditPermissions () {
    var currentPredefTheme = $('#themeName').val ();
    if (x2.profileSettings.uploadedByAttrs[currentPredefTheme] === 
        yii.profile.username) {

        return true;
    } else {
        return false;
    }
}

/*
Shows the save theme button if the user has edit permissions for the current 
predefined theme and hides it otherwise.
*/
function showHideThemeSaveButton () {
    var currentPredefTheme = $('#themeName').val ();
    consoleLog (x2.profileSettings.uploadedByAttrs[currentPredefTheme]);
    consoleLog (yii.profile.username);
    if (currentPredefTheme === 'Custom') {
        $('#prefs-save-theme-button').hide (); 
    } else if (checkPredefThemeEditPermissions ()) {
        $('#prefs-save-theme-button').show (); 
        $('#prefs-save-theme-hint').show (); 
    } else {
        $('#prefs-save-theme-button').hide (); 
        $('#prefs-save-theme-hint').hide (); 
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
                $('.theme-attr').unbind ('change', changeThemeAttr);
                consoleLog ('requestTheme ajax ret');
                consoleDebug (data);
                if (data === '') return;
                var theme = JSON.parse (data);
                consoleLog (theme);
                for (var attrName in theme) {
                    consoleLog (attrName);
                    consoleLog ($('#' + attrName).length);
                    if ($('#' + attrName).length !== 0) {
                        if (attrName.match (/Color/)) {
                            theme[attrName] = theme[attrName];
                        }
                        $('#' + attrName).val (theme[attrName]);
                        $('#' + attrName).change ();
                    }
                }
                $('#themeName').bind ('change', selectTheme);
                $('.theme-attr').bind ('change', changeThemeAttr);
                showHideThemeSaveButton ();
            }
        });
    }

    function selectTheme () {
        if ($(this).find (':selected').attr ('id') === 'custom-theme-option') {
            $('#prefs-save-theme-button').hide (); 
            $('#prefs-save-theme-hint').hide (); 
            return;
        }
        requestTheme ($('#themeName').val ());
    }

    $('#themeName').bind ('change', selectTheme);

}

// main function
$(document).ready(function profileSettingsMain () {
    setupPrefsEventListeners ();
    setupThemeSelection ();
    setupThemeCreation ();
    setupThemeSaving ();

    showHideThemeSaveButton ();

    $('#prefs-save-theme-hint').qtip({
       position:{'my':'top right','at':'bottom left'},
       content: x2.profileSettings.saveThemeHint
    });

    $('#prefs-create-theme-hint').qtip({
       position:{'my':'top right','at':'bottom left'},
       content: x2.profileSettings.createThemeHint
    });
});



