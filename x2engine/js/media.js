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

    var iframeId = document.getElementById("upload_iframe");

    // Add event...
    var eventHandler = function () {
        if (iframeId.detachEvent)
            iframeId.detachEvent("onload", eventHandler);
        else
            iframeId.removeEventListener("load", eventHandler, false);

        // Message from server...
        if (iframeId.contentDocument) {
            var content = iframeId.contentDocument.body.innerHTML;
        } else if (iframeId.contentWindow) {
            var content = iframeId.contentWindow.document.body.innerHTML;
        } else if (iframeId.document) {
            var content = iframeId.document.body.innerHTML;
        }
        console.log(content);

        //console.log(content);
        var response = typeof content === 'string' ? $.parseJSON(content) : content;

        if (response['status'] === 'success') {
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
                'name': 'AttachmentFiles[types][]',
                'value': 'temp'
            });

            var parent = fileField.parent();

            parent.find('.temp-file-id').val(response['id']);
            parent.find('.filename').html(response['name']);

            // height
            var chooseFileButtonCenter = parseInt($('.x2-file-wrapper').children('.x2-button').prop('clientHeight'), 10) / 2;
            var halfSpanHeight = parseInt($('.x2-file-wrapper').children('.filename').height(), 10) / 2;
            var spanTop = chooseFileButtonCenter - halfSpanHeight;
            $('.x2-file-wrapper').children('.filename').css('top', spanTop + 'px');

            $('#choose-file-saving-icon').animate({opacity: 0.0});
            parent.find('.filename').html(response['name']).animate({opacity: 1.0});
            parent.find('.error').html("");


            form.removeAttribute("target");
            form.removeAttribute("enctype");
            form.removeAttribute("encoding");

            form.setAttribute("action", $(form).data('oldAction'));

        } else if (response['status'] == 'notsent') {
            $('#choose-file-saving-icon').animate({opacity: 0.0});
            form.removeAttribute("target");
            form.removeAttribute("enctype");
            form.removeAttribute("encoding");
            form.setAttribute("action", $(form).data('oldAction'));
        } else {
            var parent = fileField.parent();
            parent.find('.error').html(response['message']);
            // clear old values
            parent.find('.filename').html("");
            parent.find('.temp-file-id').val("");
            parent.val("");
            parent.find('input[type="button"]').css({opacity: 1.0});
            $('#choose-file-saving-icon').css({opacity: 0.0});
            form.setAttribute("action", $(form).data('oldAction'));

        }

        // Del the iframe...
        setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
    };

    // save the form object, to be restored after uploading temp file
    $(form).data('oldAction', $(form).attr('action'));

    if (iframeId.addEventListener)
        iframeId.addEventListener("load", eventHandler, true);
    if (iframeId.attachEvent)
        iframeId.attachEvent("onload", eventHandler);

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

function mediaCheckName(el) {
    // array with disallowed extensions
    var illegal_ext = ['exe', 'bat', 'dmg', 'js', 'jar', 'swf', 'php', 'pl', 'cgi', 'htaccess', 'py'];
    // - www.coursesweb.net
    // get the file name and split it to separe the extension
    var name = el.value;
    var ar_name = name.split('.');

    ar_ext = ar_name[ar_name.length - 1].toLowerCase();

    // check the file extension
    var re = 1;
    for (var i in illegal_ext) {
        if (illegal_ext[i] === ar_ext) {
            re = 0;
            break;
        }
    }

    // if re is 1, the extension isn't illegal
    if (re === 1) {
        return true;
    } else {
        var filenameError = "{X} is not an allowed filetype.";
        alert(filenameError.replace('{X}', ar_ext));
        return false;
    }
}

$(function () {
    $('form').submit(function () {
        var tempFileId = $('.x2-file-wrapper').children('.temp-file-id');
        if (tempFileId.val() === '') { // user hasn't choosen a file
            alert("Please choose a file.");
            return false;
        }
    });
});

function mediaSubmit() {
    var tempfileid = $('.x2-file-wrapper').children('.temp-file-id');
    if (tempfileid.val() !== '') { // we have a valid temp file
        tempfileid.form.submit();
    } else {
        alert("Please choose a file.");
    }
}

function showAssociationAutoComplete(associationType) {
    var name = $(associationType).val();

    if (name !== 'bg' && name !== 'none') {
        $('#Media_associationId').val('');
        $('.ui-autocomplete-input').css('display', 'none');
        $('#' + name + '-auto-select').css('display', 'inline-block')
    }
}

function toggleUserMedia(userMedia, showhide) {
    userMedia.toggle('blind');
    var buttonClass = $(showhide).find('i').attr('class');
    if (buttonClass.match('left')) {
        buttonClass = buttonClass.replace(/left/, 'down');
    } else {
        buttonClass = buttonClass.replace(/down/, 'left');
    }
    showhide.find('i').attr('class', buttonClass);
}
