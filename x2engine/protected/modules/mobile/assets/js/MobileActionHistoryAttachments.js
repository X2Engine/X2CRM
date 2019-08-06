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




x2.MobileActionHistoryAttachments = (function () {

function MobileActionHistoryAttachments (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);
    this.init ();
}

MobileActionHistoryAttachments.prototype = auxlib.create (x2.Widget.prototype);

MobileActionHistoryAttachments.prototype.setUpLocationPhotoUpload = function () {
    var that = this;
    var form$ = $.mobile.activePage.find ('.publisher-file-upload-form');
    this.form$ = form$;
    var publisher$ = $.mobile.activePage.find ('.publisher-menu');
    var buttons$ = publisher$.find ('ul li');
    var togglePublisher$ = $.mobile.activePage.find ('#publisher-menu-button');

    new x2.LocationButton ({
        element$: buttons$.filter ('.location-attachment-button'),
        success: function (position) {
            var pos = {
               lat: position.coords.latitude,
               lon: position.coords.longitude
             };
            that.form$.find ('#geoCoords').val(JSON.stringify (pos));
            that.form$.find ('#geoLocationCoords').val("set");
            $.mobile.loading ('show');
            x2.mobileForm.submitWithFiles (
               that.form$, 
               function (response) {
                    try {
                        if (that.publisherIsActive) togglePublisher$.click ();
                        $.mobile.activePage.append ($(response).find ('.refresh-content'));
                        x2.main.refreshContent ();
                        $.mobile.loading ('hide');
                    } catch (e) {
                        alert(e);
                    }
               }, function (jqXHR, textStatus, errorThrown) {
                   $.mobile.loading ('hide');
                   x2.main.alert (textStatus, 'Error');
               }
           ); 
           that.form$.find ('#geoLocationCoords').val("unset");
        }
    });



 
};

MobileActionHistoryAttachments.prototype.setUpVideoUpload = function () {
    var that = this;
    var form$ = $.mobile.activePage.find ('.publisher-file-upload-form');
    this.form$ = form$;
    var publisher$ = $.mobile.activePage.find ('.publisher-menu');
    var buttons$ = publisher$.find ('ul li');
    var togglePublisher$ = $.mobile.activePage.find ('#publisher-menu-button');


    new x2.VideoButton ({
        element$: buttons$.filter ('.video-attachment-button'),
        success: function (data) {
            var attachment$ = x2.mobileForm.makeVideoAttachment (data.type, data.fullPath);
            attachment$.hide ();
            that.form$.find ('.' + x2.mobileForm.videoAttachmentClass).remove ();
            that.form$.append (attachment$);
            $.mobile.loading ('show');
            x2.mobileForm.submitWithVideo (
                data.type,
                that.form$.attr ('action'), 
                that.form$, 
                'Actions[upload]',
                function (response) {
                    if (response.responseCode == 200)  {
                        if (that.publisherIsActive) togglePublisher$.click ();
                        $.mobile.activePage.append ($(response.response).find ('.refresh-content'));
                        x2.main.refreshContent ();
                        $.mobile.loading ('hide');
                    } else {
                        $.mobile.loading ('hide');
                        x2.main.alert ('Upload failed', 'Error');
                    }
                },
                function (error) {
                    $.mobile.loading ('hide');
                    x2.main.alert (error.body, 'Error');
                }
            );
        }
    });


 
};

MobileActionHistoryAttachments.prototype.setUpAudioUpload = function () {
    var that = this;
    var form$ = $.mobile.activePage.find ('.publisher-file-upload-form');
    this.form$ = form$;
    var publisher$ = $.mobile.activePage.find ('.publisher-menu');
    var buttons$ = publisher$.find ('ul li');
    var togglePublisher$ = $.mobile.activePage.find ('#publisher-menu-button');

    new x2.AudioButton ({
        element$: buttons$.filter ('.audio-attachment-button'),
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
                'Actions[upload]',
                function (response) {
                    if (response.responseCode == 200)  {
                        if (that.publisherIsActive) togglePublisher$.click ();
                        $.mobile.activePage.append ($(response.response).find ('.refresh-content'));
                        x2.main.refreshContent ();
                        $.mobile.loading ('hide');
                    } else {
                        $.mobile.loading ('hide');
                        x2.main.alert ('Upload failed', 'Error');
                    }
                },
                function (error) {
                    $.mobile.loading ('hide');
                    x2.main.alert (error.body, 'Error');
                }
            );
        }
    });


 
};
 
MobileActionHistoryAttachments.prototype.setUpPhotoUpload = function () {
    var that = this;
    var form$ = $.mobile.activePage.find ('.publisher-photo-upload-form');
    this.form$ = form$;
    var publisher$ = $.mobile.activePage.find ('.publisher-menu');
    var buttons$ = publisher$.find ('ul li');
    var togglePublisher$ = $.mobile.activePage.find ('#publisher-menu-button');
    new x2.CameraButton ({
        element$: buttons$.filter ('.photo-attachment-button'),
        success: function (data) {
            var attachment$ = x2.mobileForm.makePhotoAttachment (data);
            attachment$.hide ();
            that.form$.find ('.' + x2.mobileForm.photoAttachmentClass).remove ();
            that.form$.append (attachment$);
            $.mobile.loading ('show');
            x2.mobileForm.submitWithPhotos (
                that.form$.attr ('action'), 
                that.form$, 
                'Actions[upload]',
                function (response) {
                    if (response.responseCode == 200)  {
                        if (that.publisherIsActive) togglePublisher$.click ();
                        $.mobile.activePage.append ($(response.response).find ('.refresh-content'));
                        x2.main.refreshContent ();
                        $.mobile.loading ('hide');
                    } else {
                        $.mobile.loading ('hide');
                        x2.main.alert ('Upload failed', 'Error');
                    }
                },
                function (error) {
                    $.mobile.loading ('hide');
                    x2.main.alert (error.body, 'Error');
                }
            );
        }
    });
};
 

MobileActionHistoryAttachments.prototype.setUpFileUpload = function () {
    var that = this;
    var form$ = $.mobile.activePage.find ('.publisher-file-upload-form');
    var togglePublisher$ = $.mobile.activePage.find ('#publisher-menu-button');
    this.form$ = form$;
    that.form$.off ('change.setUpFileUpload').on ('change.setUpFileUpload', function () {
        $.mobile.loading ('show');
        x2.mobileForm.submitWithFiles (
            that.form$, 
            function (data) {
                if (that.publisherIsActive) togglePublisher$.click ();
                $.mobile.activePage.append ($(data).find ('.refresh-content'));
                x2.main.refreshContent ();
                that.form$.find ('input[type="file"]').val ('');
                $.mobile.loading ('hide');
            }, function (jqXHR, textStatus, errorThrown) {
                $.mobile.loading ('hide');
                x2.main.alert (textStatus, 'Error');
        });
        
    });
};

MobileActionHistoryAttachments.prototype.setUpPublisher = function () {
    var that = this;
    var publisher$ = $.mobile.activePage.find ('.publisher-menu');
    var buttons$ = publisher$.find ('ul li');
    var togglePublisher$ = $.mobile.activePage.find ('.publisher-menu-button');

    // set up open/close behavior of publisher
    this.publisherIsActive = false;
    var clickOutEvt;
    togglePublisher$.click (function () {
        // add classes which trigger animation
        publisher$.toggleClass ('active', !that.publisherIsActive);
        $(this).toggleClass ('inactive', that.publisherIsActive);
        $(this).toggleClass ('active', !that.publisherIsActive);
        if (that.publisherIsActive)
            $('.ui-content').trigger ('scroll.footerFix')
        that.publisherIsActive = !that.publisherIsActive;

        // allow click outside event to trigger menu close
        if (clickOutEvt) publisher$.unbind (clickOutEvt);
        clickOutEvt = auxlib.onClickOutside (publisher$, function () {
            if (that.publisherIsActive)
                togglePublisher$.click (); 
        }, true);
        return false;
    });
    
    that.setUpLocationPhotoUpload (); 
     
    that.setUpPhotoUpload (); 
    
    that.setUpFileUpload (); 
    
    that.setUpVideoUpload ();
    
    that.setUpAudioUpload ();
    
};

MobileActionHistoryAttachments.prototype.init = function () {
    var that = this;

    x2.main.onPageShow (function(){ 
        that.setUpPublisher ();
    });
};

return MobileActionHistoryAttachments;

}) ();
