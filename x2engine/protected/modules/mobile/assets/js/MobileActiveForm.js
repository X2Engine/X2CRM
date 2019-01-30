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




x2.MobileActiveForm = (function () {

function MobileActiveForm (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        photoAttrName: null,
        locationAttrName: null,
        redirectUrl: null,
        submitButtonSelector: null,
        translations: {},
        validate: function () { return true; }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.X2Form.call (this, argsDict);
}

MobileActiveForm.prototype = auxlib.create (x2.X2Form.prototype);

MobileActiveForm.prototype.setUpPhotoSubmission = function () {
    var that = this;
    this.form$ = $(this.formSelector);
    this.submitButton$ = this.submitButtonSelector ? 
    $(this.submitButtonSelector) : this.form$.find ('.submit-button');

    this.submitButton$.click (function () {
        if (!that.validate ()) {
            return;
        } else {
            if (that.form$.find ('.photo-attachment').not ('.dummy-attachment').length) {
                $.mobile.loading ('show');
                x2.mobileForm.submitWithPhotos (
                    that.form$.attr ('action'), 
                    that.form$, 
                    that.photoAttrName,
                    function (response) {
                        $.mobile.loading ('hide'); 
                        if (response.responseCode == 200)  {
                            try {
                                data = JSON.parse (response.response);
                                if (data.redirectUrl) {
                                    $(':mobile-pagecontainer').pagecontainer (
                                        'change', 
                                        data.redirectUrl, { transition: 'none' }); 
                                    return;
                                }
                            } catch (e) {
                            }
                            $(':mobile-pagecontainer').pagecontainer (
                                'change', 
                                that.redirectUrl, { transition: 'none' }); 
                        }
                    },
                    function (error) {
                        x2.main.alert (that.translations['Upload failed']);
                        $.mobile.loading ('hide');
                    }
                );
            } else {
                that.form$.submit ();
            }
        }
    });   
    var cameraButton$ = $.mobile.activePage.find ('.photo-attach-button');
    var attachmentsContainer$ = this.form$.find ('.photo-attachments-container');

    new x2.CameraButton ({
        element$: cameraButton$,
        validate: function (callback) {
            if (!that.form$.find ('.' + x2.mobileForm.photoAttachmentClass).length) {
                callback ();
            }
        },
        success: function (data) {
            var attachment$ = x2.mobileForm.makePhotoAttachment (data);
            attachmentsContainer$.append (attachment$);
        },
        failure: function (message) {
        }
    });
    

};

MobileActiveForm.prototype.setUpInteractions = function () {
    var that = this;
    this.form$ = $(this.formSelector);
    this.form$.find (':input').change (function () {
        $(that.submitButtonSelector).removeClass ('disabled');
    });
    this.form$.find ('input[type="text"]').keydown (function () {
        $(that.submitButtonSelector).removeClass ('disabled');
    });
};

MobileActiveForm.prototype._init = function () {
    x2.X2Form.prototype._init.call (this);

    var that = this;
    x2.main.getController ().documentEvents.push (x2.main.onPageShow (function () {
        that.setUpInteractions ();
        that.setUpPhotoSubmission ();
    }, x2.main.getController ().constructor.name));
};

return MobileActiveForm;

}) ();
