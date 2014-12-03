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
        enableSelectAllOnAllPages: true,
        // used to validate super mass action query results
        totalItemCount: null,
        // used to validate super mass action query results
        idChecksum: null
    };

    this._previouslySelectedRecords = null; // records selected before grid update
    this._stickyHeaderNamespace = this.namespacePrefix + 'stickyHeader';
    this._successFlashFadeTimeout = null;

    auxlib.applyArgs (this, defaultArgs, argsDict);

    if (this.totalItemCount === null || this.idChecksum === null) {
        throw new Error ('totalItemCount or idChecksum not set');
    }

    this._topPagerNamespace = this.namespacePrefix + 'TopPagerManager'; 
    this._elementSelector = '#' + this.gridId + '-mass-action-buttons';

    /**
     * @var bool If true, user has selected all records on all pages
     */
    this._allRecordsOnAllPagesSelected = false;

    this.massActionInProgress = false;
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

GridViewMassActionsManager.prototype.loading = function () {
    $('#' + this.gridId).addClass ('grid-view-loading');
};

GridViewMassActionsManager.prototype.gridElem = function () {
    return $('#' + this.gridId);
};

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
                'class': 'flashes-container-close-button',
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

GridViewMassActionsManager.prototype._displayFlashesList = function (flashes, listContainer) {
    var that = this; 
    that.DEBUG && console.log ('x2.massActions._displayFlashes: flashes = ');
    that.DEBUG && console.log (flashes);
    if (!flashes['success'] && !flashes['notice'] && !flashes['error']) return;

    for (var i in flashes['success']) {
        $(listContainer).append ($('<div>', {
            'class': 'success-flash', 
            text: flashes['success'][i]
        }));
    }
    for (var i in flashes['notice']) {
        $(listContainer).append ($('<div>', {
            'class': 'notice-flash', 
            text: flashes['notice'][i]
        }));
    }
    for (var i in flashes['error']) {
        $(listContainer).append ($('<div>', {
            'class': 'error-flash', 
            text: flashes['error'][i]
        }));
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
        if (typeof x2[massActionName] !== 'undefined') {
            this.massActionObjects[massActionName] = new x2[massActionName] ({
                massActionsManager: this,
                massActionName: massActionName
            })
        } else {
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
    $(massActionButtons).css ({ display: 'inline-block', visibility: 'visible' });

    for (var massActionName in this.massActionObjects) {
        this.massActionObjects[massActionName].showUI ();
    }
};

GridViewMassActionsManager.prototype._hideButtons = function () {
    var that = this;
    var massActionButtons = $('#' + that.gridId + '-mass-action-buttons');
    $('#' + that.gridId).removeClass ('show-mass-action-buttons');
    $(massActionButtons).css ({ visibility: 'hidden' });
};

/**
 * @return bool true if all checkboxes in checkbox column are checked, false otherwise 
 */
GridViewMassActionsManager.prototype._allChecked = function () {
    var checkAllCheckbox$ = $('#' + this.namespacePrefix + 'C_gvCheckbox_all');
    var checkboxColCheckboxes$ = $('#' + this.gridId).find ('.checkbox-column :checkbox');
    var allChecked = checkAllCheckbox$.is (':checked');
    checkboxColCheckboxes$.each (function () {
        allChecked &= $(this).is (':checked');
    });
    return allChecked;
};

GridViewMassActionsManager.prototype._checkAll = function () {
    $('#' + this.gridId).find ('.checkbox-column :checkbox:enabled').each (function () {
        this.checked = true; 
    });
};

//GridViewMassActionsManager.prototype.showUI = function () {
//    var that = this;
//    that._showButtons ();
//    if (that.condenseExpandTitleBar) {
//        that.condenseExpandTitleBar ($(that._elementSelector).parent ().next ().
//            position ().top);
//    }
//};
//
//GridViewMassActionsManager.prototype.hideUI = function () {
//    that._hideButtons ();
//};
//
//GridViewMassActionsManager.prototype.checkUIShow = function () {
//    this._checkUIShow (false, null);
//};

/**
 * Check whether mass action buttons should be displayed and display them if so
 * @param bool justChanged this is set to true when this function is called as the callback of 
 *  a change event
 * @param object checkBox A jQuery object which is passed to this function by the change event
 *  handler. This will only be set when justChanged is also set.
 */
GridViewMassActionsManager.prototype._checkUIShow = function (justChanged, checkBox) {
    var that = this; 

    justChanged = typeof justChanged === 'undefined' ? true : justChanged;
    var massActionButtons = $('#' + that.gridId + '-mass-action-buttons');
    if (justChanged) { 

        // at this point, the state of the checkboxes does not reflect the records that the user 
        // has selected (that gets handled by CCheckBox). So, to determine whether all records 
        // are selected, this checks whether or not all checkboxes *should* be checked.
        if (($(checkBox).attr ('id') === this.namespacePrefix + 'C_gvCheckbox_all' &&
             $(checkBox).is (':checked')) || // either the check-all checkbox is being checked
            // or all other checkboxes are now checked
            ($(checkBox).attr ('id') !== this.namespacePrefix + 'C_gvCheckbox_all' &&
             $('#' + that.gridId).find ('.checkbox-column-checkbox:checkbox').length ===
             $('#' + that.gridId).find ('.checkbox-column-checkbox:checked').length)) {

            allSelected = true;
        } else {
            allSelected = false;
        }

        if (this.enableSelectAllOnAllPages) {

            // hide/show the check all records bar
            if (allSelected) {
                this.superCheckAllManager.showInterface ();
            } else {
                this.superCheckAllManager.hideInterface ();
            }
        }

        // do nothing if additional checkbox is checked/unchecked
        if ($(checkBox).is (':checked') && $(massActionButtons).css ('visibility') === 'visible' ||
            !$(checkBox).is (':checked') && $(massActionButtons).css ('visibility') === 'hidden') {
            return;
        }

        // hide ui when uncheck all box is unchecked
        if ($(checkBox).attr ('id') == this.namespacePrefix + 'C_gvCheckbox_all' &&
            !$(checkBox).is (':checked') && $(massActionButtons).css ('visibility') === 'visible') {

            that._hideButtons ();
            return;
        }
    }

    var foundChecked = false; 
    $(that.gridSelector).find ('[type=\"checkbox\"]').each (function () {
        if ($(this).is (':checked')) {
            foundChecked = true;
            return;
        }
    });

    if (foundChecked) {
        that._showButtons ();
        if (that.condenseExpandTitleBar) {
            //that.condenseExpandTitleBar ($(that._elementSelector).parent ().next ().
                //position ().top);
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
    $(that.gridSelector + ' .x2grid-body-container, ' + 
        that.gridSelector + ' .x2grid-header-container').off ('change').
        on ('change', '[type=\"checkbox\"]', 

        function (evt) { 
            that._checkUIShow (true, this); 
            evt.stopPropagation ();
        });
    $('#' + that.namespacePrefix + 'C_gvCheckbox_all').off ('change').on ('change', 
        function (evt) { 
            that._checkUIShow (true, this); 
            evt.stopPropagation ();
        });
};

GridViewMassActionsManager.prototype._element = function () {
    return $('#' + this.gridId);
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
        $(function () {
            x2.layoutManager.addFnToResizeQueue (function (windowWidth) {
                if (windowWidth < 1115) {
                    that.condenseExpandTitleBar (true);
                } else {
                    that.condenseExpandTitleBar (false);
                }
            });
            $(window).resize ();
        });
    }
};

GridViewMassActionsManager.prototype._element = function () {
    return $(this._elementSelector);
};

/**
 * set up mass action ui behavior, this gets run on every grid update
 */
GridViewMassActionsManager.prototype._init = function () {
    var that = this; 
    that.DEBUG && console.log ('main');
    this.massActionInProgress = false;

    if (that._previouslySelectedRecords) that._checkX2GridViewRows ();

    that._setUpMoreButtonBehavior ();
    that._setUpMassActions ();
    that._setUpUIHideShowBehavior ();
    that._setUpTitleBarResponsiveness ();
    that._checkUIShow (false);
    this.superCheckAllManager = 
        new GridViewMassActionsManager.SuperCheckAllManager ({
            massActionsManager: this 
        });
};  

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
        !$('#x2-gridview-top-bar-outer').hasClass ('x2-gridview-fixed-top-bar-outer')) {

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

