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
 * Manages behavior of profile widgets as a set. Behavior of individual profile widgets is managed
 * in separate Widget prototypes.
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function ProfileWidgetManager (argsDict) {
    var defaultArgs = {
        cssSelectorPrefix: 'profile', 
        widgetType: 'profile',
        connectedContainerSelector: '',
    };

	SortableWidgetManager.call (this, argsDict);	

    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

ProfileWidgetManager.prototype = auxlib.create (SortableWidgetManager.prototype);

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
 * Override parent method. In addition to parent behavior, check if widget layout should be changed 
 */
ProfileWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    SortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (this, widgetSelector);
    x2.profile.checkRemoveWidgetsColumn ();
};

/**
 * Checks if either of the widget containers are empty and adds/removes css class as appropriate
 * Used to ensure that empty widget boxes can be dragged into
 */
ProfileWidgetManager.prototype.padEmptyWidgetBoxes = function () {
    var that = this; 
    that._padEmptyWidgetBox (this._widgetsBoxSelector);
    that._padEmptyWidgetBox (this._widgetsBoxSelector2);
};

/**
 * Checks if either of the widget containers are empty and removes css classes. Undoes changes made
 * by padEmptyWidgetBoxes ().
 */
ProfileWidgetManager.prototype.unpadEmptyWidgetBoxes = function () {
    var that = this; 
    $(this._widgetsBoxSelector).removeClass ('empty-widget-container');
    $(this._widgetsBoxSelector2).removeClass ('empty-widget-container');
};

/*
Private instance methods
*/

/**
 * Checks if the specified widget container is empty and adds/removes css class as appropriate
 * @param string widgetBoxSelector jQuery selector for widget box
 */
ProfileWidgetManager.prototype._padEmptyWidgetBox = function (widgetBoxSelector) {
    var foundVisible = false;

    $(widgetBoxSelector).children ().each (function () {
        if ($(this).is (':visible')) foundVisible = true;
    });
    if (!foundVisible) {
        $(widgetBoxSelector).addClass ('empty-widget-container');
    } else {
        $(widgetBoxSelector).removeClass ('empty-widget-container');
    }
};

/**
 * Returns an array of widget class names in the order that the corresponding widgets are in in the
 * layout.
 * @return array widgetOrder An array of strings where each string corresponds to a widget class
 */
ProfileWidgetManager.prototype._getWidgetOrder = function () {
    var widgetOrder = [];
    var widgetClass;
    $(this._widgetsBoxSelector).children (this._widgetContainerSelector).each (function () {
        widgetClass = $(this).attr ('id').replace (/-widget-container/, ''); 
        widgetOrder.push (widgetClass);
    });
    $(this._widgetsBoxSelector2).children (this._widgetContainerSelector).each (function () {
        widgetClass = $(this).attr ('id').replace (/-widget-container/, ''); 
        widgetOrder.push (widgetClass);
    });
    return widgetOrder;
};

/**
 * Makes the widgets sortable. Overrides parent method to allow widgets to be dragged between
 * columns.
 */
ProfileWidgetManager.prototype._setUpSortability = function () {
    var that = this;
    that.DEBUG && console.log ('SortableWidgetManager: _setUpSortability');
    this._startedSortUpdate = false;
    $(this._widgetsBoxSelector + ',' + this._widgetsBoxSelector2).sortable ({
        items: that._widgetContainerSelector,
        connectWith: that.connectedContainerSelector,
        tolerance: 'pointer',
        activate: function (event, ui) {
            // event gets triggered twice, only perform udpates once
            if (that._startedSortUpdate) return;
            that._startedSortUpdate = true;

            that.padEmptyWidgetBoxes ();            
            var thisWidget = SortableWidget.getWidgetFromWidgetContainer (ui.item);
            thisWidget.onDragStart ();
            SortableWidget.turnOnSortingMode (thisWidget); // custom iframe fix
        },
        deactivate: function (event, ui) {
            // event gets triggered twice, only perform udpates once
            if (!that._startedSortUpdate) return;

            that.unpadEmptyWidgetBoxes ();
            var thisWidget = SortableWidget.getWidgetFromWidgetContainer (ui.item);
            thisWidget.onDragStop ();
            SortableWidget.turnOffSortingMode (thisWidget);
            
            that._startedSortUpdate = false;
        },
        update: function (event, ui) {

            // save sort order
            $.ajax ({
                url: that.setSortOrderUrl,
                type: "POST",
                data: {
                    widgetOrder: that._getWidgetOrder (),
                    widgetType: that.widgetType
                },
                success: function (data) {
                }
            });

            // update container number
            var currContainer = $(ui.item).parents (that.connectedContainerSelector)[0]
            var containerNumber = 
                (currContainer === $(that._widgetsBoxSelector)[0] ? 1 : 2);
            var widget = SortableWidget.getWidgetFromWidgetContainer (ui.item);
            widget.setProperty ('containerNumber', containerNumber);

            SortableWidget.refreshWidgets ();
        },
        handle: this._widgetHandleSelector
    });
};

ProfileWidgetManager.prototype._setUpAddProfileWidgetMenu = function () {
};

/**
 * Show text in hidden profile widget menu indicating that there aren't any hidden widgets 
 */
ProfileWidgetManager.prototype._hideShowHiddenProfileWidgetsText = function () {
    if (this.hiddenWidgetsMenuIsEmpty ())
        $(this._hiddenWidgetsMenuSelector).find ('.no-hidden-profile-widgets-text').show ();
    else
        $(this._hiddenWidgetsMenuSelector).find ('.no-hidden-profile-widgets-text').hide ();
};

ProfileWidgetManager.prototype._afterCloseWidget = function () {
    this._hideShowHiddenProfileWidgetsText ();
};

/**
 * Check if layout should be rearranged after widget is added to layout 
 */
ProfileWidgetManager.prototype._afterShowWidgetContents = function () {
    this._hideShowHiddenProfileWidgetsText ();
    x2.profile.checkAddWidgetsColumn (); 
};

ProfileWidgetManager.prototype._init = function () {
    this._widgetsBoxSelector2 = '#' + this.cssSelectorPrefix + '-widgets-container-2';
    this._setUpAddProfileWidgetMenu ();
    SortableWidgetManager.prototype._init.call (this);
};
