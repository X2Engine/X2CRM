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

TwitterFeedWidget = (function () {

function TwitterFeedWidget (argsDict) {
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        lastTweetId: null,
        screenName: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    SortableWidget.call (this, argsDict);
}

TwitterFeedWidget.prototype = auxlib.create (SortableWidget.prototype);

TwitterFeedWidget.prototype._loadMoreTweets = function (reload) {
    reload = typeof reload === 'undefined' ? false : reload; 
    var that = this;
    var data = {
        twitterFeedAjax: true,
    };
    if (!reload) {
        data.maxTweetId = this.lastTweetId;
    }
    if (this.screenName) {
        data.twitterScreenName = this.screenName;
    }
    $.ajax ({
        url: window.location.href,
        type: 'GET',
        data: data,
        success: function (data) {
            that._listView$.replaceWith (data);
            that._listView$ = that.element.find ('.list-view');
            if (reload) {
                that._origListViewHeight = that._listView$.height ();
            }
            that._listView$.parent ().css ({
                'max-height': that._origListViewHeight + 'px',
                'overflow-y': 'auto'
            });
        },
        error: function (data) {
            x2.topFlashes.displayFlash (data.responseText, 'error', 'clickOutside', true);
        }
    });

};

TwitterFeedWidget.prototype._setUpPaginationButtonBehavior = function () {
    var that = this;
    this._paginationButton$.click (function () {
        that._loadMoreTweets (); 
    });
};

TwitterFeedWidget.prototype._setUpScreenNameSelection = function () {
    var that = this;
    var screenNameSelector$ = $('#screen-name-selector');
    screenNameSelector$.change (function () {
        that.screenName = $.trim ($(this).val ()); 
        that._listView$.parent ().attr ('style', '');
        that._loadMoreTweets (true);
    });
};

TwitterFeedWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    if (!this.hasError) {
        this._paginationButton$ = this.element.find ('.load-more-tweets-button');
        this._listView$ = this.element.find ('.list-view');
        this._origListViewHeight = this._listView$.height ();
        this._setUpPaginationButtonBehavior ();
        this._setUpScreenNameSelection ();
    }
};

return TwitterFeedWidget;

}) ();



