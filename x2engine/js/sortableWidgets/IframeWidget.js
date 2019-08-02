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
 * Manages behavior of the iframe widget
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function IframeWidget (argsDict) {
    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
}

IframeWidget.prototype = auxlib.create (SortableWidget.prototype);


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
 * Hide iframe to prevent lag 
 */
IframeWidget.prototype.onDragStart = function () {
    // prevent default text from shifting when iframe is hidden
    this.contentContainer.height (this.contentContainer.height ());

    this._iframeElem.hide ();
    SortableWidget.prototype.onDragStart.call (this);
};

IframeWidget.prototype.onDragStop = function () {
    this.contentContainer.height ('');
    this.contentContainer.width ('');
    this._iframeElem.show ();
    SortableWidget.prototype.onDragStop.call (this);
};

/*
Private instance methods
*/

IframeWidget.prototype._setUpDefaultTextBehavior = function () {
    if (this.url) return;
    var that = this;
    this.element.find ('.default-text-container a').click (function (evt) {
        evt.preventDefault ();
        that._changeUrlButton.click ();
        return false;
    });
};

/**
 * Show dialog with doc selection form when settings menu item is clicked 
 */
IframeWidget.prototype._setUpChangeUrlBehavior = function () {
    var that = this; 

    var selectedDocUrl = ''; // set by autocomplete
    var selectedDocLabel; // set by autocomplete

    this._changeUrlButton.unbind ('click._setUpChangeUrlBehavior'); 
    this._changeUrlButton.bind ('click._setUpChangeUrlBehavior', function () {

        auxlib.destroyErrorFeedbackBox ($(that._changeUrlDialog).find ('.iframe-url'));
        selectedDocUrl = '';

        // already created
        if ($(this).closest ('.ui-dialog').length) {
            $('#change-url-submit-button-' + that.widgetUID).removeClass ('highlight');
            $(this).dialog ('open');
            return;
        }

        // generate select a doc dialog
        that._changeUrlDialog.dialog ({
            title: that.translations['dialogTitle'],
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: that.translations['closeButton'],
                    click: function () { $(this).dialog ('close'); }
                },
                {
                    text: that.translations['selectButton'],
                    id: 'change-url-submit-button-' + that.widgetUID,
                    /*
                    Validate input and save/display error
                    */
                    click: function () {
                        var url = $.trim ($(that._changeUrlDialog).find ('.iframe-url').val ());
                        auxlib.destroyErrorFeedbackBox (
                            $(that._changeUrlDialog).find ('.iframe-url'));

                        if (url !== '') {
                            if (!url.match (/^https?:\/\//)) url = 'http://' + url;
                            that.element.find ('iframe').attr ('src', url);
                            that.setProperty ('url', url);
                            $(this).dialog ('close');
                            that.element.find ('.default-text-container').remove ();
                        } else {
                            auxlib.createErrorFeedbackBox ({
                                prevElem: $(that._changeUrlDialog).find ('.iframe-url'),
                                message: that.translations['urlError']
                            });
                        }
                    }
                }
            ],
            close: function () {
                that._changeUrlDialog.hide ();
            },
        });

        that._changeUrlDialog.find ('.iframe-url').keydown (function () {
            $('#change-url-submit-button-' + that.widgetUID).addClass ('highlight'); 
        });
        that._changeUrlDialog.find ('.iframe-url').change (function () {
            if ($(this).val () === '') 
                $('#change-url-submit-button-' + that.widgetUID).removeClass ('highlight'); 
        });
    }); 
};

/**
 * Update iframe height on widget resize 
 */
IframeWidget.prototype._resizeEvent = function () {
    var that = this; 
    that._iframeElem.attr ('height', that.contentContainer.height ());
};

/**
 * Save iframe height on resize stop 
 */
IframeWidget.prototype._afterStop = function () {
    var that = this; 
    that.setProperty ('height', that._iframeElem.attr ('height'));
};

/**
 * Places a div over the iframe so that it doesn't interfere with mouse dragging 
 */
IframeWidget.prototype._turnOnSortingMode = function () {
    this._iframeOverlay = $('<div>', {
        width: this.contentContainer.width (),
        height: this.contentContainer.height (),
        css: {
            position: 'absolute',
            'z-index': 100
        }
    });
    this.contentContainer.append (this._iframeOverlay);
    this._iframeOverlay.position ({
        my: 'left top',
        at: 'left top',
        of: this.contentContainer
    });
};

/**
 * removes iframe overlay created by _turnOnSortingMode ()
 */
IframeWidget.prototype._turnOffSortingMode = function () {
    this._iframeOverlay.remove ();
};

IframeWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._changeUrlSelector = this.elementSelector + ' .change-url-button';
    this._changeUrlButton = $(this._changeUrlSelector);
    this._changeUrlDialog = $('#change-url-dialog-' + this.widgetUID);
    this._iframeElem = this.contentContainer.find ('iframe');
    this._iframeSrc = '';
    this._setUpChangeUrlBehavior ();
    this._setUpDefaultTextBehavior ();
};

