/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

x2.mobileForm = (function () {

function MobileForm (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    this.photoAttachmentClass = 'photo-attachment';
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

 

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
    remove$.append ($('<i>', { 'class': 'fa fa-close' }));
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
