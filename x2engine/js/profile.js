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

if (typeof x2 === 'undefined') x2 = {};
if (typeof x2.profile === 'undefined') x2.profile = {};

x2.profile._widgetLayoutMode = null; // current layout mode
x2.profile._widgetLayoutSwitchThreshold = 1250; 
x2.profile._widgetLayoutModes = {'narrow': 0, 'wide': 1}; // types of layout modes

/**
 * When a widget is shown, this can be called to check if the widget column should be shown
 */
x2.profile.checkAddWidgetsColumn = function () {
    if (x2.layoutManager.contentWidth >= x2.profile._widgetLayoutSwitchThreshold) {
        x2.profile._addWidgetsColumn ();
    }
};

/**
 * When a widget is closed, this can be called to check if the widget column should be removed
 */
x2.profile.checkRemoveWidgetsColumn = function () {
    if (x2.layoutManager.contentWidth >= x2.profile._widgetLayoutSwitchThreshold &&
        SortableWidget.allWidgetsHidden ()) {

        x2.profile._removeWidgetsColumn ();
    }
};

/**
 * Switch to wide layout 
 */
x2.profile._addWidgetsColumn = function () {

    // only add widget column if there are widgets shown
    if (!SortableWidget.allWidgetsHidden ()) {
        x2.profile._widgetLayoutMode = x2.profile._widgetLayoutModes['wide'];
        $('#content').addClass('wide-profile-widget-layout');
    }
};

/**
 * Switch to narrow layout 
 */
x2.profile._removeWidgetsColumn = function () {
    $('#content').removeClass('wide-profile-widget-layout');
    x2.profile._widgetLayoutMode = x2.profile._widgetLayoutModes['narrow'];
};

x2.profile._setUpProfileWidgetResponsiveness = function () {
    x2.layoutManager.addFnToResizeQueue (function (windowWidth, contentWidth) {

        // determine which layout to use
        if(contentWidth < x2.profile._widgetLayoutSwitchThreshold)
            var newWidgetLayoutMode = x2.profile._widgetLayoutModes['narrow'];
        else
            var newWidgetLayoutMode = x2.profile._widgetLayoutModes['wide'];
            
        // switch layout if necessary
        if(x2.profile._widgetLayoutMode !== newWidgetLayoutMode) {
            if(newWidgetLayoutMode === x2.profile._widgetLayoutModes['wide']) {
                x2.profile._addWidgetsColumn ();
            } else {
                x2.profile._removeWidgetsColumn ();
            }
        }
    });
    $(window).resize ();
};

x2.profile._main = function () {
    if (x2.profile.isMyProfile) {
        x2.profile._setUpProfileWidgetResponsiveness ();
    }
};

$(function () {
    x2.profile._main ();
});


