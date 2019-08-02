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

x2.RecordIndexControllerBase = (function () {

function RecordIndexControllerBase (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        hasSettingsMenu: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Controller.call (this, argsDict);
}

RecordIndexControllerBase.prototype = auxlib.create (x2.Controller.prototype);

RecordIndexControllerBase.prototype.setUpPagination = function () {
    var that = this;
    this.listView$ = x2.main.activePage$.find ('.record-index-list-view');
    this.moreButton$ = this.listView$.find ('.more-button');

    this.moreButton$.unbind ('click.setUpPagination').bind ('click.setUpPagination', function () {
        that.fetchPage ($(this).attr ('href'));
        return false;
    });
};

/**
 * Fetch next page of records and append to the list view. Also replace the more button and rebind
 * its click event handler.
 */
RecordIndexControllerBase.prototype.fetchPage = function (url) {
    var that = this;
    var listView$;
    $.mobile.loading ('show');
    $.ajax ({
        method: 'GET', 
        url: url,
        success: function (data) {
            $.mobile.loading ('hide');
            if (listView$ = $(data).find ('.record-index-list-view')) {
                that.listView$.find ('.items').append (listView$.find ('.items'));
                that.moreButton$.replaceWith (listView$.find ('.more-button'));
                that.setUpPagination ();
            }
        }
    });
};

/**
 * Refresh just the page results 
 */
RecordIndexControllerBase.prototype.refreshResults = function (url, data) {
    var that = this;
    var listView$;
    $.ajax ({
        method: 'GET', 
        url: url,
        data: data,
        success: function (data) {
            if (listView$ = $(data).find ('.record-index-list-view')) {
                that.listView$.replaceWith (listView$);
                that.setUpPagination ();
            }
        }
    });
};


RecordIndexControllerBase.prototype.init = function () {
    var that = this;
    this.documentEvents.push (x2.main.onPageShow (function () {
        that.setUpPagination ();
    }, this.constructor.name));
};


return RecordIndexControllerBase;

}) ();
