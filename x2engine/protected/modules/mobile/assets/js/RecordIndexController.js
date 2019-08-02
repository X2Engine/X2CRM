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

x2.RecordIndexController = (function () {

function RecordIndexController (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        hasSettingsMenu: false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.RecordIndexControllerBase.call (this, argsDict);
}

RecordIndexController.prototype = auxlib.create (x2.RecordIndexControllerBase.prototype);

RecordIndexController.prototype.setUpSearch = function () {
    var that = this;
    var searchButton$ = $('#header .search-button');
    var searchBox$ = x2.main.activePage$.find ('.search-box');
    var searchInput$ = searchBox$.find ('input');

    searchButton$.click (function () {
        //$('#header').toggle (); 
        searchBox$.toggle (); 
        searchBox$.find ('input').focus ();
    });
    searchBox$.find ('.search-cancel-button').click (function () {
        //$('#header').toggle (); 
        searchBox$.toggle (); 
        searchBox$.find ('.search-clear-button').click ();
    });
    searchBox$.find ('.search-clear-button').click (function () {
        if (searchBox$.find ('input').val ()) {
            searchBox$.find ('input').val (''); 
            searchBox$.find ('form').submit ();
        }
    });
    searchInput$.focus (function () {
        that.createButton$.parent ().hide ();
    });
    searchInput$.blur (function () {
        that.createButton$.parent ().show ();
    });

    searchBox$.find ('form').submit (function () {
        var data = {}; 
        data[$(this).find ('input').attr ('name')] = $(this).find ('input').val ();
        that.refreshResults ($(this).attr ('action'), data);
        return false;
    });
};

RecordIndexController.prototype.setUpClickBehavior = function () {
    var clickedLink = false;
    this.listView$.find ('.record-list-item').click (function () {
        if (!clickedLink && $(this).attr ('data-x2-href')) {
            $(':mobile-pagecontainer').pagecontainer (
                'change', $(this).attr ('data-x2-href'), { transition: 'none' }); 
            return false;
        }
        clickedLink = false;
    });
    this.listView$.find ('a').click (function () {
        clickedLink = true;
    });
};

RecordIndexController.prototype.init = function () {
    var that = this;
    x2.RecordIndexControllerBase.prototype.init.call (this);
    this.documentEvents.push (x2.main.onPageShow (function () {
        that.createButton$ = x2.main.activePage$.find ('.record-create-button');
        that.setUpSearch ();
        that.setUpClickBehavior ();
    }, 'RecordIndexController'));
};


return RecordIndexController;

}) ();
