/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

x2.mobileForm = (function () {

function MobileForm (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    this.photoAttachmentClass = 'photo-attachment';
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

/**
 * Submit file-input-containing form
 */
MobileForm.prototype.submitWithFiles = function (form$, success, failure) {
    var formData = new FormData (form$.get (0));
    $.ajax ({
        url: form$.attr ('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            success (data);
        },
        error: function () {
            failure.call (null, Array.prototype.slice.call (arguments));
        }
    });
};

MobileForm.prototype.submitWithLocation = function (
    locationUploadUrl, form$, fileKey, success, failure) {

    if (!x2.main.isPhoneGap) 
        throw new Error ('MobileForm::submitWithLocation is not browser compatible');

    var params = {};
    form$.find (':input').not (':disabled').each (function () {
        params[$(this).attr ('name')] = $(this).val ();
    });
    
    var photo$ = form$.find ('.' + this.photoAttachmentClass);
    var fileUrl = photo$.attr ('src');
    //console.log ([fileUrl, locationUploadUrl, fileKey, params, success, failure]);
    //x2touch.API.uploadFile (fileUrl, locationUploadUrl, fileKey, params, success, failure);
};

MobileForm.prototype.submitWithPhotos = function (
    photoUploadUrl, form$, fileKey, success, failure) {

    if (!x2.main.isPhoneGap) 
        throw new Error ('MobileForm::submitWithPhotos is not browser compatible');

    var params = {};
    form$.find (':input').not (':disabled').each (function () {
        params[$(this).attr ('name')] = $(this).val ();
    });

// TODO: add support for multiple upload (but don't use file upload directly. make calls to 
// x2touch.API instead).
//    form$.find ('.' + this.photoAttachmentClass).each (function () {
//        var options = new FileUploadOptions ();
//        options.fileKey = fileKey;
//        options.mimeType = 'image/jpeg';
//        options.params = params;
//        var ft = new FileTransfer ();
//        ft.upload (
//            $(this).attr ('data-x2-file-url'), 
//            encodeURI (photoUploadUrl),
//            function () {},
//            function () {},
//            options);
//    });
    
    var photo$ = form$.find ('.' + this.photoAttachmentClass);
    var fileUrl = photo$.attr ('src');
    //console.log ([fileUrl, photoUploadUrl, fileKey, params, success, failure]);
    x2touch.API.uploadFile (fileUrl, photoUploadUrl, fileKey, params, success, failure);
};


MobileForm.prototype.makePhotoAttachment = function (data) {
    var attachment$ = $('<div>', {
        'class': x2.mobileForm.photoAttachmentClass + '-container',
    });
    var img$ = $('<img>', {
        'class': x2.mobileForm.photoAttachmentClass,
        src: data
    });
    var remove$ = $('<div>', {
        'class': 'remove-attachment-button'
    });
    remove$.append ($('<i>', { 'class': 'fa fa-circle' }));
    remove$.append ($('<i>', { 'class': 'fa fa-times-circle' }));
    attachment$.append (img$);
    attachment$.append (remove$);
    remove$.click (function () { $(this).parent ().remove (); });
    return attachment$;
};

return new MobileForm ();

}) ();

$(document).on ('pagecontainershow', function (evt, ui) {
    // add error class to jqm input wrapper in order to allow them to be given error styling
    $('input.error').each (function () { 
        var parent$ = $(this).parent ();
        if (parent$.hasClass ('ui-input-text')) {
            parent$.addClass ('error');
        }
    });

    // prevents Android keyboard from popping up when clicking datepicker input
    $('.x2-mobile-datepicker').each (function () { 
        $(this).focus (function () { 
            $(this).blur ();
            return false;
        });
    });
});
