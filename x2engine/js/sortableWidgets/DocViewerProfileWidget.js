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
 * Manages behavior of the doc viewer profile widget
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function DocViewerProfileWidget (argsDict) {
    var defaultArgs = {
        getItemsUrl: '', // used to populate autocomplete
        getDocUrl: '', // url to request a doc
        docId: '', // the id of the doc currently being viewed
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
}

DocViewerProfileWidget.prototype = auxlib.create (IframeWidget.prototype);


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

DocViewerProfileWidget.prototype._setUpDefaultTextBehavior = function () {
    if (this.docId !== '') return;
    var that = this;
    this.element.find ('.default-text-container a').click (function (evt) {
        evt.preventDefault ();
        that._selectADocButton.click ();
        return false;
    });
};

/**
 * Show dialog with doc selection form when settings menu item is clicked 
 */
DocViewerProfileWidget.prototype._setUpSelectADocBehavior = function () {
    var that = this; 

    var selectedDocUrl = ''; // set by autocomplete
    var selectedDocId; // set by autocomplete
    var selectedDocLabel; // set by autocomplete

    this._selectADocButton.unbind ('click.selectADoc'); 
    this._selectADocButton.bind ('click.selectADoc', function () {

        auxlib.destroyErrorFeedbackBox ($(that._selectADocDialog).find ('.selected-doc'));
        selectedDocUrl = '';

        // already created
        if ($(this).closest ('.ui-dialog').length) {
            $('#doc-select-button').removeClass ('highlight');
            $(this).dialog ('open');
            return;
        }

        // generate select a doc dialog
        that._selectADocDialog.dialog ({
            title: that.translations['dialogTitle'],
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: that.translations['selectButton'],
                    id: 'doc-select-button',
                    /*
                    Validate input and save/display error
                    */
                    click: function () {
                        if (selectedDocUrl !== '') {
                            that.element.find ('iframe').attr ('src', selectedDocUrl);
                            $(this).dialog ('close');
                            that.setProperty ('docId', selectedDocId);
                            that.docId = selectedDocId;
                            that.changeLabel (selectedDocLabel);
                            that.element.find ('.default-text-container').remove ();
                        } else {
                            auxlib.createErrorFeedbackBox ({
                                prevElem: $(that._selectADocDialog).find ('.selected-doc'),
                                message: that.translations['docError']
                            });
                        }
                    }
                },
                {
                    text: that.translations['closeButton'],
                    click: function () { $(this).dialog ('close'); }
                }
            ],
            close: function () {
                that._selectADocDialog.hide ();
            },
            drag: function () {
                $(that._selectADocDialog).find ('.selected-doc').autocomplete ('widget').
                    position ({
                        my: 'left top',
                        at: 'left bottom',
                        of: $(that._selectADocDialog).find ('.selected-doc')
                    });
            }
        });
    }); 

    // instantiate autocomplete with doc items
    $(this._selectADocDialog).find ('.selected-doc').autocomplete ({
        'minLength':'1',
        'source': this.getItemsUrl,
        'select': function (event, ui) {
            $(this).val (ui.item.value);
            selectedDocUrl = that.getDocUrl + '?id=' + ui.item.id;
            selectedDocId = ui.item.id;
            selectedDocLabel = ui.item.label;

            $('#doc-select-button').addClass ('highlight');
            return false; 
        }
    });

    $(this._selectADocDialog).find ('.selected-doc').autocomplete ('widget').
        css ({
            'z-index': 1400
    });
};

/**
 * Detects presence of UI elements (and sets properties accordingly), calls their setup methods
 */
DocViewerProfileWidget.prototype._callUIElementSetupMethods = function () {
    SortableWidget.prototype._callUIElementSetupMethods.call (this);
};

/**
 * Hides/shows title bar buttons on mouseleave/mouseover 
 */
DocViewerProfileWidget.prototype._setUpTitleBarBehavior = function () {
    var that = this; 
    that._cursorInWidget = false;
    if ($(this.element).find ('.widget-minimize-button').length ||
        $(this.element).find ('.widget-close-button').length) {

        $(this.element).mouseover (function () {
            that._cursorInWidget = true;
            $(that.element).find ('.submenu-title-bar .x2-icon-button').each (function () {
                $(this).show ();
            });
        });
        $(this.element).mouseleave (function () {
            that._cursorInWidget = false;
            if (!(that._settingsBehaviorEnabled &&
                  $(that.elementSelector  + ' .widget-settings-menu-content').is (':visible'))) {
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
            }
        });
    }
    if (this.element.find ('.delete-widget-button').length) {
        this._setUpWidgetDeletion ();
    }
};

DocViewerProfileWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._selectADocButtonSelector = this.elementSelector + ' .select-a-document-button';
    this._selectADocButton = $(this._selectADocButtonSelector);
    this._selectADocDialog = $('#select-a-document-dialog-' + this.widgetUID);
    this._iframeElem = this.contentContainer.find ('iframe');
    this._iframeSrc = '';
    this._setUpSelectADocBehavior ();
    this._setUpDefaultTextBehavior ();
    this.element.find ('.default-text-container').show ();

};
