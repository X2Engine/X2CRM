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
 * Manages behavior of sortable widgets as a set. Behavior of individual widgets is managed
 * in separate Widget prototypes.
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function SortableWidgetManager (argsDict) {
    var defaultArgs = {
        setSortOrderUrl: '', // the url used to call the set widget property action
        showWidgetContentsUrl: '', // the url used to call the get widget contents action
        cssSelectorPrefix: '', // used to prefix id and class attributes of html elements
        widgetType: '', // (profileWidgetLayout)
        DEBUG: true,
        settingsModelName: null,
        settingsModelId: null,
        translations: []
    };

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

SortableWidgetManager.prototype.afterDelete = function () {};

SortableWidgetManager.prototype.rebindEventFns = function () {
    this._setUpSortability ();
};

SortableWidgetManager.prototype.getWidgetClass = function (widgetSelector) {
    return $(widgetSelector).attr ('id').replace (/-widget-container-(\w+)?$/, '');
};

SortableWidgetManager.prototype.getWidgetKey = function (widgetSelector) {
    var uid = this.getWidgetUID (widgetSelector);
    if (uid !== '') {
        return this.getWidgetClass (widgetSelector) + '_' + uid;
    } else {
        return this.getWidgetClass (widgetSelector);
    }
};

SortableWidgetManager.prototype.getWidgetUID = function (widgetSelector) {
    return $(widgetSelector).attr ('id').replace (/^[^-]+-widget-container-/, '');
};

SortableWidgetManager.prototype.getWidgetContainerSelector = function () {
    return this._widgetContainerSelector;
};

/**
 * Add an entry corresponding to the specified widget to the hidden widgets menu
 * @param object widgetSelector a jQuery selector for the widget container
 */
SortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    var widgetClass = this.getWidgetClass (widgetSelector);
    var widgetLabel = $(widgetSelector).find (this._widgetTitleSelector).text ();

    $(this._hiddenWidgetsMenuSelector).append (
        $('<li>').append (
            $('<span>', {
                'id': this.getWidgetKey (widgetSelector),
                'class': $.trim (this._hiddenWidgetsMenuItemSelector.replace (/\./g, ' ')),
                text: widgetLabel
            })
        )
    );
    hideShowHiddenWidgetSubmenuDividers ();
    this._setUpHiddenWidgetsMenuBehavior ();
    this._afterCloseWidget ();
};

SortableWidgetManager.prototype.hiddenWidgetsMenuIsEmpty = function () {
    return ($(this._hiddenWidgetsMenuSelector).find (
        this._hiddenWidgetsMenuItemSelector).length === 0);
};

/*
Private instance methods
*/

/**
 * Returns an array of widget class names in the order that the corresponding widgets are in in the
 * layout.
 * @return array widgetOrder An array of strings where each string corresponds to a widget class
 */
SortableWidgetManager.prototype._getWidgetOrder = function () {
    var that = this;
    var widgetOrder = [];
    var widgetClass;
    $(this._widgetsBoxSelector).children (this._widgetContainerSelector).each (function () {
        widgetClass = that.getWidgetClass (this);
        widgetOrder.push (widgetClass);
    });
    return widgetOrder;
};

/**
 * Makes the widgets sortable
 */
SortableWidgetManager.prototype._setUpSortability = function () {
    var that = this;
    $(this._widgetsBoxSelector).sortable ({
        items: that._widgetContainerSelector,
        update: function (event, ui) {
            $.ajax ({
                url: that.setSortOrderUrl,
                type: "POST",
                data: {
                    widgetOrder: that._getWidgetOrder (),
                    widgetType: that.widgetType,
                    settingsModelName: that.settingsModelName,
                    settingsModelId: that.settingsModelId,
                },
                success: function (data) {
                }
            });
        },
        handle: this._widgetHandleSelector
    });
};

/**
 * Override in child prototype. Gets called after a widgets gets added to the layout
 */
SortableWidgetManager.prototype._afterShowWidgetContents = function () {};

SortableWidgetManager.prototype._afterCloseWidget = function () {};

/**
 * @param string widgetClass
 * @return object GET parameters to pass with request to the show widget contents URL
 */
SortableWidgetManager.prototype._getShowWidgetContentsData = function (widgetClass) {
    var that = this;
    return {
        widgetClass: widgetClass, 
        widgetType: that.widgetType,
        settingsModelName: that.settingsModelName,
        settingsModelId: that.settingsModelId
    };
};

SortableWidgetManager.prototype.refreshWidget = function (widgetKey, callback) {
    this._showWidgetContents (widgetKey, callback);
};

/**
 * Request widget HTML and display it 
 * @param string widgetClass The name of the widget class
 */
SortableWidgetManager.prototype._showWidgetContents = function (widgetKey, callback) {
    callback = typeof callback === 'undefined' ? function () {} : callback; 
    var that = this;
    var url = this.showWidgetContentsUrl;
    if (this.showWidgetContentsUrl.match (/\?\w+$/)) {
       url += '&'; 
    } else {
       // url += '?'; 
    }
    $.ajax ({
        url: url,
        type: "GET",
        data: that._getShowWidgetContentsData (widgetKey),
        dataType: 'json',
        success: function (data) {
            if (data !== 'failure') {
                var widget$ = 
                    $('#' + widgetKey.replace (/_.*$/, '') + '-widget-container-' + data.uid);

                widget$.replaceWith (data.widget);
                hideShowHiddenWidgetSubmenuDividers ();
                widget$ = 
                    $('#' + widgetKey.replace (/_.*$/, '') + '-widget-container-' + data.uid);
                callback ();
                that._afterShowWidgetContents (widget$);
            }
        }
    });
};

/**
 * Sets up behavior of the hidden widgets menu 
 */
SortableWidgetManager.prototype._setUpHiddenWidgetsMenuBehavior = function () {
    var that = this;

    // show widgets when hidden widget menu item gets clicked
    $(this._hiddenWidgetsMenuSelector).find ('li').unbind (
        'click.showSortableWidget');
    $(this._hiddenWidgetsMenuSelector).find ('li').bind (
        'click.showSortableWidget', function () {

        var widgetKey = $(this).find (that._hiddenWidgetsMenuItemSelector).
            attr ('id');
        $(this).remove ();
        that._showWidgetContents (widgetKey);
    });
};

/**
 * Sets up the widget manager 
 */
SortableWidgetManager.prototype._init = function () {
    var that = this;

    // the jQuery selector for the element that contains all the widgets
    if (typeof this._widgetsBoxSelector === 'undefined')
        this._widgetsBoxSelector = '#' + this.cssSelectorPrefix + 'widgets-container-inner';

    // the jQuery selector for elements that contain widgets
    this._widgetContainerSelector = '.sortable-widget-container';

    // the jQuery selector for the element that contains the widget title bar
    this._widgetHandleSelector = '.widget-title-bar, .sortable-widget-handle';

    // the jQuery selector for the element that contains the widget label 
    this._widgetTitleSelector = '.widget-title';

    // the jQuery selector for the element that contains the widget label 
    if (typeof this._hiddenWidgetsMenuSelector === 'undefined')
        this._hiddenWidgetsMenuSelector = '#x2-hidden-' + this.cssSelectorPrefix + 'widgets-menu';

    // the jQuery selector for the hidden widget menu item associated with this type of widget
    if (typeof this._hiddenWidgetsMenuItemSelector === 'undefined')
        this._hiddenWidgetsMenuItemSelector = 
            '.x2-hidden-widgets-menu-item.' + this.cssSelectorPrefix + 'widget';

    this._setUpSortability ();
    this._setUpHiddenWidgetsMenuBehavior ();
};
