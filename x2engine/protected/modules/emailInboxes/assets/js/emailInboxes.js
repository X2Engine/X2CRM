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






if (typeof x2 == "undefined") {
    x2 = {};
}

x2.EmailInbox = (function () {

function EmailInbox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        noneSelectedText: null,
        deleteConfirmTxt: null,
        pollTimeout: null,
        emailFolder: null,
        /**
         * @var bool loadMessagesOnPageLoad 
         */
        loadMessagesOnPageLoad: true,
        /**
         * @var bool notConfigured 
         */
        notConfigured: true,
        disableHistory: false,
        gridId: 'email-list',
        updateParams: {
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._pollTimeoutMs = 60 * 1000 * this.pollTimeout;   
    this._emailInboxGridViewManager = this.getGrid ().data ('x2-emailInboxesGridSettings');
    this._init ();
}

EmailInbox.prototype.getGrid = function () {
    return $('#' + this.gridId);
};

/**
 * Initiate a request to perform an email action
 * and update the grid view
 */
EmailInbox.prototype.performEmailAction = function(action, args, complete) {
    var settings = {
        emailAction: action
    };
    $.extend (settings, args);
    this._emailInboxGridViewManager.showIndex ();
    this.update (settings, function () { complete (); });
}

/**
 * Change the currently selected folder
 */
EmailInbox.prototype.selectFolder = function(folder) {
    var that = this;
    that.emailFolder = folder;
    var overrideParams = 0;
    var newUrl = $.param.querystring (window.location.href, {emailFolder: folder}, overrideParams);
    this.getGrid ().find ('.keys').attr ('title', newUrl);
    !this.disableHistory && x2.history.pushState ({emailFolder: folder}, '', newUrl);

    that.performEmailAction ('selectFolder', {
        'emailFolder': folder
    }, function () {
        $('.current-folder').removeClass ('current-folder');
        $('.folder-link').each (function () { 
            that._emailInboxGridViewManager.showIndex ();
            var linkFolder = $(this).attr ('data-folder');
            if (linkFolder === folder) {
                $(this).addClass ('current-folder');
                return false;
            }
        });
    });
};

EmailInbox.prototype.update = function (data, complete) {
    data = typeof data === 'undefined' ? {} : data; 
    complete = typeof complete === 'undefined' ? function () {} : complete; 

    $.fn.yiiGridView.update(this.gridId, {
        type: 'post',
        data: $.extend ({}, this.updateParams, data),
        complete: function(jqXHR, textStatus) {
            complete (jqXHR, textStatus);
        }
    });
};

/**
 * Handle polling for new emails according to admin-defined settings
 */
EmailInbox.prototype.poll = function() {
    var that = this;
    if ($('#reply-form').is (':visible')) {
        window.setTimeout (function () { that.poll (); }, that._pollTimeoutMs);
    } else {
        this.update ({ emailAction: 'refresh' }, function (jqXHR, textStatus) {
            if (textStatus === 'success') 
                window.setTimeout (function () { that.poll (); }, that._pollTimeoutMs);
        });
    }
};

EmailInbox.prototype._setUpPolling = function () {
    var that = this;
    // Fetch the data provider and begin polling
    if (that.loadMessagesOnPageLoad/* && $(this.gridId).length*/) {
        this.update ({}, function (xhr, status) {
            if (status === "success") {
                that.getGrid ().find ('.empty-text-progress-bar').attr ('style', '');
                that.getGrid ().data ('x2EmailInboxesGridSettings').pauseLoadingBar ();
                window.setTimeout(
                    function () { that.poll (); }, that._pollTimeoutMs);
            }
        });
    } else {
        window.setTimeout(function () { that.poll (); }, that._pollTimeoutMs);
    }
};

EmailInbox.prototype._setUpInboxMenu = function () {
    var that = this;
    var inboxMenu$ = $('#inbox-menu');    
    inboxMenu$.find ('.folder-link').click (function () {
        var folder = $(this).attr ('data-folder');
        that.selectFolder (auxlib.htmlDecode (folder));
        return false;
    });

};

/**
 * Bind state change event to preserve browser back button functionality across ajax-loaded
 * pages.
 */
EmailInbox.prototype._setUpHistoryStateChange = function () {
    var that = this;

    !this.disableHistory && x2.history.bind (function () {
        var state = window.History.getState ();
        //console.log ('EmailInbox: state.data = ');
        //console.log (state.data);

        if (typeof state.data['emailFolder'] !== 'undefined') {
            var folder = state.data['emailFolder'];
            if (folder !== that.emailFolder)
                that.selectFolder (folder);
        } else if (that.emailFolder && that.emailFolder !== 'INBOX') {
            that.selectFolder ('INBOX');
        }
    });
};

EmailInbox.prototype._init = function () {
    if (!this.notConfigured) this._setUpPolling ();
    this._setUpInboxMenu ();
    this._setUpHistoryStateChange ();
};

return EmailInbox;

}) ();
