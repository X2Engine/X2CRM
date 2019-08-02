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




x2.EventCommentPublisherController = (function () {

function EventCommentPublisherController (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        translations: {},
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Controller.call (this, argsDict);
}

EventCommentPublisherController.prototype = auxlib.create (x2.Controller.prototype);

EventCommentPublisherController.prototype.setUpForm = function () {
    var that = this;
    this.form$ = $.mobile.activePage.find ('form.comment-publisher-form');
    this.submitButton$ = this.form$.find ('.submit-button');
    var commentBox$ = that.form$.find ('.reply-box');

    commentBox$.keyup (function () {
        that.submitButton$.toggleClass ('disabled', !$.trim (commentBox$.val ()));
    });

    var cameraButton$ = $('#footer .photo-attach-button');
    var attachmentsContainer$ = this.form$.find ('.photo-attachments-container');
    var audioButton$ = $('#footer .audio-attach-button');
    var videoButton$ = $('#footer .video-attach-button');
    
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
    
    new x2.AudioButton ({
        element$: audioButton$,
        success: function (data) {
            var attachment$ = x2.mobileForm.makeAudioAttachment (data.type,data.fullPath);
            attachment$.hide ();
            that.form$.find ('.' + x2.mobileForm.audioAttachmentClass).remove ();
            that.form$.append (attachment$);
            $.mobile.loading ('show');
            x2.mobileForm.submitWithAudio (
                data.type,
                that.form$.attr ('action'), 
                that.form$, 
                'EventCommentPublisherFormModel[audio]',
                function (response) {
                    if (response.responseCode == 200)  {
                        if (that.publisherIsActive) togglePublisher$.click ();
                        $.mobile.activePage.append ($(response.response).find ('.refresh-content'));
                        x2.main.refreshContent ();
                        $.mobile.loading ('hide');
                    } else {
                        $.mobile.loading ('hide');
                        x2.main.alert (that.translations['Upload failed'], that.translations['Error']);
                    }
                },
                function (error) {
                    $.mobile.loading ('hide');
                    x2.main.alert (error.body, that.translations['Error']);
                }
            );
        }
    });
    
    new x2.VideoButton ({
        element$: videoButton$,
        success: function (data) {
            var attachment$ = x2.mobileForm.makeVideoAttachment (data.type,data.fullPath);
            attachment$.hide ();
            that.form$.find ('.' + x2.mobileForm.videoAttachmentClass).remove ();
            that.form$.append (attachment$);
            $.mobile.loading ('show');
            x2.mobileForm.submitWithVideo (
                data.type,
                that.form$.attr ('action'), 
                that.form$, 
                'EventCommentPublisherFormModel[video]',
                function (response) {
                    if (response.responseCode == 200)  {
                        if (that.publisherIsActive) togglePublisher$.click ();
                        $.mobile.activePage.append ($(response.response).find ('.refresh-content'));
                        x2.main.refreshContent ();
                        $.mobile.loading ('hide');
                    } else {
                        $.mobile.loading ('hide');
                        x2.main.alert (that.translations['Upload failed'], that.translations['Error']);
                    }
                },
                function (error) {
                    $.mobile.loading ('hide');
                    x2.main.alert (error.body, that.translations['Error']);
                }
            );
        }
    });

    this.locationButton$ = $.mobile.activePage.find ('.location-attach-button');
    this.locationButton$.click (function () {
        if (x2.main.isPhoneGap) {
            x2touch.API.getCurrentPosition(function(position) {
                var pos = {
                   lat: position.coords.latitude,
                   lon: position.coords.longitude
                 };

                that.form$.find ('#geoCoords').val(JSON.stringify (pos));
                that.form$.find ('#geoLocationCoords').val("set");
                x2.mobileForm.submitWithFiles (
                   that.form$, 
                   function (response) {
                       try {
                            var data = JSON.parse(response);
                            var theAddress = data['results'][0]['formatted_address'];
                            $.mobile.activePage.find.find ('.reply-box').val(
                                $.mobile.activePage.find ('.reply-box').val()+" - "+theAddress
                            );
                       } catch (e) {
                           alert(that.translations['failed to parse response from server']);
                       }

                       x2.main.refreshContent ();
                       $.mobile.loading ('hide');
                   }, function (jqXHR, textStatus, errorThrown) {
                       $.mobile.loading ('hide');
                       x2.main.alert (textStatus, that.translations['Error']);
                   }
               ); 
               this.form$.find ('#geoLocationCoords').val("unset");
            }, function (error) {
                alert(that.translations['error code']+': ' + error.code    + '\n' +
                      that.translations['error message']+': ' + error.message + '\n');
            }, {});         
        
        } 
        
    });
};

EventCommentPublisherController.prototype.init = function () {
    x2.Controller.prototype.init.call (this);
    var that = this;
    this.documentEvents.push (x2.main.onPageShow (function () {
        that.setUpForm ();
    }, 'EventCommentPublisherController'));
};

return EventCommentPublisherController;

}) ();
