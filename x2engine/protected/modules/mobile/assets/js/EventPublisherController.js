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

x2.EventPublisherController = (function () {

function EventPublisherController (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        photoAttrName: '',

    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Controller.call (this, argsDict);
}

EventPublisherController.prototype = auxlib.create (x2.Controller.prototype);

EventPublisherController.prototype.setUpForm = function () {
    var that = this;
    this.submitButton$ = $('#header .post-event-button');
    this.form$ = $.mobile.activePage.find ('form.publisher-form');
    var eventBox$ = that.form$.find ('.event-text-box');

    $.mobile.activePage.find ('.event-publisher').click (function () {
        eventBox$.focus (); 
    });

    eventBox$.keyup (function () {
        that.submitButton$.toggleClass ('disabled', !$.trim (eventBox$.val ()));
    });

    this.submitButton$.click (function () {
        if (!$.trim (eventBox$.val ()) && !that.form$.find ('.photo-attachment').length) {
            return;
        } else {
            if (that.form$.find ('.photo-attachment').length) {
                x2.mobileForm.submitWithPhotos (
                    that.form$.attr ('action'), 
                    that.form$, 
                    that.photoAttrName,
                    function (response) {
                        if (response.responseCode == 200)  {
                            $(':mobile-pagecontainer').pagecontainer (
                                'change', 
                                x2.main.createUrl (
                                    '/profile/mobileActivity'), { transition: 'none' }); 
                        }
                    }
                );
            } else {
                that.form$.submit ();
            }
        }
    });

    var cameraButton$ = $('#footer .photo-attach-button');
    var attachmentsContainer$ = this.form$.find ('.photo-attachments-container');
    new x2.CameraButton ({
        element$: cameraButton$,
        validate: function () {
            return !that.form$.find ('.' + x2.mobileForm.photoAttachmentClass).length;
        },
        success: function (data) {
            var attachment$ = x2.mobileForm.makePhotoAttachment (data);
            attachmentsContainer$.append (attachment$);
        },
        failure: function (message) {

        }
    });
};

EventPublisherController.prototype.init = function () {
    x2.Controller.prototype.init.call (this);
    var that = this;
    this.documentEvents.push (x2.main.onPageShow (function () {
        that.setUpForm ();
    }, 'EventPublisherController'));
};

return EventPublisherController;

}) ();
