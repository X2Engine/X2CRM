/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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


$(function() {
	// $('div.x2-layout .formSection:not(.showSection) .tableWrapper').hide();

	// $('div.x2-layout .formItem').disableSelection();

	$('div.x2-layout .formSectionShow, .formSectionHide').click(function() {
		toggleFormSection($(this).closest('.formSection'));
		saveFormSections();
	});
	
	$('a#showAll, a#hideAll').click(function() {
		$('a#showAll, a#hideAll').toggleClass('hide');
		if($('#showAll').hasClass('hide')) {
			$('div.x2-layout .formSection:not(.showSection)').each(function() {
				if($(this).find('a.formSectionHide').length > 0)
					toggleFormSection(this);
			});
		} else {
			$('div.x2-layout .formSection.showSection').each(function() {
				if($(this).find('a.formSectionHide').length > 0)
					toggleFormSection(this);
			});
		}
	});

	$('.inlineLabel').find('input:text, textarea').focus(function() { formFieldFocus(this); }).blur(function() { formFieldBlur(this); });
	

	// set up x2 helper tooltips
	$('.x2-hint').qtip();
});


function toggleFormSection(section) {
	if($(section).hasClass('showSection'))
		$(section).find('.tableWrapper').slideToggle(400,function(){
			$(this).parent('.formSection').toggleClass('showSection');
			saveFormSections();
		});
	else {
		$(section).toggleClass('showSection').find('.tableWrapper').slideToggle(400);
		saveFormSections();
	}
}
function saveFormSections() {
	var formSectionStatus = [];
	$('div.x2-layout .formSection').each(function(i,section) {
		formSectionStatus[i] = $(section).hasClass('showSection')? '1' : '0';
	});
	var formSettings = '['+formSectionStatus.join(',')+']';
	$.ajax({
		url: yii.baseUrl+'/index.php/site/saveFormSettings',
		type: 'GET',
		data: 'formName='+window.formName+'&formSettings='+encodeURI(formSettings)
	});
}

function toggleText(field) {
	if (field.defaultValue==field.value) {
		field.value = ''
		field.style.color = 'black'
	} else if (field.value=='') {
		field.value = field.defaultValue
		field.style.color = '#aaa'
	}
}
function formFieldFocus(elem) {
	var field = $(elem);
	if (field.val() == field.attr('title')) {
		field.val('');
		field.css('color','#000');
	}
}
function formFieldBlur(elem) {
	var field = $(elem);
	if (field.val() == '') {
		field.val(field.attr('title'));
		field.css('color','#aaa');
	}
}

// $(function() {
	// placeholderTest = document.createElement('input');
	// if(!('placeholder' in placeholderTest)) {
	
		// var active = document.activeElement;
		
		// $.delegate(':text','focus',function() {
			// if ($(this).attr('placeholder') != '' && $(this).val() == $(this).attr('placeholder'))
				// $(this).val('').removeClass('placeholder');
		// });
		
		// $.delegate(':text','blur',function() {
			// if ($(this).attr('placeholder') != '' && ($(this).val() == '' || $(this).val() == $(this).attr('placeholder')))
				// $(this).val($(this).attr('placeholder')).addClass('placeholder');
		// });
		// $(':text').blur();
		// $(active).focus();
		// $('form').submit(function () {
			// $(this).find('.placeholder').each(function() { $(this).val(''); });
		// });
	// }
// });

function submitForm(formName) {
	document.forms[formName].submit();
}
function toggleForm(formName,duration) {
	if($(formName).is(':hidden')) {
		$('html,body').animate({
			scrollTop: ($('#action-form').offset().top-200)
		}, 300);
	}
	$(formName).toggle('blind',{},duration);
	
}
function hide(field) {
	$(field).hide(); //field.style.display="none";
	// button=document.getElementById('save-changes');
	$('#save-changes').addClass('highlight'); //button.style.background='yellow';
}
function show(field) {
	$(field).show();
	// field.style.display="block";
}

function fileUpload(form, fileField, action_url, remove_url) {
    // Create the iframe...
    var iframe = document.createElement("iframe");
    iframe.setAttribute("id", "upload_iframe");
    iframe.setAttribute("name", "upload_iframe");
    iframe.setAttribute("width", "0");
    iframe.setAttribute("height", "0");
    iframe.setAttribute("border", "0");
    iframe.setAttribute("style", "width: 0; height: 0; border: none;");
 
    // Add to document...
    form.parentNode.appendChild(iframe);
    window.frames['upload_iframe'].name = "upload_iframe";
 
    iframeId = document.getElementById("upload_iframe");
 
    // Add event...
    var eventHandler = function () {
 
            if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
            else iframeId.removeEventListener("load", eventHandler, false);
 
            // Message from server...
            if (iframeId.contentDocument) {
                var content = iframeId.contentDocument.body.innerHTML;
            } else if (iframeId.contentWindow) {
                var content = iframeId.contentWindow.document.body.innerHTML;
            } else if (iframeId.document) {
                var content = iframeId.document.body.innerHTML;
            }
            
            var response = $.parseJSON(content)
             
            if(response['status'] == 'success') {
            	// success uploading temp file
            	// save it's name in the form so it gets attached when the user clicks send
            	var file = $('<input>', {
            		'type': 'hidden',
            		'name': 'AttachmentFiles[id][]',
            		'class': 'AttachmentFiles',
            		'value': response['id'], // name of temp file
            	});
            	
            	var temp = $('<input>', {
            		'type': 'hidden',
            		'name': 'AttachmentFiles[temp][]',
            		'value': true,
            	});
            	
            	var parent = fileField.parent().parent().parent();
            	
            	parent.parent().find('.error').html(''); // clear error messages
            	var newFileChooser = parent.clone(); // save copy of file upload span before we start making changes
            	
            	parent.removeClass('next-attachment');
            	parent.append(file);
            	parent.append(temp);
            	
            	var remove = $("<a>", {
            		'href': "#",
            		'html': "[x]",
            	});
            	
            	parent.find('.filename').html(response['name']);
            	parent.find('.remove').append(remove);
            	
            	remove.click(function() {removeAttachmentFile(remove.parent().parent(), remove_url); return false;});
            	
            	fileField.parent().parent().remove();
            	
            	parent.after(newFileChooser);
            	initX2FileInput();
            	
            } else {
            	fileField.parent().parent().parent().find('.error').html(response['message']);
            	fileField.val("");
            }
 			
            // Del the iframe...
            setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
        }
 
    if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
    if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);
 
    // Set properties of form...
    form.setAttribute("target", "upload_iframe");
    form.setAttribute("action", action_url);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");
 
    // Submit the form...
    form.submit(); 
}

// remove an attachment that is stored on the server as a temp file
function removeAttachmentFile(attachment, remove_url) {
	var id = attachment.find(".AttachmentFiles");
	$.post(remove_url, {'id': id.val()});

	attachment.remove();
}

// set up x2 file input
// call this function everytime an x2 file input is created
function initX2FileInput() {
	// bind hover and click effects
	$('input.x2-file-input[type=file]').hover(function() {
		var button = $('input.x2-file-input[type=file]').next();
		if(button.hasClass('active') == false) {
			$('input.x2-file-input[type=file]').next().addClass('hover');
		}
	}, function() {
		$('input.x2-file-input[type=file]').next().removeClass('hover');
	});
	
	$('input.x2-file-input[type=file]').mousedown(function() {
		$('input.x2-file-input[type=file]').next().removeClass('hover');
		$('input.x2-file-input[type=file]').next().addClass('active');
	});
	
	$('body').mouseup(function() {
		$('input.x2-file-input[type=file]').next().removeClass('active');
	});
	
	// position the saving icon for uploading files
	// width
	var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('width'), 10)/2;
	var halfIconWidth = parseInt($('#choose-file-saving-icon').css('width'), 10)/2;
	var iconLeft = chooseFileButtonCenter - halfIconWidth;
	$('#choose-file-saving-icon').css('left', iconLeft + 'px');
	
	// height
	var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('height'), 10)/2;
	var halfIconHeight = parseInt($('#choose-file-saving-icon').height(), 10)/2;
	var iconTop = chooseFileButtonCenter - halfIconHeight;
	$('#choose-file-saving-icon').css('top', iconTop + 'px');

}


