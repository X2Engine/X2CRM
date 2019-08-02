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




if (typeof x2 === 'undefined') x2 = {};

x2.profile = (function () {

function Profile (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict; 
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._widgetLayoutMode = null; // current layout mode
    this._widgetLayoutSwitchThreshold = 950; 
    this._widgetLayoutModes = {'narrow': 0, 'wide': 1}; // types of layout modes

    var that = this;
    $(function () { that._main (); });
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
 * When a widget is shown, this can be called to check if the widget column should be shown
 */
Profile.prototype.checkAddWidgetsColumn = function () {
    if (x2.layoutManager.contentWidth >= this._widgetLayoutSwitchThreshold) {
        this._addWidgetsColumn ();
    }
};

/**
 * When a widget is closed, this can be called to check if the widget column should be removed
 */
Profile.prototype.checkRemoveWidgetsColumn = function () {
    if (x2.layoutManager.contentWidth >= this._widgetLayoutSwitchThreshold &&
        SortableWidget.allWidgetsHidden ()) {

        this._removeWidgetsColumn ();
    }
};


/*
Private instance methods
*/

/**
 * Switch to wide layout 
 */
Profile.prototype._addWidgetsColumn = function () {

    // only add widget column if there are widgets shown
    if (!SortableWidget.allWidgetsHidden ()) {
        this._widgetLayoutMode = this._widgetLayoutModes['wide'];
        $('#content').addClass('wide-profile-widget-layout');
    }
};

/**
 * Switch to narrow layout 
 */
Profile.prototype._removeWidgetsColumn = function () {
    $('#content').removeClass('wide-profile-widget-layout');
    this._widgetLayoutMode = this._widgetLayoutModes['narrow'];
};

Profile.prototype._setUpProfileWidgetResponsiveness = function () {
    x2.layoutManager.addFnToResizeQueue (function (windowWidth, contentWidth) {

        // determine which layout to use
        if(contentWidth < this._widgetLayoutSwitchThreshold)
            var newWidgetLayoutMode = this._widgetLayoutModes['narrow'];
        else
            var newWidgetLayoutMode = this._widgetLayoutModes['wide'];
            
        // switch layout if necessary
        if(this._widgetLayoutMode !== newWidgetLayoutMode) {
            if(newWidgetLayoutMode === this._widgetLayoutModes['wide']) {
                this._addWidgetsColumn ();
            } else {
                this._removeWidgetsColumn ();
            }
        }
    });
    $(window).resize ();
};

Profile.prototype._main = function () {
    if (this.isMyProfile) {
        this._setUpProfileWidgetResponsiveness ();
    }
};



return new Profile;

}) ();
