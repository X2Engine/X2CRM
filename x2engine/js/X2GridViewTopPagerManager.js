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

function X2GridViewTopPagerManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        gridSelector: '',
        gridId: '',
        namespacePrefix: ''
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._massActionsNamespace = this.namespacePrefix + 'MassActionsManager'; 
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

X2GridViewTopPagerManager.prototype.reinit = function () { this._init (); };

/**
 * The public method. Holds the result of _condenseExpandTitleBar.
 */
X2GridViewTopPagerManager.prototype.condenseExpandTitleBar = function () {}; 

/*
Private instance methods
*/

/**
 * The private method
 * Creates a closure to keep track of state information about the title bar.
 */
X2GridViewTopPagerManager.prototype._condenseExpandTitleBar = function () {
    var that = this;
    var hiddenButtons = 0;
    var rightmostPosRightElems;
    var leftMostTopPosLeftElems = $('#' + that.gridId + '-top-pager').position ().top;
    var moveMoreButtonMenuItemIntoButtons;

    /*
    Checks whether the top bar UI should be expanded or condensed and performs the appropriate
    action.
    Parameters:
        newLeftMostTopPosLeftElems - if set, the top offset of the top bar pagination buttons will 
            be checked. This check has the function of determining whether the pagination buttons
            have been moved down due to a lack of space. Having the optional variable eliminates
            the need for calling position ().top every execution (a costly operation).
    */
    return function (newLeftMostTopPosLeftElems) {
        if (!that._massActionsEnabled) return;

        var newLeftMostTopPosLeftElems = 
            typeof newLeftMostTopPosLeftElems === 'undefined' ? undefined : 
                newLeftMostTopPosLeftElems; 
        var moreButton = $('#' + that.gridId + ' .mass-action-more-button');
    
        if (typeof rightmostPosRightElems === 'undefined') { // calculate once and cache
            var rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        }
        var leftMostPosLeftElems = $('#' + that.gridId + '-top-pager').position ().left;
        var titleBarEmptySpace = leftMostPosLeftElems - rightmostPosRightElems;

        /*that.DEBUG && console.log (titleBarEmptySpace);
        that.DEBUG && console.log ('hiddenButtons = ');
        that.DEBUG && console.log (hiddenButtons);*/
    
        if (newLeftMostTopPosLeftElems && hiddenButtons == 0 &&
            newLeftMostTopPosLeftElems > leftMostTopPosLeftElems) {

            if (x2[that._massActionsNamespace].moveButtonIntoMoreMenu ()) hiddenButtons++;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace < 80 && hiddenButtons === 0) {
            if (x2[that._massActionsNamespace].moveButtonIntoMoreMenu ()) hiddenButtons++;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace < 70 && hiddenButtons === 1) {
            if (x2[that._massActionsNamespace].moveButtonIntoMoreMenu ()) hiddenButtons++;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace >= 80 && hiddenButtons == 2) {
            if (x2[that._massActionsNamespace].moveMoreButtonMenuItemIntoButtons ()) hiddenButtons--;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace >= 90 && hiddenButtons > 0) {
            if (x2[that._massActionsNamespace].moveMoreButtonMenuItemIntoButtons ()) hiddenButtons--;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } 
    }
}

/**
 * Sets up behavior which will hide/show mass action buttons when there isn't space for them
 */
X2GridViewTopPagerManager.prototype._setUpTitleBarResponsiveness = function () {
    var that = this;
    if (typeof x2[that._massActionsNamespace] === 'undefined') return; 

    that.condenseExpandTitleBar = that._condenseExpandTitleBar ();

    $(window).unbind ('resize.topPager').bind (
        'resize.topPager', that.condenseExpandTitleBar);

    $(document).on ('showWidgets', function () {
        if ($('body').hasClass ('no-widgets')) return;
        that.DEBUG && console.log ('showWidgets');
        var posTop = $('#' + that.gridId + '-top-pager').position ().top;
        that.condenseExpandTitleBar (posTop);
    });
};

/**
 * Check if grid view is on the first page 
 * @return bool 
 */
X2GridViewTopPagerManager.prototype._checkFirstPage = function () {
    var that = this;
    return $('#' + that.gridId).find ('.pager').find ('.previous').hasClass ('hidden');
};

/**
 * Check if grid view is on the last page 
 * @return bool 
 */
X2GridViewTopPagerManager.prototype._checkLastPage = function () {
    var that = this;
    return $('#' + that.gridId).find ('.pager').find ('.next').hasClass ('hidden');
};

/**
 * Check if pager button should be disabled and if so, disable it
 */
X2GridViewTopPagerManager.prototype._checkDisableButton = function (prev) {
    var that = this;
    if (prev && that._checkFirstPage ()) {
        $('#' + that.gridId + '-top-pager .top-pager-prev-button').addClass ('disabled');
    } else if (!prev && that._checkLastPage ()) {
        $('#' + that.gridId + '-top-pager .top-pager-next-button').addClass ('disabled');
    }
}

/**
 * Set up behavior of pager buttons 
 */
X2GridViewTopPagerManager.prototype._setUpButtonBehavior = function () {
    var that = this;

    that._checkDisableButton (true);
    that._checkDisableButton (false);
    $('#' + that.gridId + '-top-pager .top-pager-prev-button').unbind ('click');
    $('#' + that.gridId + '-top-pager .top-pager-prev-button').bind ('click', function () {
        that.DEBUG && console.log ('prev');
        $('#' + that.gridId).find ('.pager').find ('.previous').find ('a').click ();
        that._checkDisableButton (true);
    });
    $('#' + that.gridId + '-top-pager .top-pager-next-button').unbind ('click');
    $('#' + that.gridId + '-top-pager .top-pager-next-button').bind ('click', function () {
        that.DEBUG && console.log ('next');
        $('#' + that.gridId).find ('.pager').find ('.next').find ('a').click ();
        that._checkDisableButton (false);
    });
};

X2GridViewTopPagerManager.prototype._init = function () {
    var that = this;
    if (!$('#' + that.gridId).find ('.pager').length) {
        $('#' + that.gridId + '-top-pager').hide ()
        return;
    }
    if (typeof x2[that._massActionsNamespace] !== 'undefined')
        that._massActionsEnabled = true;
    else 
        that._massActionsEnabled = false;
    that._setUpTitleBarResponsiveness ();
    that._setUpButtonBehavior ();
};
