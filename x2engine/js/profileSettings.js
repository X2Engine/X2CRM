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

function highlightSave() {
	$('#save-changes').addClass('highlight'); //css('background','yellow');
}
$(document).ready(function() {
	$('#backgroundColor').modcoder_excolor({
		hue_bar : 3,
		hue_slider : 5,
		border_color : '#aaa',
		sb_border_color : '#d6d6d6',
		round_corners : false,
		shadow_color : '#000000',
		background_color : '#f0f0f0',
		backlight : false,
		callback_on_ok : function() {
			$('#backgroundColor').change();
			// var text = $('#backgroundColor').val();
			// $('#backgroundColor').val(text.substring(1,7));
			// $('body, #header').css('background-color',text);
			// highlightSave();
		}
	});
 	$('#menuBgColor').modcoder_excolor({
		hue_bar : 3,
		hue_slider : 5,
		border_color : '#aaa',
		sb_border_color : '#d6d6d6',
		round_corners : false,
		shadow_color : '#000000',
		background_color : '#f0f0f0',
		backlight : false,
		callback_on_ok : function() {
			$('#menuBgColor').change();
			// var text = $('#menuBgColor').val();
			// $('#menuBgColor').val(text.substring(1,7));
			// $('#header').css('background-color',text);
			// highlightSave();
		}
	});
	$('#menuTextColor').modcoder_excolor({
		hue_bar : 3,
		hue_slider : 5,
		border_color : '#aaa',
		sb_border_color : '#d6d6d6',
		round_corners : false,
		shadow_color : '#000000',
		background_color : '#f0f0f0',
		backlight : false,
		callback_on_ok : function() {
			$('#menuTextColor').change();
			// var text = $('#menuTextColor').val();
			// $('#menuTextColor').val(text.substring(1,7));
			// $('#main-menu-bar a, #main-menu-bar span').css('color',text);
			// highlightSave();
		}
	});
	$('#pageHeaderBgColor').modcoder_excolor({
		hue_bar : 3,
		hue_slider : 5,
		border_color : '#aaa',
		sb_border_color : '#d6d6d6',
		round_corners : false,
		shadow_color : '#000000',
		background_color : '#f0f0f0',
		backlight : false,
		callback_on_ok : function() {
			$('#pageHeaderBgColor').change();
		}
	});
	$('#pageHeaderTextColor').modcoder_excolor({
		hue_bar : 3,
		hue_slider : 5,
		border_color : '#aaa',
		sb_border_color : '#d6d6d6',
		round_corners : false,
		shadow_color : '#000000',
		background_color : '#f0f0f0',
		backlight : false,
		callback_on_ok : function() {
			$('#pageHeaderTextColor').change();
		}
	});

	$('#backgroundColor').change(function() {
		var text = $('#backgroundColor').val();
		if(text == '') {
			$('body').css('background-color','#efeee8');
		} else {
			$('#backgroundColor').val(text.substring(1,7));
			$('body').css('background-color',text);
		}
		highlightSave();

	});
	$('#menuBgColor').change(function() {
		var text = $('#menuBgColor').val();
		if(text == '') {
			$('#header').css('background-color','').addClass('defaultBg');
		} else {
			$('#menuBgColor').val(text.substring(1,7));
			$('#header').removeClass('defaultBg').css('background-color',text);
		}
		highlightSave();
	});
	$('#menuTextColor').change(function() {
		var text = $('#menuTextColor').val();
		if(text == '')
			$('ul.main-menu > li > a, ul.main-menu > li > span').css('color','#fff');
		else {
			$('#menuTextColor').val(text.substring(1,7));
			$('ul.main-menu > li > a, ul.main-menu > li > span').css('color',text);
		}
		highlightSave();
	});


	$('#pageHeaderBgColor').change(function() {
		var text = $('#pageHeaderBgColor').val();
		if(text == '') {
			$('.page-title').css('background-color','#444');
		} else {
			$('#pageHeaderBgColor').val(text.substring(1,7));
			$('.page-title').css('background-color',text);
		}
		highlightSave();
	});
	$('#pageHeaderTextColor').change(function() {
		var text = $('#pageHeaderTextColor').val();
		if(text == '') {
			$('.page-title, .page-title h2').css('color','#fff');
		} else {
			$('#pageHeaderTextColor').val(text.substring(1,7));
			$('.page-title, .page-title h2').css('color',text);
		}
		highlightSave();
	});

	$('#backgroundTiling').change(function() {
		var val = $(this).val();
		var noBorders = false;
		switch(val) {
			case 'repeat-x':
			case 'repeat-y':
			case 'repeat':
				$("body").css({"background-attachment":"","background-size":"","background-position":"","background-repeat":val});
				break;
			case 'center':
				$("body").css({"background-attachment":"","background-size":"","background-repeat":"no-repeat","background-position":"center center"});
				break;
			case 'stretch':
				$("body").css({"background-attachment":"fixed","background-size":"cover","background-position":"","background-repeat":""});
				noBorders = true;
				break;
		}
		$("body").toggleClass("no-borders",noBorders);

		highlightSave();
	}).change();

	$('#ProfileChild_enableFullWidth').change(function() {
		window.enableFullWidth = $(this).is(':checked');
		$(window).resize();
		highlightSave();
	});

});

function setLoginSound(id, filename, uploadedBy) {
    if(filename!=null){
        if(uploadedBy){
            $('#loginSound').attr('src',yii.baseUrl+'/uploads/'+uploadedBy+'/'+filename);
        }else{
            $('#loginSound').attr('src',yii.baseUrl+'/uploads/'+filename);
        }
        var sound = $("#loginSound")[0];
        sound.play();
    }
    $.ajax({
            url: yii.scriptUrl+'/profile/setLoginSound',
            type: 'post',
            data: 'name='+filename
        });
        $('.loginSound-row a').css('font-weight','normal');
        $('#sound-'+id).css('font-weight','bold');
}

function setNotificationSound(id ,filename, uploadedBy) {
    if(filename!=null){
        if(uploadedBy){
            $('#notification').attr('src',yii.baseUrl+'/uploads/'+uploadedBy+'/'+filename);
        }else{
            $('#notification').attr('src',yii.baseUrl+'/uploads/'+filename);
        }
        var sound = $("#notification")[0];
        sound.play();
    }
	$.ajax({
		url: yii.scriptUrl+'/profile/setNotificationSound',
		type: 'post',
		data: 'name='+filename
	});
    $('.notificationSound-row a').css('font-weight','normal');
    $('#sound-'+id).css('font-weight','bold');
}
function deleteLoginSound(id, filename){
    $.ajax({
        url: yii.scriptUrl+'/profile/deleteLoginSound',
        type: 'get',
        data: 'id='+id,
        success: function(response){
            $('#loginSound_'+id).hide();
        }
    })
}
function deleteNotificationSound(id, filename){
    $.ajax({
        url: yii.scriptUrl+'/profile/deleteNotificationSound',
        type: 'get',
        data: 'id='+id,
        success: function(response){
            $('#notificationSound_'+id).hide();
        }
    })
}
//js to change background image
function setBackground(filename) {
	$.ajax({
		url: yii.scriptUrl+'/profile/setBackground',
		type: 'post',
		data: 'name='+filename
	});
		// success: function(response) {
			// if(response=='success') {
		if(filename=='') {
			// if($('#backgroundColor').val() == '')
				// $('#header').addClass('defaultBg').css('background-image','');
				// $('body').css('background-image','');
			// else
				$('body').css('background-image','none').removeClass("no-borders");
		} else {
			// $('#header').removeClass('defaultBg').css('background-image','url('+yii.baseUrl+'/uploads/'+filename+')');
			$('body').css('background-image','url('+yii.baseUrl+'/uploads/'+filename+')').toggleClass("no-borders",($('#backgroundTiling').val() == 'stretch'));
			$(window).trigger('resize');
		}
			// }
		// }
	// });
}
function deleteBackground(id,filename) {
	$.ajax({
		url: yii.scriptUrl+'/profile/deleteBackground',
		type: 'get',
		data: 'id='+id,
		success: function(response) {
			if(response=='success') {
				$('#background_'+id).hide();
				if($('#header').css('background-image').indexOf(filename) > -1) {		// if this is the current background,
					if($('#backgroundColor').val() == '')							// remove it from the page
						$('#header').addClass('defaultBg').css('background-image','');
					else
						$('#header').removeClass('defaultBg').css('background-image','');

				}
			}
		}
	});
}

// background uploader
function showAttach() {
	e=document.getElementById('attachments');
	if(e.style.display=='none')
		e.style.display='block';
	else
		e.style.display='none';
}
var ar_ext = ['png', 'jpg','jpe','jpeg','gif','svg'];        // array with allowed extensions

function checkName() {
// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = $('#backgroundImg').val();
	var ar_name = name.split('.');

	// check the file extension
	var re = 0;
	for(var i=0; i<ar_ext.length; i++) {
		if(ar_ext[i] == ar_name[1].toLowerCase()) {
			re = 1;
			break;
		}
	}
	// if re is 1, the extension is in the allowed list
	if(re==1) {
		// enable submit
		$('#upload-button').removeAttr('disabled');
	} else {
		// delete the file name, disable Submit, Alert message
		$('#backgroundImg').val('');
		$('#upload-button').attr('disabled','disabled');
		alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
	}
}

var s_ext = ['mp3', 'wav', 'aiff'];

function checkSoundName() {
    var name = $('#sound').val();
    var ar_name = name.split('.');

    var re=0;
    for(var i=0; i<s_ext.length; i++){
        if(s_ext[i] == ar_name[1].toLowerCase()) {
            re = 1;
            break;
        }
    }
    if(re==1){
        $('#sound-upload-button').removeAttr('disabled');
    } else {
		// delete the file name, disable Submit, Alert message
	$('#sound').val('');
	$('#sound-upload-button').attr('disabled','disabled');
	alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
    }
}
