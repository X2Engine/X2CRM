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
if (typeof x2.gridViewStickyHeader === 'undefined') {

x2.GridViewStickyHeader = (function () {

function GridViewStickyHeader (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        gridId: null,
        DEBUG: true && x2.DEBUG
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._isStuck;
    this._cachedTitleContainerOffsetTop;
    this._columnSelectorWasVisible;

    this._headerContainer =
        $('#' + this.gridId).find ('.x2grid-header-container');
    this._titleContainer = $('#x2-gridview-top-bar-outer');
    this._bodyContainer =
        $('#' + this.gridId).find ('.x2grid-body-container');
    this._pagerHeight =
        $('#' + this.gridId).find ('.pager').length ?
            $('#' + this.gridId).find ('.pager').height () : 7;
    this._stickyHeaderHeight =
        $(this._headerContainer).height () +
        $(this._titleContainer).height ();
    this._x2TitleBarHeight = $('#header-inner').height ();


    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

GridViewStickyHeader.prototype.bodyContainer = function () {
    return $('#' + this.gridId).find ('.x2grid-body-container');
};

GridViewStickyHeader.prototype.getIsStuck = function () {
    return this._isStuck;
};

GridViewStickyHeader.prototype.makeStickyForMobile = function () {
    this._stateBeforeMobile = this._isStuck;
    this.makeSticky ();
};

GridViewStickyHeader.prototype.makeUnstickyForMobile = function () {
    if (this._stateBeforeMobile)
        this.makeUnsticky ();
};

GridViewStickyHeader.prototype.makeSticky = function () {
    var bodyContainer = this.bodyContainer ();
    var $titleBar =
        $('#x2-gridview-top-bar-outer').
            removeClass ('x2-gridview-fixed-top-bar-outer')

    $(bodyContainer).find ('table').
        removeClass ('x2-gridview-body-with-fixed-header');
    $(bodyContainer).find ('table').addClass ('x2-gridview-body-without-fixed-header');

    $('.column-selector').addClass ('stuck');
    $('#' + this.gridId + 'more-drop-down-list').
        addClass ('stuck');
    this._isStuck = true;
};

GridViewStickyHeader.prototype.makeUnsticky = function () {
    var bodyContainer = this.bodyContainer ();
    var $titleBar =
        $('#x2-gridview-top-bar-outer').addClass ('x2-gridview-fixed-top-bar-outer')
    $(bodyContainer).find ('table').addClass ('x2-gridview-body-with-fixed-header');
    $(bodyContainer).find ('table').removeClass ('x2-gridview-body-without-fixed-header');

    $('.column-selector').removeClass ('stuck');
    $('#' + this.gridId + 'more-drop-down-list').
        removeClass ('stuck');
    this._isStuck = false;
};

/*
Bound to window scroll event. Check if the grid header should be made sticky.
*/
GridViewStickyHeader.prototype.checkX2GridViewHeaderSticky = function () {
    var that = this;

    if (this._isStuck) return;

    var headerContainer = this._headerContainer;
    var titleContainer = this._titleContainer;
    var bodyContainer = this._bodyContainer;
    var pagerHeight = this._pagerHeight;
    var stickyHeaderHeight = this._stickyHeaderHeight;
    var x2TitleBarHeight = this._x2TitleBarHeight;

    // check if none of grid view body is visible
    if (($(bodyContainer).offset ().top + $(bodyContainer).height ()) -
        ($(window).scrollTop () + stickyHeaderHeight + x2TitleBarHeight + 5) < 0) {

        //x2.gridviewStickyHeader.isStuck = true;
        this.DEBUG && console.log ('sticky');

        $(titleContainer).hide ();
        if ($('#' + this.gridId + 'more-drop-down-list').length) {
            if ($('#' + this.gridId + 'more-drop-down-list').is (':visible')) {
                x2.gridViewStickyHeader.listWasVisible = true;
                $('#' + this.gridId + 'more-drop-down-list').hide ();
            } else {
                x2.gridViewStickyHeader.listWasVisible = false;
            }
        }
        //$('#' + this.gridId + 'more-drop-down-list').hide ();

        /* unfix header */
        //$(bodyContainer).hide ();
        /*var \$titleBar =
            $('#x2-gridview-top-bar-outer').removeClass (
                'x2-gridview-fixed-top-bar-outer')
        \$titleBar.attr (
            'style', 'margin-top: ' +
            (($(bodyContainer).height () - stickyHeaderHeight - pagerHeight) + 5) +
            'px');*/

        // hide mass actions dropdown
        /*if ($('#more-drop-down-list').length) {
            if ($('#more-drop-down-list').is (':visible')) {
                x2.gridviewStickyHeader.listWasVisible = true;
                $('#more-drop-down-list').hide ();
            } else {
                x2.gridviewStickyHeader.listWasVisible = false;
            }
        }*/

        if ($('.column-selector').length) {
            if ($('.column-selector').is (':visible')) {
                this._columnSelectorWasVisible = true;
                $('.column-selector').hide ();
            } else {
                this._columnSelectorWasVisible = false;
            }
        }

        $(window).unbind ('scroll.stickyHeader').
            bind ('scroll.stickyHeader',
                function () { that.checkX2GridViewHeaderUnsticky (); });

        this._cachedTitleContainerOffsetTop =
            $(titleContainer).offset ().top;
    } else {
        return false;
    }
};

/*
Bound to window scroll event. Check if the grid header should be made fixed.
*/
GridViewStickyHeader.prototype.checkX2GridViewHeaderUnsticky = function () {
    var that = this;
    var titleContainer = this._titleContainer;
    var x2TitleBarHeight = this._x2TitleBarHeight;


    // check if grid header needs to be made unsticky
    if ((($(window).scrollTop () + x2TitleBarHeight) -
        this._cachedTitleContainerOffsetTop) < 20) {
        //x2.gridviewStickyHeader.DEBUG && console.log ('unsticky');

        $(titleContainer).show ();
        if (x2.gridViewStickyHeader.listWasVisible &&
                $('#' + this.gridId + 'more-drop-down-list').length) {
            $('#' + this.gridId + 'more-drop-down-list').show ();
        }

        /*var bodyContainer = x2.gridviewStickyHeader.bodyContainer;
        x2.gridviewStickyHeader.isStuck = false;*/

        /* fix header */
        /*var \$titleBar =
            $('#x2-gridview-top-bar-outer').
                addClass ('x2-gridview-fixed-top-bar-outer');
        \$titleBar.attr ('style', '');
        $(bodyContainer).show ();*/

        //for (var i = 0; i < 1000; ++i) console.log (i);

        // show mass actions dropdown
        /*if (x2.gridviewStickyHeader.listWasVisible &&
              $('#more-drop-down-list').length) {
            $('#more-drop-down-list').show ();
        }*/
        if (this._columnSelectorWasVisible &&
            $('.column-selector').length &&
            $('.column-selector-link').hasClass ('clicked')) {

            $('.column-selector').show ();
        }

        $(window).unbind ('scroll.stickyHeader').
            bind ('scroll.stickyHeader', function () { 
                that.checkX2GridViewHeaderSticky (); 
            });
    }
};


/*
Private instance methods
*/

GridViewStickyHeader.prototype._init = function () {
};

return GridViewStickyHeader;

}) ();

}

