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




if (typeof x2 === 'undefined') x2 = {};

x2.RecordViewController = (function () {

function RecordViewController (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        hasSettingsMenu: true,
        modelName: null,
        modelEmail: null,
        modelId: null,
        myProfileId: null,
        supportsActionHistory: false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Controller.call (this, argsDict);
}

RecordViewController.prototype = auxlib.create (x2.Controller.prototype);

RecordViewController.prototype.setUpMail = function () {
    var that = this;
    var mailButton$ = $('#header .mail-button');
    mailButton$.click (function () {
        x2touch.API.setEmail (that.modelEmail);
    });
};

RecordViewController.prototype.setUpEdit = function () {
    var editButton$ = $('#header .edit-button');
    editButton$.click (function () {
        $(':mobile-pagecontainer').pagecontainer (
            'change', $(this).attr ('data-x2-url'));
    });
};

RecordViewController.prototype.setUpDelete = function () {
    var that = this;
//    var deleteButton$ = $.mobile.activePage.find ('.delete-button');
//    deleteButton$.click (function () {
//        x2.main.confirm (
//            that.translations.deleteConfirm,
//            '',
//            [
//                that.translations.deleteConfirmOkay,
//                that.translations.deleteConfirmCancel
//            ],
//            function () {
//                $(':mobile-pagecontainer').pagecontainer (
//                    'change', deleteButton$.attr ('href'));
//            }
//        );
//        return false;
//    });

};

RecordViewController.prototype.setUpProfile = function () {
     
    if (this.myProfileId !== this.modelId) return;
    var that = this;
    if (x2.main.isPhoneGap) {
        var avatar$ = $.mobile.activePage.find ('.avatar-image');
        var uploadLink$ = $.mobile.activePage.find ('.photo-upload-link');
        new x2.CameraButton ({
            element$: avatar$.add (uploadLink$),
            //direction: 1, // front-facing
            enableCrop: true,
            validate: function (callback) {
                x2.main.confirm (
                    "Upload a new profile photo?", ' ', ['Okay', 'Cancel'], callback);
            },
            success: function (data) {
                var attachment$ = x2.mobileForm.makePhotoAttachment (data);
                attachment$.hide ();
                avatar$.parent ().find ('.' + x2.mobileForm.photoAttachmentClass).remove ();
                avatar$.parent ().append (attachment$);
                var form$ = avatar$.closest ('form');
                x2.mobileForm.submitWithPhotos (
                    form$.attr ('action'), 
                    form$, 
                    'Profile[photo]',
                    function (response) {
                        if (response.responseCode == 200)  {
                            $.mobile.activePage.append (response.response);
                            x2.main.refreshContent ();
                            that.setUpProfile ();
                        } else {
                            x2.main.alert ('Upload failed', 'Error');
                        }
                    },
                    function (error) {
                        x2.main.alert (error.body, 'Error');
                    }
                );
            },
            failure: function (message) {

            }
        });
    }
     
};

RecordViewController.prototype.swipedInTarget = function (start) {
    return !x2.panel.swipedInTarget (start);
};

RecordViewController.prototype.setUpTabs = function () {
    var that = this;
    var tabs$ = $.mobile.activePage.find ('.record-view-tabs');
    var navBar$ = tabs$.find ('.ui-navbar');
    tabs$.tabs ({
        // fixes mysterious bug which causes jQuery UI to attempt to fetch tabs via ajax when 
        // tabs widget is instantiated. Bug only occurs when making an ajax transition to the
        // record view page.
        beforeLoad: function () { return false; },
        beforeActivate: function (evt, ui) {
            $.mobile.activePage.attr ('data-x2-tab-name', $(ui.newTab).attr ('data-x2-tab-name'));
        },
        activate: function () {
//            if (x2.main.isPhoneGap) {
//                x2.main.instantiateNano ($.mobile.activePage.find ('.ui-content'));
//            }
        }
    });

    // fix for ios. moves navbar one below body preventing display issues due to iOS handling of 
    // fixed positioning
    if (x2.main.platform === 'iOS') {
        $('#header').after (navBar$.detach ());
    }

    function bindSwipe (direction) {
        $(document).off ('swipe' + direction + '.setUpTabs').
            on ('swipe' + direction + '.setUpTabs', function (evt) {

            var pointerMethod = direction === 'left' ? 'next' : 'prev';

            if (that.swipedInTarget (evt.swipestart.coords) &&
                $.mobile.activePage.jqmData ('panel') !== 'open') {

                var activeTab$ = navBar$.find ('.ui-state-active');
                if (activeTab$[pointerMethod] ('li').length) {
                    activeTab$[pointerMethod] ('li').find ('a').click ();
                }
            }
        });
    }

    bindSwipe ('left');
    bindSwipe ('right');

};

RecordViewController.prototype.setUpHistoryPagination = function () {
    var that = this;
    this.listView$ = x2.main.activePage$.find ('.record-index-list-view');
    this.moreButton$ = {};
    
    this.listView$.each(function(index){
        var moreButton = $(this).find ('.more-button');
        if(moreButton.length > 0){
            that.moreButton$[index] = moreButton;
            moreButton.unbind ('click.setUpHistoryPagination').bind ('click.setUpHistoryPagination', function () {
                that.fetchHistoryPage (index, $(this).attr ('href'));
                return false;
            });
        }
    });

    if (x2.main.platform === 'iOS') {
        var homeButton$ = $.mobile.activePage.find('#home-btn');
        homeButton$.hide();
    }
    
    
};

/**
 * Fetch next page of records and append to the list view. Also replace the more button and rebind
 * its click event handler.
 */
RecordViewController.prototype.fetchHistoryPage = function (index, url) {
    var that = this;
    var listView$;
    
    $.mobile.loading ('show');
    $.ajax ({
        method: 'GET', 
        url: url,
        success: function (data) {
            $.mobile.loading ('hide');
            if (listView$ = $(data).find ('.record-index-list-view').eq(index)) {
                that.listView$.eq(index).find ('.items').append (listView$.find ('.items'));
                that.moreButton$[index].replaceWith (listView$.find ('.more-button'));
                that.setUpHistoryPagination ();
            }
        }
    });
};


RecordViewController.prototype.init = function () {
    var that = this;
    this.documentEvents.push (x2.main.onPageShow (function () {
        that.setUpEdit ();
        that.setUpMail ();
        that.setUpDelete ();
        if (that.modelName === 'Profile') {
            that.setUpProfile ();
        }
        if (that.supportsActionHistory) {
            that.setUpTabs ();
            that.setUpHistoryPagination ();
        }
    }, this.constructor.name));
};


return RecordViewController;

}) ();
