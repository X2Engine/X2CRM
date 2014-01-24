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
 * Manages x2 gridview mass action actions and ui element behavior  
 */

function X2GridViewMassActionsManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActions: [], // enabled mass actions
        gridId: '', // id of associated grid view
        namespacePrefix: '', // used to access other x2gridview javascript objects
        gridSelector: '', // can be used to select associated grid view element
        fixedHeader: false, // whether or not grid view has a fixed header
        executeUrls: [], // urls to make ajax requests
         
        modelName: '', // name of model associated with grid
        translations: [], 
        expandWidgetSrc: '', // image src
        collapseWidgetSrc: '', // image src
        closeWidgetSrc: '', // image src
    };

    this._previouslySelectedRecords = null; // records selected before grid update
    this._topPagerNamespace = this.namespacePrefix + 'TopPagerManager'; 
    this._stickyHeaderNamespace = this.namespacePrefix + 'stickyHeader';
    this.tagContainer = null;
    this._successFlashFadeTimeout = null;

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

X2GridViewMassActionsManager.prototype.saveSelectedRecords = function () {
    this._previouslySelectedRecords = this._getSelectedRecords ();
};

/**
 * Public function for condensing interface
 */
X2GridViewMassActionsManager.prototype.moveButtonIntoMoreMenu = function () {
    var that = this; 
    var moreButton = $('#' + that.gridId + '-mass-action-buttons .mass-action-more-button');
    var buttonSet = $('#' + that.gridId + '-mass-action-buttons .mass-action-button-set');
    var buttons = $(buttonSet).children ();
    var visibleCount = 0;

    // get last visible button
    $(buttons).each (function () {
        that.DEBUG && console.log ($(this));
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
    var lastButtonAction = $(lastButton).attr ('class').match (/[^-]+$/)[0];

    $('#' + that.gridId + '-mass-action-buttons .mass-action-' + lastButtonAction).show ();

    return true;
};

/**
 * Public function for expanding interface
 */
X2GridViewMassActionsManager.prototype.moveMoreButtonMenuItemIntoButtons = function () {
    var that = this; 
    var buttonSet = $('#' + that.gridId + '-mass-action-buttons .mass-action-button-set');
    var buttons = $(buttonSet).children ();
    var moreButton = $('#' + that.gridId + '-mass-action-buttons .mass-action-more-button');
    var moreDropDownList = $('#' + that.gridId + '-mass-action-buttons .more-drop-down-list');
    var listItems = $(moreDropDownList).children ();
    var firstItem;

    // get first non hidden element in button list 
    $(listItems).each (function () {
        that.DEBUG && console.log ($(this));
        if ($(this).attr ('style') !== 'display: none;') {
            firstItem = $(this); 
            return false;
        }
    });
    if (typeof firstItem === 'undefined') return false;

    // hiden button list item and show button set button
    $(firstItem).hide ();
    var lastButtonAction = $(firstItem).attr ('class').match (/[^-]+$/)[0];
    $('#' + that.gridId + '-mass-action-buttons .mass-action-button-' + lastButtonAction).show ();

    if ($(buttons).length - 
        $(buttonSet).children ('[style=\"display: none;\"]').length !== 1) {

        $(buttons).first ('.pseudo-only-child').removeClass ('pseudo-only-child');
    } else {
        $('#' + that.gridId + '-mass-action-buttons .mass-action-button-' + lastButtonAction).
            addClass ('pseudo-only-child');
    }

    return true;
};

X2GridViewMassActionsManager.prototype.reinit = function () {
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
X2GridViewMassActionsManager.prototype._displayKeyFlashes = function (key, flashes) {
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
X2GridViewMassActionsManager.prototype._appendFlashSectionContainer = function (key, parent) {
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
X2GridViewMassActionsManager.prototype._displayFlashes = function (flashes) {
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
X2GridViewMassActionsManager.prototype._checkFlashesSticky = function () {
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
X2GridViewMassActionsManager.prototype._checkFlashesUnsticky = function () {
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


/***********************************************************************
* Execute mass actions functions 
***********************************************************************/



/**
 * Execute add to list mass action
 * @param object dialog a jquery dialog object
 */
X2GridViewMassActionsManager.prototype._executeRemoveFromList = function (dialog) {
    var that = this; 
    var listId = window.location.search.replace (/(?:^[?]id=([^&]+))/, '$1');
    var selectedRecords = that._getSelectedRecords () 
    $.ajax({
        url: that.executeUrls['removeFromList'],
        type:'post',
        data:{
            massAction: 'removeFromList',
            listId: listId,
            gvSelection: selectedRecords
        },
        success: function (data) { 
            that.DEBUG && console.log ('_executeRemoveFromList: ajax ret: ' + data);
            var response = JSON.parse (data);
            $(dialog).dialog ('close');
            that._displayFlashes (response);
            if (response['success']) {
                that._updateGrid ();
            }
        }
    });
};

/**
 * Execute add to list mass action
 * @param object dialog a jquery dialog object
 */
X2GridViewMassActionsManager.prototype._executeAddToList = function (dialog) {
    var that = this; 
	var targetList = $('#addToListTarget').val();

    $.ajax({
        url: that.executeUrls['addToList'],
        type:'post',
        data:{
            massAction: 'addToList',
            listId: targetList,
            gvSelection: that._getSelectedRecords () 
        },
        success: function (data) { 
            that.DEBUG && console.log ('executeDeleteSelected: ajax ret: ' + data);
            var response = JSON.parse (data);
            $(dialog).dialog ('close');
            that._displayFlashes (response);
        }
    });
};

/**
 * Execute create new list mass action
 * @param object dialog a jquery dialog object
 */
X2GridViewMassActionsManager.prototype._executeCreateNewList = function (dialog) {
    var that = this; 
    var newListName = $('#' + that.gridId + '-new-list-dialog').find ('.new-list-name');
    auxlib.destroyErrorFeedbackBox ($(newListName));
    var listName = $(newListName).val ();
    if(listName !== '' && listName !== null) {
        $.ajax({
            url: that.executeUrls['createNewList'],
            type:'post',
            data: {
                massAction: 'createList',
                listName: listName,
                gvSelection: that._getSelectedRecords () 
            },
            success: function (data) { 
                that.DEBUG && console.log ('executeDeleteSelected: ajax ret: ' + data);
                var response = JSON.parse (data);
                $(newListName).val ('');
                $(dialog).dialog ('close');
                that._displayFlashes (response);
            }
        });
    } else {
        auxlib.createErrorFeedbackBox ({
            prevElem: $(newListName),
            message: that.translations['blankListNameError']
        });
        $('#mass-action-dialog-loading-anim').remove ();
        $(dialog).dialog ('widget').find ('.x2-dialog-go-button').show ();
    }
};

/**
 * Open dialog for mass action form
 * @param dictionary argsList
 *  object dialogElem A jQuery object corresponding to the html element which will be converted into
 *      a dialog.
 *  string title the dialog title
 *  string goButtonLabel the label for the go button
 *  function goFunction the function which will get executed when the go button is pressed
 */
X2GridViewMassActionsManager.prototype._massActionDialog = function (argsList) {
    var that = this; 
    var dialog = argsList['dialogElem'];
    $('#' + that.gridId + '-mass-action-buttons .mass-action-button').
        attr ('disabled', 'disabled');

    $(dialog).show ();
    if ($(dialog).closest ('.ui-dialog').length) {
        $(dialog).dialog ('open');
        return;
    }

    var title = argsList['title'];
    var goButtonLabel = argsList['goButtonLabel'];
    var goFunction = argsList['goFunction'];

    $(dialog).dialog ({
        title: title,
        autoOpen: true,
        width: 500,
        buttons: [
            {
                text: goButtonLabel,
                'class': 'x2-dialog-go-button',
                click: function () { 
                    $(dialog).dialog ('widget').find ('.x2-dialog-go-button').hide ();
                    $(dialog).dialog('widget').find ('.x2-dialog-go-button').before ($('<div>', {
                        'class': 'x2-loading-icon left', 
                        id: 'mass-action-dialog-loading-anim'
                    }));
                    goFunction (dialog);
                }
            },
            {
                text: that.translations['cancel'],
                click: function () { $(dialog).dialog ('close'); }
            }
        ],
        close: function () {
            $(dialog).hide ();
            $('#mass-action-dialog-loading-anim').remove ();
            $(dialog).dialog ('widget').find ('.x2-dialog-go-button').show ();
            $('#' + that.gridId + '-mass-action-buttons .mass-action-button').
                removeAttr ('disabled', 'disabled');
        }
    });

};

/**
 * Call function which opens dialog for specified mass action
 * @param string massAction The name of the mass action
 */
X2GridViewMassActionsManager.prototype._executeMassAction = function (massAction) {
    var that = this; 
    that.DEBUG && console.log ('executeMassAction: massAction = ' + massAction);
    var selectedRecords = that._getSelectedRecords ();

    if(selectedRecords.length === 0) {
        return;
    }

    switch (massAction) {
        case 'newList':
            that._massActionDialog ({
                dialogElem: $('#' + that.gridId + '-new-list-dialog'),
                title: that.translations['newList'],
                goButtonLabel: that.translations['create'],
                goFunction: function (params) { that._executeCreateNewList (params); }
            });
            break;
        case 'addToList':
            that._massActionDialog ({
                dialogElem: $('#' + that.gridId + '-add-to-list-dialog'),
                title: that.translations['addToList'],
                goButtonLabel: that.translations['add'],
                goFunction: function (params) { that._executeAddToList (params); }
            });
            break;
        case 'removeFromList':
            that._massActionDialog ({
                dialogElem: $('#' + that.gridId + '-remove-from-list-dialog'),
                title: that.translations['removeFromList'],
                goButtonLabel: that.translations['remove'],
                goFunction: function (params) { that._executeRemoveFromList (params); }
            });
            break;

        default:
            auxlib.error ('executeMassAction: default on switch');
            break;
    }
};



/**
 * Recheck records whose checkboxes were cleared by ajax update
 */
X2GridViewMassActionsManager.prototype._checkX2GridViewRows = function () {
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
X2GridViewMassActionsManager.prototype._setUpMoreButtonBehavior = function () {
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
        if (that.fixedHeader) {
            $(moreDropDownList).attr ('style', 'left: ' + $(this).position ().left + 'px;');
        } else {
            var yPos = $(that.gridSelector).find ('.x2grid-resizable').eq(0).parent().
                position().top - 1;
            $(moreDropDownList).attr (
                'style', 'left: ' + $(this).position ().left + 'px;' +
                    'top: ' + yPos + 'px;');
        }
        return false;
    }

    $(document).on ('click.' + this.namespacePrefix + 'moreDropDownList', 
        function () { $(moreDropDownList).hide (); });

    $('#' + that.gridId + ' .mass-action-more-button').unbind ('click').
        click (massActionMoreButtonBehavior);
};


/**
 * Set up mass action button behavior and initialize content within dialogs
 */
X2GridViewMassActionsManager.prototype._setUpMassActions = function () {
    var that = this; 



    if ($('#' + that.gridId + ' .mass-action-more-button').length) {
        var moreDropDownList = $('#' + that.gridId + '-mass-action-buttons .more-drop-down-list');
        $(moreDropDownList).find ('li').on ('click', function () {
            $(moreDropDownList).hide ();
            var massAction = $(this).attr ('class').match (/[^-]+$/)[0];
            that._executeMassAction (massAction);
            return false;
        });
    }
};

/**
 * Check whether mass action buttons should be displayed and display them if so
 * @param bool justChanged this is set to true when this function is called as the callback of 
 *  a change event
 * @param object checkBox A jQuery object which is passed to this function by the change event
 *  handler. This will only be set when justChanged is also set.
 */
X2GridViewMassActionsManager.prototype._checkUIShow = function (justChanged, checkBox) {
    var that = this; 
    that.DEBUG && console.log ('checkUIShow');
    justChanged = typeof justChanged === 'undefined' ? true : justChanged;
    var massActionButtons = $('#' + that.gridId + '-mass-action-buttons');
    if (justChanged) { 

        // do nothing if additional checkbox is checked/unchecked
        if ($(checkBox).is (':checked') && $(massActionButtons).is (':visible') ||
            !$(checkBox).is (':checked') && !$(massActionButtons).is (':visible')) {
            return;
        }

        // hide ui when uncheck all box is unchecked
        if ($(checkBox).parents ('.x2grid-header-container').length &&
            !$(checkBox).is (':checked') &&
            $(massActionButtons).is (':visible')) {

            $(massActionButtons).hide ();
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
        $(massActionButtons).show ();
        if (x2[that._topPagerNamespace] && x2[that._topPagerNamespace].condenseExpandTitleBar) {
            x2[that._topPagerNamespace].
                condenseExpandTitleBar ($('#x2-gridview-top-pager').position ().top);
        }
    } else  {
        $(massActionButtons).hide ();
    }
};

/**
 * Allows mass action buttons to be shown only when records are selected
 */
X2GridViewMassActionsManager.prototype._setUpUIHideShowBehavior = function () {
    var that = this; 
    that.DEBUG && console.log ('setUpUIHideShowBehavior');
    $(that.gridSelector).on ('change', '[type=\"checkbox\"]', 
        function (justChanged) { that._checkUIShow (justChanged, this); });
};

/**
 * @return array ids of currently selected records 
 */
X2GridViewMassActionsManager.prototype._getSelectedRecords = function () {
    var that = this; 
    return $.fn.yiiGridView.getChecked(that.gridId, that.namespacePrefix + 'C_gvCheckbox');
};

/**
 * Removes objects which will get reconstructed after the grid updates and then updates the grid
 */
X2GridViewMassActionsManager.prototype._updateGrid = function () {
    var that = this; 
    $('#' + that.gridId).yiiGridView ('update', {
        complete: function () {
            that.DEBUG && console.log ('x2.massActions._updateGrid complete');
        }
    });
};

/**
 * set up mass action ui behavior, this gets run on every grid update
 */
X2GridViewMassActionsManager.prototype._init = function () {
    var that = this; 
    that.DEBUG && console.log ('main');

    if (that._previouslySelectedRecords) that._checkX2GridViewRows ();

    that._checkUIShow (false);
    that._setUpMoreButtonBehavior ();
    that._setUpMassActions ();
    that._setUpUIHideShowBehavior ();
};
