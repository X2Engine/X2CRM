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
 * Manages behavior of the doc viewer profile widget
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function DocViewerProfileWidget (argsDict) {
    var defaultArgs = {
        translations: [],
        getItemsUrl: '', // used to populate autocomplete
        getDocUrl: '', // url to request a doc
        docId: '', // the id of the doc currently being viewed
        editDocUrl: '', // url to edit a doc
        canEdit: false, // has permission to edit current doc
        checkEditPermissionUrl: ''
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
}

DocViewerProfileWidget.prototype = auxlib.create (SortableWidget.prototype);


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
DocViewerProfileWidget.prototype.onDragStart = function () {
    this._iframeElem.hide ();
    SortableWidget.prototype.onDragStart.call (this);
};

DocViewerProfileWidget.prototype.onDragStop = function () {
    this._iframeElem.show ();
    SortableWidget.prototype.onDragStop.call (this);
};

/*
Private instance methods
*/

DocViewerProfileWidget.prototype._setUpDefaultTextBehavior = function () {
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
                            that._checkEditPermission ();
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
 * Update iframe height on widget resize 
 */
DocViewerProfileWidget.prototype._resizeEvent = function () {
    var that = this; 
    that._iframeElem.attr ('height', that.contentContainer.height ());
};

/**
 * Save iframe height on resize stop 
 */
DocViewerProfileWidget.prototype._afterStop = function () {
    var that = this; 
    that.setProperty ('height', that._iframeElem.attr ('height'));
};

/**
 * Places a div over the iframe so that it doesn't interfere with mouse dragging 
 */
DocViewerProfileWidget.prototype._turnOnSortingMode = function () {
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
DocViewerProfileWidget.prototype._turnOffSortingMode = function () {
    this._iframeOverlay.remove ();
};

DocViewerProfileWidget.prototype._setUpEditBehavior = function () {
    var that = this; 
    $(this.element).find ('.widget-edit-button').unbind ('click.widgetEdit');
    $(this.element).find ('.widget-edit-button').bind ('click.widgetEdit', function (evt) {
        evt.preventDefault ();
        window.location = that.editDocUrl + '?id=' + that.docId;
        return false;
    });
};

/**
 * Detects presence of UI elements (and sets properties accordingly), calls their setup methods
 */
DocViewerProfileWidget.prototype._callUIElementSetupMethods = function () {
    if ($(this.element).find ('.widget-edit-button').length) {
        this._setUpEditBehavior ();
        this._editBehaviorEnabled = true;
    } else {
        this._editBehaviorEnabled = false;
    }

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
                if ($(this).hasClass ('widget-edit-button') && !that.canEdit) {
                    return true;
                } else {
                    $(this).show ();
                }
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
};

DocViewerProfileWidget.prototype._hideShowEditButton = function () {
    if (this.canEdit && this._cursorInWidget)
        this._editButton.show ();
    else
        this._editButton.hide ();
};

DocViewerProfileWidget.prototype._checkEditPermission = function () {
    var that = this; 
    $.ajax ({
        method: 'GET',
        url: this.checkEditPermissionUrl,
        data: {
            id: this.docId
        },
        success: function (data) {
            if (data === 'true') {
                that.canEdit = true;
            } else {
                that.canEdit = false;
            }
            that._hideShowEditButton ();
        }
    });
};

DocViewerProfileWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._selectADocButtonSelector = this.elementSelector + ' .select-a-document-button';
    this._selectADocButton = $(this._selectADocButtonSelector);
    this._selectADocDialog = $('#select-a-document-dialog');
    this._editButton = $(this.element).find ('.widget-edit-button');
    this._iframeElem = this.contentContainer.find ('iframe');
    this._iframeSrc = '';
    this._setUpSelectADocBehavior ();

    if (this.docId === '') {
        this._setUpDefaultTextBehavior ();
    }
};

