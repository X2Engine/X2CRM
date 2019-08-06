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
 * EnlargeableImage prototype
 * Image enlargement is done by overriding css height styling.
 * An enlargeable image, when clicked is enlarged and placed inside a modal
 */

x2.EnlargeableImage = (function () {

function EnlargeableImage (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        elem: null
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
 * closes the modal and removes the node
 */
EnlargeableImage.prototype.close = function () {
    var that = this;
    $(that._modal).remove ();
};

/*
Private instance methods
*/

/**
 * @return bool true if height of image is larger, false otherwise
 */
EnlargeableImage.prototype._heightIsLarger = function () {
    return $(this.elem).height () > $(this.elem).width ();
};

/**
 * Clone the image and place it in a modal which closes when the user clicks outside the image
 * or when the close button is clicked.
 */
EnlargeableImage.prototype._createEnlargedImageModal = function () {
    var that = this;    

    // construct modal
    this._modal = $('<div>', {
        'class': 'x2-enlargeable-image-modal'
    }).append ($('<img>', {
        'src': $(this.elem).attr ('src'),
    })).append ($('<input>', {
        type: 'image',
        src: yii.themeBaseUrl+'/images/icons/Close_Widget.png'
    }));

    // set max height and width based on larger dimension
    if (this._heightIsLarger ()) {
        this._modal.find ('img').css ({
            'max-height': '70%',
            'max-width': '80%'
        });
    } else {
        this._modal.find ('img').css ({
            'max-width': '70%',
            'max-height': '80%'
        });
    }

    // close button behavior
    this._modal.find ('input').unbind ('click');
    this._modal.find ('input').bind ('click', function () {
        that.close ();
    });

    // close on click outside image
    auxlib.onClickOutside (this._modal.find ('img'), function () { that.close (); });

    $('body').append (this._modal);
};

EnlargeableImage.prototype._init = function () {
    this._modal = null;
    var that = this;

    // for styling
    $(this.elem).addClass ('x2-enlargeable-image');

    // open modal on image click
    $(this.elem).unbind ('click');
    $(this.elem).bind ('click', function () {
        that._createEnlargedImageModal (); 
        return false;
    });
};

return EnlargeableImage;

}) ();


