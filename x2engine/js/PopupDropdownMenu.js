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




/**
 * Creates a popup dropdown menu 
 */

function PopupDropdownMenu (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        containerElemSelector: '', // the container to be turned into a popup dropdown menu
        openButtonSelector: '',// the button which opens/closes the popup dropdown menu
        onClose: function () {}, // function to be called when menu is closed
        autoClose: true, // if true, menu is closed on click inside
        closeOnClickOutside: true, // if true, menu is closed on click outside

        // used to determine which elements can be clicked without closing the drop down 
        onClickOutsideSelector: null, 
        defaultOrientation: 'right', 
        css: {} // css to be applied to the popup dropdown menu on open
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    if (this.onClickOutsideSelector === null) {
        this.onClickOutsideSelector = this.containerElemSelector + ', ' + this.openButtonSelector;
    }
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

/**
 * close menu 
 */
PopupDropdownMenu.prototype.close = function () {
    var that = this; 
    that._containerElem.attr ('style', '');
    that._containerElem.hide ();

    that.onClose ()
};

/*
Private instance methods
*/

PopupDropdownMenu.prototype._positionMenuLeft = function () {
    var that = this;
    that._containerElem.position ({
        my: 'right top', 
        at: 'left+22 bottom',
        of: that._openButton,
        using: function (css) {
            that._containerElem.css ($.extend (css, that.css));
        }
    });
    that._containerElem.addClass ('flipped');
};

PopupDropdownMenu.prototype._positionMenuRight = function () {
    var that = this;
    that._containerElem.css ($.extend ({
        top: that._openButton.offset ().top + 26,
        left: that._openButton.offset ().left - 3
    }, that.css));
    that._containerElem.removeClass ('flipped');
};

/**
 * position menu below button 
 */
PopupDropdownMenu.prototype._positionMenu = function () {
    var that = this; 

    if (that.defaultOrientation === 'left') {
        if (that._openButton.offset ().left - that._containerElem.width () > 0) {

            that._positionMenuLeft ();
            return;
        } else if (
            that._openButton.offset ().left + that._containerElem.width () > $(window).width ()) {

            that._positionMenuRight ();
            return;
        }

    } else {
        if (
            that._openButton.offset ().left + that._containerElem.width () > $(window).width ()) {

            that._positionMenuLeft ();
            return;
        } else if (that._openButton.offset ().left - that._containerElem.width () > 0) {

            that._positionMenuRight ();
            return;
        }
    }
    that._positionMenuLeft ();
};

/**
 * Sets up event which opens/closes dropdown menu 
 */
PopupDropdownMenu.prototype._setUpOpenButtonBehavior = function () {
    var that = this; 
    that._openButton.unbind ('click.PopupDropdownMenu._setUpOpenButtonBehavior').
        bind ('click.PopupDropdownMenu._setUpOpenButtonBehavior', function (evt) {

        evt.preventDefault ();
        if (!that._containerElem.is (':visible')) {
            that._positionMenu ();
            that._containerElem.fadeIn (100);
            if (that.autoClose) {
                $(document).one ('click', function () {
                    that.close ();
                });
            }
            if (that.closeOnClickOutside) {
                auxlib.onClickOutside (
                    $(that.onClickOutsideSelector), 
                    function () { that.close (); }, true);
            }
        } else {
            that.close ();
            return true;
        }
        return false;
    });
};

PopupDropdownMenu.prototype._init = function () {
    var that = this; 
    that._openButton = $(this.openButtonSelector);
    that._containerElem = $(this.containerElemSelector);
    that._containerElem.addClass ('popup-dropdown-menu');
    that._containerElem.css (this.css);
    that._setUpOpenButtonBehavior ();

    // hide menu on resize
    $(window).resize (function (e) {
        if (that._containerElem.is (':visible')) {
            that.close ();
        }
    });
};


(function () {

/*
Auto-instantiates popup dropdown menus.
For this to be used, the elements must be set up as follows:

-there must be a button element with an id and the class 'x2-popup-dropdown-button'
-there must be a menu container element with an id and the class 'x2-popup-dropdown-menu'.
 this element must directly follow the button element
*/
$('.x2-popup-dropdown-button').each (function () {
    var containerElemSelector;
    if ($(this).next ('.x2-popup-dropdown-menu').length) {
        containerElemSelector = '#' + $(this).next ().attr ('id');
    } else if ($(this).attr ('data-menu-selector')) {
        containerElemSelector = $(this).attr ('data-menu-selector');
    }
    auxlib.assert (typeof containerElemSelector !== 'undefined');

    new PopupDropdownMenu ({
        containerElemSelector: containerElemSelector,
        openButtonSelector: '#' + $(this).attr ('id'),
        defaultOrientation: 'left'
    });
});

}) ();
