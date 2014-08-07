/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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
 * Manages x2 gridview mass action actions and ui element behavior  
 */

x2.GridViewMassActionsManager = (function () {

function GridViewMassActionsManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActions: [], // enabled mass actions
        gridId: '', // id of associated grid view
        namespacePrefix: '', // used to access other x2gridview javascript objects
        gridSelector: '', // can be used to select associated grid view element
        fixedHeader: false, // whether or not grid view has a fixed header
        massActionUrl: '',
         
        modelName: '', // name of model associated with grid
        translations: [], 
        expandWidgetSrc: '', // image src
        collapseWidgetSrc: '', // image src
        closeWidgetSrc: '', // image src
        progressBarDialogSelector: null,
        enableSelectAllOnAllPages: true
    };

    this._previouslySelectedRecords = null; // records selected before grid update
    this._stickyHeaderNamespace = this.namespacePrefix + 'stickyHeader';
    this._successFlashFadeTimeout = null;

    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._topPagerNamespace = this.namespacePrefix + 'TopPagerManager'; 
    this._elementSelector = '#' + this.gridId + '-mass-action-buttons';

    /**
     * @var bool If true, user has selected all records on all pages
     */
    this._allRecordsOnAllPagesSelected = false;

    this.massActionInProgress = false;

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
 * @return bool true if the gridview header is fixed, false otherwise
 */
GridViewMassActionsManager.prototype.headerIsFixed = function () {

    // header can only be unfixed if sticky header is enabled
    if (typeof x2.gridViewStickyHeader !== 'undefined') {
        return !x2.gridViewStickyHeader.getIsStuck ();
    }
    return true;
};

GridViewMassActionsManager.prototype.saveSelectedRecords = function () {
    this._previouslySelectedRecords = this._getSelectedRecords ();
};

/**
 * Public function for condensing interface
 */
GridViewMassActionsManager.prototype.moveButtonIntoMoreMenu = function () {
    var that = this; 
    var moreButton = $('#' + that.gridId + '-mass-action-buttons .mass-action-more-button');
    var buttonSet = $('#' + that.gridId + '-mass-action-buttons .mass-action-button-set');
    var buttons = $(buttonSet).children ();
    var visibleCount = 0;
    var lastButton;

    // get last visible button
    $(buttons).each (function () {
        if ($(this).attr ('style') !== 'display: none;') {
            lastButton = $(this); 
            visibleCount++;
        }
    });

    if (typeof lastButton === 'undefined') return false;

    $(lastButton).hide (); // hide button in button group

    // give a solitary button proper styling
    if (visibleCount === 2) $(buttons).first ().addClass ('pseudo-only-child');

    // show button in list
    var lastButtonAction = $(lastButton).attr ('class').match (/mass-action-button-([^ ]+)/)[1];

    $('#' + that.gridId + '-mass-action-buttons .mass-action-' + lastButtonAction).show ();

    return true;
};

/**
 * Public function for expanding interface
 */
GridViewMassActionsManager.prototype.moveMoreButtonMenuItemIntoButtons = function () {
    var that = this; 
    var buttonSet = $('#' + that.gridId + '-mass-action-buttons .mass-action-button-set');
    var buttons = $(buttonSet).children ();
    var moreButton = $('#' + that.gridId + '-mass-action-buttons .mass-action-more-button');
    var moreDropDownList = $('#' + that.gridId + '-mass-action-buttons .more-drop-down-list');
    var listItems = $(moreDropDownList).children ();
    var firstItem;

    var buttonActions = auxlib.map (function (a) {
        return $(a).attr ('class').match (/mass-action-button-([^ ]+)/)[1];
    }, $.makeArray (buttons));

    // get first non hidden element in button list 
    $(listItems).each (function () {
        that.DEBUG && console.log ($(this));
        if ($(this).attr ('style') !== 'display: none;') {
            firstItem = $(this); 
            return false;
        }
    });
    if (typeof firstItem === 'undefined') {
         return false;
    }

    var lastButtonAction = $(firstItem).attr ('class').match (/[^-]+$/)[0];

    if ($.inArray (lastButtonAction, buttonActions) === -1) return false;

    // hide button list item and show button set button
    $(firstItem).hide ();
    $('#' + that.gridId + '-mass-action-buttons .mass-action-button-' + lastButtonAction).show ();

    if ($(buttons).length - 
        $(buttonSet).children ('[style="display: none;"]').length !== 1) {

        $(buttons).first ('.pseudo-only-child').removeClass ('pseudo-only-child');
    } else {
        $('#' + that.gridId + '-mass-action-buttons .mass-action-button-' + lastButtonAction).
            addClass ('pseudo-only-child');
    }

    return true;
};

GridViewMassActionsManager.prototype.reinit = function () {
    this._init ();
};

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
GridViewMassActionsManager.prototype._displayKeyFlashes = function (key, flashes) {
    var that = this;
    that.DEBUG && console.log ('x2.massActions._displayKeyFlashes');
    var flashNum = flashes.length;
    var hideList = false;


    if (flashNum > 3) { // show header and make flash list expandable

        // add list header
        $('#x2-gridview-flash-' + key + '-container').append (
            $('<p>', {
                'class': 'flash-list-header left',
                text: that.translations[key + 'FlashList'] + ' ' + flashNum + ' ' +
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
        $('#x2-gridview-flash-' + key + '-container').find ('.flash-list-left-arrow').
            click (function () {

            $(this).hide ();
            $(this).next ().show ();
            $('#x2-gridview-flashes-' + key + '-list').show ();
        });
        $('#x2-gridview-flash-' + key + '-container').find ('.flash-list-down-arrow').
            click (function () {

            $(this).hide ();
            $(this).prev ().show ();
            $('#x2-gridview-flashes-' + key + '-list').hide ();
        });

        hideList = true;
    }

    // build flashes list
    $('#x2-gridview-flash-' + key + '-container').append ($('<ul>', {
        id: 'x2-gridview-flashes-' + key + '-list',
        'class': 'x2-gridview-flashes-list',
        style: (hideList ? 'display: none;' : '')
    }));
    for (var i in flashes) {
        that.DEBUG && console.log ('x2.massActions._displayKeyFlashes: i = ' + i);
        $('#x2-gridview-flashes-' + key + '-list').append ($('<li>', {
            text: flashes[i]
        }));
    }

    if (key === 'success') { // other types of flash containers have close buttons
        if (that._successFlashFadeTimeout) window.clearTimeout (that._successFlashFadeTimeout);
        that._successFlashFadeTimeout = setTimeout (
            function () { $('#x2-gridview-flash-' + key + '-container').fadeOut (3000); }, 2000);
    }
}

/**
 * Append flash section container div to parent element
 * @param string key the type of flash
 * @param object parent the jQuery object for the flashes container associated with key
 */
GridViewMassActionsManager.prototype._appendFlashSectionContainer = function (key, parent) {
    var that = this; 
    $(parent).append (
        $('<div>', {
            id: 'x2-gridview-flash-' + key + '-container',
            'class': 'flash-' + key 
        })
    )

    // add close button, not needed for success flash container since it fades out
    if (key === 'notice' || key === 'error') {
        $('#x2-gridview-flash-' + key + '-container').append (
            $('<img>', {
                id: key + '-container-close-button',
                'class': 'right',
                title: that.translations['close'],
                'src': that.closeWidgetSrc,
                alt: '[x]'
            })
        );
    
        // set up close button behavior
        $('#' + key + '-container-close-button').click (function () {
            $('#x2-gridview-flash-' + key + '-container').fadeOut ();
        });
    }
};

/**
 * Build the flash container, fill it with given flashes
 * @param dictionary flashes keys are the type of flash ('success', 'notice', 'error'), values
 *  are arrays of messages
 */
GridViewMassActionsManager.prototype._displayFlashes = function (flashes) {
    var that = this; 
    that.DEBUG && console.log ('x2.massActions._displayFlashes: flashes = ');
    that.DEBUG && console.log (flashes);
    if (!flashes['success'] && !flashes['notice'] && !flashes['error']) return;

    // remove previous flashes container
    if ($('#x2-gridview-flashes-container').length) {
        $('#x2-gridview-flashes-container').remove ();
    }

    // build new flashes container
    $('#content-container').append (
        $('<div>', {
            id: 'x2-gridview-flashes-container'
        })
    ); 
    
    // fill container with flashes
    if (flashes['success'] && flashes['success'].length > 0) {
        that._appendFlashSectionContainer (
            'success', $('#x2-gridview-flashes-container'));
        var successFlashes = flashes['success'];
        that._displayKeyFlashes ('success', successFlashes);
    }
    if (flashes['notice'] && flashes['notice'].length > 0) {
        that._appendFlashSectionContainer (
            'notice', $('#x2-gridview-flashes-container'));
        var noticeFlashes = flashes['notice'];
        that._displayKeyFlashes ('notice', noticeFlashes);
    }
    if (flashes['error'] && flashes['error'].length > 0) {
        that._appendFlashSectionContainer ('error', $('#x2-gridview-flashes-container'));
        var errorFlashes = flashes['error'];
        that._displayKeyFlashes ('error', errorFlashes);
    }

    var flashesContainer = $('#x2-gridview-flashes-container');
    $('#content-container').attr (
        'style', 'padding-bottom: ' + $(flashesContainer).height () + 'px;');
    $(flashesContainer).width ($('#content-container').width () - 5);
    $(window).unbind ('resize.contentContainer').bind ('resize.contentContainer', function () {
        $(flashesContainer).width ($('#content-container').width () - 5);
    });

    that.DEBUG && console.log ('$(flashesContainer).positoin ().top = ');
    that.DEBUG && console.log ($(flashesContainer).position ().top);

    if (!that._checkFlashesUnsticky ()) {
        $(window).unbind ('scroll', that._checkFlashesUnsticky).
            bind ('scroll', that._checkFlashesUnsticky);
    }
};

/**
 * Checks if flashes container should be made sticky and if so, makes it sticky
 */
GridViewMassActionsManager.prototype._checkFlashesSticky = function () {
    var that = this; 
    var flashesContainer = $('#x2-gridview-flashes-container');

    if ($(flashesContainer).position ().top > 
        $('#content-container').position ().top + $('#content-container').height ()) {
         $(flashesContainer).removeClass ('fixed-flashes-container');
        $(window).unbind ('scroll', that._checkFlashesUnsticky).
            bind ('scroll', that._checkFlashesUnsticky);
    }
};

/**
 * Checks if flashes container should be made unsticky and if so, unsticks it
 */
GridViewMassActionsManager.prototype._checkFlashesUnsticky = function () {
    var that = this; 
    var flashesContainer = $('#x2-gridview-flashes-container');

    if ($(flashesContainer).offset ().top - $(window).scrollTop () >
        ($(window).height () - 5) - $(flashesContainer).height ()) {

        $(flashesContainer).addClass ('fixed-flashes-container');
        $(window).unbind ('scroll', that._checkFlashesSticky).
            bind ('scroll', that._checkFlashesSticky);
    } else {
        return false;
    }
};


/**
 * Call function which opens dialog for specified mass action
 * @param string massAction The name of the mass action
 */
GridViewMassActionsManager.prototype._executeMassAction = function (massAction) {
    var that = this; 
    that.DEBUG && console.log ('executeMassAction: massAction = ' + massAction);
    var selectedRecords = that._getSelectedRecords ();

    if(selectedRecords !== null && selectedRecords.length === 0) {
        return;
    }
    if (!this.massActionInProgress) {
        this.massActionInProgress = true;
        that.massActionObjects[massAction].openDialog ();
    }
};

/**
 * Recheck records whose checkboxes were cleared by ajax update
 */
GridViewMassActionsManager.prototype._checkX2GridViewRows = function () {
    var that = this; 
    var idsOfchecked = that._previouslySelectedRecords;

    // create a dictionary for O(1) access
    var dictOfIdsOfChecked = {};
    for (var i in idsOfchecked) dictOfIdsOfChecked[idsOfchecked[i]] = true;
    that.DEBUG && console.log ('checkX2GridViewRows:  dictOfIdsOfChecked = ');
    that.DEBUG && console.log (dictOfIdsOfChecked);

    $(that.gridSelector).find ('[type=\"checkbox\"]').each (function () {
        if (dictOfIdsOfChecked[$(this).val ().toString ()]) {
            $(this).attr ('checked', 'checked');
        }
    });

    that._previouslySelectedRecords = undefined;
};

/**
 * Sets up open/close behavior of more actions list
 */
GridViewMassActionsManager.prototype._setUpMoreButtonBehavior = function () {
    var that = this; 

    var moreDropDownList = $('#' + that.gridId + '-mass-action-buttons .more-drop-down-list');

    // action more button behavior
    function massActionMoreButtonBehavior () {
        if ($(moreDropDownList).is (':visible')) {
            $(moreDropDownList).hide ();
            return false;
        } 

        if (x2[that._stickyHeaderNamespace] && 
            !$(x2[that._stickyHeaderNamespace].titleContainer).is (':visible')) return false;

        $(moreDropDownList).show ();
        that.DEBUG && console.log ('massActionMoreButtonBehavior');
        if (that.headerIsFixed () && !$('body').hasClass ('x2-mobile-layout')) {
            $(moreDropDownList).css ({
                left: $(this).position ().left + 'px',
                top: '24px',
                width: '175px'
            });
        } else if ($('body').hasClass ('x2-mobile-layout')) {
            var moreButtonOffsetLeft = $(this).offset ().left;
            var moreMenuWidth = $(moreDropDownList).width ();

            if (moreButtonOffsetLeft + moreMenuWidth > $(window).width ()) {
                var moreButtonWidth = $(this).width ();
                $(moreDropDownList).css ({
                    left: ($(this).position ().left - moreMenuWidth + moreButtonWidth) + 'px',
                    top: '34px',
                    width: '175px'
                });
            } else {
                $(moreDropDownList).css ({
                    left: ($(this).position ().left) + 'px',
                    top: '34px',
                    width: '175px'
                });
            }
            $(window).one ('resize', function () { 
                $(moreDropDownList).hide (); 
            });
        }else {
            /*var yPos = $(that.gridSelector).find ('.x2grid-resizable').eq(0).parent().
                position().top - 1;*/
            $(moreDropDownList).css ({
                left: $(this).position ().left + 'px',
                top: '24px',
                width: '175px'
            });
        }

        return false;
    }

    $(document).on ('click.' + this.namespacePrefix + 'moreDropDownList', function () { 
        $(moreDropDownList).hide (); 
    });

    $('#' + that.gridId + ' .mass-action-more-button').unbind ('click').
        click (massActionMoreButtonBehavior);
};


/**
 * Set up mass action button behavior and initialize content within dialogs
 */
GridViewMassActionsManager.prototype._setUpMassActions = function () {
    var that = this; 

    // instantiate mass action objects 
    this.massActionObjects = {};
    for (var i = 0; i < this.massActions.length; i++) {
        var massActionName = this.massActions[i];
        switch (massActionName) {
            case 'completeAction':
                this.massActionObjects[massActionName] = 
                    new GridViewMassActionsManager.MassCompleteAction ({
                        massActionsManager: this,
                    });
                break;
            case 'uncompleteAction':
                this.massActionObjects[massActionName] = 
                    new GridViewMassActionsManager.MassUncompleteAction ({
                        massActionsManager: this,
                    });
                break;
            case 'newList':
                this.massActionObjects[massActionName] = 
                    new GridViewMassActionsManager.NewListFromSelected ({
                        massActionsManager: this,
                        dialogElem$: $('#' + that.gridId + '-new-list-dialog'),
                        dialogTitle: that.translations['newList'],
                        goButtonLabel: that.translations['create'],
                    });
                break;
            case 'addToList':
                this.massActionObjects[massActionName] = 
                    new GridViewMassActionsManager.MassAddToList ({
                        massActionsManager: this,
                        dialogElem$: $('#' + that.gridId + '-add-to-list-dialog'),
                        dialogTitle: that.translations['addToList'],
                        goButtonLabel: that.translations['add']
                    });
                break;
            case 'removeFromList':
                this.massActionObjects[massActionName] = 
                    new GridViewMassActionsManager.MassRemoveFromList ({
                        massActionsManager: this,
                        dialogElem$: $('#' + that.gridId + '-remove-from-list-dialog'),
                        dialogTitle: that.translations['removeFromList'],
                        goButtonLabel: that.translations['remove']
                    });
                break;
            
            default:
                throw new Error ('Invalid mass action name: ' + massActionName);
        }
    }

    

    if ($('#' + that.gridId + ' .mass-action-more-button').length) {
        var moreDropDownList = $('#' + that.gridId + '-mass-action-buttons .more-drop-down-list');
        $(moreDropDownList).find ('li').on ('click', function () {
            $(moreDropDownList).hide ();
            var massAction = $(this).attr ('class').replace (/mass-action-button/, '').
                match (/mass-action-([^ ]+)/)[1];
            that._executeMassAction (massAction);
            return false;
        });
    }
};

GridViewMassActionsManager.prototype._showButtons = function () {
    var that = this;
    var massActionButtons = $('#' + that.gridId + '-mass-action-buttons');
    $('#' + that.gridId).addClass ('show-mass-action-buttons');
    $(massActionButtons).show ().css ({ display: 'inline-block' });
};

GridViewMassActionsManager.prototype._hideButtons = function () {
    var that = this;
    var massActionButtons = $('#' + that.gridId + '-mass-action-buttons');
    $('#' + that.gridId).removeClass ('show-mass-action-buttons');
    $(massActionButtons).hide ();
};

/**
 * Check whether mass action buttons should be displayed and display them if so
 * @param bool justChanged this is set to true when this function is called as the callback of 
 *  a change event
 * @param object checkBox A jQuery object which is passed to this function by the change event
 *  handler. This will only be set when justChanged is also set.
 */
GridViewMassActionsManager.prototype._checkUIShow = function (justChanged, checkBox) {
    var that = this; 
    that.DEBUG && console.log ('checkUIShow');
    justChanged = typeof justChanged === 'undefined' ? true : justChanged;
    var massActionButtons = $('#' + that.gridId + '-mass-action-buttons');
    if (justChanged) { 

        if (this.enableSelectAllOnAllPages && 
            $(checkBox).parents ('.x2grid-header-container').length) {

            // hide/show the check all records bar
            if ($(checkBox).is (':checked')) {
                this.superCheckAllManager.showInterface ();
            } else {
                this.superCheckAllManager.hideInterface ();
            }
        }

        // do nothing if additional checkbox is checked/unchecked
        if ($(checkBox).is (':checked') && $(massActionButtons).is (':visible') ||
            !$(checkBox).is (':checked') && !$(massActionButtons).is (':visible')) {
            return;
        }

        // hide ui when uncheck all box is unchecked
        if ($(checkBox).parents ('.x2grid-header-container').length &&
            !$(checkBox).is (':checked') && $(massActionButtons).is (':visible')) {

            that._hideButtons ();
            return;
        }
    }

    var foundChecked = false; 
    $(that.gridSelector).find ('[type=\"checkbox\"]').each (function () {
        if ($(this).is (':checked')) {
            that.DEBUG && console.log ('found checked');
            foundChecked = true;
            return;
        }
    });

    if (foundChecked) {
        that._showButtons ();
        if (that.condenseExpandTitleBar) {
            that.condenseExpandTitleBar ($(that._elementSelector).parent ().next ().
                position ().top);
        }
    } else  {
        that._hideButtons ();
    }
};

/**
 * Allows mass action buttons to be shown only when records are selected
 */
GridViewMassActionsManager.prototype._setUpUIHideShowBehavior = function () {
    var that = this; 
    that.DEBUG && console.log ('setUpUIHideShowBehavior');
    $(that.gridSelector).on ('change', '[type=\"checkbox\"]', 
        function (justChanged) { that._checkUIShow (justChanged, this); });
};

/**
 * @return array ids of currently selected records 
 */
GridViewMassActionsManager.prototype._getSelectedRecords = function () {
    var that = this; 
    if (!this._allRecordsOnAllPagesSelected)
        return $.fn.yiiGridView.getChecked(that.gridId, that.namespacePrefix + 'C_gvCheckbox');
    else 
        return null;
};

/**
 * Removes objects which will get reconstructed after the grid updates and then updates the grid
 */
GridViewMassActionsManager.prototype._updateGrid = function (afterUpdate) {
    var afterUpdate = typeof afterUpdate === 'undefined' ? function () {} : afterUpdate; 
    var that = this; 
    $('#' + that.gridId).yiiGridView ('update', {
        complete: function () {
            afterUpdate ();
            that.superCheckAllManager.hideInterface ();
            that.DEBUG && console.log ('x2.massActions._updateGrid complete');
        }
    });
};

GridViewMassActionsManager.prototype.condenseExpandTitleBar = function (condense) {
    var that = this;
    var condense = typeof condense === 'undefined' ? false : condense; 
    if (typeof x2[that._topPagerNamespace] === 'undefined') return;

    if (condense) {
        that.moveButtonIntoMoreMenu ();
        that.moveButtonIntoMoreMenu ();
    } else {
        that.moveMoreButtonMenuItemIntoButtons ();
        that.moveMoreButtonMenuItemIntoButtons ();
    }
};


/*
Private instance methods
*/

/**
 * Sets up behavior which will hide/show mass action buttons when there isn't space for them
 */
GridViewMassActionsManager.prototype._setUpTitleBarResponsiveness = function () {
    var that = this;

    if (!$('#' + this.gridId).hasClass ('fullscreen')) {
        that.condenseExpandTitleBar (true);
    } else {
        x2.layoutManager.addFnToResizeQueue (function (windowWidth) {
            if (windowWidth < 1115) {
                that.condenseExpandTitleBar (true);
            } else {
                that.condenseExpandTitleBar (false);
            }
        });
        $(window).resize ();
    }
};

/**
 * set up mass action ui behavior, this gets run on every grid update
 */
GridViewMassActionsManager.prototype._init = function () {
    var that = this; 
    that.DEBUG && console.log ('main');

    if (that._previouslySelectedRecords) that._checkX2GridViewRows ();

    that._checkUIShow (false);
    that._setUpMoreButtonBehavior ();
    that._setUpMassActions ();
    that._setUpUIHideShowBehavior ();
    that._setUpTitleBarResponsiveness ();
    this.superCheckAllManager = 
        new GridViewMassActionsManager.SuperCheckAllManager ({
            massActionsManager: this 
        });
};  


GridViewMassActionsManager.MassAction = (function () {

/**
 * Abstract base for mass action classes 
 */
function MassAction (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        dialogElem$: null,
        dialogTitle: '',
        goButtonLabel: '',
        progressBarLabel: '',
        massActionsManager: null,
        updateAfterExecute: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.translations = this.massActionsManager.translations;
    this.progressBarDialogTitle = this.translations['progressBarDialogTitle'];
    this.massActionStarted
}

MassAction.prototype.validateMassActionDialogForm = function () {
    return true;
};

MassAction.prototype.afterExecute = function () {
    var that = this;
    this.dialogElem$.dialog ('close');
    this.massActionsManager._updateGrid ();
};

MassAction.prototype.afterSuperExecute = function () {
    this.massActionsManager.massActionInProgress = false;
};

/**
 * Open dialog for mass action form
 */
MassAction.prototype.openDialog = function () {
    var that = this; 
    var dialog = this.dialogElem$;
    $('#' + that.massActionsManager.gridId + '-mass-action-buttons .mass-action-button').
        attr ('disabled', 'disabled');

    $(dialog).show ();
    if ($(dialog).closest ('.ui-dialog').length) {
        $(dialog).dialog ('open');
        return;
    }

    var superExecute = false;
    $(dialog).dialog ({
        title: this.dialogTitle,
        autoOpen: true,
        width: 500,
        buttons: [
            {
                text: this.goButtonLabel,
                'class': 'x2-dialog-go-button',
                click: function () { 
                    if (that.validateMassActionDialogForm ()) {
                        $(dialog).dialog ('widget').find ('.x2-dialog-go-button').hide ();
                        $(dialog).dialog('widget').find ('.x2-dialog-go-button').before (
                            $('<div>', {
                                'class': 'x2-loading-icon left', 
                                id: 'mass-action-dialog-loading-anim'
                            }));
                        if (that.massActionsManager._allRecordsOnAllPagesSelected) {
                            superExecute = true;
                            that.superExecute ();
                        } else {
                            that.execute ();
                        }
                    }
                }
            },
            {
                text: that.translations['cancel'],
                click: function () { 
                    $(dialog).dialog ('close'); 
                }
            }
        ],
        close: function () {
            $(dialog).hide ();
            $('#mass-action-dialog-loading-anim').remove ();
            $(dialog).dialog ('widget').find ('.x2-dialog-go-button').show ();
            $('#' + that.massActionsManager.gridId + '-mass-action-buttons .mass-action-button').
                removeAttr ('disabled', 'disabled');
            if (!superExecute) that.massActionsManager.massActionInProgress = false;
        }
    });

};

/**
 * Execute mass action on checked records
 */
MassAction.prototype.execute = function () {
    var that = this;
    var selectedRecords = that.massActionsManager._getSelectedRecords () 
    $.ajax({
        url: that.massActionsManager.massActionUrl,
        type:'POST',
        data:this.getExecuteParams (),
        success: function (data) { 
            var response = JSON.parse (data);
            var returnStatus = response[0];
            if (response['success']) {
                that.afterExecute ();
            } 
            that.massActionsManager._displayFlashes (response);
        }
    });
};

MassAction.prototype._nextBatch = function (dialog, dialogState) {
    var that = this;
    dialogState.batchOperInProgress = true;
    $.ajax({
        url: that.massActionsManager.massActionUrl,
        type:'POST',
        data: $.extend (dialogState.superExecuteParams, {
            uid: dialogState.uid
        }),
        dataType: 'json',
        success: function (data) { 
            dialogState.batchOperInProgress = false;
            var response = data;
            that.massActionsManager._displayFlashes (response);
            if (response['complete']) {
                $(dialog).dialog ('close');
            } else if (response['batchComplete']) {
                that.progressBar.incrementCount (response['successes']);
                dialogState.uid = response['uid'];
                if (!dialogState.stop && !dialogState.pause) { 
                    that._nextBatch (dialog, dialogState);
                } else {
                    dialogState.loadingAnim$.hide ();
                    if (dialogState.stop) {
                        that.massActionsManager._updateGrid (function () {
                            that.afterSuperExecute ();
                        });
                        return;
                    }

                    var interval = setInterval (function () { 
                        if (dialogState.stop || !dialogState.pause) {
                            clearInterval (interval);
                        } 
                        if (!dialogState.stop && !dialogState.pause) {
                            dialogState.loadingAnim$.show ();
                            that._nextBatch (dialog, dialogState);
                        }
                    }, 500)
                }
            }
        }
    });
};

/**
 * Execute mass action on all records on all pages
 */
MassAction.prototype.superExecute = function (uid) {
    var that = this;
    var uid = typeof uid === 'undefined' ? null : uid; 
    if (that.dialogElem$ !== null) that.dialogElem$.dialog ('close');
    this.progressBarDialog$ = $(this.massActionsManager.progressBarDialogSelector);
    this.progressBar = this.progressBarDialog$.find ('.x2-progress-bar-container').
        data ('progressBar');
    this.progressBar.updateLabel (this.progressBarLabel);
    var dialogState = {
        pause: false,
        stop: false,
        uid: uid,
        loadingAnim$: null,
        batchOperInProgress: false,
        superExecuteParams: this.getSuperExecuteParams ()
    };
    //console.log ('superExecute');
    this.progressBarDialog$.dialog ({
        title: this.progressBarDialogTitle, 
        autoOpen: true,
        width: 500,
        buttons: [
            { 
                text: this.translations['pause'],
                'class': 'pause-button',
                click: function () {
                    $(this).dialog ('widget').find ('.pause-button').hide ();
                    $(this).dialog ('widget').find ('.resume-button').show ();
                    dialogState.pause = true;
                }
            },
            { 
                text: this.translations['resume'],
                'class': 'resume-button',
                'style': 'display: none;',
                click: function () {
                    $(this).dialog ('widget').find ('.resume-button').hide ();
                    $(this).dialog ('widget').find ('.pause-button').show ();
                    dialogState.pause = false;
                }
            },
            { 
                text: this.translations['stop'],
                'class': 'stop-button',
                click: function () {
                    $(this).dialog ('close');
                }
            },
        ],
        /*
        Opens the dialog and starts making requests to perform mass updates on batches. Updates
        progress bar as records are updated.
        */
        open: function () {
            var dialog = this;
            that._nextBatch (dialog, dialogState);
        },
        close: function () {
            $(this).dialog ('destroy');
            dialogState.stop = true;
            if (dialogState.uid !== null) {
                $.ajax({
                    url: that.massActionsManager.massActionUrl,
                    type:'POST',
                    data: $.extend (dialogState.superExecuteParams, {
                        uid: dialogState.uid,
                        clearSavedIds: true
                    }),
                });
            }
            if (!dialogState.batchOperInProgress) that.massActionsManager._updateGrid (
                function () {
                    that.afterSuperExecute ();
                });
        }
    });
    dialogState.loadingAnim$ = $('<div>', {
        'class': 'x2-loading-icon updating-field-input-anim',
        style: 'float: left; margin-right: 14px',
    });
    this.progressBarDialog$.dialog ('widget').find ('.pause-button').before (
        dialogState.loadingAnim$);

};

MassAction.prototype.getExecuteParams = function () {
    var params = {};
    params['massAction'] = this.massActionName;
    params['gvSelection'] = this.massActionsManager._getSelectedRecords ();
    return params;
};

MassAction.prototype.getSuperExecuteParams = function () {
    var params = this.getExecuteParams ();
    params['superCheckAll'] = true;
    updateParams = $('#' + this.massActionsManager.gridId).gvSettings ('getUpdateParams');

    params = $.extend (params, updateParams);
    return params;
};

MassAction.prototype._init = function () {};

return MassAction;

}) ();

GridViewMassActionsManager.MassCompleteAction = (function () {

function MassCompleteAction (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'completeAction'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    GridViewMassActionsManager.MassAction.call (this, argsDict);
}

MassCompleteAction.prototype = auxlib.create (GridViewMassActionsManager.MassAction.prototype);

MassCompleteAction.prototype.openDialog = function () {
    if (this.massActionsManager._allRecordsOnAllPagesSelected) {
        this.superExecute ();
    } else {
        this.execute ();
    }
};

MassCompleteAction.prototype.afterExecute = function () {
    var that = this;
    this.massActionsManager.massActionInProgress = false;
    this.massActionsManager._updateGrid ();
};

return MassCompleteAction;

}) ();

GridViewMassActionsManager.MassUncompleteAction = (function () {

function MassUncompleteAction (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'uncompleteAction'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    GridViewMassActionsManager.MassAction.call (this, argsDict);
}

MassUncompleteAction.prototype = auxlib.create (GridViewMassActionsManager.MassAction.prototype);

MassUncompleteAction.prototype.afterExecute = function () {
    var that = this;
    this.massActionsManager.massActionInProgress = false;
    this.massActionsManager._updateGrid ();
};

MassUncompleteAction.prototype.openDialog = function () {
    if (this.massActionsManager._allRecordsOnAllPagesSelected) {
        this.superExecute ();
    } else {
        this.execute ();
    }
};

return MassUncompleteAction;

}) ();

GridViewMassActionsManager.NewListFromSelected = (function () {

function NewListFromSelected (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'createList'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    GridViewMassActionsManager.MassAction.call (this, argsDict);
}

NewListFromSelected.prototype = auxlib.create (GridViewMassActionsManager.MassAction.prototype);

NewListFromSelected.prototype.validateMassActionDialogForm = function () {
    var that = this;
    var newListName = $('#' + that.massActionsManager.gridId + '-new-list-dialog').find (
        '.new-list-name');
    auxlib.destroyErrorFeedbackBox ($(newListName));
    var listName = $(newListName).val ();
    if(listName === '' || listName === null) {
        auxlib.createErrorFeedbackBox ({
            prevElem: $(newListName),
            message: that.translations['blankListNameError']
        });
        $('#mass-action-dialog-loading-anim').remove ();
        this.dialogElem$.dialog ('widget').find ('.x2-dialog-go-button').show ();
        return false;
    }
    return true;
};

NewListFromSelected.prototype.afterExecute = function () {
    var that = this;
    var newListName = $('#' + that.massActionsManager.gridId + '-new-list-dialog').find (
        '.new-list-name');
    $(newListName).val ('');
    this.dialogElem$.dialog ('close');
    this.massActionsManager.massActionInProgress = false;
};

NewListFromSelected.prototype.getExecuteParams = function () {
    var that = this;
    var params = GridViewMassActionsManager.MassAction.prototype.getExecuteParams.call (this);
    var newListName = $('#' + that.massActionsManager.gridId + '-new-list-dialog').find (
        '.new-list-name');
    var listName = $(newListName).val ();
    params['listName'] = listName;
    return params;
};

/**
 * This complicated method is used to switch mass actions (from new list to add to list) after
 * the first batch is completed.
 * @return MassAddToList
 */
NewListFromSelected.prototype.convertToAddToList = function (listId, dialogState) {
    var that = this;
    var addToList = this.massActionsManager.massActionObjects['addToList'];
    var newListName = $('#' + that.massActionsManager.gridId + '-new-list-dialog').find (
        '.new-list-name');
    var listName = $(newListName).val ();
    addToList.addListOption (listId, listName);
    addToList.setListId (listId);
    addToList.progressBar = this.progressBar;
    dialogState.superExecuteParams.listId = listId;
    dialogState.superExecuteParams.massAction = addToList.massActionName;

    return addToList;
};

/**
 * Overrides parent method so that after the first batch is completed, requests are made to add to
 * that list. This is accomplished by swapping out the mass action objects after the first 
 * response. This method also handles the case where the list could not be created successfully.
 */
NewListFromSelected.prototype._nextBatch = function (dialog, dialogState) {
    var that = this;
    dialogState.batchOperInProgress = true;
    $.ajax({
        url: that.massActionsManager.massActionUrl,
        type:'POST',
        data: $.extend (dialogState.superExecuteParams, {
            uid: dialogState.uid
        }),
        dataType: 'json',
        success: function (data) { 
            dialogState.batchOperInProgress = false;
            var response = data;
            that.massActionsManager._displayFlashes (response);

            if (response['successes'] === -1) { // list could not be created
                dialog.dialog ('close');
                return;
            }

            if (response['complete']) {
                $(dialog).dialog ('close');
            } else if (response['batchComplete']) {
                that.progressBar.incrementCount (response['successes']);
                dialogState.uid = response['uid'];
                listId = response['listId'];

                if (!dialogState.stop && !dialogState.pause) { 
                    that.convertToAddToList (listId, dialogState)._nextBatch (dialog, dialogState);
                } else {
                    dialogState.loadingAnim$.hide ();
                    if (dialogState.stop) {
                        that.massActionsManager._updateGrid (function () {
                            that.afterSuperExecute ();
                        });
                        return;
                    }

                    var interval = setInterval (function () { 
                        if (dialogState.stop || !dialogState.pause) {
                            clearInterval (interval);
                        } 
                        if (!dialogState.stop && !dialogState.pause) {
                            dialogState.loadingAnim$.show ();
                            that.convertToAddToList (listId, dialogState)._nextBatch (
                                dialog, dialogState);
                        }
                    }, 500)
                }
            }
        }
    });
};


return NewListFromSelected;

}) ();

GridViewMassActionsManager.MassAddToList = (function () {

function MassAddToList (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    GridViewMassActionsManager.MassAction.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'addToList',
        progressBarLabel: this.translations['added']
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

MassAddToList.prototype = auxlib.create (GridViewMassActionsManager.MassAction.prototype);

MassAddToList.prototype.addListOption = function (listId, listName) {
    $('#addToListTarget').append ($('<option>', {
        val: listId,
        text: listName
    }));
};

MassAddToList.prototype.setListId = function (listId) {
    $('#addToListTarget').val(listId);
};

MassAddToList.prototype.getExecuteParams = function () {
    var params = GridViewMassActionsManager.MassAction.prototype.getExecuteParams.call (this);
    params['listId'] = $('#addToListTarget').val();
    return params;
};

MassAddToList.prototype.afterExecute = function () {
    this.dialogElem$.dialog ('close');
    this.massActionsManager.massActionInProgress = false;
};

return MassAddToList;

}) ();

GridViewMassActionsManager.MassRemoveFromList = (function () {

function MassRemoveFromList (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    GridViewMassActionsManager.MassAction.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'removeFromList',
        progressBarLabel: this.translations['removed']
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

MassRemoveFromList.prototype = auxlib.create (GridViewMassActionsManager.MassAction.prototype);

MassRemoveFromList.prototype.getExecuteParams = function () {
    var params = GridViewMassActionsManager.MassAction.prototype.getExecuteParams.call (this);
    params['listId'] = window.location.href.replace (/.*contacts\/list\/id\/([0-9]+)#?$/, '$1');
    return params;
};

return MassRemoveFromList;

}) ();







GridViewMassActionsManager.SuperCheckAllManager = (function () {

/**
 * Manages behavior of super check all feature
 */
function SuperCheckAllManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionsManager: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._superCheckAllStripContainer$ = $(this.massActionsManager.gridSelector).
        find ('.select-all-records-on-all-pages-strip-container').first ();
    this.selectAllLink$ = 
        this._superCheckAllStripContainer$.find (
        '.select-all-records-on-all-pages').first ();
    this.unselectAllLink$ = 
        this._superCheckAllStripContainer$.find (
        '.unselect-all-records-on-all-pages').first ();
    this.selectAllNotice$ = 
        this._superCheckAllStripContainer$.find ('.select-all-notice').first ();
    this.allSelectedNotice$ = 
        this._superCheckAllStripContainer$.find (
        '.all-selected-notice').first ();
    this.checkAllCheckbox$ = 
        $('#' + this.massActionsManager.namespacePrefix + 'C_gvCheckbox input');



    this._init ();
}

SuperCheckAllManager.prototype.addContainerClone = function () {
    if (!$('#x2-gridview-top-bar-outer').length ||
        !$('#x2-gridview-top-bar-outer').hasClass ('.x2-gridview-fiex-top-bar-outer')) {

        return;
    }

    // place clone above grid items so that grid items automatically get pushed down to accomodate
    // notice container
    this.containerClone$ = this._superCheckAllStripContainer$.first ().clone ()
    this.containerClone$.addClass ('container-clone');
    if (!$('#' + this.massActionsManager.gridId).find ('.x2grid-body-container .items').first ().
        siblings ('.container-clone').length) {

        $('#' + this.massActionsManager.gridId).find ('.x2grid-body-container .items').first ().
            before (this.containerClone$);
    }

};

SuperCheckAllManager.prototype.removeContainerClone = function () {
    $('#' + this.massActionsManager.gridId).find ('.container-clone').remove ();
};

/**
 * hide and reset the check all interface
 */
SuperCheckAllManager.prototype.hideInterface = function () {
    var that = this;
    //console.log ('hideInterface');
    this.removeContainerClone ();
    that.selectAllNotice$.show ();
    that.allSelectedNotice$.hide (); 
    that.massActionsManager._allRecordsOnAllPagesSelected = false;
    this._superCheckAllStripContainer$.hide ();
};

/**
 * show the check all interface
 */
SuperCheckAllManager.prototype.showInterface = function () {
    this._superCheckAllStripContainer$.show ();
    this.addContainerClone ();
};

SuperCheckAllManager.prototype._init = function () {
    var that = this;

    // select all
    this.selectAllLink$.unbind ('click._setUpSelectAllRecordsOnAllPagesBehavior').
        bind ('click._setUpSelectAllRecordsOnAllPagesBehavior', function () {

        that.selectAllNotice$.hide ();
        that.allSelectedNotice$.show (); 
        //console.log ('setting to true');
        that.massActionsManager._allRecordsOnAllPagesSelected = true;
        return false;
    });

    // clear selection
    this.unselectAllLink$.unbind ('click._setUpSelectAllRecordsOnAllPagesBehavior').
        bind ('click._setUpSelectAllRecordsOnAllPagesBehavior', function () {

        // uncheck check-all checkbox
        that.checkAllCheckbox$.prop ('checked', false);
        // uncheck all other check boxes
        $('input[name="' + that.massActionsManager.namespacePrefix + 'C_gvCheckbox[]"]').each (
            function () {
                this.checked = false; 
            });
        that.hideInterface ();
        that.massActionsManager._checkUIShow (true);
        return false;
    });
};

return SuperCheckAllManager;

}) ();

return GridViewMassActionsManager;

}) ();

