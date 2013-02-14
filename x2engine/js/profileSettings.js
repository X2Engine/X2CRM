/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

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
	});
	
	$('#ProfileChild_enableFullWidth').change(function() {
		window.enableFullWidth = $(this).is(':checked');
		$(window).resize();
		highlightSave();
	});

});

// js to change background image
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