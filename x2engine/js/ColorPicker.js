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

x2.colorPicker = (function () {

function ColorPicker (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
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


ColorPicker.prototype.setUp = function (element, replaceHash /* optional */) {
    var that = this;
    if ($(element).next ('.sp-replacer').length) return; // already set up
    replaceHash = typeof replaceHash === 'undefined' ? false : replaceHash;

    $(element).spectrum ({
        move: function (color) {
            $(element).data ('ignoreChange', true);
        },
        hide: function (color) {

            that.removeCheckerImage ($(element));
            $(element).data ('ignoreChange', false);

            if (replaceHash) {
                var text = color.toHexString ().toUpperCase ().replace (/#/, '');
            } else {
                var text = color.toHexString ().toUpperCase ();
            }

            $(element).val (text);
            $(element).change ();
        }
    });
    
    $(element).show ();
    if ($(element).val () === '') {
        that.addCheckerImage ($(element));
    }

    $(element).blur (function () {
        var color = $(this).val ();

        // make color picker color match input field without triggering change events
        if (color !== '') { 
            that.removeCheckerImage ($(this));

            if (replaceHash) {
                var text = '#' + color;
            } else {
                var text = color;
            }

            // set the color of the color picker element
            $(element).spectrum ('set', text);
            // now hide and show it, triggering the hide event handler defined above, converting 
            // inputted color value to a hex value
            $(element).spectrum ('show');
            $(element).spectrum ('hide');
        }
    });

    $(element).change (function () {
        var text = $(this).val ();
        if (text === '') {
            that.addCheckerImage ($(this));
        }
    });

};

ColorPicker.prototype.removeCheckerImage = function (element) {
    $(element).next ('div.sp-replacer').find ('.sp-preview-inner').css (
        'background-image', '');
};

ColorPicker.prototype.addCheckerImage = function (element) {
    $(element).next ('div.sp-replacer').find ('.sp-preview-inner').css (
        'background-image', 'url("' + yii.baseUrl + '/themes/x2engine/images/checkers.gif")');
};

/*
Private instance methods
*/

ColorPicker.prototype._initializeX2ColorPicker = function () {
    var that = this;
    $('.x2-color-picker').each (function () {
        that.setUp ($(this), !$(this).hasClass ('x2-color-picker-hash'));
    });
};

ColorPicker.prototype._init = function () {
    this._initializeX2ColorPicker ();
};

return new ColorPicker ();

}) ();
