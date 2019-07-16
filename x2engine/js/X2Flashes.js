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





x2.Flashes = (function () {

/**
 * Manages x2 gridview mass action actions and ui element behavior  
 */

function X2Flashes (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        containerId: null, // used to create container to hold flashes
        translations: [], 
        expandWidgetSrc: '', // image src
        collapseWidgetSrc: '', // image src
        closeWidgetSrc: '', // image src
        successFadeTimeout: 3000, // time before success flashes begin to fade out
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.container$ = null;
    this._successFlashFadeTimeout = null;

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


/*
Private instance methods
*/

/***********************************************************************
* Flashes setup functions
***********************************************************************/

/**
 * Display flashes of a given type
 * @param string key the type of flash ('notice' | 'error' | 'success')
 * @param array of strings flashes flash messages which will be displayed
 */
X2Flashes.prototype._displayKeyFlashes = function (key, flashes, rawHtml) {
    var that = this;
    rawHtml = typeof rawHtml === 'undefined' ? false : rawHtml; 
    that.DEBUG && console.log ('x2.massActions._displayKeyFlashes');
    that.DEBUG && console.log ('flashes = ');
    that.DEBUG && console.log (flashes);

    var flashNum = flashes.length;
    var hideList = false;
    var $flashContainer = this[key + 'container'];
    $flashContainer.show ();
   that.DEBUG && console.log ('$flashContainer = ');
    that.DEBUG && console.log ($flashContainer);


    if (flashNum > 3 || flashes['header']) { // show header and make flash list expandable

        // add list header
        $flashContainer.append (
            $('<p>', {
                'class': 'flash-list-header left',
                text: flashes['header'] ? flashes['header'] :
                    that.translations[key + 'FlashList'] + ' ' + flashNum + ' ' +
                    that.translations[key + 'ItemName']
            }),
            $('<img>', {
                'class': 'flash-list-left-arrow',
                'src': that.expandWidgetSrc,
                'alt': '<'
            }),
            $('<img>', {
                'class': 'flash-list-down-arrow',
                'style': 'display: none;',
                'src': that.collapseWidgetSrc,
                'alt': 'v'
            })
        );

        // set up flashes list expand and collapse behavior
        $flashContainer.find ('.flash-list-left-arrow').
            click (function () {

            $(this).hide ();
            $(this).next ().show ();
            $flashContainer.find ('.x2-flashes-list').show ();
        });
        $flashContainer.find ('.flash-list-down-arrow').
            click (function () {

            $(this).hide ();
            $(this).prev ().show ();
            $flashContainer.find ('.x2-flashes-list').hide ();
        });

        hideList = true;
    }

    // build flashes list
    var $flashList = $('<ul>', {
        'class': 'x2-flashes-list' + (hideList ? '' : ' x2-flashes-list-no-style'),
        style: (hideList ? 'display: none;' : '')
    });
    $flashContainer.append ($flashList);
    for (var i in flashes) {
        if (!i.match (/^\d+$/)) continue;
        that.DEBUG && console.log ('x2.massActions._displayKeyFlashes: i = ' + i);
        var attrs = {};
        if (rawHtml) attrs.html = flashes[i];
        else attrs.text = flashes[i];
        $flashContainer.find ('.x2-flashes-list').append ($('<li>', attrs));
    }

    if (key === 'success' && flashes['fade'] !== '0') { 
        // other types of flash containers have close buttons

        //if (that._successFlashFadeTimeout) window.clearTimeout (that._successFlashFadeTimeout);
        /*that._successFlashFadeTimeout = */
        setTimeout (
            function () { 
                $flashContainer.fadeOut (3000, function () {
                    $flashContainer.remove ();
                });
            }, that.successFadeTimeout);
    }
}

/**
 * Append flash section container div to parent element
 * @param string key the type of flash
 * @param object parent the jQuery object for the flashes container associated with key
 */
X2Flashes.prototype._appendFlashSectionContainer = function (key, parent, flashes) {
    var that = this; 
    var $flashContainer = 
        $('<div>', {
            'class': 'flash-' + key,
            style: 'display: none;'
        })
    $(parent).append ($flashContainer);

    // add close button, not needed for success flash container since it fades out
    if (key === 'notice' || key === 'error' || flashes['fade'] === '0') {
        $flashContainer.append (
            $('<img>', {
                //id: key + '-container-close-button',
                'class': 'right',
                title: that.translations['close'],
                'src': that.closeWidgetSrc,
                alt: '[x]'
            })
        );
    
        // set up close button behavior
        $flashContainer.find ('img').click (function () {
            $flashContainer.fadeOut (function () {
                $flashContainer.remove ();
            });
        });
    }
    this[key + 'container'] = $flashContainer;
};

/**
 * Build the flash container, fill it with given flashes
 * @param dictionary flashes keys are the type of flash ('success', 'notice', 'error'), values
 *  are arrays of messages
 */
X2Flashes.prototype.displayFlashes = function (flashes, rawHtml) {
    var that = this; 
    that.DEBUG && console.log ('x2.massActions._displayFlashes: flashes = ');
    that.DEBUG && console.log (flashes);
    if (!flashes['success'] && !flashes['notice'] && !flashes['error']) return;
    rawHtml = typeof rawHtml === 'undefined' ? false : rawHtml; 

    this.container$.show ();
    // remove previous flashes container
    /*if ($('#x2-gridview-flashes-container').length) {
        $('#x2-gridview-flashes-container').remove ();
    }*/

    // fill container with flashes
    var types = ['success', 'notice', 'error'];
    for (var i in types) {
        var type = types[i];
        var flashesOfType = flashes[type];
        if (flashes[type] && (flashesOfType.length > 0 || auxlib.keys (flashesOfType).length > 0)) {
            that._appendFlashSectionContainer (type, this.container$, flashesOfType);
            that._displayKeyFlashes (type, flashesOfType, rawHtml);
        }
    }
    $('#content-container').css ('margin-bottom', this.container$.height ());

};

X2Flashes.prototype.clearFlashes = function () {
    this.container$.children ().remove ();
};

/**
 * Checks if flashes container should be made sticky and if so, makes it sticky
 */
X2Flashes.prototype._checkFlashesSticky = function () {
    var that = this; 

    if (this.container$.position ().top + $(window).scrollTop () > 
        $('#content-container').position ().top + $('#content-container').height ()) {
         this.container$.removeClass ('fixed-flashes-container');
        $('#content-container').css ('margin-bottom', '');
        $(window).unbind ('scroll._checkFlashesSticky').
            bind ('scroll._checkFlashesSticky', function () { 
                return that._checkFlashesUnsticky (); });
    } 
};

/**
 * Checks if flashes container should be made unsticky and if so, unsticks it
 */
X2Flashes.prototype._checkFlashesUnsticky = function () {
    var that = this; 

    if (this.container$.offset ().top - $(window).scrollTop () >
        ($(window).height () - 5) - this.container$.height ()) {

        this.container$.addClass ('fixed-flashes-container');
        $('#content-container').css ('margin-bottom', this.container$.height ());
        $(window).unbind ('scroll._checkFlashesUnsticky').
            bind ('scroll._checkFlashesUnsticky', function () { that._checkFlashesSticky (); });
    } else {
        return false;
    }
};


/**
 * set up mass action ui behavior, this gets run on every grid update
 */
X2Flashes.prototype._init = function () {
    var that = this; 

    // build new flashes container
    this.container$ = $('<div>', { 
        id: this.containerId,
        'class': 'flashes-container'
    });
    $('#content-container').append (this.container$);
    
    $('#content-container').attr (
        'style', 'padding-bottom: ' + this.container$.height () + 'px;');
    this.container$.width ($('#content-container').width () - 10);
    $(window).unbind ('resize.contentContainer').bind ('resize.contentContainer', function () {
        that.container$.width ($('#content-container').width () - 10);
    });

    that.DEBUG && console.log ('this.container$.positoin ().top = ');
    that.DEBUG && console.log (this.container$.position ().top);

    if (!that._checkFlashesUnsticky ()) {
        $(window).unbind ('scroll._X2Flashes', that._checkFlashesUnsticky).
            bind ('scroll._X2Flashes', function () { that._checkFlashesUnsticky (); });
    }
};

return X2Flashes;
}) ();
