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

x2.ComboBox = (function () {

/**
 * @class
 * @name ComboBox autocomplete dropdown element with ajax-loaded options
 */
function ComboBox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        /**
         * @var object|string selector for input element which will be converted to a combo box or
         *  the input element itself
         */
        element: null,
        /**
         * @var string url to request to get items to populate dropdown
         */
        getItemsUrl: null,
        /**
         * @var int size of pages requested from options dropdown
         */
        pageSize: 20,
        /**
         * @var function function to call when option is clicked 
         */
        optionClick: function (option$) {},
        /**
         * @var function function to call when options are shown
         */
        optionClick: function (option$) {},
        onShow: function () {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this.element$ = $(this.element);
    this._dropdown$ = null; // dropdown element to be populated with search results
    this._button$ = null; // button element which opens dropdown
    this._page = null; // the page of the most recently returned results
    this._cachedStartPage = null; // page number of first page in options dropdown
    this._cachedEndPage = null; // page number of last page in options dropdown
    // max number of pages of options that can be stored in dropdown before auto-removal occurs
    this._maxCachedPages = 10; 
    this._mode = null; // whether filtered or unfiltered results are displayed
    this._changed = false; // whether the user has changed the input value

    this._init ();
}

/**
 * Refresh options in dropdown based on search string
 * @param bool all determines whether or not search string is used
 */
ComboBox.prototype.refreshDropdown = function (all) {
    var all = typeof all === 'undefined' ? true : all; 
    var that = this;
    if (this._mode === 'all' && all) { 
        this._showOptions ();
        return;
    }

    $.ajax ({ 
        url: this.getItemsUrl,
        type: 'POST',
        data: {
            prefix: all ? '' : this.element$.val (),
            page: 0,
            pageSize: this.pageSize
        },
        dataType: 'json',
        success: function (data) {
            that._mode = all ? 'all' : 'filtered';
            //console.log ('data = ');
                //console.log (data);
            if (!data.length) {
                that._hideOptions ();
            } else {
                that._page = 0;
                that._cachedStartPage = that._cachedEndPage = 0;
                that._addOptions (data);
                that._showOptions (true);
            }
        }
    });
};

/**
 * Retrieve page of results and add to options dropdown.
 * @param bool next if true, next page of results will be requested. Otherwise, previous page will
 *  be requested.
 * @param bool all determines whether or not search string is used
 */
ComboBox.prototype._getPage = function (next, all) {
    var next = typeof next === 'undefined' ? true : next; 
    var all = typeof all === 'undefined' ? false : all; 
    // display all options for inputs on updated records
    if (!this._changed) all = true;
    // can't get previous page if we already have the first page
    if (this._cachedStartPage === 0 && !next) return;

    var that = this;
    $.ajax ({ 
        url: this.getItemsUrl,
        type: 'POST',
        data: {
            prefix: all ? '' : this.element$.val (),
            page: next ? this._cachedEndPage + 1 : this._cachedStartPage - 1,
            pageSize: this.pageSize
        },
        dataType: 'json',
        success: function (data) {
            if (data.length) {
                if (next) {
                    that._cachedEndPage++;
                } else {
                    that._cachedStartPage--;
                }
                that._addOptions (data, next, false);
                that._showOptions ();
                that._invalidateCacheEntries (next);
            }
        }
    });
};

/**
 * Remove pages from dropdown options until max cached pages is no longer exceeded 
 * @param bool startAtTop if true, pages will be removed one at a time from the top of the
 *  options dropdown. Otherwise, removal will start at the bottom.
 */
ComboBox.prototype._invalidateCacheEntries = function (startAtTop) {
    var that = this;
    var startAtTop = typeof startAtTop === 'undefined' ? true : startAtTop; 
    var debug = 10;

    if (that._cachedEndPage - that._cachedStartPage + 1 > that._maxCachedPages) {

        if (startAtTop) {
            // save old scroll position info
            var originalScrollPos = this._dropdown$.scrollTop ();
            var originalScrollHeight = this._dropdown$.prop ('scrollHeight');
            var scrollBottom = originalScrollHeight - originalScrollPos;
        }

        // clear cached pages
        do {
            if (!debug--) break;
            if (startAtTop) {
                that._cachedStartPage++;
                this._dropdown$.find ('li').slice (0, this.pageSize).remove (); 
            } else {
                that._cachedEndPage--;
                $($.makeArray (this._dropdown$.find ('li')).reverse ()).slice (0, this.pageSize).
                    remove (); 
            }
        } while (that._cachedEndPage - that._cachedStartPage + 1 > that._maxCachedPages);

        if (startAtTop) {
            // restore scroll position
            var newScrollPos = this._dropdown$.prop ('scrollHeight') - scrollBottom;
            this._dropdown$.scrollTop (newScrollPos);
        }
    }
};

/**
 * Show the options dropdown 
 * @param bool scrollToTop If true, dropdown options container will be scrolled to the top
 */
ComboBox.prototype._showOptions = function (scrollToTop) {
    var scrollToTop = typeof scrollToTop === 'undefined' ? false : scrollToTop; 
    this._dropdown$.show ();
    if (scrollToTop) {
        this._dropdown$.scrollTop (0);
    }
    this.onShow ();
};

/**
 * Hide the options dropdown 
 */
ComboBox.prototype._hideOptions = function () {
    this._dropdown$.hide ();
};

/**
 * Adds options to the options dropdown 
 * @param array options names of records indexed by value
 * @param bool append if true, options will be appended to the dropdown. Otherwise options will
 *  be prepended to the dropdown
 * @param bool replace if true, dropdown options will be replaced
 */
ComboBox.prototype._addOptions = function (options, append, replace) {
    var append = typeof append === 'undefined' ? true : append; 
    var replace = typeof replace === 'undefined' ? true : replace; 
    if (replace) {
        this._dropdown$.find ('li').remove ();
    }
    var operation = append ? 'append' : 'prepend';
    if (!append) {
        // save scroll position
        var originalScrollPos = this._dropdown$.prop ('scrollHeight');
        // reverse options so that they get prepended in the correct order
        options = options.reverse ();
    }

    for (var i = 0; i < options.length; i++) {
        var name = options[i][0];
        var value = options[i][1];
        var listItem$ = $('<li>', {
            text: name
        });
        listItem$.data ('data-val', value);
        this._dropdown$.find ('ul')[operation] (listItem$);
    }

    if (!append) {
        // restore scroll position
        this._dropdown$.scrollTop (this._dropdown$.prop ('scrollHeight') - originalScrollPos);
    }
};

/**
 * Add combo box related elements (button, button image, dropdown) 
 */
ComboBox.prototype._setUpElements = function () {
    this.element$.addClass ('x2-combo-box-input');
    this.element$.attr ('autocomplete', 'off');
    this._button$ = $('<button>', {
        type: 'button', 
        'class': 'x2-combo-box-button x2-button',
        html: $('<img>', {
            src: yii.themeBaseUrl + '/css/gridview/arrow_down.png'
        })
    });
    this._dropdown$ = $('<div>', {
        'class': 'x2-combo-box-dropdown',
        style: 'display: none;',
        html: $('<ul>')
    });
    this.element$.after (this._button$);
    this._button$.after (this._dropdown$);
};

/**
 * Set up behavior of combo box 
 */
ComboBox.prototype._setUpEvents = function () {
    var that = this;  
    this.element$.keyup (function () {
        that.refreshDropdown (false); // display filtered options
        that._changed = true;
    });
    this._button$.click (function () {
        if (that._dropdown$.is (':visible')) {
            that._hideOptions ();
        } else {
            that.refreshDropdown (); // display unfiltered options 
        }
    });
    this._dropdown$.on ('click', 'li', function () {
        that.optionClick ($(this));
        that._hideOptions ();
    });

    auxlib.onClickOutside (
        '.x2-combo-box-dropdown, .x2-combo-box-button, ' +
        this.element$.selector, function () {
        that._hideOptions ();
    });

    // TODO: replace this with calls to InfinityScroll.js 

    // fetch next or previous page if user scrolls to the end or beginning of the options list
    var pause = null;
    var prevScrollPos = 0;
    var movingDown = true;
    var mousedown = false;
    $(document).on ('mousedown.ComboBox::setUpEvents', function () {
        mousedown = true;
    });
    $(document).on ('mouseup.ComboBox::setUpEvents', function () {
        mousedown = false;
    });
    this._dropdown$.scroll (function () {
        var scrollHeight = $(this).prop ('scrollHeight'); 
        var clientHeight = $(this).prop ('clientHeight'); 
        var scrollPos = $(this).scrollTop ();
        var movingDown = scrollPos >= prevScrollPos;
        var movingUp = scrollPos <= prevScrollPos;

        if (!pause) {
            var setPause = false;
            if (movingDown && scrollHeight - scrollPos - clientHeight < 10) {
                //console.log ('next page');
                that._getPage (true, false);
                setPause = true;
            } else if (movingUp && scrollPos < 10) {
                //console.log ('prev page');
                that._getPage (false, false);
                setPause = true;
            }
            if (setPause) {
                pause = window.setTimeout (function () { 
                    pause = null;
                    // Check again to see if we're still past the pagination threshold.
                    // This check is necessary if user drags and holds scroll bar to the bottom
                    // of the container.
                    if (mousedown) that._dropdown$.scroll (); 
                }, 300)
            }
        }
        prevScrollPos = scrollPos;
    });
};

ComboBox.prototype._init = function () {
    //console.log ('init');
    this._setUpElements ();
    this._setUpEvents ();
};

return ComboBox;

}) ();
