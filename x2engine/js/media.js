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

function mediaFileUpload(form, fileField, action_url, remove_url) {
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
            		'value': response['id'] // name of temp file
            	});
            	
            	var temp = $('<input>', {
            		'type': 'hidden',
            		'name': 'AttachmentFiles[temp][]',
            		'value': true
            	});
            	
            	
            	var parent = fileField.parent();
            	
            	parent.find('.temp-file-id').val(response['id']);
            	parent.find('.filename').html(response['name']);
            	
				// height
				var chooseFileButtonCenter = parseInt($('.x2-file-wrapper').children('.x2-button').prop('clientHeight'), 10)/2;
				var halfSpanHeight = parseInt($('.x2-file-wrapper').children('.filename').height(), 10)/2;
				var spanTop = chooseFileButtonCenter - halfSpanHeight;
				$('.x2-file-wrapper').children('.filename').css('top', spanTop + 'px');
            	
 				$('#choose-file-saving-icon').animate({opacity: 0.0});
 				parent.find('.filename').html(response['name']).animate({opacity: 1.0});
 				
 				
    			form.removeAttribute("target");
    			form.removeAttribute("enctype");
    			form.removeAttribute("encoding");
    			
    			form.setAttribute("action", $(form).data('oldAction'));
            	
            } else if(response['status'] == 'notsent') {
            	$('#choose-file-saving-icon').animate({opacity: 0.0});
    			form.removeAttribute("target");
    			form.removeAttribute("enctype");
    			form.removeAttribute("encoding");
            	form.setAttribute("action", $(form).data('oldAction'));
            } else {
            	fileField.parent().find('.error').html(response['message']);
            	fileField.val("");
            }
 			
            // Del the iframe...
            setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
        }
        
    $(form).data('oldAction', $(form).attr('action')); // save the form object, to be restored after uploading temp file
 
    if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
    if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);
 
    // Set properties of form...
    form.setAttribute("target", "upload_iframe");
    form.setAttribute("action", action_url);
    form.setAttribute("method", "post");
    form.setAttribute("enctype", "multipart/form-data");
    form.setAttribute("encoding", "multipart/form-data");
 
 	$('.x2-file-wrapper').children('.x2-button').animate({opacity: 0.0});
 	$('#choose-file-saving-icon').animate({opacity: 1.0});
 
    // Submit the form...
    form.submit(); 
}

var illegal_ext = ['exe','bat','dmg','js','jar','swf','php','pl','cgi','htaccess','py'];    // array with disallowed extensions

function mediaCheckName(el) {
    // - www.coursesweb.net
    // get the file name and split it to separe the extension
    var name = el.value;
    var ar_name = name.split('.');

    ar_ext = ar_name[ar_name.length - 1].toLowerCase();
    
    // check the file extension
    var re = 1;
    for(i in illegal_ext) {
        if(illegal_ext[i] == ar_ext) {
            re = 0;
            break;
        }
    }

    // if re is 1, the extension isn't illegal
    if(re==1) {
    	return true;
    }
    else {
        alert(filenameError.replace('{X}',ar_ext));
        return false;
    }
}

$(function() {
	$('form').submit(function() {
		var tempFileId = $('.x2-file-wrapper').children('.temp-file-id');
		if(tempFileId.val() == '') { // user hasn't choosen a file
			alert("Please choose a file.");
			return false;
		}
	});
});

function mediaSubmit() {
	var tempfileid = $('.x2-file-wrapper').children('.temp-file-id');
	if(tempfileid.val() != '') { // we have a valid temp file
		tempfileid.form.submit();
	} else {
		alert("Please choose a file.");
	}
}

function showAssociationAutoComplete(associationType) {
	var name = $(associationType).val();
	
	if(name != 'bg' && name != 'none') {
		$('#Media_associationId').val('');
		$('.ui-autocomplete-input').css('display', 'none');
		$('#' + name + '-auto-select').css('display', 'inline-block')
	}
}

function toggleUserMedia(userMedia, showhide, response) {
	userMedia.toggle('blind');
	var text;
	if(response == true) {
		var text = "[&ndash;]";
	} else {
		var text = "[+]";
	}
	showhide.html(text);
}
