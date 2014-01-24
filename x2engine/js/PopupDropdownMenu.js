/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * Creates a popup dropdown menu 
 */

function PopupDropdownMenu (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        containerElemSelector: '', // the container to be turned into a popup dropdown menu
        openButtonSelector: '',// the button which opens/closes the popup dropdown menu
        onClose: function () {} // function to be called when menu is closed
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
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

/**
 * position menu below button 
 */
PopupDropdownMenu.prototype._positionMenu = function () {
    var that = this; 

    // flip menu if it would go past the right edge of the window
    if (that._openButton.offset ().left + that._containerElem.width () > $(window).width  ()) {
        that._containerElem.position ({
            my: 'right top', 
            at: 'left+22 bottom',
            of: that._openButton
        });
        that._containerElem.addClass ('flipped');
    } else {
        that._containerElem.css ({
            top: that._openButton.offset ().top + 30,
            left: that._openButton.offset ().left 
        });
        that._containerElem.removeClass ('flipped');
    }
};

/**
 * Sets up event which opens/closes dropdown menu 
 */
PopupDropdownMenu.prototype._setUpOpenButtonBehavior = function () {
    var that = this; 
    that._openButton.click (function (evt) {
        evt.preventDefault ();
        if (!that._containerElem.is (':visible')) {
            that._positionMenu ();
            that._containerElem.fadeIn ();
            $(document).one ('click', function () {
                that.close ();
            });
        } else {
            that.close ();
        }
        return false;
    });
};

PopupDropdownMenu.prototype._init = function () {
    var that = this; 
    that._openButton = $(this.openButtonSelector);
    that._containerElem = $(this.containerElemSelector);
    that._containerElem.addClass ('popup-dropdown-menu');
    that._setUpOpenButtonBehavior ();

    // hide menu on resize
    $(window).resize (function (e) {
        if (that._containerElem.is (':visible')) {
            that._containerElem.hide ();
        }
    });
};
