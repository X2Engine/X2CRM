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

GooglePlusProfileWidget = (function () {

function GooglePlusProfileWidget (argsDict) {
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        userId: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    SortableWidget.call (this, argsDict);
}

GooglePlusProfileWidget.prototype = auxlib.create (SortableWidget.prototype);

GooglePlusProfileWidget.prototype.addProfile = function (userId, displayName) {
    var userIdSelector$ = $('#google-plus-user-id-selector');
    userIdSelector$.prepend ($('<option>', {
        value: userId,
        text: displayName
    }));
    auxlib.selectOptionFromSelector (userIdSelector$, userId);
};

GooglePlusProfileWidget.prototype._resizeEvent = function () {
    this._listView$.height (
        this._listView$.height () + this.contentContainer.height () - this._oldContainerHeight);
    this._oldContainerHeight = this.contentContainer.height ();
    this.contentContainer.attr ('style', '');
};

GooglePlusProfileWidget.prototype._setUpResizeBehavior = function () {
    SortableWidget.prototype._setUpResizeBehavior.call (this);
    this._oldContainerHeight = this.contentContainer.height ();
};

GooglePlusProfileWidget.prototype.refresh = function (afterLoad) {
    if (!this.hasError) {
        afterLoad = typeof afterLoad === 'undefined' ? function () {} : afterLoad; 
        var that = this;
        this._listView$ = that.element.find ('.list-view');

        this.resizeHandles = {
            s: this.element.find ('.resize-handle')
        };
        this.element.find ('.resize-handle').addClass ('ui-resizable-handle');
        this.element.find ('.resize-handle').addClass ('ui-resizable-s');
        that._paginationButton$ = that.element.find ('.load-more-posts-button');
        this._setUpUserIdSelection ();
        this._setUpPaginationButtonBehavior ();
        this._setUpVideoPosts ();

        // replace max height with actual height to allow resizing
        var listViewHeight = that._listView$.height ();
        that._listView$.attr ('style', '').height (listViewHeight);
    }
};

GooglePlusProfileWidget.prototype._setUpVideoPosts = function () {
    this.element.find ('.video-container').not ('.video-upload-container').each (function () {
        $(this).find ('.video-image, .video-play-button').click (function () {
            var container$ = $(this).parent ();
            if (container$.find ('iframe').length) {
            } else {
                var imageWidth = container$.find ('img').width ();
                var imageHeight = container$.find ('img').height ();
                container$.find ('.video-image, .video-play-button').hide ();
                container$.append ($('<iframe>').attr ({
                    src: container$.find ('img').attr ('data-embed'),
                    style: 'width: ' + imageWidth + 'px; height: ' + imageHeight + 'px'
                }));
            }
        });
    });
};

GooglePlusProfileWidget.prototype._loadMoreActivities = function (reload) {
    reload = typeof reload === 'undefined' ? false : reload; 
    var that = this;

    var oldHeight = this._listView$.height ();

    var data = {
        googlePlusProfileAjax: true,
    };
    if (!reload) {
        data.maxActivityId = this._listView$.find ('.post-container').length - 1;
    }
    if (this.userId) {
        data.googlePlusUserId = this.userId;
    }

    var scrollPos = this._listView$.scrollTop ();

    $.ajax ({
        url: window.location.href,
        type: 'GET',
        data: data,
        success: function (data) {
            that.contentContainer.html (data);
            that.refresh ();
            that._setUpResizeBehavior ();
            if (!reload) {
                that._listView$.height (oldHeight);
                that._listView$.scrollTop (scrollPos);
            }
        },
        error: function (data) {
            x2.topFlashes.displayFlash (data.responseText, 'error', 'clickOutside', true);
        }
    });
};

GooglePlusProfileWidget.prototype._setUpUserIdSelection = function () {
    var that = this;
    var userIdSelector$ = $('#google-plus-user-id-selector');
    userIdSelector$.off ('change._setUpUserIdSelection').
        on ('change._setUpUserIdSelection', function () {

        that.userId = $.trim ($(this).val ()); 
        that._loadMoreActivities (true);
    });
};

GooglePlusProfileWidget.prototype._setUpPaginationButtonBehavior = function () {
    var that = this;
    this._paginationButton$.off ('click._setUpPaginationButtonBehavior').
        on ('click._setUpPaginationButtonBehavior', function () {

        that._loadMoreActivities (); 
    });
};

GooglePlusProfileWidget.prototype._init = function () {
    if (!this.hasError) {
        this.refresh ();
    }
    SortableWidget.prototype._init.call (this);
};

return GooglePlusProfileWidget;

}) ();



