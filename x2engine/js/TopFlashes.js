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



$(function() {
    
x2.topFlashes = (function () {

function TopFlashes (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        successFadeTimeout: 1700
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

TopFlashes.prototype.displayFlash = function (message, type, closeMethod, encode) {
    if ($.inArray (type, ['error', 'success', 'warning', 'loading', 'tip']) === -1) {
        throw new Error ('invalid flash type');
    }
    closeMethod = typeof closeMethod === 'undefined' ? 'fade' : closeMethod; 
    encode = typeof encode === 'undefined' ? true : encode; 
    var that = this;

    var flashContainer$ = $('<div>', {
        id: 'top-flashes-container',
        'class': 'flash-' + type
    });

    if ($.type (message) === 'array' || $.type (message) === 'object') {
        if (message.header) {
            flashContainer$.addClass ('has-header');
            if (encode) {
                flashContainer$.append ($('<span>', {
                    text: message.header
                }));
            } else {
                flashContainer$.append ($('<span>', {
                    html: message.header
                }));
            }
        }
        var message$ = $('<ul>', {
            id: 'top-flashes-message'
        });
        for (var i in message) {
            if (!i.match (/^\d+$/)) continue;
            if (encode) {
                message$.append ($('<li>', {
                    text: message[i] 
                }));
            } else {
                message$.append ($('<li>', {
                    html: message[i] 
                }));
            }
        }
        flashContainer$.append (message$);
    } else {
        if (encode) {
            flashContainer$.append ($('<div>', {
                text: message,
                id: 'top-flashes-message'
            }));
        } else {
            flashContainer$.append ($('<div>', {
                html: message,
                id: 'top-flashes-message'
            }));
        }
    }

    this.clearFlash ();
    this.container$.append (flashContainer$);
    this._setUpCloseMethod (flashContainer$, closeMethod);

};

TopFlashes.prototype._setUpCloseMethod = function (flashContainer$, closeMethod) {
    var that = this;

    switch (closeMethod) {
        case 'fade':
            setTimeout (
                function () { 
                    flashContainer$.fadeOut (3000, function () {
                        flashContainer$.remove ();
                    });
                }, that.successFadeTimeout);
            break;
        case 'clickOutside':
            auxlib.onClickOutside (flashContainer$, function () {
                flashContainer$.remove ();
            }) 
            break;
    }
};

TopFlashes.initializeContainer = function () {
    if ($('#top-flashes-container').length) {
        x2.topFlashes._setUpCloseMethod ($('#top-flashes-container'), 'clickOutside');
    }
};

TopFlashes.prototype.clearFlash = function () {
    this.container$.children ().remove ();
};

TopFlashes.prototype._init = function () {
    this.container$ = $('<div>', {
        id: 'top-flashes-container-outer'
    });
    $('#page-container').append (this.container$);
};

$(function () {
    TopFlashes.initializeContainer (); 
});

return new TopFlashes;

}) ();

});
